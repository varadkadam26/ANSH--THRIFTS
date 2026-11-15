<?php
/**
 * ANSH THRIFTS — Privacy Policy
 * privacy-policy.php
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Privacy Policy · ' . APP_NAME;
$activeNav = '';
$policyTitle = 'Privacy Policy';
$policySubtitle = 'How ANSH THRIFTS collects, utilizes, safeguards, and respects your personal data';
$policyLastUpdated = 'November 2026';
$activePolicy = 'privacy';

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
            <h6 class="fw-bold text-dark mb-2" style="font-size: 0.9rem;">Need Assistance?</h6>
            <p class="text-muted small mb-2">Have questions about your data or account privacy?</p>
            <a href="/contact-info.php" class="btn btn-sm btn-outline-dark w-100">Contact Privacy Team</a>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="col-lg-9">
      <div class="policy-content bg-white p-4 p-md-5 rounded-3 shadow-sm border">
        
        <div class="policy-highlight-box">
          <h5 class="fw-bold mb-1" style="color: #1b2e25;">Commitment to Your Privacy</h5>
          <p class="mb-0 small text-muted">
            At <strong><?= e(APP_NAME) ?></strong> ("we", "our", or "us"), we operate a curated pre-loved thrift apparel platform. We recognize that you value your privacy, and we are dedicated to protecting the personal data you share when browsing our website (<a href="/"><?= e($_SERVER['HTTP_HOST'] ?? 'anshthrifts.in') ?></a>), purchasing vintage apparel, or contacting our team.
          </p>
        </div>

        <p class="small text-muted">
          This Privacy Policy describes our practices in accordance with the Information Technology Act, 2000, the Information Technology (Reasonable Security Practices and Procedures and Sensitive Personal Data or Information) Rules, 2011, and the Consumer Protection (E-Commerce) Rules, 2020 of the Republic of India.
        </p>

        <h2>1. Information We Collect</h2>
        <p>We collect only the information necessary to provide, fulfill, and improve your shopping experience:</p>
        <ul>
          <li><strong>Contact & Delivery Details:</strong> Full name, shipping destination address, active phone/mobile number, and email address collected during checkout or when submitting our contact inquiry form.</li>
          <li><strong>Order & Transaction Records:</strong> Purchased item SKU/names, order value, payment status, chosen shipping method (Online Payment / COD), and delivery fulfillment tracking status.</li>
          <li><strong>Payment Information:</strong> All payment transactions (UPI, Debit/Credit Card, NetBanking, Wallets) are handled directly through our PCI-DSS Level 1 compliant payment gateway partner, <strong>Razorpay</strong>. <span class="fw-semibold">ANSH THRIFTS never stores, records, or views your full credit card numbers, CVVs, UPI PINs, or banking passwords.</span></li>
          <li><strong>Technical & Browsing Data:</strong> Standard server logs, IP address, browser user-agent, operating system, and anonymous cookie identifiers used for cart session continuity and site security.</li>
        </ul>

        <h2>2. How We Store and Process Your Data</h2>
        <p>In accordance with our application architecture, order records, delivery tracking details, customer contact messages, and inventory statuses are securely stored and synced through authorized, encrypted connections to our Google Cloud / Google Workspace storage systems (Google Sheets API v4 with Service Account OAuth2 credentials). We maintain strict principle-of-least-privilege access controls.</p>

        <div class="policy-notice-box">
          <h6 class="fw-bold mb-1">Data Storage & Service Account Credentials</h6>
          <p class="mb-0 small text-muted">
            All database communications occur over SSL/TLS encrypted transport. System credentials and private keys are isolated strictly outside public document directories and never transmitted to client browsers or third-party advertising trackers.
          </p>
        </div>

        <h2>3. Purposes for Which We Use Your Information</h2>
        <p>We process your personal information strictly for legitimate e-commerce operations, including:</p>
        <ol>
          <li>Processing, confirming, and dispatching your unique thrift and vintage clothing orders.</li>
          <li>Generating delivery consignments with logistics and courier partners for doorstep transit across India.</li>
          <li>Sending automated transactional confirmations, order status updates, and tracking links via Email/SMS.</li>
          <li>Responding to your product condition questions, sizing inquiries, or grievance tickets.</li>
          <li>Preventing fraudulent orders, duplicate payment charges, and unauthorized administrative access.</li>
          <li>Complying with statutory accounting and consumer protection obligations under Indian law.</li>
        </ol>

        <h2>4. Disclosure of Personal Information to Third Parties</h2>
        <p>We do not sell, rent, or trade your personal information. Disclosures are limited solely to trusted operational service providers under confidential data protection obligations:</p>
        <ul>
          <li><strong>Payment Processors:</strong> Razorpay Software Private Limited for secure processing of online payments and instant UPI refunds (when applicable).</li>
          <li><strong>Logistics Partners:</strong> Third-party couriers (such as India Post, Delhivery, Blue Dart, DTDC, or Shiprocket) to execute physical parcel deliveries to your shipping address.</li>
          <li><strong>Google Cloud Platform:</strong> For encrypted backend data synchronization and automated inventory recording.</li>
          <li><strong>Statutory Authorities:</strong> When compelled by valid legal process, court orders, or law enforcement requests under applicable Indian jurisdiction.</li>
        </ul>

        <h2>5. Cookies and Local Storage</h2>
        <p>
          We use functional session cookies (such as standard PHP session identifiers) strictly to maintain your active shopping cart items, verify CSRF security tokens, and maintain authenticated admin dashboard sessions. We do not employ intrusive cross-site surveillance advertising cookies. You may disable cookies through your browser settings, though doing so may prevent adding items to your cart or completing checkout.
        </p>

        <h2>6. Data Retention Policy</h2>
        <p>
          We retain order, invoice, and delivery records for a minimum duration of <span class="policy-owner-input">[OWNER INPUT REQUIRED: Standard financial retention period, e.g., 7 years]</span> as mandated by Indian taxation and commercial record-keeping regulations. Non-transactional inquiries submitted via the contact form are retained for <span class="policy-owner-input">[OWNER INPUT REQUIRED: e.g., 180 days]</span> before periodic pruning.
        </p>

        <h2>7. Your Rights and Choices</h2>
        <p>As a valued patron, you have the right to:</p>
        <ul>
          <li>Request confirmation of whether we hold personal data relating to you.</li>
          <li>Request correction or updating of inaccurate contact or shipping details before dispatch.</li>
          <li>Request erasure of non-statutory personal data, subject to completion of ongoing orders and regulatory record-keeping requirements.</li>
          <li>Opt-out of promotional communications at any time.</li>
        </ul>

        <h2>8. Grievance Officer & Contact</h2>
        <p>
          In accordance with the Information Technology Act 2000 and the Consumer Protection (E-Commerce) Rules 2020, our appointed Grievance Officer can be contacted for privacy or data concerns at:
        </p>
        <div class="p-3 bg-light rounded-3 border">
          <p class="mb-1"><strong>Name / Designation:</strong> <span class="policy-owner-input">[OWNER INPUT REQUIRED: Legal Name / Grievance Officer]</span></p>
          <p class="mb-1"><strong>Store Entity:</strong> <?= e(APP_NAME) ?></p>
          <p class="mb-1"><strong>Email:</strong> <span class="policy-owner-input">[OWNER INPUT REQUIRED: privacy@anshthrifts.in or contact email]</span></p>
          <p class="mb-0"><strong>Address:</strong> <span class="policy-owner-input">[OWNER INPUT REQUIRED: Physical Business Address, City, State, PIN, India]</span></p>
        </div>

        <div class="legal-review-note mt-4">
          <strong>[LEGAL REVIEW NOTE: Indian DPDP Act 2023]</strong> This policy reflects current IT Act 2000 and IT Rules 2011 standards. Upon complete notification of the Digital Personal Data Protection Act (DPDPA) 2023 rules by the Government of India, additional consent management and data fiduciary disclosures should be reviewed by legal counsel.
        </div>

      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

