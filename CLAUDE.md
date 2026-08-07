# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

WAA Commission is an Inertia (Vue 3 + TypeScript) / Laravel 12 app that manages sales commissions for Water Analytics Australia, sourcing sales/product/contact data from an Odoo ERP instance and a legacy system, then running those orders through a custom commission-calculation engine with multi-role approval.

## Common Commands

- `composer dev` — run the full local stack concurrently (Laravel server, queue listener, Vite dev server).
- `composer dev:ssr` — same, but builds and serves Inertia SSR.
- `composer test` / `php artisan test` — run the Pest/PHPUnit suite (clears config cache first).
- `php artisan test --compact --filter=TestName` — run a single test.
- `npm run dev` / `npm run build` — Vite frontend only.
- `npm run lint` — ESLint with `--fix`.
- `npm run format` / `npm run format:check` — Prettier over `resources/`.
- `vendor/bin/pint --dirty --format agent` — format PHP; run after any PHP edit (see Boost rules below).
- `php artisan odoo:sync-all [--all]` ([SyncAll](app/Console/Commands/SyncAll.php)) — runs every Odoo sync step sequentially in one process. **This is the manual/backfill entry point only** — the scheduler does *not* call it; it dispatches the equivalent steps as a queued job chain instead (see "Queue & sync pipeline" below). Individual steps are also available as `odoo:sync-contacts`, `odoo:sync-products`, `odoo:sync-stocks`, `odoo:sync-sales`, `odoo:sync-installation-dates`.
- `php artisan commissions:calculate-missing` ([CalculateMissingCommissions](app/Console/Commands/CalculateMissingCommissions.php)) — calculates commissions for sales orders that have none yet; with `--update-unconfirmed` also recalculates existing ones that are still `pending` with no manual adjustment. Runs automatically as the last step of the queued pipeline, so it's usually only invoked by hand for large backfills (`--all`).
- `php artisan horizon` — process queued jobs. Requires `pcntl`/`posix`, so it only runs on Linux (the Docker containers) — **not** natively on Windows; local dev uses `queue:listen` via `composer dev` instead.

## Architecture

### Queue & sync pipeline

Queues run on Redis via Laravel Horizon (`QUEUE_CONNECTION=redis`). [routes/console.php](routes/console.php) schedules a closure every minute that dispatches a `Bus::chain()` of six jobs from `app/Jobs/` onto a dedicated `odoo-sync` queue (isolated supervisor in [config/horizon.php](config/horizon.php) so a stuck Odoo API call can't starve other queued work):

`SyncOdooContactsJob → SyncOdooProductsJob → SyncOdooStocksJob → SyncOdooSalesOrdersJob → SyncOdooInstallationDatesJob → CalculateMissingCommissionsJob`

Each job extends [OdooSyncStepJob](app/Jobs/OdooSyncStepJob.php) and just shells out to its artisan command via `Artisan::call()` — the sync logic and each command's own `SyncLog` entry are unchanged, so the commands remain independently runnable by hand. The chain order is a real dependency order (later steps assume earlier ones ran), which is why it's a chain and not a batch.

Two non-obvious things to preserve when touching this:

- **Overlap is guarded by a manual `Cache::add()`/`Cache::forget()` mutex** (`OdooSyncStepJob::PIPELINE_LOCK_KEY`, 900s TTL), acquired in the scheduler closure and released by the final job (success) or the chain's `->catch()` (failure). Do *not* replace this with `ShouldBeUnique` on the first job — `Bus::chain()` never checks it (Laravel only honors it in `PendingDispatch`), so it silently does nothing and every tick dispatches a duplicate chain. `withoutOverlapping()` on the schedule only guards the near-instant dispatch closure, not the queued run it kicks off.
- **`config/horizon.php`'s `environments` uses a `'*'` wildcard key**, not `'production'`. Horizon matches `APP_ENV` against those keys and silently deploys *zero* supervisors if nothing matches — the master process starts, logs "started successfully", and reports Active with no workers and no error. `'local'` must stay listed *before* `'*'`, since the first matching key wins.

### Data sources: Odoo + legacy sync

Sales orders, contacts, products, and stock all originate in Odoo (via `obuchmann/odoo-jsonrpc`, configured in [config/odoo.php](config/odoo.php) / `ODOO_*` env vars) and are synced into local tables by the `app/Console/Commands/Sync*` commands, in the dependency order described under "Queue & sync pipeline" above (contacts → products → stocks → sales orders → installation dates — later steps assume earlier ones already ran). `LegacySyncService` / `LegacyEndpointService` pull from an older system for historical/migration data. `SyncLogger` records outcomes to the `SyncLog` model, surfaced at `admin/logs` (Admin-only, see [SyncLogController](app/Http/Controllers/Admin/SyncLogController.php)).

Because commission calculation and reporting all key off of `SalesOrder`/`Contact`/`Product` records populated by this pipeline, most "data looks wrong" bugs trace back to a sync step, not the calculator itself — check `SyncLog` and the relevant `Sync*` command before assuming the commission math is broken.

### Commission calculation engine

[CommissionCalculator](app/Services/CommissionCalculator.php) is the core business-logic service — it is not a generic calculator, it encodes specific WAA pricing/commission rules:

- **Salesperson resolution** is a priority-ordered fallback chain (explicit `Contact.user_id` owner → salesperson-partner contact owner → Odoo user/salesperson ID match → name match) — an order with no resolvable user is silently skipped (logged, not calculated) and surfaces in the UI as "Pending Mapping". When commissions "aren't appearing" for an order, this resolution chain is usually where to look first. The lookup maps are memoized per `CommissionCalculator` instance (`loadSalespersonLookupMaps()`), so a whole backfill run costs 2 queries rather than ~5 per order — keep that in mind before injecting a fresh instance per order.
- **Selling price**: cash payment types use the order total as-is; all other payment types get a 10% discount factor (`NON_CASH_DISCOUNT_FACTOR`).
- **Additional cost / landing price**: order lines are split into ones with a matching `LandingPrice` record (installation service or supply-only cost, chosen based on whether the order has any supply-only line) vs. everything else (summed with a 10% markup as "additional cost"). A line's landing price is time-scoped by `effective_from` against the order's `create_date`.
- **Profit** = selling price − additional cost − landing price; commission = a flat base (self-generated vs. company-lead rate, or a hardcoded $200 for the special product `usro-6s1-2w`) + a split of profit (or the full negative profit as a penalty) + any manual adjustment.
- `applyManualAdjustment()` and `recalculate()` preserve status and manual adjustments across recalculation — always go through these rather than mutating `CommissionCalculation` directly, or you'll lose the audit trail (`CommissionAdjustment` records).

**When recalculation is triggered** — this has been the source of several "commission is stale/missing" bugs, so be careful changing it. There are two automatic paths, and both gate on `status = 'pending' AND manual_adjustment = 0`:

1. `SyncOdooSalesOrders::recalculateCommissionsForSyncedOrders()` recalculates exactly the orders touched by *that* sync run (accumulated by `odoo_id` across all paginated pages). This catches new orders and changed Odoo data (e.g. `x_studio_sales_source` flipping self-gen → company-lead), but by definition cannot catch an order whose Odoo data didn't change.
2. `CalculateMissingCommissionsJob` (last in the chain) sweeps a bounded batch (`--limit=200`) of *any* order still missing a commission or still pending. This is what catches orders that failed salesperson resolution at sync time but would resolve now because a local `Contact`/`User` mapping was fixed afterward — path 1 can never pick those up.

Anything `approved`/`rejected`/`paid`, or with a non-zero `manual_adjustment`, is deliberately never recalculated automatically: a human has already signed off on or corrected those numbers, and `calculateCommission()` re-derives every `sales_source`-dependent field on each call, so the total would shift underneath them. Note `confirmed_by_manager` is a *separate* sales-manager signoff flag and is **not** a valid substitute for `status` in these gates — it can be true while status is still `pending`, and false on an already-approved commission.

### Approval workflow & roles

Commission and user/team management use `spatie/laravel-permission` roles (`Admin`, `Sales Manager`, `Sales Team Manager`, `Account Officer`, etc.) gating route groups in [routes/web.php](routes/web.php) — most admin-only resources (`products`, `commissions`, `users`, `roles`, `teams`) sit behind `role:Admin`, while commission status transitions (`adjust`/`confirm`/`approve`/`reject`/`mark-paid`/`mark-odoo`) are split across different role middleware per step. `User` has manager/team-hierarchy helpers (`getTeamUserIds()`, `getAncestorUserIds()`) used to scope "my team" views — check these before writing new team-scoped queries rather than re-deriving hierarchy manually.

### Frontend

Inertia pages live under `resources/js/Pages/**` (one directory per resource, matching controller names), shared layouts in `resources/js/layouts/`, reusable UI in `resources/js/components/ui/` (do not lint-fix this directory — it's excluded in [eslint.config.js](eslint.config.js)). Routes/controller actions are consumed from generated Wayfinder modules (`@/actions`, `@/routes`) rather than hand-written URL strings.

---

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- inertiajs/inertia-laravel (INERTIA_LARAVEL) - v2
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v12
- laravel/nightwatch (NIGHTWATCH) - v1
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- laravel/wayfinder (WAYFINDER) - v0
- tightenco/ziggy (ZIGGY) - v2
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- @inertiajs/vue3 (INERTIA_VUE) - v2
- tailwindcss (TAILWINDCSS) - v4
- vue (VUE) - v3
- @laravel/vite-plugin-wayfinder (WAYFINDER_VITE) - v0
- eslint (ESLINT) - v9
- prettier (PRETTIER) - v3

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/Pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

# Inertia v2

- Use all Inertia features from v1 and v2. Check the documentation before making changes to ensure the correct approach.
- New features: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app/Console/Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== wayfinder/core rules ===

# Laravel Wayfinder

Use Wayfinder to generate TypeScript functions for Laravel routes. Import from `@/actions/` (controllers) or `@/routes/` (named routes).

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

=== inertia-vue/core rules ===

# Inertia + Vue

Vue components must have a single root element.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

</laravel-boost-guidelines>
