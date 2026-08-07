# Handover Manual — WAA Commission

Written for a developer taking this project over with no one to ask. It covers the
things you **can't** learn by reading the code: why decisions were made, what has
already broken in production, and what to do when it breaks again.

Read alongside — don't duplicate:

| Document | Purpose |
|---|---|
| [README.md](README.md) | Setup, tech stack, API reference, deployment steps |
| [CLAUDE.md](CLAUDE.md) | Architecture summary + conventions (written for AI agents, but accurate and concise) |
| This file | Tribal knowledge, runbooks, incident history, known issues |

---

## 1. What this system actually does

Water Analytics Australia (WAA) sells and installs water filtration systems. Sales
are recorded in **Odoo** (an external ERP SaaS) — Odoo is the source of truth for
orders, customers, products and stock. **This app does not create sales.**

What this app adds on top of Odoo:

1. **Pulls** order/customer/product/stock data out of Odoo on a schedule.
2. **Calculates** each salesperson's commission using WAA-specific rules that Odoo
   cannot express (see §4).
3. **Runs an approval workflow** — commissions move `pending → approved → paid`,
   gated by role, with an audit trail.

If you internalise one thing: **this is a read-mostly mirror of Odoo plus a
commission engine.** When data looks wrong, suspect the sync before the maths.

### The business vocabulary

| Term | Meaning |
|---|---|
| **Sales source** | How the lead was obtained: `self_gen` (rep found it themselves) or `company_lead` (company handed it to them). Drives a different base commission rate per user. |
| **Commission owner** | The `User` who gets paid for an order. Resolved from Odoo data — *not* stored in Odoo directly (see §4.1). |
| **Landing price** | WAA's true cost for an installed/supplied product, maintained in this app (not Odoo), time-scoped by `effective_from`. |
| **Pending Mapping** | UI label meaning "we couldn't work out who to pay for this order". Not an error state — see §7.1. |
| **Manual adjustment** | A human override on a commission amount, with an audit record. Protected from being overwritten. |

---

## 2. First day orientation

```bash
git clone <repo> && cd wateranalytics-australia-api
cp .env.example .env
composer install && npm install
php artisan key:generate
php artisan migrate --seed          # seeds roles + an admin user
npm run build
composer dev                        # server + queue listener + vite
```

Then read, in this order:

1. `app/Services/CommissionCalculator.php` — the entire business value of the app.
2. `app/Console/Commands/SyncOdooSalesOrders.php` — the biggest, gnarliest file (~460 lines). Everything else is easier than this.
3. `routes/console.php` — how the sync actually runs.
4. `routes/web.php` — the role gating.

Run the tests (`php artisan test`) — 126 of them. They encode most of the
commission rules and the pipeline behaviour, and several exist *specifically*
because the thing they test broke in production. If one fails, take it seriously.

> **Windows note:** Horizon needs the `pcntl`/`posix` PHP extensions, which don't
> exist on native Windows. `php artisan horizon` will not start locally. Use
> `composer dev` (which runs `queue:listen`) or work inside WSL/Docker. Jobs still
> process either way; you just don't get the dashboard.

---

## 3. Environments and access

| | Staging | Production |
|---|---|---|
| URL | `commissionsstaging.wateranalytics.com.au` | `commissions.wateranalytics.com.au` |
| Host | AWS EC2 (Ubuntu, ARM64) | Separate AWS EC2 instance |
| Branch | `staging` | `main` |

Both run the same `docker-compose.prod.yml` stack: `app`, `web` (nginx), `mysql`,
`queue` (Horizon), `scheduler`, `redis`, `nightwatch`, `certbot`.

### Things you must obtain from someone (not in the repo)

- [ ] SSH keys / AWS console access for **both** EC2 instances
- [ ] Odoo credentials (`ODOO_HOST`, `ODOO_DATABASE`, `ODOO_USERNAME`, `ODOO_PASSWORD`) — and ideally a contact on the Odoo side
- [ ] GitHub repo access + a PAT (deploys pull over HTTPS)
- [ ] AWS S3 credentials (database backups are written here nightly)
- [ ] Legacy API base URL + token, if the legacy import is still relevant
- [ ] An Admin login for each environment

> ⚠️ **`.env` is per-server and hand-maintained.** It is *not* in git and *not*
> synced from `.env.example` by the deploy script. When you add a new env var, you
> must SSH into **each** server and add it manually. This has already caused one
> outage (see §6.1).

---

## 4. The commission engine

`app/Services/CommissionCalculator.php`. This is not a generic calculator — every
rule is a specific WAA business decision. **Do not "simplify" it without asking
the business.** Money depends on these numbers.

### 4.1 Salesperson resolution (the #1 source of support tickets)

Odoo does not tell us which local `User` should be paid. We infer it, trying in
order until one hits:

1. The customer `Contact` (`partner_id`) has an explicit `user_id` (commission owner).
2. The salesperson `Contact` (`salesperson_partner_id`) has a `user_id`.
3. `User.odoo_user_id` **or** `User.odoo_salesperson_id` matches the order's `user_id`.
4. `User.name` exactly matches the order's `user_name`.
5. A `Contact` whose `display_name` matches the order's `user_name` → its `user_id`.
6. A `Contact` whose `odoo_id` matches the order's `user_id` → its `user_id`.

**If all six fail, the order is skipped** — no commission row is created, and the
UI shows *"Pending Mapping"*. It's logged, not thrown. See §7.1 for the fix.

> Performance note: the lookup maps are memoised per `CommissionCalculator`
> instance (`loadSalespersonLookupMaps()`), so a bulk run costs 2 queries instead
> of ~5 per order. If you ever construct a fresh instance per order, you'll
> silently reintroduce an N+1 across thousands of records.

### 4.2 The formulas

```
selling price   = cash payment    → amount_total
                  anything else   → amount_total × 0.9      (NON_CASH_DISCOUNT_FACTOR)

additional cost = Σ (tax-exclusive line amount × 1.1) for lines with NO landing price,
                  excluding installation-service and supply-only lines

landing price   = Σ LandingPrice for matching lines
                    → uses the supply_only cost if the order has ANY supply-only line,
                      otherwise the installation_service cost
                    → time-scoped: latest effective_from <= order create_date

profit          = selling price − additional cost − landing price

base            = special product (name contains 'usro-6s1-2w') → flat $200
                  self_gen                                       → user.self_gen_base
                  company_lead                                   → user.company_lead_base

extra           = profit > 0  → profit × (user.commission_split / 100)
                  profit ≤ 0  → the full negative profit (a penalty, intentionally)

final           = base + extra + manual_adjustment
```

Note the asymmetry in `extra`: profits are shared by a percentage, but **losses are
absorbed in full** by the salesperson. That's deliberate business policy, not a bug.

### 4.3 When commissions recalculate — and when they must not

Two automatic paths, **both** gated on `status = 'pending' AND manual_adjustment = 0`:

1. **After sales-order sync** (`SyncOdooSalesOrders::recalculateCommissionsForSyncedOrders`)
   — recalculates only the orders that sync run touched. Catches new orders and
   changed Odoo data (e.g. sales source flipping). *Cannot* catch an order whose
   Odoo data didn't change.
2. **The final pipeline job** (`commissions:calculate-missing --limit=200 --update-unconfirmed`)
   — sweeps any order still missing a commission or still pending. This is what
   catches orders that failed resolution earlier but would resolve now because
   someone fixed a `Contact`/`User` mapping.

**Never widen these gates without thinking hard.** `calculateCommission()`
re-derives every figure on each call, so recalculating an `approved` or `paid`
commission silently changes a number a manager signed off on — or that has already
been paid out. Same for `manual_adjustment`: the adjustment amount survives, but
the *total* around it shifts, which surprises whoever entered it.

> 🪤 **`confirmed_by_manager` is NOT `status`.** It's a separate sales-manager
> sign-off flag. It can be `true` while status is still `pending`, and `false` on an
> already-`approved` commission. Gating recalculation on it — which the code used to
> do — is wrong in both directions and caused real stale-commission bugs. Always
> gate on `status`.

### 4.4 Approval workflow

`pending → approved → paid`, with `rejected` as a side exit. Role gating (`routes/web.php`):

| Action | Who |
|---|---|
| `adjust`, `confirm`, `reset-confirm` | Admin, Sales Manager |
| `approve`, `reject`, `mark-paid`, `recalculate`, `bulk-approve` | Admin only |
| `mark-odoo` (mark as entered into Odoo) | Admin, Account Officer |

Roles: `Admin`, `Sales Manager`, `Sales Team Manager`, `Sales Person`,
`Sales - Internal`, `Account Officer` — see `RolesAndPermissionsSeeder`. Team
scoping uses `User::getTeamUserIds()` / `getAncestorUserIds()`; use those rather
than re-deriving hierarchy in a new query.

There is **no notification system**. Approval is entirely pull-based — managers
have to go look. If someone asks "why didn't I get an email", that's why.

---

## 5. The sync pipeline

The scheduler fires every minute and dispatches a **chain of six queued jobs** onto
a dedicated `odoo-sync` Redis queue, processed by Horizon:

```
SyncOdooContactsJob → SyncOdooProductsJob → SyncOdooStocksJob
  → SyncOdooSalesOrdersJob → SyncOdooInstallationDatesJob
  → CalculateMissingCommissionsJob
```

Order is a real dependency order (sales orders need contacts and products to exist
first) — that's why it's a chain, not a batch. Each job just calls its artisan
command via `Artisan::call()`, so every step remains independently runnable by hand.

Most steps sync **incrementally**, filtering Odoo by `write_date` since the last
successful `SyncLog`. `--all` forces a full re-fetch.

### 🪤 Three landmines in this area

1. **`Bus::chain()` ignores `ShouldBeUnique`.** Laravel only honours it in
   `PendingDispatch` (plain `Job::dispatch()`) — `PendingChain` never checks it.
   Overlap is therefore guarded by a **manual `Cache::add()`/`Cache::forget()`
   mutex** (`OdooSyncStepJob::PIPELINE_LOCK_KEY`, 900s TTL) acquired in
   `routes/console.php`, released by the last job or the chain's `->catch()`.
   Don't "tidy this up" back into `ShouldBeUnique` — it silently does nothing.

2. **`config/horizon.php` `environments` must keep its `'*'` wildcard key.**
   Horizon matches `APP_ENV` against those keys; if nothing matches it deploys
   **zero supervisors** — while still logging "started successfully" and showing
   "Active" on the dashboard. `'local'` must stay listed *before* `'*'`, because
   the first matching key wins. There's a test guarding this
   (`HorizonEnvironmentConfigTest`).

3. **Stock sync deliberately does NOT filter on `product.product.write_date`.**
   Quantities are *computed* fields derived from `stock.quant`; a stock movement
   never bumps the product's `write_date`, so filtering on it would serve stale
   quantities. Instead it does a cheap `stock.quant` activity check per warehouse
   and skips only provably-untouched warehouses — failing **open** (syncs anyway)
   on any error.

### 🪤 Hardcoded Odoo coupling

`SyncOdooSalesOrders` filters on `['tag_ids', 'in', [2]]` — **only Odoo orders
tagged with tag ID `2` are synced at all**, plus `state = 'sale'`. This is a magic
number pointing at a specific tag in WAA's Odoo instance. If orders are
"missing from the app entirely", check the tag in Odoo before debugging code.
Other `x_studio_*` fields are Odoo Studio custom fields — renaming one in Odoo
breaks the sync silently.

---

## 6. Incident history

Real production incidents from this codebase. Each one is a trap you could fall
into again.

### 6.1 Horizon running with zero workers
**Symptom:** Jobs piled up in "Pending" forever. Dashboard said "Active", logs said
"Horizon started successfully", no errors anywhere. `ps aux` in the queue container
showed only PID 1 — no worker children.
**Cause:** `config/horizon.php` only defined `production` and `local` environment
keys. Staging's `APP_ENV` matched neither, so Horizon deployed zero supervisors and
reported success.
**Fix:** the `'*'` wildcard key (§5, landmine 2).
**Lesson:** "Active" on the Horizon dashboard does **not** mean work is being
processed. Always verify with `ps aux`.

### 6.2 A duplicate sync chain queued every single minute
**Symptom:** Hundreds of `SyncOdooContactsJob` entries stacking up.
**Cause:** the overlap guard was `ShouldBeUnique` on the first job, which
`Bus::chain()` silently ignores (§5, landmine 1).
**Fix:** manual cache mutex.

### 6.3 Jobs queued where Horizon couldn't see them
**Symptom:** Empty Horizon dashboard, nothing processing.
**Cause:** the server's `.env` still had `QUEUE_CONNECTION=database`. `.env.example`
had been updated to `redis`, but server `.env` files are hand-maintained (§3).
**Lesson:** after any env-var change, SSH into **every** server.

### 6.4 Commissions silently stale / never calculated
Three related bugs, all now fixed and regression-tested:
- Installation-date sync existed but was **never scheduled or called anywhere**.
- The auto-recalc used `commissions:calculate-missing --limit=$totalSynced`, which
  sorts by `id` ascending — a backlog of old orders consumed the limit before ever
  reaching the just-synced ones.
- The gate used `confirmed_by_manager` instead of `status` (§4.3).

### 6.5 A merged PR that never reached `staging`
PR B was opened against PR A's branch. PR A merged into `staging` first; PR B then
merged into A's (already-merged) branch. Both showed "Merged" — but B's code never
reached `staging`.
**Lesson:** when stacking PRs, verify with `git log origin/main..origin/staging`
rather than trusting the merged badge.

### 6.6 Expired SSL certificate
Certbot renews into a shared volume but has no way to signal nginx to reload, so
nginx served the cert it loaded at boot until restarted. The nginx container now
self-reloads every 6h. A cert that has *already* expired still needs a manual
`certbot renew --force-renewal` + container restart.

---

## 7. Runbook

### 7.1 "Pending Mapping" on orders / commission not calculating

Means salesperson resolution failed (§4.1). Usually a data problem, not a bug.

```bash
# Inspect one order — swap S19094 for the real order name
docker compose -f docker-compose.prod.yml exec -T app php artisan tinker --execute='
$o = \App\Models\SalesOrder::where("name","S19094")->first();
echo "user_id={$o->user_id} user_name={$o->user_name}
";
echo "salesperson_partner_id={$o->salesperson_partner_id} partner_id={$o->partner_id}
";
$c = \App\Models\Contact::where("odoo_id",$o->salesperson_partner_id)->first();
echo "salesperson contact user_id=" . ($c->user_id ?? "NONE") . "
";
'
```

Fix by linking the `Contact` to the right `User` (set `Contact.user_id`), or by
setting `odoo_user_id` / `odoo_salesperson_id` on the `User`. The next pipeline run
picks it up automatically within ~a minute. To force it:

```bash
php artisan commissions:calculate-missing --all
```

### 7.2 Sync appears stopped

```bash
# 1. Are workers actually running? (only PID 1 = broken, see §6.1)
docker compose -f docker-compose.prod.yml exec -T queue ps aux

# 2. What does the queue think?
docker compose -f docker-compose.prod.yml exec -T app php artisan horizon:status
docker compose -f docker-compose.prod.yml logs queue --tail 100

# 3. Is the scheduler alive?
docker compose -f docker-compose.prod.yml logs scheduler --tail 50
```

Then check `/horizon` (Failed Jobs) and `/admin/logs` (`SyncLog`) in the UI.

**If the pipeline lock got stuck** (a worker was killed mid-run, nothing dispatches
for up to 15 min): it self-heals via the 900s TTL, or clear it manually:

```bash
php artisan tinker --execute='Cache::forget(\App\Jobs\OdooSyncStepJob::PIPELINE_LOCK_KEY);'
```

**If duplicate jobs have piled up:**
```bash
php artisan horizon:clear --queue=odoo-sync
```

### 7.3 Deploying

```bash
ssh <server>
cd wateranalytics-australia-api
git pull origin main            # or 'staging' on the staging box
./deployment/deploy.sh <domain> <email>
```

`deploy.sh` does a full `--no-cache --pull` rebuild and recreates every container,
runs `migrate --force`, clears caches and re-optimises. Because containers are
recreated, config changes are picked up automatically — no separate
`horizon:terminate` needed.

**Always verify after deploy:**
```bash
docker compose -f docker-compose.prod.yml ps          # all Up?
docker compose -f docker-compose.prod.yml exec -T queue ps aux   # workers exist?
```

If you deploy config-only changes *without* a rebuild, you must restart Horizon
manually (`php artisan horizon:terminate`) — workers only read config at boot.

### 7.4 Branch and release flow

`feature branch → PR → staging → PR → main`. Both CI workflows (`tests`, `linter`)
run on PRs. Deploy staging first, verify, then release to `main`.

### 7.5 Backups

`spatie/laravel-backup` runs nightly at 01:00 (cleanup 01:30) writing to **S3**.
Retention: 7 days all, 16 daily, 8 weekly, 4 monthly, 2 yearly.
⚠️ **I have never performed a restore drill.** Verify backups actually exist in S3
and practise a restore into a scratch environment before you need it for real.

---

## 8. Known issues and outstanding work

Nothing here is urgent; all are known and deliberate.

| Item | Notes |
|---|---|
| **Login page missing links** | `Auth/Login.vue` accepts `canResetPassword` / `canRegister` props but renders no "Forgot password?" or "Sign up" links. Users cannot self-serve a password reset. |
| **Dead debug routes** | `/odoo-test` and `/odoo-sales` (`OdooController`) make live blocking Odoo calls inside a web request. They look like leftover integration testing — recommend deleting. |
| **Prettier drift** | ~39 frontend files aren't Prettier-clean. CI runs `npm run format` (auto-write), *not* `format:check`, so it isn't a blocking gate. |
| **`calculate-missing` full-table scan** | Its `whereDoesntHave` scan covers the whole `sales_orders` table every run. Correctly indexed and negligible today, but the cost grows with total order count and isn't bounded by `--limit`. If it ever matters, reduce the frequency rather than "optimising" the query. |
| **Composer audit disabled** | `composer.json` sets `audit.block-insecure: false`. Guzzle has open CVEs with no patched version in range of current constraints, and the gate blocked *all* installs. Re-enable once Guzzle can be upgraded. |
| **`SyncOdooSalesOrders` complexity** | ~460 lines in one method-heavy command. It works and is partly tested, but it's the riskiest file to change. |
| **No Odoo-client test harness** | No test mocks the Odoo JSON-RPC client, so sync commands' Odoo interaction is untested. Adding one is the highest-value testing investment available. |
| **`refreshDenormalizedData()`** | `Contact` method that is an empty placeholder (`return $this;`) but is still called in sync loops. Harmless, but misleading — either implement or remove. |

---

## 9. Rules of thumb

1. **Suspect the sync before the maths.** Most "wrong data" bugs are a sync step, not `CommissionCalculator`. Check `/admin/logs` first.
2. **Never silently change a number someone signed off on.** `approved`, `paid`, and manually-adjusted commissions are off-limits to automatic recalculation.
3. **"Active" ≠ "working".** Verify Horizon with `ps aux`, not the dashboard.
4. **After changing `.env`, update every server.** It isn't in git.
5. **Test money paths.** Anything touching commission amounts gets a test — several existing tests exist purely because their subject broke in production.
6. **Run `vendor/bin/pint --dirty` after any PHP edit** and `php artisan test` before pushing. CI enforces both.
7. **Verify stacked PR merges actually landed** (`git log origin/main..origin/staging`).

---

*Last updated: August 2026. If something here contradicts the code, trust the code
and please fix this file.*
