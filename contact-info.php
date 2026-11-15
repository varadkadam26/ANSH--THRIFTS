<?php
/**
 * ANSH THRIFTS — Official Contact Information
 * contact-info.php
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Contact Information & Business Details · ' . APP_NAME;
$activeNav = '';
$policyTitle = 'Official Contact Information';
$policySubtitle = 'Verified business coordinates, registered entity details, and communication channels';
$policyLastUpdated = 'November 2026';
$activePolicy = 'contact';

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
            <h6 class="fw-bold text-dark mb-2" style="font-size: 0.9rem;">Send an Inquiry</h6>
            <p class="text-muted small mb-2">Prefer our online contact form? Submit your note directly to our team.</p>
            <a href="/contact.php" class="btn btn-sm btn-outline-dark w-100">Go to Contact Form</a>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="col-lg-9">
      <div class="policy-content bg-white p-4 p-md-5 rounded-3 shadow-sm border">

        <div class="policy-highlight-box">
          <h5 class="fw-bold mb-1" style="color: #1b2e25;">Statutory Legal Disclosures</h5>
          <p class="mb-0 small text-muted">
            In compliance with Rule 4(1) and Rule 4(2) of the <strong>Consumer Protection (E-Commerce) Rules, 2020</strong>, the following official business details, physical address, and contact coordinates for <strong><?= e(APP_NAME) ?></strong> are provided for consumer knowledge and official notices.
          </p>
        </div>

        <h2>1. Legal Entity & Business Details</h2>
        <div class="p-4 bg-light rounded-3 border my-3">
          <div class="row g-3">
            <div class="col-sm-4 text-muted small fw-semibold">Brand / Platform Name:</div>
            <div class="col-sm-8 fw-bold text-dark"><?= e(APP_NAME) ?></div>

            <div class="col-sm-4 text-muted small fw-semibold">Legal Entity Type:</div>
            <div class="col-sm-8"><span class="policy-owner-input">[OWNER INPUT REQUIRED: Sole Proprietorship / Partnership / Private Limited Company]</span></div>

            <div class="col-sm-4 text-muted small fw-semibold">Registered Entity Name:</div>
            <div class="col-sm-8"><span class="policy-owner-input">[OWNER INPUT REQUIRED: Legal Registered Entity Name]</span></div>

            <div class="col-sm-4 text-muted small fw-semibold">Goods & Services Tax (GSTIN):</div>
            <div class="col-sm-8"><span class="policy-owner-input">[OWNER INPUT REQUIRED: 15-digit GSTIN or "Exempt / Registration Pending" if below threshold]</span></div>

            <div class="col-sm-4 text-muted small fw-semibold">Permanent Account No. (PAN):</div>
            <div class="col-sm-8"><span class="policy-owner-input">[OWNER INPUT REQUIRED: Business / Proprietor PAN if disclosed]</span></div>
          </div>
        </div>

        <h2>2. Physical Registered & Operational Address</h2>
        <div class="p-4 bg-light rounded-3 border my-3">
          <div class="row g-3">
            <div class="col-sm-4 text-muted small fw-semibold">Registered Office:</div>
            <div class="col-sm-8">
              <span class="policy-owner-input">[OWNER INPUT REQUIRED: Building Name, Street / Road, Landmark, City, District, State, PIN Code, India]</span>
            </div>

            <div class="col-sm-4 text-muted small fw-semibold">Fulfillment / Studio Facility:</div>
            <div class="col-sm-8">
              <span class="policy-owner-input">[OWNER INPUT REQUIRED: Dispatch Center / Studio Address or "Same as Registered Office"]</span>
            </div>
          </div>
        </div>

        <h2>3. Direct Customer Service Channels</h2>
        <p>You may reach our team through any of the following channels during working hours:</p>
        <div class="row g-3 my-2">
          <div class="col-md-6">
            <div class="p-3 border rounded-3 h-100 bg-white">
              <h6 class="fw-bold mb-1"><i class="bi bi-envelope me-1 text-primary"></i> Email Support</h6>
              <p class="small text-muted mb-1">For general inquiries, order status, or pre-sales questions:</p>
              <p class="mb-0 fw-semibold"><span class="policy-owner-input">[OWNER INPUT REQUIRED: contact@anshthrifts.in or support email]</span></p>
            </div>
          </div>
          <div class="col-md-6">
            <div class="p-3 border rounded-3 h-100 bg-white">
              <h6 class="fw-bold mb-1"><i class="bi bi-telephone me-1 text-success"></i> Phone & WhatsApp</h6>
              <p class="small text-muted mb-1">Direct telephonic customer service & quick messaging:</p>
              <p class="mb-0 fw-semibold"><span class="policy-owner-input">[OWNER INPUT REQUIRED: +91 XXXXX XXXXX]</span></p>
            </div>
          </div>
          <div class="col-md-6">
            <div class="p-3 border rounded-3 h-100 bg-white">
              <h6 class="fw-bold mb-1"><i class="bi bi-clock me-1 text-warning"></i> Operating Hours</h6>
              <p class="small text-muted mb-1">Customer support and dispatch operational hours:</p>
              <p class="mb-0 fw-semibold"><span class="policy-owner-input">[OWNER INPUT REQUIRED: Monday – Saturday, 10:00 AM – 7:00 PM IST]</span> (Closed on National Holidays)</p>
            </div>
          </div>
          <div class="col-md-6">
            <div class="p-3 border rounded-3 h-100 bg-white">
              <h6 class="fw-bold mb-1"><i class="bi bi-instagram me-1 text-danger"></i> Social Channels</h6>
              <p class="small text-muted mb-1">Follow our drops and DM us on social platforms:</p>
              <p class="mb-0 fw-semibold"><span class="policy-owner-input">[OWNER INPUT REQUIRED: @anshthrifts on Instagram]</span></p>
            </div>
          </div>
        </div>

        <h2>4. Statutory Grievance Redressal</h2>
        <p>
          For formal legal escalations or consumer complaints under the Consumer Protection Act, 2019, please consult our dedicated <a href="/grievance.php">Grievance Redressal Policy</a> for appointed officer credentials and statutory escalation steps.
        </p>

        <div class="legal-review-note mt-4">
          <strong>[LEGAL REVIEW NOTE: Consumer Protection (E-Commerce) Rules 2020, Rule 4(1)]</strong> An e-commerce entity is legally required to prominently display the legal name of the e-commerce entity, principal geographic address of its headquarters and all branches, the name and details of its website, and email and phone contact details.
        </div>

      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

