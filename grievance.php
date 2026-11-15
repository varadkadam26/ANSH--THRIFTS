<?php
/**
 * ANSH THRIFTS — Grievance Redressal Policy
 * grievance.php
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Grievance Redressal Policy · ' . APP_NAME;
$activeNav = '';
$policyTitle = 'Grievance Redressal Policy';
$policySubtitle = 'Statutory consumer complaint escalation framework under Consumer Protection & IT Rules';
$policyLastUpdated = 'November 2026';
$activePolicy = 'grievance';

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
          <h5 class="fw-bold mb-1" style="color: #1b2e25;">Statutory Grievance Redressal Mechanism</h5>
          <p class="mb-0 small text-muted">
            In compliance with the <strong>Consumer Protection Act, 2019</strong>, the <strong>Consumer Protection (E-Commerce) Rules, 2020</strong>, and the <strong>Information Technology (Intermediary Guidelines and Digital Media Ethics Code) Rules, 2021</strong>, <strong><?= e(APP_NAME) ?></strong> has established a time-bound, accessible grievance resolution procedure to address customer disputes fairly and transparently.
          </p>
        </div>

        <h2>1. Level 1: Standard Customer Support</h2>
        <p>
          For general product inquiries, sizing checks, parcel tracking updates, or initial post-delivery concerns, please first reach out to our primary customer care team:
        </p>
        <ul>
          <li><strong>Online Contact Form:</strong> <a href="/contact.php">Submit a support message</a></li>
          <li><strong>Email Support:</strong> <span class="policy-owner-input">[OWNER INPUT REQUIRED: support@anshthrifts.in or primary email]</span></li>
          <li><strong>WhatsApp / Phone Support:</strong> <span class="policy-owner-input">[OWNER INPUT REQUIRED: Customer Support Number]</span></li>
          <li><strong>Operating Hours:</strong> <span class="policy-owner-input">[OWNER INPUT REQUIRED: e.g., Monday to Saturday, 10:00 AM – 7:00 PM IST]</span></li>
          <li><strong>Standard Resolution Timeline:</strong> We endeavour to acknowledge and resolve general support inquiries within 24 to 48 business hours.</li>
        </ul>

        <h2>2. Level 2: Formal Escalation to the Grievance Officer</h2>
        <p>
          If your issue has not been resolved satisfactorily through our Level 1 customer support, or if you wish to report an infringement of statutory consumer rights, data privacy violation, or intellectual property complaint, you may formally escalate the matter to our designated Grievance Officer.
        </p>

        <div class="p-4 bg-light rounded-3 border my-3">
          <h5 class="fw-bold mb-3 text-dark">Designated Grievance Officer Details</h5>
          <div class="row g-2">
            <div class="col-sm-4 text-muted small fw-semibold">Name & Designation:</div>
            <div class="col-sm-8"><span class="policy-owner-input">[OWNER INPUT REQUIRED: Full Name of Grievance Officer]</span>, Grievance Officer</div>
            
            <div class="col-sm-4 text-muted small fw-semibold">Entity Name:</div>
            <div class="col-sm-8"><?= e(APP_NAME) ?></div>

            <div class="col-sm-4 text-muted small fw-semibold">Physical Office Address:</div>
            <div class="col-sm-8"><span class="policy-owner-input">[OWNER INPUT REQUIRED: Registered Business Address, Street, City, State, PIN Code]</span>, India</div>

            <div class="col-sm-4 text-muted small fw-semibold">Direct Grievance Email:</div>
            <div class="col-sm-8"><span class="policy-owner-input">[OWNER INPUT REQUIRED: grievance@anshthrifts.in or designated legal email]</span></div>

            <div class="col-sm-4 text-muted small fw-semibold">Direct Contact Number:</div>
            <div class="col-sm-8"><span class="policy-owner-input">[OWNER INPUT REQUIRED: Official Contact Number]</span></div>
          </div>
        </div>

        <h2>3. How to Submit a Formal Grievance</h2>
        <p>To ensure swift investigation, please include the following in your formal complaint email:</p>
        <ol>
          <li>Subject Line format: <code>Formal Grievance: Order ID #[Your ID] - [Brief Summary]</code></li>
          <li>Your Full Legal Name, Registered Mobile Number, and Delivery Address.</li>
          <li>Comprehensive description of the grievance and history of prior Level 1 communication.</li>
          <li>Relevant supporting documentation (e.g., invoices, payment transaction IDs, photographs, courier AWB tracking receipt, or raw unboxing video link).</li>
          <li>The specific resolution or remedy you are seeking.</li>
        </ol>

        <h2>4. Statutory Acknowledgment and Redressal Timelines</h2>
        <p>
          Under the Consumer Protection (E-Commerce) Rules, 2020:
        </p>
        <div class="table-responsive my-3">
          <table class="table table-bordered small">
            <thead class="table-dark">
              <tr>
                <th style="width: 35%;">Stage</th>
                <th>Statutory Requirement</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><strong>Formal Acknowledgment</strong></td>
                <td>Within <strong>48 hours</strong> of receipt of the grievance, an acknowledgment and unique tracking/ticket reference number will be issued to the complainant.</td>
              </tr>
              <tr>
                <td><strong>Investigation & Redressal</strong></td>
                <td>Within <strong>1 month (30 days)</strong> from the date of receipt, the Grievance Officer will conduct an impartial inquiry and convey the final resolution in writing to the consumer.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <h2>5. National Consumer Helpline (NCH) Redressal Option</h2>
        <p>
          In the event that you remain dissatisfied after exhausting our internal grievance redressal mechanism, you have the statutory right under Indian law to register your complaint with the Government of India's National Consumer Helpline:
        </p>
        <ul>
          <li><strong>Toll-Free National Helpline:</strong> 1915 or 1800-11-4000</li>
          <li><strong>Online Portal:</strong> <a href="https://consumerhelpline.gov.in" target="_blank" rel="noopener">consumerhelpline.gov.in</a></li>
          <li><strong>E-Daakhil Portal:</strong> <a href="https://edaakhil.nic.in" target="_blank" rel="noopener">edaakhil.nic.in</a> (for filing online consumer complaints in District / State Consumer Commissions)</li>
        </ul>

        <div class="legal-review-note mt-4">
          <strong>[LEGAL REVIEW NOTE: Consumer Protection (E-Commerce) Rules 2020, Rule 4(4)]</strong> Appointment of a Grievance Officer resident in India, along with publication of their name, designation, and contact details on the website, is a mandatory statutory requirement for all e-commerce entities operating in India.
        </div>

      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

