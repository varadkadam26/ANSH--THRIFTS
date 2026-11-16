# ANSH THRIFTS (PHP 8.2+, MySQL / MariaDB & Bootstrap 5)

> **Pre-Loved Fashion, New Stories.**  
> Completely migrated to PHP 8.2+, MySQL / MariaDB, PDO prepared statements, HTML5, CSS3, Bootstrap 5, and JavaScript. Fully optimized for **Hostinger Web Hosting**.

---

## 🌟 Architecture & Features

- **Storefront & Catalog**:
  - `index.php`: Hero banner with vintage thrift imagery, dynamic newly added thrift pieces carousel with batching.
  - `shop.php`: Real-time search, category filtering (Jackets, T-Shirts, Jeans, Shirts, Hoodies), and responsive product card grid.
  - `product.php`: Dedicated single product view with condition notes, responsive gallery, Add to Cart and Buy Now workflows.
  - `cart.php`: Responsive shopping cart with desktop table and mobile card views, dynamic item quantity updates, order summary, and Razorpay / Cash on Delivery checkout with mandatory legal consent.
  - `about.php`: Brand story, circular fashion mission, and sustainability values.
  - `contact.php`: Customer contact form with direct database logging and dual-email notification workflow via Gmail SMTP.

- **Relational MySQL / MariaDB Database**:
  - Fully normalized database schema with primary keys, foreign keys (`ON DELETE CASCADE`), indexes, and constraints.
  - **10 Primary Tables**:
    1. `products` (ID, Name, Slug, Price, Category, Details, Image File, Status, Created At, Updated At)
    2. `product_images` (Image ID, Product ID, Image File, Primary Flag, Sort Order, Created At)
    3. `inventory` (Product ID, SKU, Stock Quantity, Reserved Quantity, Available Quantity, Low Stock Alert, Updated At)
    4. `customers` (Customer ID, Full Name, Email, Phone, Total Orders, Total Spent, First Order Date, Last Active)
    5. `orders` (Order ID, Customer ID, Customer Name, Email, Phone, Total Amount, Payment Method, Payment Status, Razorpay Order ID, Razorpay Payment ID, Shipping Address, Order Status, Created At)
    6. `order_items` (Item ID, Order ID, Product ID, Product Name, Price, Quantity, Created At)
    7. `deliveries` (Delivery ID, Order ID, Recipient Name, Recipient Phone, Shipping Address, Carrier, Tracking Number, Status, Estimated Delivery, Dispatched At, Delivered At, Notes)
    8. `contact_messages` (Message ID, Sender Name, Email, Phone, Subject, Message, Status, Received At, Responded At)
    9. `admins` (Admin ID, Username, Password Hash, Role, Created At, Last Login)
    10. `settings` (Setting Key, Setting Value, Description, Updated At)
  - Pure PDO prepared statements with `PDO::ATTR_EMULATE_PREPARES => false` (prevents SQL injection).
  - Database transactions (`beginTransaction`, `commit`, `rollBack`) for orders and inventory deductions.

- **Admin Management Panel (`/admin`)**:
  - Role-protected dashboard requiring admin authentication (`admin` / `admin123`).
  - Full CRUD operations for Products with instantaneous database updates.
  - Live Inventory and Stock quantity management with overselling prevention.
  - Order status management (`Placed`, `Confirmed`, `Processing`, `Dispatched`, `Delivered`, `Cancelled`).
  - Delivery logistics tracker with automated tracking IDs.
  - Contact messages viewer with response status toggling.
  - Customer directory with lifetime spend tracking.

- **Payment & Security**:
  - Razorpay Online Checkout (UPI, Cards, NetBanking) with HMAC-SHA256 server-side signature verification.
  - Idempotency guard preventing duplicate payment processing and double stock deductions.
  - Cash on Delivery (COD) mode for instant order placement.
  - Dedicated Legal & Policy section covering Indian thrift fashion: Privacy Policy, Refund Policy, Terms & Conditions, Shipping Policy, Cancellation Policy, Payment Policy, Grievance Redressal, and Contact Information.

---

## 🚀 Deployment Instructions for Hostinger Web Hosting

Hostinger supports Apache / LiteSpeed web servers with PHP 8.2+ and MySQL / MariaDB out of the box.

### Step 1: Create Database in Hostinger hPanel
1. Log into your **Hostinger hPanel**.
2. Go to **Databases** → **Management**.
3. Create a new MySQL database:
   - **Database Name**: e.g., `u123456789_ansh`
   - **Username**: e.g., `u123456789_ansh`
   - **Password**: Enter a strong password
4. Note down your Database Name, Username, and Password.

### Step 2: Import Database Schema via phpMyAdmin
1. In Hostinger hPanel, click **Enter phpMyAdmin** next to your newly created database.
2. Click the **Import** tab.
3. Choose the file `database/schema.sql` from your project and click **Import**.
4. Choose the file `database/seed.sql` and click **Import** to seed the catalog items, inventory, settings, and admin user.

### Step 3: Upload Application Files via File Manager or FTP
1. Navigate to **Websites** → Select your domain → **File Manager** (or connect via SFTP / FTP).
2. Open the `public_html/` directory.
3. Upload all project files into `public_html/`.

### Step 4: Configure Environment Variables (`.env`)
1. Create or edit the `.env` file in `public_html/` (or one directory above for added security):
   ```env
   APP_NAME="ANSH THRIFTS"
   APP_TAGLINE="Pre-Loved Fashion, New Stories."
   APP_ENV=production

   # Database Configuration
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=u761076349_ANSH_THRIFTS
   DB_USERNAME=u761076349_ANSH
   DB_PASSWORD=YourDatabasePassword
   DB_CHARSET=utf8mb4

   # Admin Credentials
   ADMIN_USERNAME=admin
   ADMIN_PASSWORD=admin123

   # Razorpay Keys
   RAZORPAY_KEY_ID=rzp_test_SIIeZtDjS4VRsM
   RAZORPAY_KEY_SECRET=jChLBRshmM64ATJ7lLVMEj6T

   # Gmail SMTP
   MAIL_SERVER=smtp.gmail.com
   MAIL_PORT=465
   MAIL_USERNAME=mitramsolutions@gmail.com
   MAIL_PASSWORD=your-gmail-app-password
   MAIL_FROM_ADDRESS=mitramsolutions@gmail.com
   MAIL_FROM_NAME="ANSH THRIFTS"
   CONTACT_RECIPIENT=mitramsolutions@gmail.com
   ```

### Step 5: Configure Permissions & PHP Version
1. Ensure `static/images/uploads` has write permissions (`755`).
2. Verify PHP version is set to **PHP 8.2** or **PHP 8.3** in **Advanced** → **PHP Configuration**.
3. Confirm `.htaccess` is present in `public_html/` — it blocks any public browser access to `.env`, `database/`, and uploaded scripts.

---

## 🧪 Testing the Application

Automated test suites are included:
```bash
# Full MySQL Migration & Storefront Test Suite (53 Tests)
php tests/test_mysql_migration.php

# Complete 9-Directive Production Audit (35 Checks)
php tests/test_audit.php

# Legal & Policies Verification Suite
php tests/test_policy_pages.php
```

All tests pass with 100% success rate.
