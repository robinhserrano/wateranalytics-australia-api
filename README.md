# WAA Commission

![php](https://img.shields.io/badge/php-8.4-777BB4?logo=php&logoColor=white)
![laravel](https://img.shields.io/badge/laravel-12-FF2D20?logo=laravel&logoColor=white)
![vue](https://img.shields.io/badge/vue-3-4FC08D?logo=vuedotjs&logoColor=white)
![pest](https://img.shields.io/badge/tested%20with-pest-red)

Commission tracking for Water Analytics Australia. Sources sales orders, contacts, and
products from Odoo (plus a legacy system for historical data), runs them through a
commission-calculation engine, and manages approval across sales roles.

---

## Getting Started 🚀

```sh
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Fill in the database and Odoo credentials in `.env` (see [Configuration](#configuration-)),
then:

```sh
php artisan migrate
composer dev
```

`composer dev` runs the Laravel server, queue listener, and Vite dev server together.
The app is served at whatever `APP_URL` / `php artisan serve` prints (default
`http://localhost:8000`).

---

## Configuration ⚙️

| Variable | Purpose |
|---|---|
| `DB_*` | MySQL connection |
| `ODOO_HOST` | Odoo instance URL, including scheme and port |
| `ODOO_DATABASE` | Odoo database name |
| `ODOO_USERNAME` / `ODOO_PASSWORD` | Odoo JSON-RPC login |
| `ODOO_FIXED_USER_ID` | Optional — skips auth and pins a specific Odoo user |
| `ODOO_SSL_VERIFY` | Set `false` to skip SSL verification (self-signed Odoo instances) |

See [config/odoo.php](config/odoo.php) for the full set of Odoo connection options.

---

## Architecture 🏗️

- **Odoo sync** — `app/Console/Commands/Sync*` pull contacts, products, stock, and sales
  orders from Odoo via `obuchmann/odoo-jsonrpc` and upsert them into local tables.
  `SyncAll` runs them in dependency order (contacts → products → stocks → sales orders)
  every minute, see [routes/console.php](routes/console.php). `LegacySyncService` /
  `LegacyEndpointService` handle one-off imports from the old system. Every run is logged
  to `SyncLog`, viewable at `/admin/logs`.
- **Commission engine** — [CommissionCalculator](app/Services/CommissionCalculator.php)
  encodes WAA's specific pricing/commission rules on top of the synced
  `SalesOrder`/`Contact`/`Product` data. It does not talk to Odoo directly — if numbers
  look wrong, check `SyncLog` and the relevant `Sync*` command first.
- **Approval & roles** — `spatie/laravel-permission` roles (Admin, Sales Manager, Sales
  Team Manager, Account Officer, etc.) gate routes and commission status transitions.
- **Frontend** — Inertia + Vue 3, pages in `resources/js/Pages`, routes consumed from
  generated Wayfinder modules (`@/actions`, `@/routes`) rather than hardcoded URLs.

---

## Running Tests 🧪

```sh
composer test
# or
php artisan test --compact --filter=TestName
```

Uses Pest. Test suites live in `tests/Feature` and `tests/Unit`.

---

## Code Style 🎨

```sh
vendor/bin/pint --dirty --format agent   # PHP
npm run lint                             # ESLint
npm run format                           # Prettier
```

---

## Useful Commands

```sh
php artisan odoo:sync-all [--all]              # full Odoo sync pipeline
php artisan odoo:sync-contacts                 # individual sync steps also available:
php artisan odoo:sync-products                 #   odoo:sync-stocks, odoo:sync-sales
php artisan commissions:calculate-missing      # backfill missing commission calcs
```
