# FusionERP

A production-ready Enterprise Resource Planning (ERP) system built with Laravel 12, featuring complete modules for user management, product & inventory control, order processing, and business intelligence reporting.

---

## Table of Contents

- [Project Overview](#project-overview)
- [Objectives](#objectives)
- [Tech Stack](#tech-stack)
- [System Architecture](#system-architecture)
- [Database Schema](#database-schema)
- [Features & Modules](#features--modules)
- [Role & Permission Matrix](#role--permission-matrix)
- [Getting Started](#getting-started)
- [Test Suite](#test-suite)
- [Default Credentials](#default-credentials)

---

## Project Overview

FusionERP is a full-featured web-based ERP system designed to manage the core operations of a small-to-medium business. It provides a unified platform for managing employees, products, stock levels, sales orders, and business performance — all behind a fine-grained role-based access control system.

The system is architected around strict module boundaries: each functional area (inventory, orders, reports) is self-contained with its own controller, service layer, policies, and tests. All stock-modifying operations run inside database transactions with row-level locking to guarantee consistency under concurrent load.

---

## Objectives

- Provide a single-pane-of-glass for operations: users, products, stock, orders, and reporting
- Enforce business rules at the domain layer (services, enums, custom exceptions) — not just at the HTTP layer
- Guarantee inventory integrity: every stock change is recorded as an immutable `InventoryMovement` audit record
- Protect historical order data: product name, SKU, and price are snapshotted on each order line at creation time
- Scale gracefully: all aggregate report queries use `selectRaw` with server-side grouping — no in-PHP collection processing
- Ship with a comprehensive test suite that runs against a real SQLite in-memory database (no mocks)

---

## Tech Stack

| Layer | Technology |
|---|---|
| Language | PHP 8.4+ (strict types, readonly properties, backed enums) |
| Framework | Laravel 12 |
| RBAC | Spatie Laravel Permission v6 |
| Database | MySQL 8.0 (production) · SQLite :memory: (tests) |
| Cache / Queue | Redis 7.4 |
| Frontend | TailwindCSS 3 · Alpine.js 3 · Chart.js 4 |
| Build | Vite 7 |
| Web Server | Nginx 1.27 |
| PHP Runtime | PHP-FPM 8.4 Alpine |
| Containerisation | Docker Compose |

---

## System Architecture

### Application Architecture

```mermaid
graph TB
    subgraph Client["Browser"]
        UI["Blade + Alpine.js + Chart.js"]
    end

    subgraph Nginx["Nginx 1.27 :8080"]
        STATIC["Static assets<br/>(Vite build)"]
        PROXY["PHP-FPM proxy"]
    end

    subgraph App["PHP-FPM 8.4 — fusion_app"]
        direction TB
        ROUTES["routes/web.php"]
        MW["Auth · Verified · can:* Middleware"]
        CTRL["Controllers"]
        SVC["Service Layer<br/>OrderService · DashboardService · ReportService"]
        POLICY["Laravel Policies<br/>+ Gate::before() admin bypass"]
        MODEL["Eloquent Models<br/>SoftDeletes · Enum casts"]
        ENUM["Backed Enums<br/>OrderStatus"]
        EX["Custom Exceptions<br/>InsufficientStockException"]
    end

    subgraph Storage["Data Layer"]
        MYSQL["MySQL 8.0 :3306<br/>fusion_mysql"]
        REDIS["Redis 7.4 :6379<br/>fusion_redis<br/>Sessions · Cache · Queues"]
    end

    subgraph Queue["Queue Worker — fusion_queue"]
        WORKER["Laravel Queue Worker"]
    end

    UI -->|HTTPS| Nginx
    Nginx --> STATIC
    Nginx --> PROXY
    PROXY --> ROUTES
    ROUTES --> MW --> CTRL
    CTRL --> POLICY
    CTRL --> SVC
    SVC --> MODEL
    MODEL --> MYSQL
    SVC --> ENUM
    SVC --> EX
    App --> REDIS
    WORKER --> REDIS
    WORKER --> MYSQL
```

### Request Flow

```mermaid
sequenceDiagram
    actor User
    participant Nginx
    participant Middleware
    participant Controller
    participant Policy
    participant Service
    participant DB

    User->>Nginx: GET /orders/create
    Nginx->>Middleware: auth + verified + can:orders.create
    Middleware-->>User: 403 if denied
    Middleware->>Controller: Authorised request
    Controller->>Policy: authorize('create', Order::class)
    Policy-->>Controller: Gate::before() bypasses for admin
    Controller->>Service: createOrder(validated, user)
    Service->>DB: BEGIN TRANSACTION
    Service->>DB: SELECT ... FOR UPDATE (products)
    Service->>DB: INSERT orders, order_items, inventory_movements
    Service->>DB: COMMIT
    Service-->>Controller: Order
    Controller-->>User: Redirect with success flash
```

### Docker Compose Topology

```mermaid
graph LR
    subgraph Host
        P8080["localhost:8080"]
        P5173["localhost:5173"]
        P3306["localhost:3306"]
        P6379["localhost:6379"]
    end

    subgraph fusion_network["fusion_network (bridge)"]
        NGINX["fusion_nginx<br/>Nginx 1.27"]
        APP["fusion_app<br/>PHP-FPM 8.4"]
        QUEUE["fusion_queue<br/>PHP-FPM 8.4<br/>queue:work"]
        VITE["fusion_vite<br/>Node 22<br/>vite --host"]
        MYSQL["fusion_mysql<br/>MySQL 8.0"]
        REDIS["fusion_redis<br/>Redis 7.4"]
    end

    P8080 --> NGINX
    P5173 --> VITE
    P3306 --> MYSQL
    P6379 --> REDIS

    NGINX -->|"proxy_pass 9000"| APP
    APP --> MYSQL
    APP --> REDIS
    QUEUE --> MYSQL
    QUEUE --> REDIS
```

---

## Database Schema

### Entity Relationship Diagram

```mermaid
erDiagram
    users {
        bigint id PK
        string name
        string email UK
        string phone
        string department
        string position
        enum status
        timestamp email_verified_at
        timestamp last_login_at
        timestamp deleted_at
    }

    roles {
        bigint id PK
        string name UK
        string guard_name
    }

    permissions {
        bigint id PK
        string name UK
        string guard_name
    }

    categories {
        bigint id PK
        string name
        string slug UK
        text description
        boolean is_active
        timestamp deleted_at
    }

    products {
        bigint id PK
        bigint category_id FK
        string name
        string slug UK
        string sku UK
        string barcode
        decimal price
        decimal cost
        int stock_quantity
        int min_stock_level
        enum status
        boolean is_featured
        timestamp deleted_at
    }

    inventory_movements {
        bigint id PK
        bigint product_id FK
        bigint user_id FK
        enum type
        int quantity
        int before_quantity
        int after_quantity
        text notes
    }

    orders {
        bigint id PK
        string order_number UK
        bigint user_id FK
        bigint cancelled_by_id FK
        string customer_name
        string customer_email
        string customer_phone
        enum status
        decimal subtotal
        decimal tax_rate
        decimal tax_amount
        decimal discount_amount
        decimal total_amount
        text notes
        timestamp cancelled_at
        timestamp deleted_at
    }

    order_items {
        bigint id PK
        bigint order_id FK
        bigint product_id FK
        string product_name "snapshot"
        string sku "snapshot"
        int quantity
        decimal unit_price "snapshot"
        decimal total_price
    }

    users }o--o{ roles : "model_has_roles"
    roles }o--o{ permissions : "role_has_permissions"
    categories |o--o{ products : "classifies"
    products ||--o{ inventory_movements : "tracked by"
    users ||--o{ inventory_movements : "performed by"
    users ||--o{ orders : "placed by"
    users |o--o{ orders : "cancelled by"
    orders ||--|{ order_items : "contains"
    products |o--o{ order_items : "referenced in"
```

### Order State Machine

```mermaid
stateDiagram-v2
    [*] --> Pending : Order created
    Pending --> Confirmed : confirm
    Pending --> Cancelled : cancel
    Confirmed --> Processing : process
    Confirmed --> Cancelled : cancel
    Processing --> Completed : complete
    Processing --> Cancelled : cancel
    Completed --> [*]
    Cancelled --> [*]

    note right of Cancelled : Inventory auto-restored<br/>InventoryMovement(in) recorded
    note right of Pending : Only editable state<br/>(items, pricing, customer)
```

---