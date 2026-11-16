<?php
/**
 * ANSH THRIFTS — Shopping Cart & Checkout Page (cart.php)
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/Database.php';

// Handle POST cart operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $productId = trim($_POST['product_id'] ?? '');

    if ($action === 'add' && $productId !== '') {
        $product = getProductById($productId);
        if ($product) {
            $qty = max(1, (int)($_POST['quantity'] ?? 1));
            addToCart($product, $qty);
            setFlash('success', "{$product['Name']} added to your cart!");
        } else {
            setFlash('danger', 'Product not found.');
        }
        if (!empty($_POST['buy_now'])) {
            redirect('/cart.php');
        }
        redirect('/cart.php');
    }

    if ($action === 'update' && $productId !== '') {
        $qty = (int)($_POST['quantity'] ?? 1);
        updateCartQuantity($productId, $qty);
        setFlash('success', 'Cart updated successfully.');
        redirect('/cart.php');
    }

    if ($action === 'remove' && $productId !== '') {
        removeFromCart($productId);
        setFlash('info', 'Item removed from your cart.');
        redirect('/cart.php');
    }

    if ($action === 'clear') {
        clearCart();
        setFlash('info', 'Cart cleared.');
        redirect('/cart.php');
    }
}

$pageTitle = 'Your Cart · ' . APP_NAME;
$activeNav = 'cart';
$cart = getCart();
$totalPrice = getCartTotal();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container page-content" style="margin-top: 100px; margin-bottom: 60px;">
  <h2 class="section-heading text-center mb-4">🛒 Your Cart</h2>

  <?php if (!empty($cart)): ?>
    <div class="row g-4">
      <!-- Cart Items Column -->
      <div class="col-lg-8">
        <!-- Desktop table view -->
        <div class="d-none d-md-block glass-effect p-3 mb-4" style="background: #ffffff;">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
              <tr>
                <th>Item</th>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($cart as $item): ?>
              <tr>
                <td style="width: 80px;">
                  <img src="/<?= e(resolveProductImage($item['image'])) ?>"
                       alt="<?= e($item['name']) ?>" class="img-thumbnail" width="70" height="70"
                       style="object-fit: cover; border-radius: 8px;">
                </td>
                <td>
                  <a href="/product.php?slug=<?= urlencode($item['slug'] ?? '') ?>" class="fw-bold text-dark text-decoration-none">
                    <?= e($item['name']) ?>
                  </a>
                </td>
                <td>₹<?= number_format((float)$item['price'], 2) ?></td>
                <td>
                  <form method="POST" action="/cart.php" class="d-flex align-items-center gap-1">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="product_id" value="<?= e($item['id']) ?>">
                    <input type="number" name="quantity" value="<?= (int)$item['quantity'] ?>"
                           min="1" max="20" class="form-control form-control-sm text-center" style="width: 65px;">
                    <button type="submit" class="btn btn-outline-primary btn-sm" title="Update quantity">✓</button>
                  </form>
                </td>
                <td><strong>₹<?= number_format((float)$item['price'] * (int)$item['quantity'], 2) ?></strong></td>
                <td>
                  <form method="POST" action="/cart.php">
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="product_id" value="<?= e($item['id']) ?>">
                    <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Remove item from cart?');">
                      Remove
                    </button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Mobile card view -->
        <div class="d-md-none mb-4">
          <?php foreach ($cart as $item): ?>
          <div class="card mb-3 shadow-sm border-0">
            <div class="card-body d-flex gap-3 align-items-center p-3">
              <img src="/<?= e(resolveProductImage($item['image'])) ?>"
                   alt="<?= e($item['name']) ?>" width="75" height="75"
                   style="object-fit: cover; border-radius: 10px; flex-shrink: 0;">
              <div class="flex-grow-1">
                <h6 class="mb-1 text-truncate" style="max-width: 170px;"><?= e($item['name']) ?></h6>
                <div class="text-muted small">₹<?= number_format((float)$item['price'], 2) ?> each</div>
                <div class="fw-bold" style="color: #d4a855;">
                  ₹<?= number_format((float)$item['price'] * (int)$item['quantity'], 2) ?>
                </div>
              </div>
              <div class="d-flex flex-column gap-2 align-items-end">
                <form method="POST" action="/cart.php" class="d-flex gap-1">
                  <input type="hidden" name="action" value="update">
                  <input type="hidden" name="product_id" value="<?= e($item['id']) ?>">
                  <input type="number" name="quantity" value="<?= (int)$item['quantity'] ?>"
                         min="1" max="20" class="form-control form-control-sm text-center" style="width: 50px;">
                  <button type="submit" class="btn btn-outline-primary btn-sm p-1 px-2">✓</button>
                </form>
                <form method="POST" action="/cart.php">
                  <input type="hidden" name="action" value="remove">
                  <input type="hidden" name="product_id" value="<?= e($item['id']) ?>">
                  <button type="submit" class="btn btn-danger btn-sm p-1 px-2" style="font-size: 0.75rem;">✕ Remove</button>
                </form>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="d-flex justify-content-between align-items-center">
          <a href="/shop.php" class="btn btn-white-outline text-dark border-secondary">← Continue Shopping</a>
          <form method="POST" action="/cart.php">
            <input type="hidden" name="action" value="clear">
            <button type="submit" class="btn btn-outline-secondary btn-sm" onclick="return confirm('Clear entire cart?');">Clear Cart</button>
          </form>
        </div>
      </div>

      <!-- Checkout Details Column -->
      <div class="col-lg-4">
        <div class="card p-4 shadow-sm border-0 rounded-3" style="background: #ffffff;">
          <h4 class="mb-3" style="font-family: 'Playfair Display', serif;">Order Summary</h4>
          
          <div class="d-flex justify-content-between py-2 border-bottom">
            <span>Subtotal</span>
            <span>₹<?= number_format($totalPrice, 2) ?></span>
          </div>
          <div class="d-flex justify-content-between py-2 border-bottom">
            <span>Shipping</span>
            <span class="text-success fw-semibold">FREE (Pre-launch Offer)</span>
          </div>
          <div class="d-flex justify-content-between py-3 mb-3 border-bottom">
            <strong>Total Amount</strong>
            <strong style="color: #d4a855; font-size: 1.25rem;">₹<?= number_format($totalPrice, 2) ?></strong>
          </div>

          <!-- Checkout Form -->
          <h5 class="mb-3" style="font-family: 'Inter', sans-serif; font-size: 1rem;">Shipping & Contact</h5>
          <form id="checkoutForm">
            <div class="mb-2">
              <input type="text" id="custName" class="form-control form-control-sm" placeholder="Full Name *" required>
            </div>
            <div class="mb-2">
              <input type="email" id="custEmail" class="form-control form-control-sm" placeholder="Email Address *" required>
            </div>
            <div class="mb-2">
              <input type="tel" id="custPhone" class="form-control form-control-sm" placeholder="10-digit Phone Number *" required>
            </div>
            <div class="mb-3">
              <textarea id="custAddress" class="form-control form-control-sm" rows="3" placeholder="Complete Delivery Address *" required></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-bold">Payment Method</label>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="payMethod" id="payRazorpay" value="Razorpay" checked>
                <label class="form-check-label small" for="payRazorpay">
                  Online Payment (UPI, Cards, NetBanking via Razorpay)
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="payMethod" id="payCOD" value="COD">
                <label class="form-check-label small" for="payCOD">
                  Cash on Delivery (COD)
                </label>
              </div>
            </div>

            <!-- Customer Policy Acknowledgment & Consent -->
            <div class="mb-3 p-2 bg-light rounded border">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="policyAgreeCheck" required>
                <label class="form-check-label text-muted" for="policyAgreeCheck" style="font-size: 0.78rem; line-height: 1.4;">
                  I have read and agree to the 
                  <a href="/terms.php" target="_blank" class="text-dark fw-semibold text-decoration-underline">Terms & Conditions</a>, 
                  <a href="/privacy-policy.php" target="_blank" class="text-dark fw-semibold text-decoration-underline">Privacy Policy</a>, and 
                  <a href="/refund-policy.php" target="_blank" class="text-dark fw-semibold text-decoration-underline">Return & Refund Policy</a> 
                  (including vintage/pre-loved 1-of-1 item conditions and unboxing video requirement).
                </label>
              </div>
            </div>

            <button type="button" id="payBtn" class="btn btn-secondary w-100 btn-lg mb-2" onclick="handleCheckout()">
              Proceed to Pay ₹<?= number_format($totalPrice, 2) ?>
            </button>
            <p class="text-muted text-center small mb-0">🔒 100% Encrypted & Safe Checkout</p>
          </form>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="text-center p-5 glass-effect" style="background: #ffffff;">
      <div style="font-size: 3rem; margin-bottom: 16px;">🛍️</div>
      <h3 class="text-muted mb-3">Your shopping cart is currently empty</h3>
      <p class="text-muted mb-4">Discover rare pre-loved fashion finds and add your favourites to cart.</p>
      <a href="/shop.php" class="btn btn-primary btn-lg">Browse Collection</a>
    </div>
  <?php endif; ?>
</div>

<!-- Razorpay Checkout Script Integration -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
function handleCheckout() {
    const form = document.getElementById('checkoutForm');
    const name = document.getElementById('custName').value.trim();
    const email = document.getElementById('custEmail').value.trim();
    const phone = document.getElementById('custPhone').value.trim();
    const address = document.getElementById('custAddress').value.trim();
    const method = document.querySelector('input[name="payMethod"]:checked').value;
    const btn = document.getElementById('payBtn');

    if (!name || !email || !phone || !address) {
        alert("Please complete all shipping address and contact fields.");
        return;
    }

    const policyCheck = document.getElementById('policyAgreeCheck');
    if (policyCheck && !policyCheck.checked) {
        alert("Please accept the Terms & Conditions and Store Policies before proceeding with your order.");
        policyCheck.focus();
        return;
    }

    btn.disabled = true;
    btn.textContent = "Processing Order...";

    // 1. If Cash on Delivery, complete immediately via API
    if (method === 'COD') {
        fetch('/api/create_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                customer_name: name,
                email: email,
                phone: phone,
                address: address,
                payment_method: 'COD'
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert("Thank you! Your Cash on Delivery order #" + data.order_id + " has been placed successfully.");
                window.location.href = '/index.php?order=placed';
            } else {
                alert("Error placing order: " + (data.error || "Unknown error"));
                btn.disabled = false;
                btn.textContent = "Proceed to Pay";
            }
        })
        .catch(err => {
            console.error(err);
            alert("Network error processing order. Please try again.");
            btn.disabled = false;
            btn.textContent = "Proceed to Pay";
        });
        return;
    }

    // 2. Online Payment via Razorpay
    fetch('/api/create_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            customer_name: name,
            email: email,
            phone: phone,
            address: address,
            payment_method: 'Razorpay'
        })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success && data.error) {
            alert("Error creating order: " + data.error);
            btn.disabled = false;
            btn.textContent = "Proceed to Pay";
            return;
        }

        const options = {
            key: data.key_id,
            amount: data.amount,
            currency: data.currency || "INR",
            name: "ANSH THRIFTS",
            description: "Thrift Store Order " + data.order_id,
            order_id: data.razorpay_order_id,
            handler: function (response) {
                // Verify payment on backend
                fetch('/api/verify_payment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        order_id: data.order_id,
                        razorpay_order_id: response.razorpay_order_id,
                        razorpay_payment_id: response.razorpay_payment_id,
                        razorpay_signature: response.razorpay_signature
                    })
                })
                .then(res => res.json())
                .then(vdata => {
                    if (vdata.success) {
                        alert("Payment successful! Order #" + data.order_id + " confirmed.");
                        window.location.href = '/index.php?order=success';
                    } else {
                        alert("Payment verification issue: " + (vdata.error || "Please contact support."));
                        btn.disabled = false;
                        btn.textContent = "Proceed to Pay";
                    }
                })
                .catch(e => {
                    alert("Error verifying payment.");
                    btn.disabled = false;
                    btn.textContent = "Proceed to Pay";
                });
            },
            prefill: {
                name: name,
                email: email,
                contact: phone
            },
            theme: { color: "#d4a855" },
            modal: {
                ondismiss: function() {
                    btn.disabled = false;
                    btn.textContent = "Proceed to Pay";
                }
            }
        };

        const rzp = new Razorpay(options);
        rzp.open();
    })
    .catch(err => {
        console.error(err);
        alert("Something went wrong initiating payment.");
        btn.disabled = false;
        btn.textContent = "Proceed to Pay";
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

