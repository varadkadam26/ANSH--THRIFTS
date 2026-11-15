<?php
/**
 * ANSH THRIFTS — Shipping & Delivery Policy
 * shipping-policy.php
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Shipping & Delivery Policy · ' . APP_NAME;
$activeNav = '';
$policyTitle = 'Shipping & Delivery Policy';
$policySubtitle = 'Dispatch timelines, domestic coverage across India, tracking updates, and handling';
$policyLastUpdated = 'November 2026';
$activePolicy = 'shipping';

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
          <h5 class="fw-bold mb-1" style="color: #1b2e25;">Domestic Shipping Across India</h5>
          <p class="mb-0 small text-muted">
            At <strong><?= e(APP_NAME) ?></strong>, we are committed to delivering your vintage finds safely and swiftly to pin codes across India. Every thrift piece undergoes thorough inspection, gentle sanitization/laundering, and eco-conscious protective packaging before handover to our delivery partners.
          </p>
        </div>

        <h2>1. Coverage & Serviceable Pin Codes</h2>
        <p>
          We currently ship across all major metropolitan cities, Tier 1, Tier 2, and most Tier 3 regions in India covered by leading domestic courier networks (including Delhivery, Blue Dart, DTDC, ExpressBees, and India Post Speed Post).
        </p>
        <p class="small text-muted">
          <em>Note:</em> We do not currently ship internationally. Deliveries are restricted exclusively to addresses within the Republic of India.
        </p>

        <h2>2. Order Processing & Dispatch Timelines</h2>
        <p>Because each garment is a vintage one-of-a-kind piece, every item is freshly reviewed and prepared prior to dispatch:</p>
        <ul>
          <li><strong>Processing & Preparation Window:</strong> Orders are packaged and dispatched within <span class="policy-owner-input">[OWNER INPUT REQUIRED: e.g., 24 to 48 business hours / 1 to 3 working days]</span> following order confirmation (excluding Sundays and national holidays).</li>
          <li><strong>Pre-Dispatch Sanitization:</strong> Our team inspects and prepares garments to ensure you receive them in the condition described.</li>
        </ul>

        <h2>3. Estimated Transit Times</h2>
        <p>Once dispatched from our facility, typical transit times are as follows:</p>
        <div class="table-responsive my-3">
          <table class="table table-bordered table-striped small mb-0">
            <thead class="table-dark">
              <tr>
                <th>Destination Region</th>
                <th>Estimated Transit Time</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Metro Cities (Mumbai, Delhi-NCR, Bangalore, Pune, Hyderabad, Chennai, Kolkata)</td>
                <td><span class="policy-owner-input">[OWNER INPUT REQUIRED: e.g., 2 to 4 working days]</span></td>
              </tr>
              <tr>
                <td>Tier 2 & State Capital Cities</td>
                <td><span class="policy-owner-input">[OWNER INPUT REQUIRED: e.g., 3 to 6 working days]</span></td>
              </tr>
              <tr>
                <td>Rest of India, Remote & North-East Locations</td>
                <td><span class="policy-owner-input">[OWNER INPUT REQUIRED: e.g., 5 to 9 working days]</span></td>
              </tr>
            </tbody>
          </table>
        </div>
        <p class="small text-muted">
          <em>Please note:</em> Estimated transit durations are provided by courier aggregators and may be subject to minor fluctuations during major festive sales, extreme monsoon weather, or regional transportation strikes.
        </p>

        <h2>4. Shipping Charges & Free Shipping Thresholds</h2>
        <ul>
          <li><strong>Prepaid Orders (UPI, Cards, NetBanking):</strong> <span class="policy-owner-input">[OWNER INPUT REQUIRED: e.g., Flat ₹60 across India / Free shipping on orders above ₹999]</span>.</li>
          <li><strong>Cash on Delivery (COD) Surcharge:</strong> <span class="policy-owner-input">[OWNER INPUT REQUIRED: e.g., Additional ₹50 COD convenience charge / Not applicable]</span>.</li>
          <li>All applicable shipping charges are clearly displayed in your order summary during cart review prior to final payment.</li>
        </ul>

        <h2>5. Order Tracking & Delivery Status</h2>
        <p>
          Once your package is dispatched, tracking details including the courier partner name and tracking airway bill (AWB) number are generated and updated in our delivery records. You will receive an SMS and/or email containing your active tracking link to monitor your parcel in real-time.
        </p>

        <h2>6. Failed Delivery Attempts & Non-Availability</h2>
        <p>
          Our courier partners typically make up to <strong>3 delivery attempts</strong> before marking a package as "Undelivered / Return to Origin (RTO)".
        </p>
        <ul>
          <li>Please ensure your contact number is active and reachable by the delivery executive.</li>
          <li>In the event of an RTO caused by an incorrect address, non-response, or unapproved rejection of a delivery, re-shipping charges of <span class="policy-owner-input">[OWNER INPUT REQUIRED: e.g., ₹80]</span> must be prepaid by the customer before re-dispatch.</li>
        </ul>

        <h2>7. Damaged Packaging or Tampering at Delivery</h2>
        <p>
          If the outer courier parcel appears noticeably cut, torn, taped with non-standard packaging tape, or otherwise heavily damaged upon arrival, <span class="fw-semibold">do not accept the package</span>. Take a clear photograph of the damaged outer bag, reject the delivery, and immediately notify us via <a href="/contact.php">contact support</a> with your order ID.
        </p>

        <div class="legal-review-note mt-4">
          <strong>[LEGAL REVIEW NOTE: Consumer Protection E-Commerce Rules]</strong> Rule 5(3)(e) requires explicit clarity regarding shipping and logistics fees, estimated delivery schedules, and responsibility for goods until safe delivery to the consumer.
        </div>

      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

