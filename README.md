# Water Analytics Australia - Commission Management System

A comprehensive commission tracking and management system built for Water Analytics Australia, featuring seamless integration with Odoo ERP, automated commission calculations, and a modern web interface.

## 🚀 Features

### Core Functionality
- **Automated Commission Calculations**: Real-time commission calculations based on sales orders, product margins, and user-specific rates
- **Odoo Integration**: Bi-directional sync with Odoo ERP for sales orders, products, contacts, and inventory
- **Role-Based Access Control**: Granular permissions for Admin, Account Officer, Sales Manager, Team Manager, and Salesperson roles
- **Legacy System Migration**: Seamless migration from legacy commission tracking system with data reconciliation

### Commission Management
- **Multi-Source Tracking**: Separate commission rates for self-generated leads vs. company leads
- **Commission Breakdown**: Detailed breakdown showing base commission, extra commission, manual adjustments, and deductions
- **Approval Workflow**: Manager confirmation and payment tracking
- **Status Filtering**: Filter by pending, approved, rejected, or paid commissions

### Sales Order Management
- **Advanced Filtering**: Filter by user, commission status, delivery status, invoice status, and date range
- **Pending Mapping**: Identify sales orders without assigned commission owners
- **Status-Based Views**: Focus on active 'sale' status orders
- **Delivery Tracking**: Track fully delivered, partially delivered, and not delivered orders

## 🛠️ Tech Stack

### Backend
- **Framework**: Laravel 12
- **Authentication**: Laravel Sanctum (API) + Fortify (Web)
- **Permissions**: Spatie Laravel Permission
- **Database**: MySQL
- **Odoo Integration**: obuchmann/odoo-jsonrpc
- **Backup**: Spatie Laravel Backup

### Frontend
- **Framework**: Vue 3 (Composition API)
- **Routing**: Inertia.js
- **UI Components**: Reka UI (Shadcn-style components)
- **Icons**: Lucide Vue Next
- **Styling**: Tailwind CSS
- **Date Handling**: @internationalized/date
- **Utilities**: @vueuse/core

### Development
- **Container**: Laravel Sail (Docker)
- **Build Tool**: Vite
- **Code Quality**: Laravel Pint
- **Debugging**: Laravel Debugbar

## 📱 Pages Overview

### Dashboard
- **Route**: `/dashboard`
- **Features**: Overview of sales, commissions, and key metrics
- **Access**: All authenticated users (role-based data)

### Sales Orders
- **Route**: `/sales-orders`
- **Features**: 
  - Searchable and filterable sales order list
  - Advanced filters (users, status, dates)
  - Minimalist top-aligned pagination
  - Badge-based status indicators
  - Commission owner assignment
- **Access**: Role-based (own orders, team orders, or all orders)

### Sales Order Details
- **Route**: `/sales-orders/{id}`
- **Features**:
  - Complete order information
  - Line items with product details and margins
  - Commission breakdown
  - Manager confirmation controls
  - Odoo entry tracking
- **Access**: Role-based

### Commissions
- **Route**: `/commissions`
- **Features**:
  - Commission calculations list
  - Filter by status and sales source
  - Summary cards (pending, approved, paid, total earnings)
  - Searchable by order or salesperson
- **Access**: Role-based

### Commission Details
- **Route**: `/commissions/{id}`
- **Features**:
  - Detailed commission breakdown
  - Adjustment history
  - Approval workflow
  - Related sales order information
- **Access**: Role-based

### Users
- **Route**: `/users`
- **Features**:
  - User management
  - Commission split configuration
  - Self-gen and company lead base rates
  - Role assignment
  - Contact assignment
  - Password management
- **Access**: Admin only

### Teams
- **Route**: `/teams`
- **Features**:
  - Team management
  - Team manager assignment
  - Member management
- **Access**: Managers and above

### Contacts
- **Route**: `/contacts`
- **Features**:
  - Customer/contact directory
  - Search functionality
  - Synced from Odoo
- **Access**: All authenticated users

### Products
- **Route**: `/products`
- **Features**:
  - Product catalog
  - Category and type filtering
  - Landing price tracking
  - Synced from Odoo
- **Access**: All authenticated users

### Product Stocks
- **Route**: `/stocks`
- **Features**:
  - Inventory levels by warehouse
  - On-hand, available, incoming, outgoing quantities
  - Stock value tracking
- **Access**: All authenticated users

### Roles & Permissions
- **Route**: `/roles`
- **Features**:
  - Role management
  - Permission assignment
- **Access**: Admin only

## 🔌 API Endpoints

All API endpoints are prefixed with `/api` and require Sanctum authentication (except login).

### Authentication
- `POST /api/login` - User login
- `POST /api/logout` - User logout
- `GET /api/user` - Get authenticated user details

### Dashboard
- `GET /api/dashboard` - Get dashboard statistics

### Sales Orders
- `GET /api/sales-orders` - List sales orders (paginated, filterable)
- `GET /api/sales-orders/{id}` - Get sales order details

### Commissions
- `GET /api/commissions/stats` - Get user commission statistics
- `GET /api/commissions` - List commissions (paginated, filterable)
- `GET /api/commissions/{id}` - Get commission details

### Teams
- `GET /api/teams` - List all teams
- `GET /api/teams/{id}` - Get team details

### Contacts
- `GET /api/contacts` - List contacts (paginated, searchable)
- `GET /api/contacts/{id}` - Get contact details

### Products
- `GET /api/products` - List products (paginated, searchable)
- `GET /api/products/{id}` - Get product details

## 🎨 UI/UX Features

### Design System
- **Shadcn-style Components**: Modern, accessible UI components
- **Consistent Theming**: Unified color palette and spacing
- **Responsive Design**: Mobile-first approach

### Custom Features
- **Badge System**: Color-coded badges for sales sources and delivery statuses
  - Self Gen: Indigo (`#5850e6`)
  - Company Lead: Slate (`#475569`)
  - Fully Delivered: Emerald (`#077959`)
  - Partially Delivered: Amber (`#b5550c`)
- **Advanced Date Picker**: Shadcn-style calendar with timezone handling
- **Searchable Dropdowns**: User picker with "Pending Mapping" option
- **Minimalist Pagination**: Top-aligned with arrows and result ranges

## 🔧 Setup Instructions

### Prerequisites
- Docker Desktop
- Git

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/robinhserrano/wateranalytics-australia-api.git
   cd wateranalytics-australia-api
   ```

2. **Copy environment file**
   ```bash
   cp .env.example .env
   ```

3. **Configure environment variables**
   Edit `.env` and set:
   - Database credentials
   - Odoo connection details
   - App URL and key

4. **Start Docker containers**
   ```bash
   ./vendor/bin/sail up -d
   ```

5. **Install dependencies**
   ```bash
   ./vendor/bin/sail composer install
   ./vendor/bin/sail npm install
   ```

6. **Generate application key**
   ```bash
   ./vendor/bin/sail artisan key:generate
   ```

7. **Run migrations**
   ```bash
   ./vendor/bin/sail artisan migrate
   ```

8. **Seed database (optional)**
   ```bash
   ./vendor/bin/sail artisan db:seed
   ```

9. **Build frontend assets**
   ```bash
   ./vendor/bin/sail npm run dev
   ```

10. **Access the application**
    - Web: http://localhost
    - API: http://localhost/api

## 📊 Key Artisan Commands

### Odoo Synchronization
```bash
# Sync legacy sales orders from old system
./vendor/bin/sail artisan legacy:sync-sales-orders

# Sync products from Odoo
./vendor/bin/sail artisan odoo:sync-products

# Sync contacts from Odoo
./vendor/bin/sail artisan odoo:sync-contacts

# Sync stock levels from Odoo
./vendor/bin/sail artisan odoo:sync-stocks
```

### Commission Management
```bash
# Recalculate all commissions
./vendor/bin/sail artisan commissions:recalculate
```

### Backup
```bash
# Run backup
./vendor/bin/sail artisan backup:run
```

## 🔐 Roles & Permissions

### Admin
- Full system access
- User management
- Role and permission management

### Account Officer
- View all sales orders and commissions
- Financial reporting access

### Sales Manager
- View team sales orders and commissions
- Approve commission calculations
- Manage direct reports

### Team Manager
- View team sales orders and commissions
- Manage team members

### Salesperson
- View own sales orders and commissions
- Track personal performance

## 📝 Commission Calculation Logic

```
Base Commission = (Sales Amount - Total Cost) × Commission Split × Base Rate
Extra Commission = Additional bonuses or incentives
Manual Adjustment = Manager-applied adjustments
Deductions = Any applicable deductions

Final Commission = Base + Extra + Manual Adjustment - Deductions
```

### Sales Source Rates
- **Self Generated**: Higher commission rate (configurable per user)
- **Company Lead**: Standard commission rate (configurable per user)

## 🔄 Data Flow

1. **Sales Order Created in Odoo** → Synced to Laravel
2. **Commission Calculation** → Automated based on product margins and user rates
3. **Manager Review** → Confirmation and adjustments
4. **Payment Tracking** → Status updates (pending → approved → paid)
5. **Mobile Access** → Real-time data via API

## 🤝 Contributing

This is a private internal application for Water Analytics Australia.

## 📄 License

Proprietary - All rights reserved by Water Analytics Australia

## 👥 Support

For technical support or questions, contact the development team.

---

**Built with ❤️ for Water Analytics Australia**
