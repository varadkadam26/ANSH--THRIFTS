<?php
/**
 * ANSH THRIFTS — Payment Policy
 * payment-policy.php
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Payment Policy · ' . APP_NAME;
$activeNav = '';
$policyTitle = 'Payment Policy';
$policySubtitle = 'Payment methods, security standards, Razorpay gateway processing, and COD terms';
$policyLastUpdated = 'November 2026';
$activePolicy = 'payment';

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
          <h5 class="fw-bold mb-1" style="color: #1b2e25;">100% Encrypted & Safe Payments</h5>
          <p class="mb-0 small text-muted">
            At <strong><?= e(APP_NAME) ?></strong>, we use banking-grade encryption and partner with industry-leading payment gateways to guarantee the security of every transaction. All prices are listed in <strong>Indian Rupees (INR ₹)</strong>.
          </p>
        </div>

        <h2>1. Accepted Online Payment Methods (via Razorpay)</h2>
        <p>
          We offer instant, secure digital checkout powered by <strong>Razorpay Software Private Limited</strong>, an authorized payment aggregator compliant with the Reserve Bank of India (RBI) regulations and certified <strong>PCI-DSS Level 1</strong>. You can pay via:
        </p>
        <ul>
          <li><strong>Unified Payments Interface (UPI):</strong> Google Pay, PhonePe, Paytm, BHIM, Cred, Amazon Pay, and all major bank UPI apps (Zero convenience fee).</li>
          <li><strong>Credit & Debit Cards:</strong> Visa, MasterCard, RuPay, Maestro, Diners Club, and American Express cards issued by Indian and select international banks.</li>
          <li><strong>Internet Banking (NetBanking):</strong> 50+ Indian commercial and public sector banks including HDFC, ICICI, SBI, Axis, Kotak, and Bank of Baroda.</li>
          <li><strong>Digital Wallets:</strong> Paytm Wallet, PhonePe Wallet, MobiKwik, and FreeCharge.</li>
        </ul>

        <h2>2. Payment Security Standards</h2>
        <p>
          Your financial security is our highest priority:
        </p>
        <ul>
          <li><strong>Zero Card Data Retention:</strong> ANSH THRIFTS does <strong>not</strong> collect, process, or store your debit/credit card numbers, CVV codes, bank credentials, or UPI PINs on any server or Google Sheets database.</li>
          <li><strong>End-to-End Encryption:</strong> All checkout sessions communicate over SSL/TLS 256-bit encryption. Payment credentials are tokenized directly within Razorpay's certified iframe.</li>
          <li><strong>Mandatory Two-Factor Authentication (2FA):</strong> All card transactions adhere to RBI guidelines requiring dynamic One-Time Passwords (OTP) or UPI MPIN verification.</li>
        </ul>

        <h2>3. Cash on Delivery (COD) Terms & Conditions</h2>
        <p>
          For buyers who prefer paying at their doorstep, we provide a Cash on Delivery option under the following terms:
        </p>
        <ul>
          <li><strong>Order Limit:</strong> COD may be restricted to orders up to <span class="policy-owner-input">[OWNER INPUT REQUIRED: e.g., ₹3,000 or ₹5,000 maximum]</span>.</li>
          <li><strong>Telephonic / OTP Verification:</strong> To prevent bogus orders, our team contacts all COD buyers via WhatsApp or phone call prior to parcel dispatch. Orders remaining unverified after <span class="policy-owner-input">[OWNER INPUT REQUIRED: e.g., 24 hours]</span> are automatically cancelled.</li>
          <li><strong>Exact Cash Requirement:</strong> Please keep the exact payable amount ready in Indian currency at the time of delivery. Delivery executives may not always carry change.</li>
          <li><strong>Inspection Policy:</strong> As per logistics partner industry standard, <span class="fw-semibold">"Open Delivery" (opening the sealed package prior to making payment to the courier executive) is strictly prohibited</span>. You must accept and pay for the parcel before opening it. If any issue arises thereafter, record your continuous unboxing video and refer to our <a href="/refund-policy.php">Return & Refund Policy</a>.</li>
        </ul>

        <h2>4. Failed, Pending, or Debited Transactions</h2>
        <p>
          Occasionally, network interruptions, bank server slowdowns, or delayed OTP confirmations may cause a transaction to drop:
        </p>
        <ol>
          <li><strong>Amount debited from bank but order not confirmed:</strong> Your funds are securely held in Razorpay’s clearing pool or your issuing bank’s gateway. In 99% of cases, the payment aggregator’s automated reconciliation system automatically rolls back the full amount to your bank account within <span class="policy-owner-input">[OWNER INPUT REQUIRED: e.g., 24 to 48 banking hours]</span>.</li>
          <li><strong>Assistance:</strong> If your bank account was debited and you did not receive an order confirmation within 2 hours, please email your transaction reference or bank screenshot to <span class="policy-owner-input">[OWNER INPUT REQUIRED: payments@anshthrifts.in or support email]</span>. We will cross-verify with Razorpay and immediately confirm your order or process a full manual refund.</li>
        </ol>

        <h2>5. Fraud Prevention & Chargebacks</h2>
        <p>
          We employ automated IP verification, session validation, and CSRF token integrity checks on every checkout request. Any attempt to use stolen credit cards, forged payment receipts, or fraudulent chargeback claims will be reported to appropriate law enforcement agencies and cybercrime authorities under the Information Technology Act, 2000.
        </p>

        <div class="legal-review-note mt-4">
          <strong>[LEGAL REVIEW NOTE: RBI & NPCI Guidelines]</strong> All digital payment flows comply with Reserve Bank of India circulars regarding card data tokenization, 2-factor authentication, and payment aggregators.
        </div>

      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

