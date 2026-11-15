<?php
/**
 * ANSH THRIFTS — Reusable Footer Component
 */

declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
?>
  <!-- Footer -->
  <footer class="footer-section py-5" style="background: #111a15; color: #cfd8d3; border-top: 3px solid #d4a855;">
    <div class="container">
      <div class="row g-4 mb-4">
        
        <!-- Brand & Mission Column -->
        <div class="col-lg-3 col-md-6">
          <h4 class="text-white fw-bold mb-3" style="font-family: 'Playfair Display', Georgia, serif;">
            <?= e(APP_NAME) ?><span style="color: #d4a855;">.</span>
          </h4>
          <p class="small text-muted mb-3" style="line-height: 1.6;">
            Pre-loved fashion, new stories. Curating unique vintage and thrift clothing across India to promote circular, sustainable style that is kind to the planet and your wallet.
          </p>
          <div class="d-flex gap-2">
            <span class="badge" style="background: rgba(212,168,85,0.15); color: #d4a855; border: 1px solid rgba(212,168,85,0.3);">
              ♻ 100% Circular Thrift
            </span>
            <span class="badge" style="background: rgba(255,255,255,0.08); color: #e0e0e0;">
              🇮🇳 Pan-India Shipping
            </span>
          </div>
        </div>

        <!-- Quick Links Column -->
        <div class="col-lg-2 col-md-6 col-6">
          <h6 class="text-uppercase text-white fw-bold mb-3" style="font-size: 0.8rem; letter-spacing: 1px; color: #d4a855 !important;">
            Explore
          </h6>
          <ul class="list-unstyled small mb-0 d-flex flex-column gap-2">
            <li><a href="/index.php" class="text-decoration-none text-muted">Home</a></li>
            <li><a href="/shop.php" class="text-decoration-none text-muted">Shop All Drops</a></li>
            <li><a href="/about.php" class="text-decoration-none text-muted">Our Story</a></li>
            <li><a href="/contact.php" class="text-decoration-none text-muted">Contact Us</a></li>
            <li><a href="/cart.php" class="text-decoration-none text-muted">Shopping Bag</a></li>
          </ul>
        </div>

        <!-- Customer Policies Column -->
        <div class="col-lg-3 col-md-6 col-6">
          <h6 class="text-uppercase text-white fw-bold mb-3" style="font-size: 0.8rem; letter-spacing: 1px; color: #d4a855 !important;">
            Customer Policies
          </h6>
          <ul class="list-unstyled small mb-0 d-flex flex-column gap-2">
            <li><a href="/refund-policy.php" class="text-decoration-none text-muted">Return & Refund Policy</a></li>
            <li><a href="/shipping-policy.php" class="text-decoration-none text-muted">Shipping & Delivery</a></li>
            <li><a href="/cancellation-policy.php" class="text-decoration-none text-muted">Cancellation Policy</a></li>
            <li><a href="/payment-policy.php" class="text-decoration-none text-muted">Payment & Security</a></li>
          </ul>
        </div>

        <!-- Legal & Compliance Column -->
        <div class="col-lg-4 col-md-6">
          <h6 class="text-uppercase text-white fw-bold mb-3" style="font-size: 0.8rem; letter-spacing: 1px; color: #d4a855 !important;">
            Legal & Compliance
          </h6>
          <ul class="list-unstyled small mb-3 d-flex flex-column gap-2">
            <li><a href="/privacy-policy.php" class="text-decoration-none text-muted">Privacy Policy</a></li>
            <li><a href="/terms.php" class="text-decoration-none text-muted">Terms & Conditions</a></li>
            <li><a href="/grievance.php" class="text-decoration-none text-muted">Grievance Redressal (Statutory)</a></li>
            <li><a href="/contact-info.php" class="text-decoration-none text-muted">Official Contact Coordinates</a></li>
          </ul>
          
          <div class="p-2 rounded-2" style="background: rgba(255,255,255,0.04); font-size: 0.75rem; color: #8e9b93;">
            <span>🔒 Payments Secured via <strong>Razorpay</strong>. PCI-DSS Certified.</span>
          </div>
        </div>

      </div>

      <!-- Divider -->
      <hr style="border-color: rgba(255,255,255,0.1); margin: 1.5rem 0 1rem 0;">

      <!-- Bottom Bar -->
      <div class="row align-items-center small text-muted">
        <div class="col-md-7 text-center text-md-start mb-2 mb-md-0">
          <p class="mb-0" style="font-size: 0.8rem;">
            &copy; <?= date('Y') ?> <?= e(APP_NAME) ?>. All Rights Reserved. Curated pre-loved clothing platform in India.
          </p>
        </div>
        <div class="col-md-5 text-center text-md-end">
          <ul class="list-unstyled d-inline-flex gap-3 mb-0" style="font-size: 0.8rem;">
            <?php if (isAdminLoggedIn()): ?>
              <li><a href="/admin/index.php" class="text-warning text-decoration-none">Admin Panel</a></li>
              <li><a href="/logout.php" class="text-muted text-decoration-none">Logout</a></li>
            <?php else: ?>
              <li><a href="/login.php" class="text-muted text-decoration-none">Admin Login</a></li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </div>
  </footer>

  <!-- Bootstrap 5 JS -->
  <script src="/static/js/bootstrap.bundle.min.js"></script>
  <?php if (!empty($extraScripts)) echo $extraScripts; ?>
</body>
</html>
