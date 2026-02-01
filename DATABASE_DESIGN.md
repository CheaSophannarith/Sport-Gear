# Sport Gear E-Commerce - Database Design Documentation

## Overview

This document provides comprehensive technical documentation for the Sport Gear E-Commerce database schema.

**Database**: MySQL 8.0+
**Total Tables**: 32 (27 custom + 5 Spatie Permission tables)
**Framework**: Laravel 12
**ORM**: Eloquent

---

## Schema Architecture

### Design Principles
1. **Normalization**: 3NF (Third Normal Form) for data integrity
2. **Performance**: Strategic indexes for query optimization
3. **Flexibility**: Many-to-many relationships for dynamic filtering
4. **Auditing**: Timestamps and status history tracking
5. **Data Preservation**: Snapshots for orders to preserve history

---

## Entity Relationship Diagram (ERD)

```
┌─────────────┐
│   users     │───┐
└─────────────┘   │
       │          │
       │ 1        │ M
       │          │
┌──────▼──────┐   │          ┌─────────────┐
│  addresses  │   └──────────│ order       │
└─────────────┘              └─────────────┘
       │                            │
       M                            │ 1
       │                            │
       │ M                          │ M
┌──────▼──────┐              ┌─────▼────────────┐
│ provinces   │              │  order_items     │
└─────────────┘              └──────────────────┘
                                    │
                                    │ M
                                    │
                             ┌──────▼──────────────┐
                             │ product_variants    │
                             └─────────────────────┘
                                    │
                                    │ M
                                    │ 1
                             ┌──────▼──────┐
                             │  products   │
                             └─────────────┘
                                    │
                         ┌──────────┼──────────┐
                         │          │          │
                    ┌────▼─────┐ ┌─▼────┐  ┌──▼─────┐
                    │ category │ │brands│  │teams   │
                    └──────────┘ └──────┘  └────────┘
```

---

## Table Specifications

### 1. User Management Tables

#### `users`
**Purpose**: All user accounts (buyers + admins)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| name | VARCHAR(255) | NOT NULL | User full name |
| email | VARCHAR(255) | UNIQUE, NOT NULL | User email |
| email_verified_at | TIMESTAMP | NULLABLE | Email verification timestamp |
| password | VARCHAR(255) | NOT NULL | Hashed password |
| phone | VARCHAR(255) | UNIQUE, NULLABLE | Phone number for loyalty tracking |
| avatar | VARCHAR(255) | NULLABLE | Profile picture path |
| loyalty_tier | ENUM('none','silver','gold') | DEFAULT 'none' | Automatic loyalty tier |
| total_spent | DECIMAL(10,2) | DEFAULT 0.00 | Lifetime purchase total |
| remember_token | VARCHAR(100) | NULLABLE | Remember me token |
| two_factor_secret | TEXT | NULLABLE | 2FA secret |
| two_factor_recovery_codes | TEXT | NULLABLE | 2FA recovery codes |
| two_factor_confirmed_at | TIMESTAMP | NULLABLE | 2FA confirmation timestamp |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Last update time |

**Indexes**:
```sql
PRIMARY KEY (id)
UNIQUE INDEX idx_users_email (email)
UNIQUE INDEX idx_users_phone (phone)
INDEX idx_users_loyalty (loyalty_tier)
```

**Business Rules**:
- `loyalty_tier` calculated based on `total_spent`:
  - `total_spent >= 100` → 'gold' (10% discount)
  - `total_spent >= 50` → 'silver' (5% discount)
  - `total_spent < 50` → 'none' (0% discount)
- `total_spent` incremented when order status = 'delivered'

---

#### Spatie Permission Tables
**Purpose**: Role-based access control

**Tables Created by Spatie Package**:
1. `roles` - Role definitions (admin, buyer)
2. `permissions` - Permission definitions
3. `model_has_roles` - Assigns roles to users
4. `model_has_permissions` - Assigns permissions to users
5. `role_has_permissions` - Assigns permissions to roles

**Standard Roles**:
| Role | Description | Permissions |
|------|-------------|-------------|
| admin | Full system access | manage-products, manage-categories, manage-orders, manage-coupons, manage-users, manage-reviews, manage-settings |
| buyer | Regular customer | (none - default user) |

**Installation**:
```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

---

#### `addresses`
**Purpose**: User shipping addresses (1:M with users)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | FK users(id) ON DELETE CASCADE | Owner user |
| province_id | BIGINT UNSIGNED | FK provinces(id) | Delivery province |
| label | VARCHAR(255) | NOT NULL | Address label (Home, Office, etc.) |
| recipient_name | VARCHAR(255) | NOT NULL | Recipient full name |
| phone | VARCHAR(255) | NOT NULL | Contact phone |
| address_line1 | VARCHAR(255) | NOT NULL | Street address line 1 |
| address_line2 | VARCHAR(255) | NULLABLE | Street address line 2 |
| is_default | BOOLEAN | DEFAULT FALSE | Default shipping address flag |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Last update time |

**Indexes**:
```sql
PRIMARY KEY (id)
INDEX idx_addresses_user (user_id)
INDEX idx_addresses_province (province_id)
INDEX idx_addresses_user_default (user_id, is_default)
```

**Business Rules**:
- Only ONE address per user can have `is_default = TRUE`
- First address for user auto-sets `is_default = TRUE`
- Updating default address must unset previous default

---

### 2. Location & Shipping Tables

#### `provinces`
**Purpose**: Cambodian provinces with shipping configuration

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| name | VARCHAR(255) | UNIQUE, NOT NULL | Province name |
| shipping_fee | DECIMAL(8,2) | DEFAULT 0.00 | Shipping cost to province |
| is_cod_available | BOOLEAN | DEFAULT FALSE | Cash on Delivery availability |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Last update time |

**Indexes**:
```sql
PRIMARY KEY (id)
UNIQUE INDEX idx_provinces_name (name)
```

**Example Data**:
```sql
INSERT INTO provinces (name, shipping_fee, is_cod_available) VALUES
('Phnom Penh', 2.00, TRUE),
('Siem Reap', 5.00, FALSE),
('Sihanoukville', 6.00, FALSE),
('Battambang', 5.50, FALSE),
('Kampong Cham', 5.00, FALSE);
```

---

### 3. Category & Filter Tables

#### `categories`
**Purpose**: Main product categories

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| name | VARCHAR(255) | UNIQUE, NOT NULL | Category name |
| slug | VARCHAR(255) | UNIQUE, NOT NULL | URL-friendly slug |
| description | TEXT | NULLABLE | Category description |
| image | VARCHAR(255) | NULLABLE | Category image path |
| sort_order | INT | DEFAULT 0 | Display order |
| is_active | BOOLEAN | DEFAULT TRUE | Active status |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Last update time |

**Indexes**:
```sql
PRIMARY KEY (id)
UNIQUE INDEX idx_categories_slug (slug)
INDEX idx_categories_sort (sort_order)
INDEX idx_categories_active (is_active)
```

---

#### `brands`
**Purpose**: Product brands (Nike, Adidas, etc.)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| name | VARCHAR(255) | UNIQUE, NOT NULL | Brand name |
| slug | VARCHAR(255) | UNIQUE, NOT NULL | URL-friendly slug |
| logo | VARCHAR(255) | NULLABLE | Brand logo path |
| description | TEXT | NULLABLE | Brand description |
| is_active | BOOLEAN | DEFAULT TRUE | Active status |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Last update time |

**Indexes**:
```sql
PRIMARY KEY (id)
UNIQUE INDEX idx_brands_slug (slug)
INDEX idx_brands_active (is_active)
```

---

#### `leagues`
**Purpose**: Football leagues

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| name | VARCHAR(255) | UNIQUE, NOT NULL | League name |
| slug | VARCHAR(255) | UNIQUE, NOT NULL | URL-friendly slug |
| logo | VARCHAR(255) | NULLABLE | League logo path |
| country | VARCHAR(255) | NULLABLE | League country |
| is_active | BOOLEAN | DEFAULT TRUE | Active status |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Last update time |

**Indexes**:
```sql
PRIMARY KEY (id)
UNIQUE INDEX idx_leagues_slug (slug)
INDEX idx_leagues_active (is_active)
```

---

#### `teams`
**Purpose**: Football teams

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| league_id | BIGINT UNSIGNED | FK leagues(id) ON DELETE SET NULL | Parent league |
| name | VARCHAR(255) | UNIQUE, NOT NULL | Team name |
| slug | VARCHAR(255) | UNIQUE, NOT NULL | URL-friendly slug |
| logo | VARCHAR(255) | NULLABLE | Team logo/crest path |
| is_active | BOOLEAN | DEFAULT TRUE | Active status |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Last update time |

**Indexes**:
```sql
PRIMARY KEY (id)
UNIQUE INDEX idx_teams_slug (slug)
INDEX idx_teams_league (league_id)
INDEX idx_teams_active (is_active)
```

**Relationships**:
- `teams.league_id` → `leagues.id` (M:1)
- Teams can exist without league (nullable)

---

#### `surface_types`
**Purpose**: Boot surface types (FG, SG, AG, etc.)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| name | VARCHAR(255) | UNIQUE, NOT NULL | Surface type name |
| slug | VARCHAR(255) | UNIQUE, NOT NULL | URL-friendly slug |
| code | VARCHAR(255) | UNIQUE, NULLABLE | Short code (FG, SG, AG) |
| description | TEXT | NULLABLE | Type description |
| is_active | BOOLEAN | DEFAULT TRUE | Active status |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Last update time |

**Indexes**:
```sql
PRIMARY KEY (id)
UNIQUE INDEX idx_surface_types_slug (slug)
UNIQUE INDEX idx_surface_types_code (code)
INDEX idx_surface_types_active (is_active)
```

**Example Data**:
```sql
INSERT INTO surface_types (name, slug, code, description) VALUES
('Firm Ground', 'firm-ground', 'FG', 'For natural grass pitches'),
('Soft Ground', 'soft-ground', 'SG', 'For wet, muddy pitches'),
('Artificial Grass', 'artificial-grass', 'AG', 'For synthetic turf'),
('Indoor Court', 'indoor-court', 'IC', 'For indoor surfaces'),
('Turf', 'turf', 'TF', 'For artificial turf');
```

---

#### `category_filters`
**Purpose**: Defines which filters apply to which categories

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| category_id | BIGINT UNSIGNED | FK categories(id) ON DELETE CASCADE | Parent category |
| filter_type | ENUM(...) | NOT NULL | Filter type identifier |
| is_required | BOOLEAN | DEFAULT FALSE | Required when creating product |
| sort_order | INT | DEFAULT 0 | Display order |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Last update time |

**ENUM Values for filter_type**:
- `'brand'`
- `'league'`
- `'team'`
- `'surface_type'`

**Indexes**:
```sql
PRIMARY KEY (id)
UNIQUE INDEX idx_category_filters_unique (category_id, filter_type)
```

**Example Configuration**:
```sql
-- Football Boots category filters
INSERT INTO category_filters (category_id, filter_type, is_required, sort_order) VALUES
(1, 'brand', TRUE, 1),
(1, 'surface_type', TRUE, 2);

-- Jerseys category filters
INSERT INTO category_filters (category_id, filter_type, is_required, sort_order) VALUES
(2, 'brand', TRUE, 1),
(2, 'league', FALSE, 2),
(2, 'team', FALSE, 3);
```

---

### 4. Product Tables

#### `products`
**Purpose**: Core product information

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| category_id | BIGINT UNSIGNED | FK categories(id) ON DELETE RESTRICT | Product category |
| name | VARCHAR(255) | NOT NULL | Product name |
| slug | VARCHAR(255) | UNIQUE, NOT NULL | URL-friendly slug |
| description | TEXT | NULLABLE | Product description |
| features | JSON | NULLABLE | Product features/specs |
| base_price | DECIMAL(10,2) | NOT NULL | Base price (before variant adjustments) |
| featured_image | VARCHAR(255) | NULLABLE | Main display image path |
| is_featured | BOOLEAN | DEFAULT FALSE | Featured on homepage |
| is_active | BOOLEAN | DEFAULT TRUE | Active status |
| view_count | BIGINT | DEFAULT 0 | Page view counter |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Last update time |

**Indexes**:
```sql
PRIMARY KEY (id)
UNIQUE INDEX idx_products_slug (slug)
INDEX idx_products_category (category_id)
INDEX idx_products_featured (is_featured, is_active)
INDEX idx_products_views (view_count)
```

**Design Notes**:
- Each color/design is a SEPARATE product
- Example: "Nike Mercurial Blue" and "Nike Mercurial Red" are 2 products
- `featured_image` is primary display image
- Additional images stored in `product_images` table

---

#### `product_images`
**Purpose**: Additional product images (gallery)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| product_id | BIGINT UNSIGNED | FK products(id) ON DELETE CASCADE | Parent product |
| image_path | VARCHAR(255) | NOT NULL | Image file path |
| alt_text | VARCHAR(255) | NULLABLE | Alt text for accessibility |
| sort_order | INT | DEFAULT 0 | Display order in gallery |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Last update time |

**Indexes**:
```sql
PRIMARY KEY (id)
INDEX idx_product_images_product_sort (product_id, sort_order)
```

---

#### `product_variants`
**Purpose**: Product size variants with stock tracking

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| product_id | BIGINT UNSIGNED | FK products(id) ON DELETE CASCADE | Parent product |
| sku | VARCHAR(255) | UNIQUE, NOT NULL | Stock Keeping Unit |
| size | VARCHAR(255) | NOT NULL | Variant size (S, M, L, 39, 40, etc.) |
| price_adjustment | DECIMAL(8,2) | DEFAULT 0.00 | +/- from base_price |
| stock_quantity | INT | DEFAULT 0 | Available stock |
| low_stock_threshold | INT | DEFAULT 5 | Alert threshold |
| is_active | BOOLEAN | DEFAULT TRUE | Active status |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Last update time |

**Indexes**:
```sql
PRIMARY KEY (id)
UNIQUE INDEX idx_product_variants_sku (sku)
INDEX idx_product_variants_product (product_id)
UNIQUE INDEX idx_product_variants_product_size (product_id, size)
INDEX idx_product_variants_stock (stock_quantity)
```

**Price Calculation**:
```
Actual Price = products.base_price + product_variants.price_adjustment
```

**Example**:
```sql
-- Product: Nike Mercurial Vapor 15 FG Blue (base_price: $120)
INSERT INTO product_variants (product_id, sku, size, price_adjustment, stock_quantity) VALUES
(1, 'NIKE-MER15-FG-BLUE-39', '39', 0.00, 5),   -- $120
(1, 'NIKE-MER15-FG-BLUE-40', '40', 0.00, 8),   -- $120
(1, 'NIKE-MER15-FG-BLUE-41', '41', 0.00, 12),  -- $120
(1, 'NIKE-MER15-FG-BLUE-42', '42', 0.00, 15),  -- $120
(1, 'NIKE-MER15-FG-BLUE-43', '43', 0.00, 10),  -- $120
(1, 'NIKE-MER15-FG-BLUE-44', '44', 0.00, 6),   -- $120
(1, 'NIKE-MER15-FG-BLUE-45', '45', 5.00, 4);   -- $125 (larger size)
```

---

#### Pivot Tables for Dynamic Filtering

##### `product_brand`
**Purpose**: Links products to brands (M:M)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| product_id | BIGINT UNSIGNED | FK products(id) ON DELETE CASCADE | Product |
| brand_id | BIGINT UNSIGNED | FK brands(id) ON DELETE CASCADE | Brand |

**Indexes**:
```sql
PRIMARY KEY (id)
UNIQUE INDEX idx_product_brand_unique (product_id, brand_id)
INDEX idx_product_brand_reverse (brand_id)
```

---

##### `product_league`
**Purpose**: Links products to leagues (M:M)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| product_id | BIGINT UNSIGNED | FK products(id) ON DELETE CASCADE | Product |
| league_id | BIGINT UNSIGNED | FK leagues(id) ON DELETE CASCADE | League |

**Indexes**:
```sql
PRIMARY KEY (id)
UNIQUE INDEX idx_product_league_unique (product_id, league_id)
INDEX idx_product_league_reverse (league_id)
```

---

##### `product_team`
**Purpose**: Links products to teams (M:M)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| product_id | BIGINT UNSIGNED | FK products(id) ON DELETE CASCADE | Product |
| team_id | BIGINT UNSIGNED | FK teams(id) ON DELETE CASCADE | Team |

**Indexes**:
```sql
PRIMARY KEY (id)
UNIQUE INDEX idx_product_team_unique (product_id, team_id)
INDEX idx_product_team_reverse (team_id)
```

---

##### `product_surface_type`
**Purpose**: Links products to surface types (M:M)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| product_id | BIGINT UNSIGNED | FK products(id) ON DELETE CASCADE | Product |
| surface_type_id | BIGINT UNSIGNED | FK surface_types(id) ON DELETE CASCADE | Surface Type |

**Indexes**:
```sql
PRIMARY KEY (id)
UNIQUE INDEX idx_product_surface_unique (product_id, surface_type_id)
INDEX idx_product_surface_reverse (surface_type_id)
```

---

#### `product_discounts`
**Purpose**: Product-specific discounts (event/seasonal)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| product_id | BIGINT UNSIGNED | FK products(id) ON DELETE CASCADE | Target product |
| discount_type | ENUM('percentage','fixed_amount') | NOT NULL | Discount type |
| discount_value | DECIMAL(8,2) | NOT NULL | Discount amount or % |
| start_date | TIMESTAMP | NULLABLE | Start date |
| end_date | TIMESTAMP | NULLABLE | End date |
| is_active | BOOLEAN | DEFAULT TRUE | Active status |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Last update time |

**Indexes**:
```sql
PRIMARY KEY (id)
INDEX idx_product_discounts_product (product_id)
INDEX idx_product_discounts_active (start_date, end_date, is_active)
```

---

### 5. Cart Tables

#### `carts`
**Purpose**: Shopping carts (guest + user)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | FK users(id) ON DELETE CASCADE, NULLABLE | Cart owner (null for guest) |
| session_id | VARCHAR(255) | UNIQUE, NULLABLE | Laravel session ID (for guest) |
| merged_at | TIMESTAMP | NULLABLE | When guest cart merged to user cart |
| expires_at | TIMESTAMP | NULLABLE | Guest cart expiration |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Last update time |

**Indexes**:
```sql
PRIMARY KEY (id)
UNIQUE INDEX idx_carts_user (user_id)
UNIQUE INDEX idx_carts_session (session_id)
INDEX idx_carts_expires (expires_at)
```

**Business Logic**:
- **Guest cart**: `user_id = NULL`, `session_id` = Laravel session
- **User cart**: `user_id` set, `session_id = NULL`
- On login: merge guest cart → set `merged_at`

---

#### `cart_items`
**Purpose**: Items in carts

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| cart_id | BIGINT UNSIGNED | FK carts(id) ON DELETE CASCADE | Parent cart |
| product_variant_id | BIGINT UNSIGNED | FK product_variants(id) ON DELETE CASCADE | Product variant |
| quantity | INT | DEFAULT 1 | Item quantity |
| price_snapshot | DECIMAL(10,2) | NOT NULL | Price at time of add |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Last update time |

**Indexes**:
```sql
PRIMARY KEY (id)
INDEX idx_cart_items_cart (cart_id)
UNIQUE INDEX idx_cart_items_unique (cart_id, product_variant_id)
```

**Business Rules**:
- `price_snapshot` prevents issues if product price changes
- Validate `quantity <= product_variants.stock_quantity` before checkout

---

### 6. Order Tables

#### `orders`
**Purpose**: Customer orders

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| order_number | VARCHAR(255) | UNIQUE, NOT NULL | Human-readable order ID |
| user_id | BIGINT UNSIGNED | FK users(id) ON DELETE SET NULL, NULLABLE | Customer |
| **Shipping Info (Snapshot)** | | | |
| recipient_name | VARCHAR(255) | NOT NULL | Recipient name |
| recipient_phone | VARCHAR(255) | NOT NULL | Contact phone |
| shipping_address | TEXT | NOT NULL | Full address |
| province_id | BIGINT UNSIGNED | FK provinces(id), NULLABLE | Delivery province |
| province_name | VARCHAR(255) | NOT NULL | Province name (snapshot) |
| shipping_fee | DECIMAL(8,2) | NOT NULL | Shipping cost |
| **Payment Info** | | | |
| payment_method | ENUM('cod','khqr') | NOT NULL | Payment method |
| payment_status | ENUM('pending','paid','failed') | DEFAULT 'pending' | Payment status |
| payment_proof | VARCHAR(255) | NULLABLE | KHQR screenshot path |
| **Pricing** | | | |
| subtotal | DECIMAL(10,2) | NOT NULL | Sum before discounts |
| discount_amount | DECIMAL(10,2) | DEFAULT 0.00 | Total discount |
| discount_source | VARCHAR(255) | NULLABLE | Source of discount |
| total | DECIMAL(10,2) | NOT NULL | Final amount |
| **Order Status** | | | |
| status | ENUM('pending','confirmed','processing','shipped','delivered') | NOT NULL | Order status |
| **Timestamps** | | | |
| confirmed_at | TIMESTAMP | NULLABLE | When confirmed |
| shipped_at | TIMESTAMP | NULLABLE | When shipped |
| delivered_at | TIMESTAMP | NULLABLE | When delivered |
| created_at | TIMESTAMP | NOT NULL | Order placement time |
| updated_at | TIMESTAMP | NOT NULL | Last update time |

**Indexes**:
```sql
PRIMARY KEY (id)
UNIQUE INDEX idx_orders_number (order_number)
INDEX idx_orders_user (user_id)
INDEX idx_orders_status (status)
INDEX idx_orders_created (created_at)
INDEX idx_orders_phone (recipient_phone)
```

**Business Rules**:
- `order_number` format: "ORD-2026-00001"
- `discount_source` examples: "coupon: NEWYEAR2026", "loyalty: gold"
- `total` = `subtotal - discount_amount + shipping_fee`
- Shipping/address info denormalized (snapshot) to preserve history

---

#### `order_items`
**Purpose**: Individual items in orders

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| order_id | BIGINT UNSIGNED | FK orders(id) ON DELETE CASCADE | Parent order |
| product_id | BIGINT UNSIGNED | FK products(id) ON DELETE SET NULL, NULLABLE | Product reference |
| product_variant_id | BIGINT UNSIGNED | FK product_variants(id) ON DELETE SET NULL, NULLABLE | Variant reference |
| **Product Snapshot** | | | |
| product_name | VARCHAR(255) | NOT NULL | Product name |
| variant_sku | VARCHAR(255) | NOT NULL | Variant SKU |
| variant_color | VARCHAR(255) | NULLABLE | Color (if applicable) |
| variant_size | VARCHAR(255) | NOT NULL | Size |
| **Pricing Snapshot** | | | |
| unit_price | DECIMAL(10,2) | NOT NULL | Price at order time |
| quantity | INT | NOT NULL | Quantity ordered |
| subtotal | DECIMAL(10,2) | NOT NULL | unit_price × quantity |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Last update time |

**Indexes**:
```sql
PRIMARY KEY (id)
INDEX idx_order_items_order (order_id)
INDEX idx_order_items_product (product_id)
INDEX idx_order_items_variant (product_variant_id)
```

**Business Rules**:
- All product details snapshotted to preserve history
- `subtotal` = `unit_price` × `quantity`

---

#### `order_status_history`
**Purpose**: Audit trail of order status changes

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| order_id | BIGINT UNSIGNED | FK orders(id) ON DELETE CASCADE | Parent order |
| status | ENUM('pending','confirmed','processing','shipped','delivered') | NOT NULL | New status |
| notes | TEXT | NULLABLE | Optional notes |
| changed_by | BIGINT UNSIGNED | NULLABLE | User ID who changed status |
| created_at | TIMESTAMP | NOT NULL | Change timestamp |

**Indexes**:
```sql
PRIMARY KEY (id)
INDEX idx_order_status_history_order_created (order_id, created_at)
```

---

### 7. Discount & Coupon Tables

#### `coupons`
**Purpose**: Admin-generated discount coupons

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| code | VARCHAR(255) | UNIQUE, NOT NULL | Coupon code |
| discount_type | ENUM('percentage','fixed_amount') | NOT NULL | Discount type |
| discount_value | DECIMAL(8,2) | NOT NULL | Discount amount or % |
| min_purchase_amount | DECIMAL(10,2) | NULLABLE | Minimum order value |
| max_discount_amount | DECIMAL(10,2) | NULLABLE | Max discount cap |
| usage_limit | INT | NULLABLE | Total usage limit |
| used_count | INT | DEFAULT 0 | Times used |
| start_date | TIMESTAMP | NULLABLE | Start date |
| end_date | TIMESTAMP | NULLABLE | End date |
| is_active | BOOLEAN | DEFAULT TRUE | Active status |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Last update time |

**Indexes**:
```sql
PRIMARY KEY (id)
UNIQUE INDEX idx_coupons_code (code)
INDEX idx_coupons_validity (start_date, end_date, is_active)
```

**Validation Rules**:
- `used_count < usage_limit` (if limit set)
- `NOW() BETWEEN start_date AND end_date` (if dates set)
- `is_active = TRUE`
- `order.subtotal >= min_purchase_amount` (if set)

---

### 8. Review & Wishlist Tables

#### `product_reviews`
**Purpose**: Customer product reviews

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| product_id | BIGINT UNSIGNED | FK products(id) ON DELETE CASCADE | Reviewed product |
| user_id | BIGINT UNSIGNED | FK users(id) ON DELETE CASCADE | Reviewer |
| order_item_id | BIGINT UNSIGNED | FK order_items(id) ON DELETE SET NULL, NULLABLE | Purchased item |
| rating | TINYINT | NOT NULL | Star rating (1-5) |
| title | VARCHAR(255) | NULLABLE | Review title |
| comment | TEXT | NULLABLE | Review comment |
| is_verified_purchase | BOOLEAN | DEFAULT FALSE | Verified purchase flag |
| is_approved | BOOLEAN | DEFAULT TRUE | Admin approval status |
| helpful_count | INT | DEFAULT 0 | "Helpful" vote count |
| created_at | TIMESTAMP | NOT NULL | Review timestamp |
| updated_at | TIMESTAMP | NOT NULL | Last update time |

**Indexes**:
```sql
PRIMARY KEY (id)
UNIQUE INDEX idx_product_reviews_unique (product_id, user_id)
INDEX idx_product_reviews_product_approved (product_id, is_approved)
INDEX idx_product_reviews_order_item (order_item_id)
```

**Business Rules**:
- `is_verified_purchase = TRUE` only if `order_item_id IS NOT NULL`
- Users can review only purchased products
- `rating` must be between 1 and 5

---

#### `review_images`
**Purpose**: Review image uploads

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| product_review_id | BIGINT UNSIGNED | FK product_reviews(id) ON DELETE CASCADE | Parent review |
| image_path | VARCHAR(255) | NOT NULL | Image file path |
| sort_order | INT | DEFAULT 0 | Display order |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Last update time |

**Indexes**:
```sql
PRIMARY KEY (id)
INDEX idx_review_images_review_sort (product_review_id, sort_order)
```

---

#### `wishlists`
**Purpose**: User wishlist items

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | FK users(id) ON DELETE CASCADE | User |
| product_id | BIGINT UNSIGNED | FK products(id) ON DELETE CASCADE | Wishlisted product |
| created_at | TIMESTAMP | NOT NULL | Added to wishlist time |

**Indexes**:
```sql
PRIMARY KEY (id)
UNIQUE INDEX idx_wishlists_unique (user_id, product_id)
INDEX idx_wishlists_user (user_id)
```

---

## Migration Execution Order

**CRITICAL**: Migrations must be run in this exact order to avoid foreign key errors.

1. ✅ Install Spatie Permission package
   ```bash
   composer require spatie/laravel-permission
   php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
   ```

2. ✅ Extend `users` table (add loyalty fields)
3. ✅ `provinces`
4. ✅ `addresses`
5. ✅ `categories`
6. ✅ `brands`
7. ✅ `leagues`
8. ✅ `teams`
9. ✅ `surface_types`
10. ✅ `category_filters`
11. ✅ `products`
12. ✅ `product_images`
13. ✅ `product_variants`
14. ✅ `product_brand` (pivot)
15. ✅ `product_league` (pivot)
16. ✅ `product_team` (pivot)
17. ✅ `product_surface_type` (pivot)
18. ✅ `product_discounts`
19. ✅ `carts`
20. ✅ `cart_items`
21. ✅ `orders`
22. ✅ `order_items`
23. ✅ `order_status_history`
24. ✅ `coupons`
25. ✅ `product_reviews`
26. ✅ `review_images`
27. ✅ `wishlists`

**Total**: 27 custom migrations + 5 Spatie migrations = **32 migrations**

---

## Eloquent Model Relationships

### User Model
```php
class User extends Authenticatable
{
    use HasRoles; // Spatie trait

    public function addresses() { return $this->hasMany(Address::class); }
    public function cart() { return $this->hasOne(Cart::class); }
    public function orders() { return $this->hasMany(Order::class); }
    public function reviews() { return $this->hasMany(ProductReview::class); }
    public function wishlist() { return $this->belongsToMany(Product::class, 'wishlists'); }
}
```

### Product Model
```php
class Product extends Model
{
    public function category() { return $this->belongsTo(Category::class); }
    public function images() { return $this->hasMany(ProductImage::class); }
    public function variants() { return $this->hasMany(ProductVariant::class); }
    public function discounts() { return $this->hasMany(ProductDiscount::class); }
    public function brands() { return $this->belongsToMany(Brand::class, 'product_brand'); }
    public function leagues() { return $this->belongsToMany(League::class, 'product_league'); }
    public function teams() { return $this->belongsToMany(Team::class, 'product_team'); }
    public function surfaceTypes() { return $this->belongsToMany(SurfaceType::class, 'product_surface_type'); }
    public function reviews() { return $this->hasMany(ProductReview::class); }
}
```

### Order Model
```php
class Order extends Model
{
    public function user() { return $this->belongsTo(User::class); }
    public function province() { return $this->belongsTo(Province::class); }
    public function items() { return $this->hasMany(OrderItem::class); }
    public function statusHistory() { return $this->hasMany(OrderStatusHistory::class); }
}
```

---

## Performance Optimization

### Query Optimization Strategies

1. **Eager Loading** for N+1 prevention:
   ```php
   Product::with(['category', 'brands', 'variants', 'images'])->get();
   ```

2. **Index Usage** for filtering:
   ```sql
   -- All indexes listed in table specifications above
   -- Critical for dynamic filtering performance
   ```

3. **Partial Indexes** (MySQL 8.0+):
   ```sql
   CREATE INDEX idx_products_active_featured
   ON products (is_active, is_featured)
   WHERE is_active = TRUE;
   ```

4. **Composite Indexes** for common query patterns:
   ```sql
   INDEX idx_products_category_active (category_id, is_active)
   INDEX idx_orders_user_created (user_id, created_at)
   ```

---

## Data Integrity Rules

### Cascade Behaviors

| Parent → Child | On Delete | Rationale |
|----------------|-----------|-----------|
| users → addresses | CASCADE | Remove user's addresses |
| users → cart | CASCADE | Remove user's cart |
| users → orders | SET NULL | Preserve order history |
| products → product_variants | CASCADE | Variants are part of product |
| products → product_images | CASCADE | Images belong to product |
| orders → order_items | CASCADE | Items are part of order |
| categories → products | RESTRICT | Prevent accidental deletion |

### Constraints

1. **Unique Constraints**:
   - Email, phone in users
   - Slugs in all filterable entities
   - SKU in product_variants
   - Order number in orders

2. **Check Constraints** (enforce at application level):
   - `rating BETWEEN 1 AND 5` (product_reviews)
   - `stock_quantity >= 0` (product_variants)
   - `discount_value > 0` (coupons, product_discounts)

---

## Backup & Maintenance

### Recommended Backup Strategy
- **Full Backup**: Daily at 2 AM
- **Incremental**: Every 6 hours
- **Retention**: 30 days
- **Test Restore**: Weekly

### Cleanup Queries

```sql
-- Delete expired guest carts (run daily)
DELETE FROM carts
WHERE expires_at < NOW() AND user_id IS NULL;

-- Archive old orders (after 2 years)
INSERT INTO orders_archive SELECT * FROM orders
WHERE created_at < DATE_SUB(NOW(), INTERVAL 2 YEAR);
```

---

## Scalability Considerations

### Horizontal Scaling
- **Read Replicas**: For product catalog queries
- **Write Master**: For orders, cart operations
- **Partitioning**: Orders table by created_at (yearly partitions)

### Caching Strategy
- **Product Catalog**: Cache category/brand/league filters (1 hour TTL)
- **Product Details**: Cache product with variants (30 min TTL)
- **User Cart**: Session-based cache

### Archive Strategy
- **Orders**: Move orders older than 2 years to archive table
- **Logs**: Truncate status_history older than 1 year
- **Reviews**: Soft delete flagged reviews after resolution

---

## Conclusion

This database schema provides a robust, scalable foundation for the Sport Gear E-Commerce platform with:
- ✅ **Normalized structure** for data integrity
- ✅ **Strategic indexes** for query performance
- ✅ **Flexible filtering** via pivot tables
- ✅ **Data preservation** through snapshots
- ✅ **Audit trails** for order lifecycle
- ✅ **Scalability** through proper design patterns

**Total Schema**: 32 tables, 100+ columns, comprehensive relationships

For implementation details, see project migrations in `database/migrations/`.
