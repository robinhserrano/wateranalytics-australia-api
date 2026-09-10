<p align="center">
  <h1 align="center">📡 Odoo Data Map</h1>
</p>
<p align="center">
  <em>A living reference for every page in the commission app, and exactly where its data comes from.</em>
</p>

<p align="center">
  <a href="#"><img src="https://img.shields.io/badge/pages_mapped-25-2F6F69.svg?style=for-the-badge" alt="pages mapped" /></a>
  <a href="#"><img src="https://img.shields.io/badge/sync_commands-5-2F6F69.svg?style=for-the-badge" alt="sync commands" /></a>
  <a href="#"><img src="https://img.shields.io/badge/live_odoo_routes-5-B5541B.svg?style=for-the-badge" alt="live odoo routes" /></a>
  <a href="#"><img src="https://img.shields.io/badge/style-very_good_docs-5B4B8A.svg?style=for-the-badge" alt="very good docs" /></a>
</p>

---

## 📚 Table of Contents

- [Why this exists](#-why-this-exists)
- [How to read it](#-how-to-read-it)
- [The sync pipeline](#-the-sync-pipeline)
- [Commission calculation](#-commission-calculation)
- [Page inventory](#-page-inventory)
- [Live Odoo endpoints](#-live-odoo-endpoints)
- [Maintaining this doc](#-maintaining-this-doc)

---

## 🧭 Why this exists

Every screen in this app shows data that either:

1. lives in a local table, refreshed by a scheduled sync command, **or**
2. is fetched from Odoo live, on the request that renders the page.

Those two paths behave very differently under load, under an Odoo outage, and under a stale-cache bug report — so knowing which one a given screen uses, at a glance, is worth more than re-deriving it from the controller every time. This document is that lookup table.

> 📄 The full breakdown — every route, controller, Inertia prop, and tab — lives in **[`odoo-data-map.md`](./odoo-data-map.md)**. This README is the map of the map.

---

## 🔍 How to read it

Every page and route in `odoo-data-map.md` carries one of these markers:

| Marker | Meaning |
| :---: | --- |
| 🟫 **local db** | Served from a table kept current by the sync pipeline. No Odoo round-trip on this request. |
| 🟢 **live odoo** | Calls Odoo directly while handling this request. Slower, and fails differently than a DB read. |
| 🟡 **hybrid** | Tries Odoo live first, falls back to the local table if that call errors. |
| 🔴 **unauthed** | No auth middleware on the route — flagged, not fixed, in this pass. |

---

## ⚙️ The sync pipeline

```
odoo:sync-contacts → odoo:sync-products → odoo:sync-stocks → odoo:sync-sales
```

Orchestrated end-to-end by `SyncAll`. Five commands, five Odoo models, five local tables — the full field-by-field breakdown is in [§1 of the data map](./odoo-data-map.md#1-the-sync-pipeline).

| Command | Odoo model | Local table |
| --- | --- | --- |
| `SyncContacts` | `res.partner` | `contacts` |
| `SyncProducts` | `product.template` | `products` |
| `SyncStocks` | `stock.warehouse`, `product.product` | `warehouses`, `product_stocks` |
| `SyncOdooSalesOrders` | `sale.order` | `sales_orders`, `sales_order_lines` |
| `SyncOdooInstallationDates` | `project.task`, `stock.move.line` | `sales_orders` (install date) |

---

## 💰 Commission calculation

`CommissionCalculator` turns a synced sales order into a commission row — the same seven steps, every time, whether it runs during a sync or a manual recalculate:

```
selling_price          cash → amount_total, else amount_total × 0.9
− additional_cost       lines with no LandingPrice match, × 1.1 markup
− landing_price         lines matching a LandingPrice, supply-only or install cost
= profit
base_commission         self_gen or company_lead rate — $200 flat for the one special product
+ extra_commission      profit × commission_split%, or the full negative profit as a penalty
+ manual_adjustment      preserved across every recalculation
= final_commission
```

Two things that make the automatic recalculation deliberately narrow:

- **Salesperson resolution is a 6-step fallback chain** (contact owner → salesperson-partner owner → Odoo user ID → name → …). No match → the order is skipped and shows as "Pending Mapping" rather than blocking the sync.
- **Anything `approved`, `rejected`, `paid`, or manually adjusted is never recalculated automatically** — both triggers (a sync run, and the nightly `CalculateMissingCommissionsJob` sweep) gate on `status = 'pending' AND manual_adjustment = 0`.

Full formula, every rule, and exactly when each recalculation path fires is in [§2 of the data map](./odoo-data-map.md#2-commission-calculation).

---

## 🗺️ Page inventory

25 Inertia pages, grouped the way the app is navigated:

| Group | Pages | Notable |
| --- | --- | --- |
| **Sales & Commissions** | Dashboard, Sales Orders, Commissions, Reports | Sales Order detail's Installation tab is the one place a *page* prop is 🟢 live — everything else here is 🟫. |
| **Operations** | Installation Tasks, Stocks | Stocks is the outlier: 🟡 hybrid on every load, not just on a tab click. |
| **People & Access** | Contacts, Products, Users, Roles, Teams, My Team | All 🟫. Roles/Permissions is the only other page with real content tabs, and both tabs are local. |
| **Admin & Settings** | Sync Logs, Profile, Password, Two-Factor, Appearance | All 🟫. No Odoo contact anywhere in this group. |

Full detail — route, controller@method, every Inertia prop, eager-loaded relations, and tab-by-tab data sources — is in [§3–§6 of the data map](./odoo-data-map.md#3-sales--commissions).

---

## 🔴 Live Odoo endpoints

Five routes touch Odoo on the request path. Three are load-bearing; two are unauthenticated debug leftovers that don't back any page:

| Route | Status |
| --- | --- |
| `GET stocks[/{id}]` | 🟡 in active use |
| `GET installation-tasks/{id}/messages` | 🟢 in active use |
| `GET installation-tasks/attachments/{id}` | 🟢 in active use |
| `GET /odoo-test` | 🔴 unauthed, unused |
| `GET /odoo-sales` | 🔴 unauthed, unused |

The last two are worth a deliberate call — remove them, or gate them like every other Odoo route in the app. See [§7 of the data map](./odoo-data-map.md#7-live-odoo-endpoints-reference) for exact fields and the reasoning.

---

## 🛠 Maintaining this doc

This map goes stale the moment a controller's props change or a new sync command lands. Treat it like a schema diagram, not prose:

- **Adding a page?** Add one entry to the matching group in `odoo-data-map.md`, with its marker.
- **Adding a sync command?** Add a row to the pipeline table in both this file and §1 of the data map.
- **Turning a 🟫 page into a 🟢 or 🟡 one?** That's the kind of change this doc exists to catch — update the marker in the same PR as the code.

---

<p align="center">
  <sub>wateranalytics-australia-api · internal engineering reference</sub>
</p>
