# ANSH THRIFTS (PHP 8+, Bootstrap 5 & Google Sheets API)

> **Pre-Loved Fashion, New Stories.**  
> Completely migrated to PHP 8+, HTML5, CSS3, Bootstrap 5, JavaScript, and powered by **Google Sheets API** as a serverless database backend. Optimized for **Hostinger Unlimited Web Hosting**.

---

## 🌟 Architecture & Features

- **Storefront & Catalog**:
  - `index.php`: Hero banner with vintage thrift imagery, dynamic product carousel with batching.
  - `shop.php`: Real-time search, category filtering (Jackets, T-Shirts, Jeans, Shirts, Hoodies), and responsive product card grid.
  - `product.php`: Dedicated single product view with condition notes, responsive gallery, Add to Cart and Buy Now workflows.
  - `cart.php`: Responsive shopping cart with desktop table and mobile card views, dynamic item quantity updates, order summary, and Razorpay / Cash on Delivery checkout.
  - `about.php`: Brand story, sustainability mission, and core values.
  - `contact.php`: Customer contact form with direct submission into the Google Sheets `Contact Messages` tab.

- **Google Sheets Database Backend**:
  - Automatically provisions and manages tabs in Spreadsheet ID `1LW8dyub59pMYU1tCWLIM4hyUcHOvDSEXS-EHJRfK-p8`.
  - **7 Primary Tabs**:
    1. `Products` (ID, Name, Slug, Price, Category, Details, Image File, Status, Created At)
    2. `Inventory` (Product ID, Product Name, SKU, Stock Quantity, Reserved Quantity, Available Quantity, Low Stock Alert, Last Updated)
    3. `Orders` (Order ID, Customer Name, Email, Phone, Total Amount, Payment Method, Payment Status, Razorpay Order ID, Razorpay Payment ID, Shipping Address, Order Status, Created At)
    4. `Deliveries` (Delivery ID, Order ID, Recipient Name, Recipient Phone, Shipping Address, Carrier, Tracking Number, Status, Estimated Delivery, Dispatched At, Delivered At, Notes)
    5. `Contact Messages` (Message ID, Sender Name, Email, Phone, Subject, Message, Status, Received At, Responded At)
    6. `Customers` (Customer ID, Full Name, Email, Phone, Total Orders, Total Spent, First Order Date, Last Active)
    7. `Settings` (Key, Value, Description, Last Updated)
  - **Professional Spreadsheet Formatting**: Branded dark charcoal headers with gold text, frozen row 1, alternating zebra row striping, column auto-sizing, and filters.
  - Pure PHP JWT RS256 token exchange with Google Service Account (Zero bloated external SDK requirements).

- **Admin Management Panel (`/admin`)**:
  - Secure role-protected dashboard requiring admin authentication (`admin` / `admin123`).
  - Full CRUD operations for Products with instant Google Sheets update.
  - Live Inventory and Stock quantity adjustment.
  - Order status management (`Placed`, `Confirmed`, `Processing`, `Dispatched`, `Delivered`, `Cancelled`).
  - Delivery logistics tracker with automated tracking IDs.
  - Contact messages viewer with response status toggling.
  - Customer directory with lifetime spend tracking.

- **Payment & Security**:
  - Razorpay Online Checkout (UPI, Cards, NetBanking) with HMAC-SHA256 server-side signature verification.
  - Cash on Delivery (COD) mode for instant order placement.
  - `credentials.json` protected by `.gitignore` and `.htaccess` rewrite rules (Never exposed to browsers or git).

---

## 🚀 Deployment Instructions for Hostinger Unlimited Web Hosting

Hostinger supports standard Apache / LiteSpeed web servers with PHP 8.0, 8.1, 8.2, and 8.3 out of the box.

### Step 1: Upload Files via Hostinger File Manager or FTP
1. Log into your **Hostinger hPanel**.
2. Navigate to **Websites** → Select your domain → **File Manager** (or connect via FileZilla FTP).
3. Open the `public_html/` directory.
4. Upload all files from this project into `public_html/`:
   ```
   public_html/
   ├── admin/
   │   ├── index.php
   │   ├── product_add.php
   │   ├── product_edit.php
   │   ├── product_delete.php
   │   ├── stock_update.php
   │   ├── order_update.php
   │   ├── delivery_update.php
   │   └── message_update.php
   ├── api/
   │   ├── create_order.php
   │   └── verify_payment.php
   ├── includes/
   │   ├── config.php
   │   ├── functions.php
   │   ├── GoogleSheetsService.php
   │   ├── header.php
   │   └── footer.php
   ├── static/
   │   ├── css/
   │   ├── js/
   │   └── images/
   │       └── uploads/
   ├── .htaccess
   ├── .env
   ├── credentials.json
   ├── index.php
   ├── shop.php
   ├── product.php
   ├── cart.php
   ├── about.php
   ├── contact.php
   ├── login.php
   └── logout.php
   ```

### Step 2: Configure PHP Version on Hostinger
1. In Hostinger hPanel, go to **Advanced** → **PHP Configuration**.
2. Select **PHP 8.2** or **PHP 8.3** (Recommended).
3. Ensure the following standard PHP extensions are enabled under the **PHP Extensions** tab (default on Hostinger):
   - `curl`
   - `openssl`
   - `json`
   - `mbstring`
   - `fileinfo`
   - `session`

### Step 3: Configure Permissions
1. Ensure `static/images/uploads` has write permissions (`755` or `775`) so admin product image uploads can be saved.
2. Confirm `.htaccess` is present in `public_html/` — it blocks any public browser access to `credentials.json`, `.env`, and config files.

### Step 4: Environment Variables (`.env`)
You can edit the `.env` file in the root folder using Hostinger File Manager with your credentials:
```env
APP_NAME="ANSH THRIFTS"
APP_TAGLINE="Pre-Loved Fashion, New Stories."
APP_ENV=production

ADMIN_USERNAME=admin
ADMIN_PASSWORD=your_strong_admin_password

GOOGLE_SPREADSHEET_ID=1LW8dyub59pMYU1tCWLIM4hyUcHOvDSEXS-EHJRfK-p8
RAZORPAY_KEY_ID=rzp_test_SIIeZtDjS4VRsM
RAZORPAY_KEY_SECRET=jChLBRshmM64ATJ7lLVMEj6T
```

---

## 🧪 Testing the Application

An automated test suite is included in `tests/test_php_migration.php`:
```bash
php tests/test_php_migration.php
```
It tests:
- Google Sheets connectivity & tab verification
- Products fetching & search filters
- Full Product CRUD (Create, Read, Update, Delete)
- Inventory stock adjustment
- Contact form message logging
- Shopping cart sessions and total calculations

All 21 tests pass with 100% success rate.
