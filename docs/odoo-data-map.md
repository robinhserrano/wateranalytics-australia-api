# Odoo Data Map

Every Inertia page in the commission app, what it renders, and where its numbers actually come from — a nightly sync into local tables, or a live round-trip to Odoo on each request.

**Source:** `app/Http/Controllers` · `app/Console/Commands` · `resources/js/Pages`

**Legend**
- 🟫 **local db** — served from a synced table
- 🟢 **live odoo** — fetched from Odoo on this request
- 🟡 **hybrid** — live with a local fallback
- 🔴 **unauthed** — no auth middleware on the route

---

## 1. The sync pipeline

Four Artisan commands pull from Odoo on a schedule and upsert into local tables. Every page below reads from these tables unless flagged otherwise.

```
odoo:sync-contacts → odoo:sync-products → odoo:sync-stocks → odoo:sync-sales
```
(orchestrated by `SyncAll`)

| Command | Odoo model(s) | Key fields | Writes to |
|---|---|---|---|
| `SyncContacts` | `res.partner`, `res.partner.category`, `res.users` | display_name, contact_address_complete, street/zip/city, state_id, phone, email, category_id, user_ids, write_date | `contacts`, `tags`, `contact_tag` |
| `SyncProducts` | `product.template` | name, default_code, list_price, qty_available, categ_id, currency_id, uom_id, type | `products` |
| `SyncStocks` | `stock.warehouse`, `stock.quant`, `product.product` | categ_id, cost_method, standard_price, qty_available, free_qty, incoming/outgoing_qty, virtual_available | `warehouses`, `product_stocks` |
| `SyncOdooSalesOrders` | `sale.order` (tag_ids ∋ 2, state=sale) | partner_id, user_id, team_id, amounts, delivery_status, installer, x_studio_* custom fields, order_line[product_id…] | `sales_orders`, `sales_order_lines` — triggers `CommissionCalculator` |
| `SyncOdooInstallationDates` | `project.task`, `stock.move.line` | date_deadline, sale_order_id.name, picking_id.{date_done, origin, picking_type_id} | `sales_orders`.installation_date / .odoo_task_id |

> `SyncLegacySalesOrders` / `SyncLegacyUsers` are not in this map — they pull from the old legacy PHP system over its own REST API, not Odoo JSON-RPC.

---

## 2. Sales & commissions

All local-table reads except the Installation tab on an order's detail page, which fetches Odoo log notes live.

### Dashboard
`GET /dashboard` → `DashboardController@index` — 🟫 local db

- `stats` — overview + commissions summary; `recent_orders` with `commissionCalculation.user` eager-loaded
- `isManager`

### Sales Orders — Index
`GET /sales-orders` → `SalesOrderController@index` — 🟫 local db

- `salesOrders` — paginated, `with(['commissionCalculation.user'])`
- `filters`, `users`, `viewScope`
- `canViewInstaller`, `canConfirmCommission`, `canMarkOdoo`

### Sales Orders — Show
`GET /sales-orders/{id}` → `SalesOrderController@show` — 🟫 local db + 🟢 live odoo

- `salesOrder` eager-loaded with `partner`, `lines.product`, `lines.landingPrice`, `commissionCalculation.user`, `commissionCalculation.salesManager`, `commissionCalculation.adjustments.adjuster`, `commissionCalculation.approvals.approver`
- `calculationError`, `canViewInstaller`, `canConfirmCommission`

**Tabs:**
| Tab | Data source |
|---|---|
| Order Details & Commission | Entirely from the `salesOrder` prop — order lines and commission breakdown, no request on tab-switch. |
| Installation Task 🟢 | Admin/Sales-Manager only. On mount, if `odoo_task_id` is set, calls `fetch(route('installation-tasks.messages', id))` → proxies Odoo's `project.task` mail thread live via `POST /mail/thread/messages`. Image attachments load through `installation-tasks.attachment`, proxying `GET /web/image/{id}` live, uncached. |

### Commissions — Index / Show
`GET /commissions[/{id}]` → `CommissionController` — 🟫 local db

- Index — `commissions` with `salesOrder, user, salesManager`; `filters`, `summary`
- Show — `commission` with `salesOrder.lines.product`, `salesOrder.lines.landingPrice`, `user`, `salesManager`, `approver`, `rejecter`, `adjustments.adjuster`, `approvals.approver`

### Reports
`GET /reports` → `ReportController@index` — 🟫 local db

- `monthly`, `totals`, `users`, `filters`
- `monthDetail` — order breakdown, `salesOrder:id,name,partner_name,amount_total,create_date,delivery_status` + `user:id,name` selects

---

## 3. Operations

Stock pages skip the local cache entirely on a normal load — the controller itself calls Odoo before rendering, and only falls back to the local table if that call fails.

### Installation Tasks — Index / Show
`GET /installation-tasks[/{id}]` → `InstallationTaskController` — 🟫 local db

- Index — `tasks`: paginated `SalesOrder` rows with installation_date/installer_id set; `filters`
- Show — `task`: SalesOrder loaded with `lines`, `partner`

### Stocks — Index / Show
`GET /stocks[/{id}]` → `ProductStockController` — 🟡 hybrid

- `stocks` / `stock` — read live from Odoo's `product.product` via `web_search_read` / `search_panel_select_multi_range`, using the controller's own session-cookie RPC helper (not the shared `Odoo` package). Falls back to the local `product_stocks` + `warehouses` tables if the RPC call fails.
- `dataSource` — literally `"live"` or `"cached"`, passed straight to the page so the UI can show which one it's looking at
- `warehouses`, `availableCategories`, `filters`
- Fields pulled: `display_name, categ_id, qty_available, free_qty, incoming_qty, outgoing_qty, virtual_available, avg_cost, total_value`

---

## 4. People & access

Contacts, products, users, roles, and teams — all read from local tables kept current by the sync pipeline in §1. None of these query Odoo mid-request.

### Contacts — Index / Show
`GET /contacts[/{id}]` · Admin — 🟫 local db

- Index — `contacts`: paginated `contacts` table, `filters`
- Show — `contact`

### Products — Index / Show
`GET /products[/{id}]` · Admin — 🟫 local db

- Index — `products` with `latestLandingPrice`, `categories`, `filters`
- Show — `product` loaded with `landingPrices`

### Users — Index / Show / Create / Edit
`GET /users/…` — 🟫 local db

- Index — `users` with `roles, salesManager, team, contacts:…` + a `latest_sale_date` subselect; `filters`
- Show — `user` with `contacts, roles, salesManager, team`; `commissionStats`, `salesOrders`
- Create — `contacts`, `roles`
- Edit — `user` with `contacts, roles`; `contacts`, `roles`

### Roles — Index / Create / Edit
`GET /roles/…` · Admin — 🟫 local db

- Index — `roles` with `permissions`; `permissions`
- Create — `permissions`
- Edit — `role` with `permissions`; `permissions`

**Tabs:** Roles / Permissions — both render purely from the `roles` / `permissions` props (Spatie permission tables), no Odoo involvement anywhere on this page.

### Teams — Index / Create / Edit / Hierarchy
`GET /teams/…` · Admin — 🟫 local db

- Index — `teams` with `teamManager.salesManager.roles`, `teamManager.team`, `members`, `withCount('members')` + `latest_sale_date` subselect
- Create — `managers`
- Edit — `team` with `teamManager, members.roles`; `managers`, `availableUsers`
- Hierarchy — `users` (mapped tree), `focusId`

### My Team
`GET /my-team` · `GET /teams/{id}/view-as-manager` — 🟫 local db

- `members`, `canEdit`, `managerHierarchy`, `managerId`, `preview`
- Admin preview mode (`previewAsManager`) renders the same page scoped to the manager being previewed.

---

## 5. Admin & settings

Sync history and account settings — no Odoo calls of any kind.

### Sync Logs
`GET /admin/logs` · Admin — 🟫 local db

- `logs` — paginated `SyncLog` rows written by every command in §1

### Settings — Profile / Password / Two-Factor / Appearance
`GET /settings/…` — 🟫 local db

- Profile — `mustVerifyEmail`, `status`
- Password — no props
- Two-Factor — `twoFactorEnabled`, `requiresConfirmation`
- Appearance — no props; its `<Tabs>`-shaped control is a light/dark/system toggle, not a data tab

---

## 6. Live Odoo endpoints (reference)

Every route in the app that talks to Odoo on the request path, in one place — the last two aren't wired into any page and carry no auth middleware.

| Route | Odoo call | Used by | |
|---|---|---|---|
| `GET stocks`, `GET stocks/{id}` | `product.product` via `web_search_read` / `search_panel_select_multi_range` | Stocks/Index, Stocks/Show | 🟡 hybrid |
| `GET installation-tasks/{id}/messages` | `project.task` mail thread — `POST /mail/thread/messages`, session-cookie auth | SalesOrders/Show, Installation tab | 🟢 live odoo |
| `GET installation-tasks/attachments/{id}` | `GET /web/image/{id}`, proxied uncached | SalesOrders/Show, message attachments | 🟢 live odoo |
| `GET /odoo-test` | `res.partner`, first 5 rows — debug endpoint | nothing — not linked from any page | 🔴 unauthed |
| `GET /odoo-sales` | `sale.order` raw JSON dump | nothing — not linked from any page | 🔴 unauthed |

> `/odoo-test` and `/odoo-sales` sit in `routes/web.php` with no auth middleware and return raw Odoo data to anyone who requests them — they read as dev/debug leftovers from `OdooController` rather than an in-use part of the app. Worth a decision: remove them, or put them behind `auth` + an admin gate like every other Odoo-touching route here.

---

*wateranalytics-australia-api — data map — generated 2026-09-10*
