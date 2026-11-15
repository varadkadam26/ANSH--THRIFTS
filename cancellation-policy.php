<?php
/**
 * ANSH THRIFTS — Cancellation Policy
 * cancellation-policy.php
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Cancellation Policy · ' . APP_NAME;
$activeNav = '';
$policyTitle = 'Cancellation Policy';
$policySubtitle = 'Guidelines regarding pre-dispatch order modifications and cancellations';
$policyLastUpdated = 'November 2026';
$activePolicy = 'cancellation';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/policy_header.php';
?>

<div class="container my-5 policy-container">
  <div class="row g-4">
    <!-- Sidebar Navigation -->
    <div class="col-lg-3 d-none d-lg-block">
      <div class="policy-sidebar">
        <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: 0.75rem; letter-spacing: 1px;">Store Policies</h6>
        <div class="list-group policy-nav shadow-sm rounded-3">
          <?php foreach ($policyNavItems as $key => $item): ?>
            <a href="<?= e($item['url']) ?>" class="list-group-item list-group-item-action <?= $key === $activePolicy ? 'active' : '' ?>">
              <?= e($item['label']) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="col-lg-9">
      <div class="policy-content bg-white p-4 p-md-5 rounded-3 shadow-sm border">

        <div class="policy-highlight-box">
          <h5 class="fw-bold mb-1" style="color: #1b2e25;">Cancellation Window for Thrift Apparel</h5>
          <p class="mb-0 small text-muted">
            Because each vintage or pre-loved garment is unique (1-of-1), reserving an item removes it from our storefront and prevents other shoppers from purchasing it. We request our patrons to order mindfully. If you need to cancel, please review our cancellation guidelines below.
          </p>
        </div>

        <h2>1. Cancellation Prior to Dispatch</h2>
        <p>
          You may request cancellation of your order within <span class="policy-owner-input">[OWNER INPUT REQUIRED: e.g., 6 to 12 hours of order placement, or strictly before the order status is marked as 'Dispatched']</span>.
        </p>
        <ul>
          <li><strong>How to Cancel:</strong> Email our support team immediately at <span class="policy-owner-input">[OWNER INPUT REQUIRED: orders@anshthrifts.in or support email]</span> or message our official WhatsApp/Instagram handle with your <strong>Order ID</strong> and cancellation request.</li>
          <li><strong>Prepaid Orders (Razorpay):</strong> Upon receipt and confirmation of a pre-dispatch cancellation, a 100% full refund will be initiated back to your source payment method via Razorpay. It typically reflects in your bank or UPI account within <span class="policy-owner-input">[OWNER INPUT REQUIRED: e.g., 5 to 7 business days]</span>.</li>
        </ul>

        <h2>2. Post-Dispatch Ineligibility</h2>
        <p>
          <strong>Orders cannot be cancelled once the package has been handed over to our courier partner and an AWB tracking number is generated.</strong>
        </p>
        <p>
          Once in transit, the shipment cannot be intercepted. If you no longer wish to receive the order, please refer to our <a href="/refund-policy.php">Return & Refund Policy</a> upon receipt.
        </p>

        <h2>3. Cash on Delivery (COD) Cancellation & Refusal Policy</h2>
        <p>
          We offer Cash on Delivery to make vintage shopping accessible. However, dispatching COD packages incurs real packaging, courier, and return logistics expenses:
        </p>
        <ul>
          <li><strong>Order Verification:</strong> All COD orders are subject to telephonic or WhatsApp confirmation before processing. If our verification team is unable to reach you within <span class="policy-owner-input">[OWNER INPUT REQUIRED: e.g., 24 hours]</span>, the order will be automatically cancelled.</li>
          <li><strong>Doorstep Rejection:</strong> Refusing a COD parcel at your doorstep without a verified defect causes significant logistical waste. Accounts with repeated doorstep COD rejections will be permanently blacklisted from future Cash on Delivery privileges and limited to prepaid checkout.</li>
        </ul>

        <h2>4. Cancellation Initiated by ANSH THRIFTS</h2>
        <p>
          In rare circumstances, ANSH THRIFTS reserves the right to cancel an order:
        </p>
        <ol>
          <li><strong>Pre-Dispatch Quality Rejection:</strong> During final quality inspection prior to packaging, if our curators notice an undisclosed structural flaw or damage to the vintage piece that makes it unsuitable for sale, we will notify you immediately and issue an instant 100% full refund.</li>
          <li><strong>Unserviceable Destination:</strong> If your delivery PIN code is found to be unserviceable by all domestic courier partners.</li>
          <li><strong>Inventory Sync Race Condition:</strong> In the very rare event that two customers successfully pay for the same 1-of-1 piece at the exact same second, the earlier timestamped order is fulfilled and the second order is immediately refunded in full.</li>
          <li><strong>Fraudulent / Incomplete Details:</strong> Incomplete address, invalid contact phone number, or automated bot activities detected.</li>
        </ol>

        <div class="legal-review-note mt-4">
          <strong>[LEGAL REVIEW NOTE: Consumer Protection (E-Commerce) Rules 2020]</strong> Under Rule 6(5), an e-commerce entity cannot impose cancellation fees on consumers unless equivalent penalties are paid by the seller if they cancel the purchase. ANSH THRIFTS ensures 100% full refunds on all pre-dispatch cancellations without hidden cancellation deduction charges.
        </div>

      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

