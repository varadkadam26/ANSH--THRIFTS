<?php
/**
 * ANSH THRIFTS — Reusable Policy Page Header
 * Include with $policyTitle, $policySubtitle, $policyLastUpdated, $activePolicy
 */
declare(strict_types=1);

$activePolicy = $activePolicy ?? '';
$policyNavItems = [
    'privacy'      => ['label' => 'Privacy Policy', 'url' => '/privacy-policy.php'],
    'terms'        => ['label' => 'Terms & Conditions', 'url' => '/terms.php'],
    'refund'       => ['label' => 'Return & Refund Policy', 'url' => '/refund-policy.php'],
    'shipping'     => ['label' => 'Shipping & Delivery', 'url' => '/shipping-policy.php'],
    'cancellation' => ['label' => 'Cancellation Policy', 'url' => '/cancellation-policy.php'],
    'payment'      => ['label' => 'Payment Policy', 'url' => '/payment-policy.php'],
    'grievance'    => ['label' => 'Grievance Redressal', 'url' => '/grievance.php'],
    'contact'      => ['label' => 'Contact Information', 'url' => '/contact-info.php'],
];
?>

<!-- Policy Hero -->
<div class="hero py-5" style="background: linear-gradient(135deg, #1b2e25 0%, #0f1c16 100%); min-height: 220px;">
  <div class="container text-center py-4">
    <div class="intro-excerpt mx-auto" style="max-width: 800px;">
      <span class="badge rounded-pill mb-2 px-3 py-2 text-uppercase" style="background: rgba(212, 168, 85, 0.2); color: #d4a855; letter-spacing: 1.5px; font-size: 0.75rem;">
        Legal & Store Policies
      </span>
      <h1 class="display-5 fw-bold text-white mb-2"><?= e($policyTitle ?? 'Store Policy') ?></h1>
      <p class="text-white-50 mb-0"><?= e($policySubtitle ?? 'Clear, honest guidelines for pre-loved fashion lovers') ?></p>
      <?php if (!empty($policyLastUpdated)): ?>
        <div class="mt-2 text-white-50 small">
          <i class="bi bi-clock-history"></i> Last Updated: <?= e($policyLastUpdated) ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<style>
/* Policy Reader Styling */
.policy-container {
  font-family: 'Inter', sans-serif;
  color: #2b2b2b;
  line-height: 1.75;
}
.policy-sidebar {
  position: sticky;
  top: 90px;
}
.policy-nav .list-group-item {
  border: 1px solid rgba(0,0,0,0.06);
  padding: 0.75rem 1rem;
  font-size: 0.9rem;
  font-weight: 500;
  color: #333;
  transition: all 0.2s ease;
}
.policy-nav .list-group-item:hover {
  background-color: #f7f9f7;
  color: #1b2e25;
  border-left: 3px solid #d4a855;
}
.policy-nav .list-group-item.active {
  background-color: #1b2e25;
  border-color: #1b2e25;
  color: #ffffff;
  font-weight: 600;
}
.policy-content h2 {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: 1.5rem;
  font-weight: 700;
  color: #1b2e25;
  margin-top: 2rem;
  margin-bottom: 0.85rem;
  padding-bottom: 0.4rem;
  border-bottom: 1px solid #e9ecef;
}
.policy-content h3 {
  font-size: 1.15rem;
  font-weight: 600;
  color: #2c3e50;
  margin-top: 1.25rem;
  margin-bottom: 0.5rem;
}
.policy-content p, .policy-content li {
  font-size: 0.95rem;
  color: #495057;
}
.policy-highlight-box {
  background: #fdfaf2;
  border-left: 4px solid #d4a855;
  padding: 1.1rem 1.25rem;
  border-radius: 6px;
  margin: 1.25rem 0;
}
.policy-notice-box {
  background: #f0f7f3;
  border-left: 4px solid #1b2e25;
  padding: 1.1rem 1.25rem;
  border-radius: 6px;
  margin: 1.25rem 0;
}
.policy-owner-input {
  background: #fff8e6;
  border: 1px dashed #d4a855;
  color: #856404;
  padding: 2px 8px;
  border-radius: 4px;
  font-weight: 600;
  display: inline-block;
  font-size: 0.85rem;
}
.legal-review-note {
  background: #f8f9fa;
  border: 1px solid #dee2e6;
  border-left: 3px solid #6c757d;
  padding: 0.65rem 0.9rem;
  border-radius: 4px;
  font-size: 0.825rem;
  color: #6c757d;
  margin: 0.85rem 0;
}
</style>

