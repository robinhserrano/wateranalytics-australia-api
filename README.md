# Water Analytics Australia - Commission Management System

A sophisticated, enterprise-grade commission tracking and management platform for Water Analytics Australia. Engineered for real-time calculations, seamless Odoo ERP integration, and complex multi-tier approval workflows.

---

## 📋 Table of Contents

- Overview
- Core Features
- Tech Stack
- System Architecture
- Prerequisites
- Getting Started
- Project Structure
- API Documentation
- Configuration
- Development Workflow
- Testing
- Deployment
- Troubleshooting

---

## Overview

Water Analytics Australia Commission Management System is a full-stack application designed to revolutionize how sales commissions are tracked, calculated, and managed. Built with modern technologies and enterprise-grade practices, it provides:

- **Real-time Commission Calculations** leveraging complex business rules and multi-tier approval workflows
- **Bi-directional Odoo Integration** ensuring single source of truth across systems
- **Role-Based Access Control** with granular permission management
- **Legacy System Migration** capabilities with full data reconciliation and audit trails
- **Comprehensive Analytics** and reporting for business intelligence

Perfect for organizations managing complex sales commission structures with multiple team hierarchies and approval workflows.

---

## 🚀 Core Features

### Commission Management

- **Automated Real-Time Calculations**: Instantly compute commissions based on sales orders, product margins, and configurable user-specific rates
- **Multi-Source Commission Tracking**: Distinguish between self-generated leads and company-provided leads with separate milestone-based rates
- **Advanced Commission Breakdown**: Granular visibility into base commission, extra commission, manual adjustments, penalties, and deductions
- **Intelligent Approval Workflows**: Multi-level manager review and approval process with status tracking and audit trails
- **Commission Status Lifecycle**: Comprehensive tracking through pending → approved → paid states with historical records

### Sales Order Management

- **Intelligent Filtering System**: Advanced filters by salesperson, commission status, delivery status, invoice status, and date ranges
- **Pending Commission Resolution**: Quick identification of sales orders requiring commission owner assignment
- **Order Status Views**: Visibility into active 'sale' status orders with delivery state tracking
- **Inventory Integration**: Track fully delivered, partially delivered, and pending orders with warehouse synchronization

### Odoo ERP Integration

- **Bi-Directional Sync**: Automatic synchronization of sales orders, products, contacts, and inventory levels
- **Conflict Resolution**: Intelligent handling of data conflicts with audit logging and resolution history
- **Real-Time Webhooks**: Event-driven updates ensuring data consistency across systems
- **Migration Tools**: Comprehensive legacy system data import with validation and reconciliation

### Access Control & Security

- **Role-Based Permissions**: Admin, Account Officer, Sales Manager, Team Manager, Salesperson
- **Row-Level Security**: Restrict data access based on team hierarchy and manager relationships
- **Audit Logging**: Complete tracking of all commission modifications with user attribution
- **API Authentication**: Secure Sanctum-based API authentication with token management

---

## 🛠️ Tech Stack

### Backend Architecture

- **Framework**: [Laravel 12](https://laravel.com/) - Modern PHP framework with elegant syntax
- **Authentication**: [Laravel Sanctum](https://laravel.com/docs/12/sanctum) - Token-based API authentication
- **Web Auth**: [Laravel Fortify](https://laravel.com/docs/12/fortify) - Backend authentication scaffolding
- **Permissions**: [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission) - Role and permission management
- **Database**: MySQL 8.4 with full-text search capabilities
- **Odoo Connector**: [obuchmann/odoo-jsonrpc](https://github.com/obuchmann/php-odoo-jsonrpc) - JSON-RPC Odoo integration
- **Queues**: [Laravel Horizon](https://laravel.com/docs/12/horizon) on Redis - dashboard, metrics and retries for the background sync pipeline
- **Backups**: [Spatie Laravel Backup](https://spatie.be/docs/laravel-backup) - Automated backup solutions
- **API Documentation**: [Scribe](https://scribe.knuckles.rocks/) - Auto-generated API documentation

### Frontend Architecture

- **Framework**: [Vue 3](https://vuejs.org/) with Composition API - Reactive, modern UI framework
- **Routing & SSR**: [Inertia.js](https://inertiajs.com/) - Seamless server-driven UI
- **UI Components**: [Reka UI](https://reka-ui.com/) - Headless, unstyled component library
- **Icons**: [Lucide Vue Next](https://lucide.dev/) - Consistent icon system
- **Styling**: [Tailwind CSS 4](https://tailwindcss.com/) with JIT compilation
- **Date Handling**: [@internationalized/date](https://react-spectrum.adobe.com/internationalized/date/) - Timezone-aware date utilities
- **Utilities**: [@vueuse/core](https://vueuse.org/) - Essential Vue composition utilities

### Development & DevOps

- **Containerization**: [Laravel Sail](https://laravel.com/docs/12/sail) - Docker-based development environment
- **Build Tool**: [Vite 7](https://vitejs.dev/) - Lightning-fast module bundler
- **Code Quality**: [Laravel Pint](https://laravel.com/docs/12/pint) - Opinionated PHP code style fixer
- **Debugging**: [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar) - Development debugging toolbar
- **Testing**: [Pest](https://pestphp.com/) - Elegant PHP testing framework
- **Linting**: [ESLint](https://eslint.org/) with TypeScript support
- **Formatting**: [Prettier](https://prettier.io/) - Code formatter for JavaScript/Vue

### Infrastructure

- **Container Orchestration**: Docker & Docker Compose
- **Deployment**: AWS EC2 (Ubuntu 24.04 LTS ARM64)
- **Web Server**: Nginx with HTTP/2 support
- **SSL/TLS**: Let's Encrypt certificates with auto-renewal
- **Reverse Proxy**: Nginx load balancing and caching

---

## 🏗️ System Architecture

### High-Level Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                        Client Layer                          │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  Vue 3 + Inertia.js (SSR-capable)                    │   │
│  │  Tailwind CSS, Reka UI Components                    │   │
│  └──────────────────────────────────────────────────────┘   │
└────────────────────┬────────────────────────────────────────┘
                     │ HTTP/HTTPS
                     ▼
┌─────────────────────────────────────────────────────────────┐
│                      API Layer                               │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  Laravel 12 REST API with Sanctum Auth              │   │
│  │  ├─ Commission Controller                            │   │
│  │  ├─ Sales Order Controller                           │   │
│  │  ├─ Dashboard Controller                             │   │
│  │  └─ Sync Controllers (Odoo)                          │   │
│  └──────────────────────────────────────────────────────┘   │
└────────────────────┬────────────────────────────────────────┘
                     │
         ┌───────────┴───────────┬──────────────┐
         ▼                       ▼              ▼
    ┌─────────┐          ┌────────────┐   ┌─────────────┐
    │ Business│          │ Odoo ERP   │   │ Database    │
    │ Logic   │          │ Integration│   │ (MySQL)     │
    │ Services│          │ Layer      │   │             │
    └─────────┘          └────────────┘   │ ├─ Users    │
         │                    │           │ ├─ Commissions
         │                    │           │ ├─ Orders   │
         └────────────────────┴───────────┤ └─ Products │
                              │           └─────────────┘
                              ▼
                      ┌──────────────────┐
                      │ Odoo ERP System  │
                      │ (External SaaS)  │
                      └──────────────────┘

    ┌──────────────────────────────────────────────────────┐
    │ Scheduler (every minute) ─dispatches─► Redis queue   │
    │   └─► Horizon workers run the 6-job sync chain:      │
    │       contacts → products → stocks → sales orders    │
    │       → installation dates → calculate commissions   │
    └──────────────────────────────────────────────────────┘
```

### Data Flow

**Odoo Sync Flow** (polling, not webhooks — Odoo does not push to this app):

1. The scheduler runs every minute and dispatches a `Bus::chain()` of six queued jobs (`app/Jobs/`) onto a dedicated `odoo-sync` Redis queue, processed by Horizon. A cache mutex prevents a new chain starting while one is still running.
2. Each job runs its artisan command: `sync-contacts` → `sync-products` → `sync-stocks` → `sync-sales` → `sync-installation-dates` → `calculate-missing`. The order is a real dependency order.
3. Most steps sync incrementally, filtering Odoo by `write_date` against the last successful `SyncLog` entry; `--all` forces a full re-fetch.
4. Records are batch-upserted by `odoo_id`, with each command's outcome written to `SyncLog` (visible at `admin/logs`).

**Commission Calculation Flow**:

1. After sales orders sync, `SyncOdooSalesOrders` recalculates commissions for exactly the orders that run touched.
2. `CommissionCalculator` resolves the salesperson via a priority-ordered fallback chain. An order with no resolvable user is skipped and shows as "Pending Mapping" in the UI.
3. Selling price, additional cost, landing price and profit are derived, then commission = base rate (self-gen vs company-lead) + profit split + any manual adjustment. The record is stored with `pending` status.
4. The final pipeline job (`commissions:calculate-missing --limit=200 --update-unconfirmed`) sweeps up any order still missing a commission or still pending — this catches orders that failed salesperson resolution earlier but would resolve now that a `Contact`/`User` mapping has been fixed.
5. Commissions are reviewed and moved through `pending` → `approved` → `paid` by role-gated actions in the UI. Both automatic paths above only ever touch commissions that are still `pending` with no manual adjustment, so approved/paid figures are never silently changed.

> Approval is a pull-based UI workflow — there is no notification/email system in the app.

---

## Prerequisites

### System Requirements

- **PHP**: ≥ 8.2 (recommended: ≥ 8.3)
- **Node.js**: ≥ 20.0
- **Docker**: ≥ 24.0 (for local development)
- **Composer**: ≥ 2.5
- **npm**: ≥ 10.0

### External Services

- **Odoo Instance**: Version 16.0+ (self-hosted or cloud)
- **MySQL Database**: ≥ 8.4 (included in Sail)
- **SMTP Server**: For email notifications (Mailgun, SendGrid, or custom)
- **Optional**: Redis for caching and queue management

### Credentials

You'll need:

- Odoo JSON-RPC login credentials
- SMTP email configuration
- AWS account (for production deployment)

---

## 🚀 Getting Started

### 1. Clone Repository

```bash
git clone <https://github.com/yourusername/wateranalytics-australia-api.git>
cd wateranalytics-australia-api
```

### 2. Environment Configuration

Copy the example environment file and customize:

```bash
cp .env.example .env
```

Edit `.env` with your configuration:

```
APP_NAME="Water Analytics Commission System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=commission_system
DB_USERNAME=sail
DB_PASSWORD=password

# Odoo Integration
ODOO_URL=https://your-odoo-instance.com
ODOO_DATABASE=your_database
ODOO_USERNAME=your_email@example.com
ODOO_PASSWORD=your_password

# Mail
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host.com
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@wateranalytics.com.au
MAIL_FROM_NAME="Water Analytics"

# App Key
APP_KEY=  # Generated in next step
```

### 3. Install Dependencies

```bash
# PHP dependencies
composer install

# Generate application key
php artisan key:generate

# Node dependencies
npm install
```

### 4. Database Setup

Using Laravel Sail:

```bash
# Start Docker containers
./vendor/bin/sail up -d

# Run migrations
./vendor/bin/sail artisan migrate

# Seed database with sample data (optional)
./vendor/bin/sail artisan db:seed
```

### 5. Build Frontend Assets

```bash
npm run build
```

### 6. Start Development Server

```bash
# Runs the Laravel server, a queue listener and Vite concurrently
composer run dev

# OR manually in separate terminals
php artisan serve        # Terminal 1
php artisan queue:listen # Terminal 2
npm run dev              # Terminal 3
```

The application will be available at: [**http://localhost**](http://localhost/)

> Local dev uses `queue:listen`, **not** Horizon. Horizon requires the `pcntl`
> and `posix` PHP extensions, which are POSIX-only — `php artisan horizon` will
> not start on native Windows (it runs fine in the Docker containers, and under
> WSL/macOS/Linux). Jobs still process normally either way; you just don't get
> the Horizon dashboard locally on Windows.

---

## 📁 Project Structure

```
wateranalytics-australia-api/
├── app/
│   ├── Actions/              # Fortify authentication actions
│   ├── Console/Commands/     # Sync + commission Artisan commands
│   ├── Jobs/                # Queued sync pipeline (Horizon)
│   │   ├── OdooSyncStepJob.php          # Base class for every step
│   │   ├── SyncOdooContactsJob.php      # 1st in the chain
│   │   ├── SyncOdooProductsJob.php
│   │   ├── SyncOdooStocksJob.php
│   │   ├── SyncOdooSalesOrdersJob.php
│   │   ├── SyncOdooInstallationDatesJob.php
│   │   └── CalculateMissingCommissionsJob.php  # last; releases the lock
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/         # REST API controllers
│   │   │   ├── Admin/       # Admin panel controllers
│   │   │   └── Settings/    # Settings management
│   │   ├── Middleware/      # HTTP middleware
│   │   ├── Requests/        # Form request validations
│   │   └── Resources/       # API response resources
│   ├── Models/              # Eloquent models
│   │   ├── CommissionCalculation.php
│   │   ├── CommissionAdjustment.php
│   │   ├── CommissionApproval.php
│   │   ├── SalesOrder.php
│   │   ├── Contact.php
│   │   ├── Product.php
│   │   └── SyncLog.php
│   ├── Services/            # Business logic services
│   │   ├── CommissionCalculator.php  # the commission engine
│   │   ├── SyncLogger.php            # writes SyncLog entries
│   │   ├── LegacySyncService.php
│   │   └── LegacyEndpointService.php
│   ├── Listeners/           # Event listeners
│   └── Providers/           # Service providers (incl. HorizonServiceProvider)
│
├── bootstrap/               # Application bootstrap
├── config/                  # Configuration files
│   ├── app.php
│   ├── database.php
│   ├── odoo.php            # Odoo configuration
│   └── permission.php      # Permission configuration
│
├── database/
│   ├── factories/          # Model factories for testing
│   ├── migrations/         # Database migrations
│   └── seeders/            # Database seeders
│
├── resources/
│   ├── css/               # Tailwind CSS entry points
│   ├── js/
│   │   ├── Pages/        # Vue page components
│   │   ├── Components/   # Reusable Vue components
│   │   ├── Layouts/      # Layout components
│   │   └── app.ts        # Vue app entry point
│   └── views/            # Blade templates (if needed)
│
├── routes/
│   ├── api.php           # API routes
│   ├── web.php           # Web routes
│   └── console.php       # Console commands
│
├── deployment/
│   ├── Dockerfile        # Production Docker image
│   ├── nginx.conf        # Nginx configuration
│   └── deploy.sh         # Deployment script
│
├── storage/
│   ├── app/              # Application storage
│   ├── logs/             # Application logs
│   └── debugbar/         # DebugBar data
│
├── tests/
│   ├── Feature/          # Feature tests
│   ├── Unit/             # Unit tests
│   └── Pest.php          # Pest configuration
│
├── vendor/               # Composer dependencies
├── node_modules/         # NPM dependencies
├── compose.yaml          # Docker Compose for development
├── docker-compose.prod.yml # Production Docker Compose
├── vite.config.ts        # Vite configuration
├── tsconfig.json         # TypeScript configuration
├── tailwind.config.ts    # Tailwind CSS configuration
├── eslint.config.js      # ESLint configuration
├── phpunit.xml           # PHPUnit configuration
├── composer.json         # PHP dependencies
├── package.json          # Node dependencies
└── README.md             # This file
```

---

## 🔌 API Documentation

### Base URL

```
<https://api.wateranalytics.com.au/api>
```

### Authentication

All API endpoints (except login) require Bearer token authentication:

```
Authorization: Bearer {your_token}
```

Obtain a token by logging in:

```bash
curl -X POST <http://localhost/api/login> \\
  -H "Content-Type: application/json" \\
  -d '{
    "email": "user@example.com",
    "password": "password"
  }'
```

### Core Endpoints

### Commissions

```
GET    /api/commissions          # List commissions with filters
GET    /api/commissions/{id}     # Get commission details
GET    /api/commissions/stats    # Get commission statistics
```

Query Parameters:

```
?user_id=123&status=pending&from_date=2024-01-01&to_date=2024-12-31
?page=1&per_page=50
```

### Sales Orders

```
GET    /api/sales-orders         # List sales orders
GET    /api/sales-orders/{id}    # Get order details
```

### Dashboard

```
GET    /api/dashboard            # Get dashboard metrics
```

### Teams

```
GET    /api/teams                # List teams
GET    /api/teams/{id}           # Get team details
```

### Products

```
GET    /api/products             # List products
GET    /api/products/{id}        # Get product details
```

### Response Format

**Success Response**:

```json
{
  "data": { /* ... */ },
  "meta": {
    "total": 100,
    "per_page": 20,
    "current_page": 1
  }
}
```

**Error Response**:

```json
{
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

### Full API Documentation

Generate interactive API documentation:

```bash
php artisan scribe:generate
```

Documentation will be available at: `/api/documentation`

---

## ⚙️ Configuration

### Environment Variables

### Application

```
APP_NAME              # Application display name
APP_ENV              # Environment (local, production)
APP_DEBUG            # Debug mode toggle
APP_URL              # Application URL
APP_KEY              # Encryption key (auto-generated)
APP_TIMEZONE         # Application timezone (default: UTC)
```

### Database

```
DB_CONNECTION        # Database driver (mysql)
DB_HOST              # Database host
DB_PORT              # Database port
DB_DATABASE          # Database name
DB_USERNAME          # Database user
DB_PASSWORD          # Database password
```

### Odoo Integration

```
ODOO_URL             # Odoo instance URL
ODOO_DATABASE        # Odoo database name
ODOO_USERNAME        # Odoo login email
ODOO_PASSWORD        # Odoo password
ODOO_SYNC_INTERVAL   # Sync interval in minutes (default: 60)
ODOO_WEBHOOK_SECRET  # Webhook signature verification secret
```

### Mail

```
MAIL_MAILER          # Mail driver (smtp, mailgun, sendgrid)
MAIL_HOST            # SMTP host
MAIL_PORT            # SMTP port (usually 587 or 465)
MAIL_USERNAME        # SMTP username
MAIL_PASSWORD        # SMTP password
MAIL_FROM_ADDRESS    # From email address
MAIL_FROM_NAME       # From display name
```

### Queue (for background jobs)

```
QUEUE_CONNECTION=redis   # Must be redis - Horizon only processes Redis queues
REDIS_CLIENT=predis
REDIS_HOST=redis         # Docker service name; 127.0.0.1 if Redis runs on the host
REDIS_PORT=6379
REDIS_PASSWORD=null
HORIZON_PATH=horizon     # Dashboard URI, Admin role only
```

> If `QUEUE_CONNECTION` is left on `database`, jobs are queued somewhere Horizon
> can't see them: the dashboard stays empty and the sync pipeline never runs.
> Worker counts live in `config/horizon.php` under `environments` — that key is
> matched against `APP_ENV`, and if nothing matches Horizon starts with **zero
> workers** while still reporting "Active", so the `'*'` wildcard entry must stay.

### Permission Configuration

Edit `config/permission.php` to define roles and permissions:

```php
'roles' => [
    'admin',
    'account_officer',
    'sales_manager',
    'team_manager',
    'salesperson',
],

'permissions' => [
    'view_commissions',
    'approve_commissions',
    'adjust_commissions',
    'view_reports',
]
```

### Odoo Configuration

Configure Odoo sync in `config/odoo.php`:

```php
return [
    'sync' => [
        'enabled' => env('ODOO_SYNC_ENABLED', true),
        'interval' => env('ODOO_SYNC_INTERVAL', 60),
        'models' => [
            'SalesOrder',
            'Product',
            'Contact',
        ],
    ],
];
```

---

## 👨‍💻 Development Workflow

### Code Quality

### Format Code

```bash
npm run format        # Format Vue/TypeScript/CSS
composer run pint    # Format PHP code
```

### Lint Code

```bash
npm run lint         # Run ESLint
php artisan pint --test  # Check PHP code style
```

### Type Check

```bash
npm run type-check   # TypeScript validation
```

### Database Management

### Create Migration

```bash
php artisan make:migration create_commissions_table
```

### Run Migrations

```bash
php artisan migrate
```

### Rollback

```bash
php artisan migrate:rollback
```

### Fresh Database

```bash
php artisan migrate:fresh --seed
```

### Generate Code

### Create Controller

```bash
php artisan make:controller CommissionController --api
```

### Create Model with Migration

```bash
php artisan make:model Commission -m
```

### Create Request Validator

```bash
php artisan make:request StoreCommissionRequest
```

---

## 🧪 Testing

### Run All Tests

```bash
composer test
```

### Run Specific Test Suite

```bash
# Feature tests only
php artisan test tests/Feature

# Unit tests only
php artisan test tests/Unit
```

### Run Single Test

```bash
php artisan test tests/Feature/CommissionTest.php
```

### Generate Coverage Report

```bash
php artisan test --coverage
```

### Test Structure

```
tests/
├── Feature/
│   ├── Auth/                             # Login, registration, 2FA, password reset
│   ├── Settings/                         # Profile & password settings
│   ├── CommissionControllerTest.php
│   ├── CalculateMissingCommissionsTest.php  # status/manual-adjustment gating
│   ├── ManualAdjustmentTest.php
│   ├── OdooSyncScheduleTest.php          # job chain + overlap lock
│   ├── SalesOrderControllerTest.php
│   ├── ReportControllerTest.php
│   ├── RouteAuthorizationTest.php        # role gating per route
│   └── DashboardTest.php
├── Unit/
│   ├── CommissionCalculatorTest.php      # the commission rules
│   ├── SyncOdooSalesOrdersRecalculateCommissionsTest.php
│   ├── SyncOdooSalesOrdersPaymentStatusTest.php
│   ├── CalculateMissingCommissionsJobTest.php
│   ├── HorizonEnvironmentConfigTest.php  # guards the '*' env key
│   ├── SalesOrderHasInstallationTest.php
│   └── UserHierarchyTest.php
└── Pest.php              # Pest configuration
```

---

## 🚢 Deployment

### Prerequisites

- AWS account with EC2 access
- Domain registered and DNS configured
- SSH key pair for EC2 access
- GitHub personal access token (for deploying from Git)

### Quick Deployment

Follow the complete deployment guide in [deployment/README.md](https://www.notion.so/deployment/README.md)

### Environment for Production

Create `.env.production`:

```
APP_NAME="Water Analytics Commission System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.wateranalytics.com.au

# Database (Production RDS or similar)
DB_CONNECTION=mysql
DB_HOST=mysql.production.internal
DB_PORT=3306
DB_DATABASE=commission_prod
DB_USERNAME=prod_user
DB_PASSWORD=secure_password_here

# Odoo
ODOO_URL=https://your-odoo-instance.com
ODOO_SYNC_ENABLED=true

# Mail
MAIL_MAILER=mailgun
MAIL_FROM_ADDRESS=noreply@wateranalytics.com.au

# Security
SESSION_DRIVER=cookie
SESSION_SECURE_COOKIES=true
SANCTUM_STATEFUL_DOMAINS=api.wateranalytics.com.au
```

### Docker Deployment

```bash
# Build production image
docker-compose -f docker-compose.prod.yml build

# Deploy
docker-compose -f docker-compose.prod.yml up -d

# Run migrations
docker-compose -f docker-compose.prod.yml exec app php artisan migrate --force
```

### Monitoring & Logs

```bash
# View application logs
docker compose -f docker-compose.prod.yml logs -f app

# View queue worker logs (sync pipeline runs here)
docker compose -f docker-compose.prod.yml logs -f queue

# View database logs
docker compose -f docker-compose.prod.yml logs -f mysql

# Confirm Horizon actually spawned workers - if this shows only PID 1
# (php artisan horizon) with no child processes, no queues are being
# consumed even though the dashboard may report "Active"
docker compose -f docker-compose.prod.yml exec -T queue ps aux
```

Queue health is best checked from the Horizon dashboard at `/horizon` (Admin
only), and sync outcomes from `/admin/logs`.

---

## 🔧 Troubleshooting

### Common Issues

### "CORS Policy" Error in Browser

```
Solution: Check CORS configuration in config/cors.php
Ensure frontend domain is in ALLOWED_ORIGINS
```

### Commission Not Calculating / shows "Pending Mapping"

"Pending Mapping" means `CommissionCalculator` could not resolve a salesperson
for the order, so no commission record was created.

```
1. Confirm the order's salesperson maps to a User: the Contact whose odoo_id
   matches the order's salesperson_partner_id (or partner_id) needs its
   user_id set, or the User needs a matching odoo_user_id/odoo_salesperson_id
2. Verify that User has commission rates configured (self_gen_base,
   company_lead_base, commission_split)
3. Check the sales order state is 'sale' - other states are not synced
4. Review logs for "Skipping commission calculation for order #X"
5. After fixing a mapping, the next pipeline run picks it up automatically
   (or force it: php artisan commissions:calculate-missing --all)
```

Already-approved/paid commissions, and any with a manual adjustment, are
deliberately never recalculated automatically — use the UI's recalculate
action if one of those genuinely needs updating.

### Odoo Sync Fails

```
1. Verify ODOO_* credentials in .env and that the instance is reachable
2. Check the sync history at /admin/logs (SyncLog) for the failing step
3. Check Horizon at /horizon - Failed Jobs shows the exception and payload
4. Tail the worker: docker compose -f docker-compose.prod.yml logs queue
5. Run a step manually to reproduce, e.g. php artisan odoo:sync-sales -v
```

### Docker Port Already in Use

```bash
# Find and kill process
lsof -i :80
kill -9 <PID>

# Or change port in compose.yaml
APP_PORT=8080
```

### Database Connection Error

```
1. Ensure MySQL container is running: docker ps
2. Verify DB credentials in .env
3. Check network configuration in compose.yaml
```

### Debug Commands

```bash
# Test Odoo connectivity (runs one real sync step)
php artisan odoo:sync-contacts -v

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Horizon status and workers
php artisan horizon:status
php artisan horizon:list

# View / retry failed jobs (also visible at /horizon)
php artisan queue:failed
php artisan queue:retry all

# Clear a backed-up queue (e.g. duplicate jobs piled up)
php artisan horizon:clear --queue=odoo-sync

# Apply new Horizon config - workers only pick it up on restart
php artisan horizon:terminate
```

---

## 📊 Performance Considerations

- **Database Indexing**: All foreign keys and frequently filtered columns are indexed
- **Query Optimization**: Use `select()` and `with()` for efficient querying
- **Queue System**: The Odoo sync pipeline runs as queued Horizon jobs, so a slow or failing sync never blocks the scheduler, and failures get automatic retries with backoff
- **Batched writes**: Sync commands `upsert()` in batches keyed on `odoo_id` rather than saving row-by-row
- **Incremental sync**: Steps filter Odoo by `write_date` since the last successful run; stock sync skips warehouses with no movement
- **Memoized lookups**: `CommissionCalculator` builds its salesperson lookup maps once per instance, so a bulk recalculation costs 2 queries instead of ~5 per order
- **API Response**: Paginated endpoints default to 50 items per page

---

## 🔒 Security

- **Authentication**: Session-based auth (Fortify) for the Inertia app; Sanctum tokens guard `routes/api.php`
- **Authorization**: Role/permission gating with spatie/laravel-permission; the Horizon dashboard is Admin-only via the `viewHorizon` gate
- **Input Validation**: All user inputs validated with Form Requests
- **CSRF Protection**: Enabled for all state-changing operations
- **SQL Injection**: Parameterized queries via Eloquent ORM
- **XSS Protection**: Vue 3 automatic escaping + Tailwind sanitization
- **Rate Limiting**: Applied to sensitive endpoints (e.g. two-factor confirmation); not yet applied globally across the API
- **Audit Logging**: All commission changes tracked with user attribution
