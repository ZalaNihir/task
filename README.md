# Inventory Management

## Setup Instructions

### Requirements

-   PHP 8.1 or higher.
-   Composer.
-   MySQL 8 or higher.
-   Redis Server.
-   PHP Redis extension.
-   Node.js and npm (only if frontend assets need to be built).

### 1. Clone the Repository

``` bash
git clone https://github.com/ZalaNihir/task
cd task
```

### 2. Install PHP Dependencies

``` bash
composer install
```

### 3. Create the Environment File

``` bash
cp .env.example .env
```

### 4. Generate the Application Key

``` bash
php artisan key:generate
```

### 5. Configure the Database

Update the database credentials in the `.env` file according to your
local MySQL configuration.

### 6. Install and Configure Redis on Linux

This project uses Redis for application caching. Redis Server and the
PHP Redis extension are separate components. Both must be installed and
available to the PHP version used to run Laravel.

#### Install Redis Server

Update the package list:

``` bash
sudo apt update
```

Install Redis:

``` bash
sudo apt install redis-server
```

Start the Redis service:

``` bash
sudo systemctl start redis-server
```

Enable Redis to start automatically when the system boots:

``` bash
sudo systemctl enable redis-server
```

Check the Redis service status:

``` bash
sudo systemctl status redis-server
```

Test the Redis server:

``` bash
redis-cli ping
```

Expected output:

``` text
PONG
```

#### Install the PHP Redis Extension

Install the PHP Redis extension:

``` bash
sudo apt install php-redis
```

Check whether the extension is enabled for the command-line PHP
installation:

``` bash
php -m | grep -i redis
```

Expected output:

``` text
redis
```

If the extension is installed but not enabled, enable it:

``` bash
sudo phpenmod redis
```

Check the PHP version used by the terminal:

``` bash
php -v
```

If you use PHP-FPM, restart the matching PHP-FPM service after
installing or enabling the extension. Replace `8.3` with your installed
PHP version:

``` bash
sudo systemctl restart php8.3-fpm
```

If you run the application using Laravel's built-in development server
(`php artisan serve`), stop and start the server again so it uses the
updated PHP environment. PHP-FPM is not required just to use
`php artisan serve`.

#### Configure Laravel to Use Redis

Update the Redis and cache settings in `.env`:

``` env
CACHE_STORE=redis

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

For Laravel versions/configurations that use `CACHE_DRIVER` instead of
`CACHE_STORE`, set the cache variable expected by that application.

Clear cached Laravel configuration:

``` bash
php artisan optimize:clear
```

**Important:** Installing Redis Server alone is not sufficient when
Laravel is configured to use the `phpredis` client. The PHP Redis
extension must also be installed and enabled for the PHP runtime running
the application.

### 7. Run Migrations and Seed the Database

``` bash
php artisan migrate --seed
```

### 8. Start the Laravel Development Server

``` bash
php artisan serve
```

The API will be available at:

``` text
http://127.0.0.1:8000/api
```
## Notes
Stock adjustments are handled using a database transaction.

Before updating stock, the corresponding product-warehouse record is
fetched using `lockForUpdate()`. This ensures that concurrent requests
for the same stock record are processed safely.

The application validates available quantity before allowing stock-out
and reservation operations.

Every successful adjustment updates the `stocks` table and creates a
corresponding record in `stock_movements`.

If any operation fails, the transaction is rolled back, so the stock and
movement history remain consistent.

I used database-level aggregation because the report needs total stock
and reserved quantities across multiple warehouses. Using SUM keeps the
calculation in the database instead of loading all stock records into
PHP. COALESCE ensures products without stock records still return zero
instead of null.

The highest-stock warehouse is also retrieved as part of the database
query.

Whenever a stock adjustment is successfully completed, I invalidate the
inventory summary cache. I do this after the database transaction
commits, so failed stock adjustments don't unnecessarily clear the
cache. The next summary request fetches fresh data and caches it again.

Movement history is retrieved for a specific product and supports
optional date filters.

The movement records are paginated, with a default page size of 20. The
associated warehouse relationship is eager loaded to avoid unnecessary
additional queries.

The total units in, total units out, and net movement are calculated
using database-level aggregation rather than loading all movement
records and calculating totals in PHP.

AI assistance (Codex) was used to debug selected errors, prepare the README documentation, and implement filtering functionality in the product module. The generated suggestions were reviewed and adapted during development.

## Caching Strategy

Redis is used as the cache driver for frequently accessed data.

Cache keys should follow a predictable naming convention. The following
convention can be used for the inventory-related endpoints:

  Endpoint            Cache Key Convention
  ------------------- -----------------------------------
  Inventory Summary   `summary`
  Low Stock Alert     `low-stock:{threshold}`
  Category Tree       `tree`

For filtered endpoints, the cache key must include the relevant filter
values so that different requests do not receive the same cached
response.

### Cache TTL

The inventory summary currently uses a TTL of 300 seconds (5 minutes).

The TTL for other endpoints should match their configured values in the
application.

### Cache Invalidation

-   **Inventory Summary:** Invalidated after a successful stock
    adjustment.
-   **Low Stock Alert:** Must be invalidated after a successful stock
    adjustment because available stock may change.
-   **Category Tree:** Must be invalidated whenever category data
    affecting the tree is changed.
-   **Product Listing:** Must be invalidated whenever a product or
    related stock change affects the cached listing.

Cache invalidation should happen only after successful database writes.

Empty result sets and error responses should not be cached.

### Cache Warming

The assessment requires cache warming for at least one endpoint. The
inventory summary is a suitable candidate because it performs
aggregation across products and warehouses and is expected to be
requested frequently.

Cache warming can be implemented through an Artisan command or scheduled
task that requests the summary and stores the result before users need
it. Document the actual command or schedule here once it is implemented.

## Query Optimization

-   Database-level aggregation is used for inventory reports and
    movement summaries.
-   Eloquent relationships are eager loaded where they are accessed in
    API responses.
-   Query scopes are used for reusable filtering logic.
-   List endpoints use pagination to avoid returning unbounded records.
-   Appropriate database indexes and constraints are used to improve
    query performance and maintain data integrity.
-   Raw SQL is avoided where Laravel's Eloquent or Query Builder can
    express the required logic cleanly.

## Indexing Decisions

Indexes and constraints are added to support frequently used lookups,
filtering, sorting, and relationship queries.

  -----------------------------------------------------------------------
  Table                   Index / Constraint      Purpose
  ----------------------- ----------------------- -----------------------
  `products`              Unique index on `sku`   Prevents duplicate
                                                  product SKUs and
                                                  supports SKU lookups.

  `stocks`                Unique composite index  Ensures one stock
                          on `product_id`,        record per product and
                          `warehouse_id`          warehouse.

  `stock_movements`       Composite index on      Supports movement
                          `product_id`,           history lookups and
                          `warehouse_id`,         date-based filtering.
                          `moved_at`              

  Related tables          Foreign key constraints Maintain valid
                                                  relationships between
                                                  related records.
  -----------------------------------------------------------------------

Add any other indexes from the actual migrations to this table so the
documentation matches the database schema.

## Assumptions

-   Available quantity is calculated as current quantity minus reserved
    quantity.
-   A stock record must already exist for the selected product and
    warehouse before an adjustment can be performed.
-   Stock-out and reservation operations cannot reduce available
    quantity below zero.
-   Reservation and reservation release operations affect reserved
    quantity, not physical stock quantity.
-   Stock movement records are maintained as historical records and are
    not used as a replacement for the current stock balance.
-   Movement history aggregation counts `stock_in` as units in and
    `stock_out` as units out. Reservation operations are not included in
    this physical stock movement total.
-   The highest-stock warehouse is determined using the warehouse with
    the highest current stock quantity for that product.
-   The low-stock threshold is exclusive: a product-warehouse
    combination is returned when available quantity is strictly less
    than the threshold.
