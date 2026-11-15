<?php
/**
 * ANSH THRIFTS — Terms & Conditions
 * terms.php
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Terms & Conditions · ' . APP_NAME;
$activeNav = '';
$policyTitle = 'Terms & Conditions';
$policySubtitle = 'Rules, user agreements, and commercial terms governing your use of ANSH THRIFTS';
$policyLastUpdated = 'November 2026';
$activePolicy = 'terms';

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
          <h5 class="fw-bold mb-1" style="color: #1b2e25;">Welcome to ANSH THRIFTS</h5>
          <p class="mb-0 small text-muted">
            These Terms & Conditions ("Terms", "Agreement") constitute a legally binding electronic contract under the Information Technology Act, 2000 between you ("User", "Customer", "You") and <strong><?= e(APP_NAME) ?></strong> ("Store", "we", "us"). By accessing our website, browsing our collections, or placing an order, you agree to comply with and be bound by these terms.
          </p>
        </div>

        <h2>1. Nature of the Service & Pre-Loved Merchandise</h2>
        <p>
          ANSH THRIFTS operates an online sustainable fashion storefront offering pre-owned, vintage, archive, and curated second-hand clothing and accessories.
        </p>
        <ul>
          <li><strong>Unique Single-Item Inventory:</strong> Unless otherwise specified, each apparel item listed is a unique 1-of-1 piece. When you purchase an item, it is marked as out-of-stock and reserved exclusively for your order.</li>
          <li><strong>Condition Disclosures:</strong> Because items are vintage or previously owned, subtle wear, fading, distressing, or vintage laundering characteristics are normal attributes of circular fashion. We provide detailed photographs, measurements, and condition notes for every item.</li>
          <li><strong>Accuracy of Information:</strong> We make reasonable efforts to display garments accurately, including true colors and measurements. However, actual colors may vary slightly depending on monitor configurations and ambient lighting.</li>
        </ul>

        <h2>2. Eligibility & User Account Responsibility</h2>
        <p>
          You represent that you are at least 18 years of age or accessing the website under the direct supervision of a parent or legal guardian capable of entering into binding contracts under the Indian Contract Act, 1872. You agree to provide accurate, truthful, and complete delivery and contact information during checkout.
        </p>

        <h2>3. Orders, Pricing, and Stock Confirmation</h2>
        <ul>
          <li><strong>Pricing:</strong> All prices displayed on our website are denominated in <strong>Indian Rupees (INR ₹)</strong>. <span class="policy-owner-input">[OWNER INPUT REQUIRED: Specify whether prices are inclusive of GST or if GST applies, e.g., "Prices are inclusive of all applicable taxes."]</span>.</li>
          <li><strong>Order Acceptance:</strong> Placement of an order represents an offer to purchase. An order is formally confirmed only when payment authorization is received via Razorpay (for online payments) or when an automated or manual confirmation message is dispatched (for COD orders).</li>
          <li><strong>Inventory Synchronization & Race Conditions:</strong> Given the one-of-one nature of vintage stock, in the rare event of simultaneous checkout or concurrency conflicts, our backend stock validation ensures no duplicate sales occur. If a payment is captured for an item whose stock was exhausted simultaneously, an immediate 100% full refund will be initiated via Razorpay.</li>
          <li><strong>Right of Refusal / Cancellation:</strong> We reserve the right to refuse or cancel any order for reasons including typographical pricing errors, unserviceable pin codes, non-response to COD verification calls, or suspected fraudulent activity.</li>
        </ul>

        <h2>4. Payment, Gateway Security, and Taxes</h2>
        <p>
          Online payments are processed securely through <strong>Razorpay Software Private Limited</strong>. By selecting Razorpay, you agree to adhere to Razorpay’s user terms. We do not store or capture debit card numbers, credit card CVVs, or UPI PINs on our servers.
        </p>

        <h2>5. Shipping, Delivery, and Courier Delays</h2>
        <p>
          Delivery obligations are governed by our separate <a href="/shipping-policy.php">Shipping & Delivery Policy</a>. While we commit to prompt dispatch with leading courier partners, ANSH THRIFTS is not liable for logistics delays caused by Force Majeure events, regional festivals, adverse weather, or courier hub transit disruptions outside our reasonable control.
        </p>

        <h2>6. Intellectual Property Rights</h2>
        <p>
          All creative material on this website—including website styling, graphics, photography, banners, text copy, code scripts, logos, and the trademark "ANSH THRIFTS"—is the proprietary property of ANSH THRIFTS and protected under Indian copyright, trademark, and intellectual property laws.
        </p>
        <p class="small text-muted">
          <em>Note regarding brand names on vintage garments:</em> Mention of designer, sports, or heritage brand names (e.g., Nike, Levi's, Carhartt, Ralph Lauren) in product descriptions is strictly descriptive to accurately identify the authentic vintage or pre-owned item. ANSH THRIFTS is an independent reseller of vintage goods and is not affiliated with, sponsored by, or endorsed by the original trademark owners.
        </p>

        <h2>7. Prohibited Uses</h2>
        <p>You agree not to:</p>
        <ol>
          <li>Use automated scrapers, bots, or scripts to monitor, scrape, or extract pricing, product data, or images.</li>
          <li>Interfere with or compromise the security or integrity of our website, servers, API endpoints, or database spreadsheets.</li>
          <li>Place fictitious, fraudulent, or speculative orders, including repeated unpaid COD refusals.</li>
          <li>Submit unlawful, defamatory, or abusive messages through our customer contact portal.</li>
        </ol>

        <h2>8. Limitation of Liability</h2>
        <p>
          To the maximum extent permitted by applicable Indian law, ANSH THRIFTS, its founders, operators, and affiliates shall not be liable for any indirect, incidental, punitive, or consequential damages resulting from the use of or inability to use this website, or from any garments purchased, beyond the actual invoice amount paid by you for the specific item in dispute.
        </p>

        <h2>9. Governing Law and Dispute Resolution</h2>
        <p>
          These Terms shall be interpreted and governed in accordance with the laws of the Republic of India. Any legal dispute, controversy, or claim arising out of or related to these Terms or orders placed on ANSH THRIFTS shall be subject to the exclusive jurisdiction of the competent civil courts situated at <span class="policy-owner-input">[OWNER INPUT REQUIRED: City, State, e.g., Mumbai, Maharashtra / Pune, Maharashtra]</span>, India.
        </p>

        <div class="legal-review-note mt-4">
          <strong>[LEGAL REVIEW NOTE: Indian Contract Act & IT Act]</strong> These electronic terms satisfy Section 10A of the Information Technology Act, 2000 for electronic contracts. Legal counsel should verify local court jurisdiction clauses and stamp duty/GST applicability.
        </div>

      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

