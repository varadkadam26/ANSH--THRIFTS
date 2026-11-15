<?php
/**
 * ANSH THRIFTS — Contact Us Page (contact.php)
 * Handles customer inquiries and saves them directly to Google Sheets "Contact Messages" tab.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/GoogleSheetsService.php';

$pageTitle = 'Contact Us · ' . APP_NAME;
$activeNav = 'contact';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? 'Thrift Store Inquiry');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        setFlash('danger', 'Please complete your Name, Email, and Message.');
    } else {
        try {
            $sheets = new GoogleSheetsService();
            $msgId = 'MSG-' . date('Ymd') . '-' . rand(100, 999);
            
            $sheets->insertRecord('Contact Messages', [
                'Message ID' => $msgId,
                'Sender Name' => $name,
                'Email' => $email,
                'Phone' => $phone,
                'Subject' => $subject,
                'Message' => $message,
                'Status' => 'New',
                'Received At' => date('Y-m-d H:i:s'),
                'Responded At' => '',
            ]);

            setFlash('success', 'Thank you! Your message has been received. Our team will get back to you shortly.');
            redirect('/contact.php');
        } catch (Throwable $e) {
            setFlash('danger', 'Unable to submit your message at this moment: ' . $e->getMessage());
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div style="background: url('/static/images/login-img.jpg') no-repeat center center/cover; background-attachment: fixed; min-height: calc(100vh - 140px); display: flex; align-items: center; padding: 100px 16px 40px;">
  <div class="container d-flex justify-content-center">
    <div class="glass-card w-100" style="max-width: 680px;">
      <h2 style="font-family: 'Playfair Display', serif; margin-bottom: 20px;">Get In Touch</h2>
      <p class="text-white-50 mb-4">Have questions about an item, sizing, or your delivery? Drop us a line.</p>

      <!-- Contact Highlights -->
      <div class="row mb-4 text-center contact-info">
        <div class="col-12 col-sm-4 mb-3 mb-sm-0">
          <div class="service-icon">📍</div>
          <p class="small mb-0 text-white">43 Raymouth Rd, Baltemoer, London 3910</p>
        </div>
        <div class="col-12 col-sm-4 mb-3 mb-sm-0">
          <div class="service-icon">📧</div>
          <p class="small mb-0 text-white"><a href="mailto:mitramsolutions@gmail.com" class="text-white text-decoration-underline">mitramsolutions@gmail.com</a></p>
        </div>
        <div class="col-12 col-sm-4">
          <div class="service-icon">📞</div>
          <p class="small mb-0 text-white">+91 98765 43210</p>
        </div>
      </div>

      <!-- Contact Form -->
      <form method="POST" action="/contact.php" class="contact-form text-start">
        <div class="row g-3">
          <div class="col-12 col-md-6">
            <label class="form-label text-white small">Your Name *</label>
            <input type="text" class="form-control" name="name" placeholder="John Doe" required>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label text-white small">Email Address *</label>
            <input type="email" class="form-control" name="email" placeholder="john@example.com" required>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label text-white small">Phone Number</label>
            <input type="tel" class="form-control" name="phone" placeholder="+91 9876543210">
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label text-white small">Subject</label>
            <input type="text" class="form-control" name="subject" placeholder="Question regarding product #">
          </div>
          <div class="col-12">
            <label class="form-label text-white small">Message *</label>
            <textarea class="form-control" name="message" rows="4" placeholder="How can we help you today?" required style="border-radius: 14px;"></textarea>
          </div>
          <div class="col-12 text-center mt-3">
            <button type="submit" class="btn btn-custom w-100 py-3">Send Message</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

