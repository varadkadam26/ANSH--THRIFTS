<?php
/**
 * ANSH THRIFTS — Admin Management Dashboard
 * Full multi-tab view and management: Products, Inventory, Orders, Deliveries, Contact Messages, Customers, Settings.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/GoogleSheetsService.php';

requireAdmin();

$sheets = new GoogleSheetsService();
$activeTab = trim($_GET['tab'] ?? 'products');

// Data fetching per tab
$products = $sheets->getAllRecords('Products');
$inventory = $sheets->getAllRecords('Inventory');
$orders = $sheets->getAllRecords('Orders');
$deliveries = $sheets->getAllRecords('Deliveries');
$contacts = $sheets->getAllRecords('Contact Messages');
$customers = $sheets->getAllRecords('Customers');
$settings = $sheets->getAllRecords('Settings');

// Key metrics
$totalRevenue = 0.0;
foreach ($orders as $o) {
    if (str_contains(strtolower($o['Payment Status'] ?? ''), 'paid') || str_contains(strtolower($o['Order Status'] ?? ''), 'placed')) {
        $totalRevenue += (float)($o['Total Amount'] ?? 0);
    }
}
$lowStockCount = 0;
foreach ($inventory as $inv) {
    if ((int)($inv['Available Quantity'] ?? 0) <= (int)($inv['Low Stock Alert'] ?? 3)) {
        $lowStockCount++;
    }
}
$pendingOrders = count(array_filter($orders, fn($o) => in_array($o['Order Status'] ?? '', ['Placed', 'Processing'])));
$unreadMessages = count(array_filter($contacts, fn($c) => in_array($c['Status'] ?? '', ['New', 'Unread'])));

$pageTitle = 'Admin Dashboard · ' . APP_NAME;
$csrfToken = getCsrfToken();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <link rel="shortcut icon" href="/static/images/favicon.png">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
  <link href="/static/css/bootstrap.min.css" rel="stylesheet">
  <link href="/static/css/style.css" rel="stylesheet">

  <style>
    body {
      background-color: #f4f6f8;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    .admin-nav {
      background: #1a1a1a;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .metric-card {
      background: #fff;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.04);
      border-left: 4px solid #d4a855;
    }
    .metric-value {
      font-size: 1.8rem;
      font-weight: 700;
      color: #1a1a1a;
    }
    .nav-pills .nav-link {
      color: #495057;
      font-weight: 600;
      border-radius: 8px;
      padding: 10px 16px;
    }
    .nav-pills .nav-link.active {
      background-color: #d4a855;
      color: #1a1a1a;
    }
    .table-container {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.04);
      padding: 20px;
    }
    .badge-status {
      font-size: 0.8rem;
      padding: 5px 10px;
      border-radius: 50px;
    }
  </style>
</head>
<body>

  <!-- Top Admin Navbar -->
  <nav class="admin-nav navbar navbar-expand-lg navbar-dark py-3">
    <div class="container-fluid px-4">
      <a class="navbar-brand font-playfair fw-bold" href="/admin/index.php">
        <?= e(APP_NAME) ?> <span class="badge bg-warning text-dark ms-2 small">Admin</span>
      </a>
      <div class="d-flex align-items-center gap-3">
        <a href="https://docs.google.com/spreadsheets/d/<?= e(GOOGLE_SPREADSHEET_ID) ?>" target="_blank" class="btn btn-outline-light btn-sm">
          📊 Open Google Sheet
        </a>
        <a href="/index.php" target="_blank" class="btn btn-outline-light btn-sm">
          👁 View Storefront
        </a>
        <a href="/logout.php" class="btn btn-danger btn-sm">
          Logout
        </a>
      </div>
    </div>
  </nav>

  <div class="container-fluid px-4 py-4 flex-grow-1">
    
    <!-- Flash Messages -->
    <?php $flashMsgs = getFlashMessages(); ?>
    <?php foreach ($flashMsgs as $flash): ?>
      <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endforeach; ?>

    <!-- Summary Metrics -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="metric-card">
          <div class="text-muted small text-uppercase fw-semibold">Total Revenue</div>
          <div class="metric-value">₹<?= number_format($totalRevenue, 2) ?></div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="metric-card" style="border-left-color: #28a745;">
          <div class="text-muted small text-uppercase fw-semibold">Total Products</div>
          <div class="metric-value"><?= count($products) ?></div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="metric-card" style="border-left-color: #0d6efd;">
          <div class="text-muted small text-uppercase fw-semibold">Pending Orders</div>
          <div class="metric-value"><?= $pendingOrders ?></div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="metric-card" style="border-left-color: <?= $lowStockCount > 0 ? '#dc3545' : '#6c757d' ?>;">
          <div class="text-muted small text-uppercase fw-semibold">Low Stock Alerts</div>
          <div class="metric-value <?= $lowStockCount > 0 ? 'text-danger' : '' ?>"><?= $lowStockCount ?></div>
        </div>
      </div>
    </div>

    <!-- Navigation Tabs for Google Sheets -->
    <ul class="nav nav-pills mb-4 gap-2 flex-nowrap overflow-auto pb-2" id="adminTabs">
      <li class="nav-item">
        <a class="nav-link <?= $activeTab === 'products' ? 'active' : '' ?>" href="/admin/index.php?tab=products">
          🛍 Products (<?= count($products) ?>)
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $activeTab === 'inventory' ? 'active' : '' ?>" href="/admin/index.php?tab=inventory">
          📦 Inventory (<?= count($inventory) ?>)
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $activeTab === 'orders' ? 'active' : '' ?>" href="/admin/index.php?tab=orders">
          📑 Orders (<?= count($orders) ?>)
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $activeTab === 'deliveries' ? 'active' : '' ?>" href="/admin/index.php?tab=deliveries">
          🚚 Deliveries (<?= count($deliveries) ?>)
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $activeTab === 'contacts' ? 'active' : '' ?>" href="/admin/index.php?tab=contacts">
          ✉ Messages (<?= count($contacts) ?>)
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $activeTab === 'customers' ? 'active' : '' ?>" href="/admin/index.php?tab=customers">
          👥 Customers (<?= count($customers) ?>)
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $activeTab === 'settings' ? 'active' : '' ?>" href="/admin/index.php?tab=settings">
          ⚙ Settings
        </a>
      </li>
    </ul>

    <!-- Tab 1: PRODUCTS -->
    <?php if ($activeTab === 'products'): ?>
    <div class="table-container">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Product Catalog</h4>
        <a href="/admin/product_add.php" class="btn btn-secondary btn-sm">+ Add New Product</a>
      </div>

      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-dark">
            <tr>
              <th>Image</th>
              <th>ID</th>
              <th>Name</th>
              <th>Category</th>
              <th>Price</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
              <td>
                <img src="/<?= e(resolveProductImage($p['Image File'] ?? '')) ?>"
                     alt="" width="50" height="50" class="img-thumbnail" style="object-fit: cover;">
              </td>
              <td><code><?= e($p['ID'] ?? '') ?></code></td>
              <td>
                <strong><?= e($p['Name'] ?? '') ?></strong>
                <div class="text-muted small"><?= e($p['Slug'] ?? '') ?></div>
              </td>
              <td><span class="badge bg-light text-dark border"><?= e($p['Category'] ?? 'General') ?></span></td>
              <td><strong>₹<?= e($p['Price'] ?? '0') ?></strong></td>
              <td>
                <span class="badge <?= ($p['Status'] ?? 'Active') === 'Active' ? 'bg-success' : 'bg-secondary' ?>">
                  <?= e($p['Status'] ?? 'Active') ?>
                </span>
              </td>
              <td>
                <div class="d-flex gap-1">
                  <a href="/product.php?slug=<?= urlencode($p['Slug'] ?? '') ?>" target="_blank" class="btn btn-outline-info btn-sm" title="View in storefront">👁</a>
                  <a href="/admin/product_edit.php?id=<?= urlencode($p['ID'] ?? '') ?>" class="btn btn-outline-primary btn-sm" title="Edit">Edit</a>
                  <form action="/admin/product_delete.php" method="POST" class="d-inline" onsubmit="return confirm('Delete this product permanently from Google Sheets?');">
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    <input type="hidden" name="id" value="<?= e($p['ID'] ?? '') ?>">
                    <input type="hidden" name="row" value="<?= (int)($p['_row_number'] ?? 0) ?>">
                    <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <!-- Tab 2: INVENTORY -->
    <?php if ($activeTab === 'inventory'): ?>
    <div class="table-container">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Stock & Inventory Management</h4>
        <small class="text-muted">Live synchronization with Google Sheets Inventory tab</small>
      </div>

      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-dark">
            <tr>
              <th>Product ID</th>
              <th>Product Name</th>
              <th>SKU</th>
              <th>Stock Qty</th>
              <th>Reserved</th>
              <th>Available</th>
              <th>Alert Threshold</th>
              <th>Status</th>
              <th>Quick Update</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($inventory as $inv): 
              $avail = (int)($inv['Available Quantity'] ?? 0);
              $alert = (int)($inv['Low Stock Alert'] ?? 3);
            ?>
            <tr>
              <td><code><?= e($inv['Product ID'] ?? '') ?></code></td>
              <td><strong><?= e($inv['Product Name'] ?? '') ?></strong></td>
              <td><span class="badge bg-secondary"><?= e($inv['SKU'] ?? '') ?></span></td>
              <td><?= (int)($inv['Stock Quantity'] ?? 0) ?></td>
              <td><?= (int)($inv['Reserved Quantity'] ?? 0) ?></td>
              <td>
                <strong class="<?= $avail <= $alert ? 'text-danger' : 'text-success' ?>">
                  <?= $avail ?>
                </strong>
              </td>
              <td><?= $alert ?></td>
              <td>
                <?php if ($avail <= 0): ?>
                  <span class="badge bg-danger">Out of Stock</span>
                <?php elseif ($avail <= $alert): ?>
                  <span class="badge bg-warning text-dark">Low Stock</span>
                <?php else: ?>
                  <span class="badge bg-success">In Stock</span>
                <?php endif; ?>
              </td>
              <td>
                <form action="/admin/stock_update.php" method="POST" class="d-flex gap-1" style="max-width: 140px;">
                  <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                  <input type="hidden" name="product_id" value="<?= e($inv['Product ID'] ?? '') ?>">
                  <input type="number" name="new_stock" value="<?= (int)($inv['Stock Quantity'] ?? 0) ?>" min="0" class="form-control form-control-sm text-center">
                  <button type="submit" class="btn btn-outline-primary btn-sm">Save</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <!-- Tab 3: ORDERS -->
    <?php if ($activeTab === 'orders'): ?>
    <div class="table-container">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Customer Orders</h4>
        <small class="text-muted">Orders synced directly from checkout to Google Sheets</small>
      </div>

      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-dark">
            <tr>
              <th>Order ID</th>
              <th>Customer</th>
              <th>Contact</th>
              <th>Amount</th>
              <th>Method</th>
              <th>Payment Status</th>
              <th>Order Status</th>
              <th>Date</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($orders)): ?>
              <tr><td colspan="9" class="text-center py-4 text-muted">No orders recorded yet.</td></tr>
            <?php else: ?>
              <?php foreach (array_reverse($orders) as $ord): ?>
              <tr>
                <td><code><?= e($ord['Order ID'] ?? '') ?></code></td>
                <td><strong><?= e($ord['Customer Name'] ?? '') ?></strong></td>
                <td>
                  <div><?= e($ord['Email'] ?? '') ?></div>
                  <small class="text-muted"><?= e($ord['Phone'] ?? '') ?></small>
                </td>
                <td><strong>₹<?= number_format((float)($ord['Total Amount'] ?? 0), 2) ?></strong></td>
                <td><span class="badge bg-info text-dark"><?= e($ord['Payment Method'] ?? 'Razorpay') ?></span></td>
                <td>
                  <span class="badge <?= str_contains(strtolower($ord['Payment Status'] ?? ''), 'paid') ? 'bg-success' : 'bg-warning text-dark' ?>">
                    <?= e($ord['Payment Status'] ?? 'Pending') ?>
                  </span>
                </td>
                <td>
                  <form action="/admin/order_update.php" method="POST" class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    <input type="hidden" name="order_id" value="<?= e($ord['Order ID'] ?? '') ?>">
                    <select name="order_status" class="form-select form-select-sm" onchange="this.form.submit()">
                      <option value="Placed" <?= ($ord['Order Status'] ?? '') === 'Placed' ? 'selected' : '' ?>>Placed</option>
                      <option value="Confirmed" <?= ($ord['Order Status'] ?? '') === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                      <option value="Processing" <?= ($ord['Order Status'] ?? '') === 'Processing' ? 'selected' : '' ?>>Processing</option>
                      <option value="Dispatched" <?= ($ord['Order Status'] ?? '') === 'Dispatched' ? 'selected' : '' ?>>Dispatched</option>
                      <option value="Delivered" <?= ($ord['Order Status'] ?? '') === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                      <option value="Cancelled" <?= ($ord['Order Status'] ?? '') === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                  </form>
                </td>
                <td class="small text-muted"><?= e($ord['Created At'] ?? '') ?></td>
                <td>
                  <button type="button" class="btn btn-outline-secondary btn-sm" 
                          onclick="alert('Shipping Address:\n<?= addslashes(e($ord['Shipping Address'] ?? '')) ?>\n\nRazorpay Payment ID:\n<?= e($ord['Razorpay Payment ID'] ?? 'N/A') ?>');">
                    Details
                  </button>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <!-- Tab 4: DELIVERIES -->
    <?php if ($activeTab === 'deliveries'): ?>
    <div class="table-container">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Delivery & Logistics Tracker</h4>
        <small class="text-muted">Automated tracking creation for every customer order</small>
      </div>

      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-dark">
            <tr>
              <th>Delivery ID</th>
              <th>Order ID</th>
              <th>Recipient</th>
              <th>Carrier</th>
              <th>Tracking #</th>
              <th>Status</th>
              <th>Est. Delivery</th>
              <th>Update Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($deliveries)): ?>
              <tr><td colspan="8" class="text-center py-4 text-muted">No delivery entries yet.</td></tr>
            <?php else: ?>
              <?php foreach (array_reverse($deliveries) as $del): ?>
              <tr>
                <td><code><?= e($del['Delivery ID'] ?? '') ?></code></td>
                <td><code><?= e($del['Order ID'] ?? '') ?></code></td>
                <td>
                  <div><?= e($del['Recipient Name'] ?? '') ?></div>
                  <small class="text-muted"><?= e($del['Recipient Phone'] ?? '') ?></small>
                </td>
                <td><?= e($del['Carrier'] ?? '') ?></td>
                <td><code><?= e($del['Tracking Number'] ?? 'TRK-PENDING') ?></code></td>
                <td>
                  <span class="badge <?= ($del['Status'] ?? '') === 'Delivered' ? 'bg-success' : 'bg-primary' ?>">
                    <?= e($del['Status'] ?? 'Processing') ?>
                  </span>
                </td>
                <td class="small"><?= e($del['Estimated Delivery'] ?? 'TBD') ?></td>
                <td>
                  <form action="/admin/delivery_update.php" method="POST" class="d-flex gap-1">
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    <input type="hidden" name="delivery_id" value="<?= e($del['Delivery ID'] ?? '') ?>">
                    <select name="status" class="form-select form-select-sm">
                      <option value="Processing" <?= ($del['Status'] ?? '') === 'Processing' ? 'selected' : '' ?>>Processing</option>
                      <option value="Dispatched" <?= ($del['Status'] ?? '') === 'Dispatched' ? 'selected' : '' ?>>Dispatched</option>
                      <option value="In Transit" <?= ($del['Status'] ?? '') === 'In Transit' ? 'selected' : '' ?>>In Transit</option>
                      <option value="Delivered" <?= ($del['Status'] ?? '') === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                      <option value="Returned" <?= ($del['Status'] ?? '') === 'Returned' ? 'selected' : '' ?>>Returned</option>
                    </select>
                    <button type="submit" class="btn btn-outline-primary btn-sm">Save</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <!-- Tab 5: CONTACT MESSAGES -->
    <?php if ($activeTab === 'contacts'): ?>
    <div class="table-container">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Customer Inquiries & Messages</h4>
        <small class="text-muted">Messages submitted through contact.php saved in Google Sheets</small>
      </div>

      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>Sender</th>
              <th>Contact</th>
              <th>Subject</th>
              <th>Message</th>
              <th>Status</th>
              <th>Received At</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($contacts)): ?>
              <tr><td colspan="8" class="text-center py-4 text-muted">No messages received yet.</td></tr>
            <?php else: ?>
              <?php foreach (array_reverse($contacts) as $c): ?>
              <tr>
                <td><code><?= e($c['Message ID'] ?? '') ?></code></td>
                <td><strong><?= e($c['Sender Name'] ?? '') ?></strong></td>
                <td>
                  <div><a href="mailto:<?= e($c['Email'] ?? '') ?>"><?= e($c['Email'] ?? '') ?></a></div>
                  <small class="text-muted"><?= e($c['Phone'] ?? '') ?></small>
                </td>
                <td><?= e($c['Subject'] ?? '') ?></td>
                <td><small><?= nl2br(e($c['Message'] ?? '')) ?></small></td>
                <td>
                  <span class="badge <?= ($c['Status'] ?? '') === 'New' ? 'bg-danger' : 'bg-secondary' ?>">
                    <?= e($c['Status'] ?? 'New') ?>
                  </span>
                </td>
                <td class="small text-muted"><?= e($c['Received At'] ?? '') ?></td>
                <td>
                  <form action="/admin/message_update.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    <input type="hidden" name="message_id" value="<?= e($c['Message ID'] ?? '') ?>">
                    <input type="hidden" name="status" value="<?= ($c['Status'] ?? '') === 'New' ? 'Responded' : 'New' ?>">
                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                      Mark <?= ($c['Status'] ?? '') === 'New' ? 'Done' : 'New' ?>
                    </button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <!-- Tab 6: CUSTOMERS -->
    <?php if ($activeTab === 'customers'): ?>
    <div class="table-container">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Customer Directory</h4>
        <small class="text-muted">Auto-aggregated customer records across all orders</small>
      </div>

      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-dark">
            <tr>
              <th>Customer ID</th>
              <th>Full Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Total Orders</th>
              <th>Total Spent</th>
              <th>First Order Date</th>
              <th>Last Active</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($customers)): ?>
              <tr><td colspan="8" class="text-center py-4 text-muted">No customers recorded yet.</td></tr>
            <?php else: ?>
              <?php foreach ($customers as $cust): ?>
              <tr>
                <td><code><?= e($cust['Customer ID'] ?? '') ?></code></td>
                <td><strong><?= e($cust['Full Name'] ?? '') ?></strong></td>
                <td><a href="mailto:<?= e($cust['Email'] ?? '') ?>"><?= e($cust['Email'] ?? '') ?></a></td>
                <td><?= e($cust['Phone'] ?? '') ?></td>
                <td><span class="badge bg-secondary"><?= e($cust['Total Orders'] ?? '1') ?></span></td>
                <td><strong>₹<?= number_format((float)($cust['Total Spent'] ?? 0), 2) ?></strong></td>
                <td class="small"><?= e($cust['First Order Date'] ?? '') ?></td>
                <td class="small text-muted"><?= e($cust['Last Active'] ?? '') ?></td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <!-- Tab 7: SETTINGS -->
    <?php if ($activeTab === 'settings'): ?>
    <div class="table-container">
      <h4 class="mb-3">Store Configuration Settings</h4>
      <p class="text-muted small">Managed within the Google Sheets Settings tab.</p>

      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-dark">
            <tr>
              <th>Setting Key</th>
              <th>Current Value</th>
              <th>Description</th>
              <th>Last Updated</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($settings as $st): ?>
            <tr>
              <td><code><?= e($st['Key'] ?? '') ?></code></td>
              <td><strong><?= e($st['Value'] ?? '') ?></strong></td>
              <td><?= e($st['Description'] ?? '') ?></td>
              <td class="small text-muted"><?= e($st['Last Updated'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

  </div>

  <footer class="py-3 bg-dark text-white-50 text-center small mt-auto">
    &copy; <?= date('Y') ?> <?= e(APP_NAME) ?> Admin Panel · Connected to Google Sheets API v4
  </footer>

  <script src="/static/js/bootstrap.bundle.min.js"></script>
</body>
</html>
