<?php
/**
 * ANSH THRIFTS — Refund, Return & Exchange Policy
 * refund-policy.php
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Return, Refund & Exchange Policy · ' . APP_NAME;
$activeNav = '';
$policyTitle = 'Return & Refund Policy';
$policySubtitle = 'Transparent guidelines tailored for vintage, pre-loved, and one-of-a-kind thrift fashion';
$policyLastUpdated = 'November 2026';
$activePolicy = 'refund';

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

        <div class="card mt-4 border-0 shadow-sm bg-light">
          <div class="card-body p-3">
            <h6 class="fw-bold text-dark mb-2" style="font-size: 0.9rem;">Need to report an issue?</h6>
            <p class="text-muted small mb-2">Wrong item or transit damage? Contact us within the notice window with your unboxing video.</p>
            <a href="/contact.php" class="btn btn-sm btn-outline-dark w-100">Raise Ticket</a>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="col-lg-9">
      <div class="policy-content bg-white p-4 p-md-5 rounded-3 shadow-sm border">

        <div class="policy-highlight-box">
          <h5 class="fw-bold mb-2" style="color: #1b2e25;">Essential Notice: The Nature of Thrift & Vintage Goods</h5>
          <p class="mb-0 small text-muted">
            All merchandise available at <strong><?= e(APP_NAME) ?></strong> consists of <strong>pre-loved, second-hand, vintage, or archival pieces</strong>. Unless specifically tagged as "Brand New with Tags" (Deadstock), items have had previous owners and life cycles. Each garment is unique and strictly <strong>1-of-1 in stock</strong>. Because no replacement piece exists in identical condition, size, or wash, our return and exchange guidelines differ from conventional fast-fashion retail.
          </p>
        </div>

        <h2>1. General "Final Sale" Rule</h2>
        <p>
          Except in explicit instances of genuine transit loss, delivery of an incorrect item, or undisclosed gross damage as specified in Section 4 below, <strong>all purchases made on ANSH THRIFTS are Final Sale</strong>. We do not accept returns or offer refunds for:
        </p>
        <ul>
          <li><strong>Change of mind or personal preference</strong> (e.g., buyer decided they no longer desire the item).</li>
          <li><strong>Sizing or fit discrepancies:</strong> Vintage clothing labels frequently deviate from modern sizing. We provide exact flat measurements (chest, length, waist, inseam) in product descriptions. Please check your measurements prior to placing an order.</li>
          <li><strong>Inherent vintage characteristics:</strong> Subtle fading, authentic vintage distressing, patina, gentle wash wear, micro-pilling, or replaced vintage tags that were photographed or mentioned in the item's condition rating.</li>
          <li><strong>Slight colour variations:</strong> Differences caused by natural studio illumination, varying monitor color profiles, or smartphone screen calibrations.</li>
        </ul>

        <h2>2. Condition Grading & Product Accuracy</h2>
        <p>
          We pride ourselves on honest, painstaking inspection of every garment. Every product listing on our website includes high-resolution photographs and a descriptive condition rating (e.g., <em>Mint / Excellent / Good Vintage Condition / Distressed</em>). Any notable flaws, such as missing buttons, micro-pinholes, or hem wear, are photographed and disclosed in the product description.
        </p>

        <h2>3. Non-Exchangeability of Thrift Inventory</h2>
        <p>
          Because our inventory consists exclusively of unique single pieces, <strong>we cannot offer size, style, or color exchanges</strong>. Once a vintage piece is sold, there is no duplicate inventory unit available to replace it.
        </p>

        <h2>4. Eligible Exceptions for Return / Refund</h2>
        <p>
          We will promptly resolve situations where an error originated on our end. You are eligible for a replacement (if an agreeable alternative thrift credit exists) or a full refund under the following strict conditions:
        </p>
        <ol>
          <li>
            <strong>Completely Wrong Item Delivered:</strong> The garment received does not correspond to the product order SKU or photos purchased (e.g., received a denim jacket instead of a graphic tee).
          </li>
          <li>
            <strong>Gross Undisclosed Defect:</strong> The item arrives with substantial structural damage (such as major unlisted rips or non-functional main zippers) that was neither pictured nor mentioned in the listing.
          </li>
          <li>
            <strong>Parcel Lost in Transit:</strong> If the logistics carrier officially declares the parcel lost or unlocatable.
          </li>
        </ol>

        <div class="policy-notice-box">
          <h6 class="fw-bold mb-1"><i class="bi bi-camera-video me-1"></i> Mandatory 360° Unboxing Video Requirement</h6>
          <p class="mb-0 small text-muted">
            To guard against fraudulent claims and ensure mutual fairness, <strong>an uncut, raw, continuous 360° unboxing video</strong> recorded from before opening the exterior sealed courier flyer/box through the complete unwrapping and inspection of the garment is mandatory for all wrong-item or transit-damage claims. The shipping label must be clearly legible at the start of the recording.
          </p>
        </div>

        <h2>5. Claim Submission Window & Procedure</h2>
        <p>
          To file a valid claim:
        </p>
        <ul>
          <li><strong>Notice Window:</strong> You must email or message our support team within <span class="policy-owner-input">[OWNER INPUT REQUIRED: e.g., 24 to 48 hours]</span> of the tracking timestamp confirming delivery. Late claims cannot be entertained under any circumstance.</li>
          <li><strong>Evidence Required:</strong> Email your Order ID, clear photos of the packaging label, photos of the claimed discrepancy, and a link or attachment of the uninterrupted unboxing video to <span class="policy-owner-input">[OWNER INPUT REQUIRED: returns@anshthrifts.in or support email]</span>.</li>
          <li><strong>Verification:</strong> Our team will verify the claim against our pre-dispatch packaging logs and notify you of the approval or rejection within <span class="policy-owner-input">[OWNER INPUT REQUIRED: e.g., 2 business days]</span>.</li>
        </ul>

        <h2>6. Return Shipping & Reverse Pickups</h2>
        <p>
          If your return claim is approved:
        </p>
        <ul>
          <li><strong>Reverse Logistics:</strong> <span class="policy-owner-input">[OWNER INPUT REQUIRED: Specify whether ANSH THRIFTS arranges a reverse pickup or if the customer self-ships via speed post with courier fee reimbursement up to a capped amount, e.g., ₹100]</span>.</li>
          <li><strong>Item Condition:</strong> The item must be returned in the identical condition received, unworn, unwashed, unaltered, and with any attached store tags intact. If an item is returned damaged, worn, or with scent/perfume applied, the return will be rejected and the item returned to the customer at their expense.</li>
        </ul>

        <h2>7. Refund Method & Processing Timelines</h2>
        <p>
          Upon safe receipt and quality verification of the returned merchandise at our facility:
        </p>
        <ul>
          <li><strong>Online Prepaid Orders (Razorpay):</strong> Refunds will be credited back to the original source payment instrument (Bank Account, UPI, or Credit Card) via Razorpay. The turnaround time is typically <span class="policy-owner-input">[OWNER INPUT REQUIRED: e.g., 5 to 7 business days]</span> depending on your issuing bank.</li>
          <li><strong>Cash on Delivery (COD) Orders:</strong> If COD was selected and delivered, the refund will be remitted to the customer via direct NEFT/IMPS or UPI ID provided during the claim process within <span class="policy-owner-input">[OWNER INPUT REQUIRED: e.g., 5 to 7 business days]</span>.</li>
          <li><strong>Store Credit Option:</strong> Customers may optionally select store credit / gift voucher for future purchases on ANSH THRIFTS with immediate issuance upon inspection.</li>
        </ul>

        <div class="legal-review-note mt-4">
          <strong>[LEGAL REVIEW NOTE: Consumer Protection E-Commerce Rules 2020]</strong> Under Rule 5(3)(e) of the Consumer Protection (E-Commerce) Rules 2020, every marketplace/seller must explicitly publish its return, refund, and exchange policy, including circumstances where returns are not accepted. The unboxing video clause and single-item thrift nature must be explicitly brought to the customer's attention prior to payment completion.
        </div>

      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

