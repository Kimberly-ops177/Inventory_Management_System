# Inventory Management System

A complete PHP-based inventory management system for local SMEs to streamline stock management, track inventory levels in real-time, generate reports, manage suppliers, and set low-stock alerts.

## Tech Stack

- **Backend:** PHP 8.1+
- **Database:** MySQL 5.7+
- **Frontend:** Bootstrap 5, JavaScript
- **Architecture:** MVC pattern with REST API

## Features

✅ **User Authentication** - Secure login/logout with session management
✅ **Dashboard** - Real-time metrics and inventory overview
✅ **Product Management** - Full CRUD operations for products
✅ **Category Management** - Organize products by categories
✅ **Supplier Management** - Track supplier information
✅ **Purchase Orders** - Create and receive purchase orders
✅ **Sales Orders** - Create and fulfill sales orders
✅ **Stock Tracking** - Real-time stock levels with movement history
✅ **Low-Stock Alerts** - Automatic alerts when stock falls below reorder level
✅ **Reports** - Stock reports, sales reports, purchase reports
✅ **Audit Logging** - Track all user actions
✅ **REST API** - Complete API for all operations

## Installation

### Prerequisites

- PHP 8.1 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Composer
- Web server (Apache/Nginx) or PHP built-in server

### Setup Steps

1. **Install Dependencies**
```bash
composer install
```

2. **Configure Environment**
```bash
# Copy .env.example to .env
cp .env.example .env

# Edit .env with your database credentials
# Update DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD
```

3. **Run Database Migration**
```bash
php database/migrate.php
```

4. **Seed Sample Data** (Optional)
```bash
php database/seed.php
```

This creates:
- Admin user (email: admin@example.com, password: password)
- Sample categories, suppliers, and products
- Initial stock levels

5. **Start PHP Development Server**
```bash
php -S localhost:8000 -t public
```

6. **Access the Application**
```
Open browser: http://localhost:8000
Login: admin@example.com / password
```

## Project Structure

```
Inventory Management System/
├── database/
│   ├── migrations/
│   │   └── 0001_init.sql          # Database schema
│   ├── migrate.php                # Migration runner
│   └── seed.php                   # Sample data seeder
├── public/
│   ├── css/
│   │   └── app.css                # Custom styles
│   ├── js/
│   │   └── app.js                 # Frontend JavaScript
│   └── index.php                  # Entry point
├── src/
│   ├── Controllers/
│   │   ├── Api/                   # REST API controllers
│   │   │   ├── ProductController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── SupplierController.php
│   │   │   ├── PurchaseOrderController.php
│   │   │   ├── SalesOrderController.php
│   │   │   ├── StockController.php
│   │   │   └── DashboardController.php
│   │   ├── AuthController.php     # Authentication
│   │   └── DashboardController.php # Web dashboard
│   ├── Http/
│   │   ├── Request.php            # HTTP request handling
│   │   ├── Response.php           # HTTP responses
│   │   └── JsonResponse.php       # JSON API responses
│   ├── Middleware/
│   │   ├── Middleware.php         # Interface
│   │   ├── AuthMiddleware.php     # Auth check
│   │   ├── JsonMiddleware.php     # JSON content-type
│   │   └── RoleMiddleware.php     # Role-based access
│   ├── Models/
│   │   ├── Model.php              # Base model (Active Record)
│   │   ├── Role.php
│   │   ├── User.php
│   │   ├── Category.php
│   │   ├── Supplier.php
│   │   ├── Product.php
│   │   ├── PurchaseOrder.php
│   │   ├── PurchaseOrderItem.php
│   │   ├── SalesOrder.php
│   │   ├── SalesOrderItem.php
│   │   ├── StockMovement.php
│   │   ├── StockAlert.php
│   │   └── AuditLog.php
│   ├── Services/
│   │   ├── StockService.php       # Inventory calculations
│   │   ├── StockAlertService.php  # Alert management
│   │   ├── ReportService.php      # Report generation
│   │   └── AuditLogger.php        # Activity logging
│   ├── Auth.php                   # Authentication logic
│   ├── Database.php               # PDO connection
│   ├── Router.php                 # HTTP routing
│   ├── View.php                   # Template rendering
│   ├── Validator.php              # Input validation
│   ├── Flash.php                  # Flash messages
│   └── bootstrap.php              # App initialization
├── views/
│   ├── layouts/
│   │   ├── app.php                # Main layout
│   │   └── auth.php               # Login layout
│   ├── partials/
│   │   ├── navbar.php             # Navigation bar
│   │   ├── sidebar.php            # Sidebar menu
│   │   └── alerts.php             # Flash messages
│   ├── auth/
│   │   └── login.php              # Login page
│   └── dashboard/
│       └── index.php              # Dashboard view
├── vendor/                         # Composer dependencies
├── .env                           # Environment config
├── .env.example                   # Example env file
├── composer.json                  # PHP dependencies
└── README.md                      # This file
```

## API Endpoints

### Authentication
- `POST /login` - User login
- `POST /logout` - User logout

### Products
- `GET /api/products` - List all products
- `GET /api/products/:id` - Get product details
- `POST /api/products` - Create product
- `PUT /api/products/:id` - Update product
- `DELETE /api/products/:id` - Delete product
- `GET /api/products/:id/stock` - Get stock history

### Categories
- `GET /api/categories` - List all categories
- `GET /api/categories/:id` - Get category details
- `POST /api/categories` - Create category
- `PUT /api/categories/:id` - Update category
- `DELETE /api/categories/:id` - Delete category

### Suppliers
- `GET /api/suppliers` - List all suppliers
- `GET /api/suppliers/:id` - Get supplier details
- `POST /api/suppliers` - Create supplier
- `PUT /api/suppliers/:id` - Update supplier
- `DELETE /api/suppliers/:id` - Delete supplier

### Purchase Orders
- `GET /api/purchase-orders` - List all purchase orders
- `GET /api/purchase-orders/:id` - Get PO details
- `POST /api/purchase-orders` - Create purchase order
- `PUT /api/purchase-orders/:id` - Update purchase order
- `DELETE /api/purchase-orders/:id` - Delete purchase order
- `POST /api/purchase-orders/:id/receive` - Receive purchase order

### Sales Orders
- `GET /api/sales-orders` - List all sales orders
- `GET /api/sales-orders/:id` - Get SO details
- `POST /api/sales-orders` - Create sales order
- `PUT /api/sales-orders/:id` - Update sales order
- `DELETE /api/sales-orders/:id` - Delete sales order
- `POST /api/sales-orders/:id/fulfill` - Fulfill sales order

### Stock Management
- `GET /api/stock/movements` - List stock movements
- `GET /api/stock/alerts` - List active stock alerts
- `POST /api/stock/alerts/:id/resolve` - Resolve alert
- `POST /api/stock/check-alerts` - Manually check all products for alerts

### Dashboard
- `GET /api/dashboard/stats` - Get dashboard statistics

## Usage Examples

### Creating a Product (API)
```bash
curl -X POST http://localhost:8000/api/products \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Laptop",
    "sku": "ELEC-003",
    "category_id": 1,
    "cost_price": 500,
    "sale_price": 899,
    "reorder_level": 5,
    "unit": "pcs"
  }'
```

### Creating a Purchase Order (API)
```bash
curl -X POST http://localhost:8000/api/purchase-orders \
  -H "Content-Type: application/json" \
  -d '{
    "supplier_id": 1,
    "reference": "PO-001",
    "items": [
      {"product_id": 1, "quantity": 20, "unit_cost": 15.00}
    ]
  }'
```

### Receiving a Purchase Order (Stock In)
```bash
curl -X POST http://localhost:8000/api/purchase-orders/1/receive \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
      {"id": 1, "received_quantity": 20}
    ]
  }'
```

### Creating a Sales Order (API)
```bash
curl -X POST http://localhost:8000/api/sales-orders \
  -H "Content-Type: application/json" \
  -d '{
    "customer_name": "John Doe",
    "reference": "SO-001",
    "items": [
      {"product_id": 1, "quantity": 5, "unit_price": 29.99}
    ]
  }'
```

### Fulfilling a Sales Order (Stock Out)
```bash
curl -X POST http://localhost:8000/api/sales-orders/1/fulfill
```

## Key Features Explained

### Stock Management
- **Real-time Calculation:** Stock levels are calculated in real-time from stock_movements table
- **Audit Trail:** Complete history of all stock in/out transactions
- **Source Tracking:** Every movement records its source (purchase_order, sales_order, etc.)

### Low-Stock Alerts
- **Automatic:** Triggered when stock falls below reorder_level
- **Manual Check:** Run `/api/stock/check-alerts` to check all products
- **Auto-Resolve:** Alerts auto-resolve when stock is replenished

### Validation
- **Server-side:** All inputs validated using Validator class
- **Unique Constraints:** SKU, email uniqueness enforced
- **Stock Validation:** Sales orders check stock availability before creation

### Security
- **Password Hashing:** bcrypt password hashing
- **Session Management:** Secure session-based authentication
- **SQL Injection Prevention:** PDO prepared statements
- **XSS Protection:** Output escaping in views

## 🔄 CI/CD Pipeline

The system includes full **Continuous Integration/Continuous Deployment** pipelines for both **GitHub Actions** and **GitLab CI**.

### Features

**Continuous Integration:**
- ✅ Automated testing (syntax, migrations, health checks)
- ✅ Security scanning (dependency audits)
- ✅ Database migration validation
- ✅ Code quality checks

**Continuous Deployment:**
- ✅ Auto-deploy to staging (`develop` branch)
- ✅ Manual deploy to production (`main` branch)
- ✅ Automated database backups
- ✅ Health monitoring post-deployment
- ✅ Automatic rollback on failure

### Quick Setup

**GitHub Actions:**
```bash
# 1. Add secrets to repository (Settings → Secrets)
STAGING_SSH_KEY, STAGING_HOST, STAGING_USER, STAGING_PATH
PRODUCTION_SSH_KEY, PRODUCTION_HOST, PRODUCTION_USER, PRODUCTION_PATH

# 2. Push to trigger
git push origin develop   # Auto-deploys to staging
git push origin main      # Manual approval for production
```

**GitLab CI:**
```bash
# 1. Add variables (Settings → CI/CD → Variables)
# Same variables as GitHub, mark as Masked/Protected

# 2. Pipeline auto-runs on push
# Manual approval required for production
```

### Workflow
```
Feature → develop → Tests + Auto-deploy to staging
develop → main → Tests + Manual deploy to production (with backup)
```

**Full Documentation:** See [CICD.md](CICD.md) and [CICD-QUICKSTART.md](CICD-QUICKSTART.md)

## 🚀 Production Deployment

### Automated Deployment

The system includes a comprehensive deployment script:

```bash
# Run deployment
./deploy.sh production
```

The script automatically:
- ✅ Validates environment and dependencies
- ✅ Tests database connectivity
- ✅ Creates database backup
- ✅ Installs/updates dependencies
- ✅ Runs pending migrations
- ✅ Sets correct file permissions
- ✅ Runs health checks
- ✅ Restarts services

### Manual Deployment Steps

1. **Web Server Configuration**

**Apache:**
- Point DocumentRoot to `/path/to/inventory/public`
- Enable `mod_rewrite`
- The `.htaccess` file is already configured

**Nginx:**
```bash
# Copy example config
cp nginx.conf.example /etc/nginx/sites-available/inventory

# Edit paths and domain
nano /etc/nginx/sites-available/inventory

# Enable site
ln -s /etc/nginx/sites-available/inventory /etc/nginx/sites-enabled/

# Test and reload
sudo nginx -t
sudo systemctl reload nginx
```

2. **Production Environment**

Update `.env`:
```bash
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE=true  # Requires HTTPS
LOG_LEVEL=error
```

3. **Set Permissions**
```bash
chmod -R 755 storage database
chmod 644 .env
chown -R www-data:www-data storage
```

### Database Backups

**Manual Backup:**
```bash
./backup.sh
```

**Automated Daily Backups:**
```bash
# Add to crontab
crontab -e

# Add this line (daily at 2 AM)
0 2 * * * /path/to/inventory/backup.sh
```

**Restore from Backup:**
```bash
gunzip -c backups/backup_inventory_db_20260210_120000.sql.gz | \
  mysql -u inventory_user -p inventory_db
```

### Health Monitoring

The system includes a health check endpoint:

```bash
# Check system health
curl http://your-domain.com/health
```

Response includes:
- Database connectivity
- Storage writability
- PHP version and extensions
- Migration status
- Session status

### Error Logging

Errors are automatically logged to `storage/logs/error.log`

```bash
# Monitor logs in real-time
tail -f storage/logs/error.log
```

### Security Checklist

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Set `SESSION_SECURE=true` (requires HTTPS)
- [ ] Generate secure `APP_KEY`
- [ ] Use strong database credentials
- [ ] Enable HTTPS with SSL certificate (Let's Encrypt)
- [ ] Set up firewall rules
- [ ] Configure daily backups
- [ ] Monitor error logs
- [ ] Keep `.env` out of version control
- [ ] Restrict database user permissions

### Performance Optimization

1. **Enable OPcache** (php.ini):
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
```

2. **Run Index Migration**:
```bash
# Adds indexes to frequently queried columns
php database/migrate.php
```

3. **Use PHP-FPM** instead of mod_php

4. **Enable Gzip** (already configured in `.htaccess`)

## 🛠️ Maintenance

### Database Optimization

```bash
# Run the index migration for better performance
php database/migrate.php
```

### Clear Logs

```bash
# Clear old logs (older than 30 days)
find storage/logs -name "*.log" -mtime +30 -delete
```

### Check System Health

```bash
# Via command line
php -r "require 'vendor/autoload.php'; require 'src/bootstrap.php'; \$h = new App\Controllers\HealthController(); echo json_encode(\$h->check(), JSON_PRETTY_PRINT);"

# Via web
curl http://localhost:8000/health
```

## Next Steps to Extend

The system is production-ready and includes:

✅ Complete web interface (all CRUD pages)
✅ REST API with full documentation
✅ Automated deployment scripts
✅ Database backup system
✅ Health monitoring endpoint
✅ Global error handling & logging
✅ Security headers and protection
✅ Performance optimizations

Optional enhancements:
- Email notifications for alerts
- PDF export for reports
- Barcode scanning integration
- API token authentication
- Multi-warehouse support
- Advanced reporting dashboards

## License

MIT License

## Support

For issues or questions, please open an issue on the project repository.
