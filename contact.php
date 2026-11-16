<?php
/**
 * ANSH THRIFTS — Contact Us Page (contact.php)
 * Handles customer inquiries, saves them to MySQL database,
 * sends confirmation to customer, and notifies store inbox via Gmail SMTP.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/SmtpMailer.php';

$pageTitle = 'Contact Us · ' . APP_NAME;
$activeNav = 'contact';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? 'Thrift Store Inquiry');
    $message = trim($_POST['message'] ?? '');

    // Server-side validation
    if ($name === '' || $email === '' || $message === '') {
        setFlash('danger', 'Please complete your Name, Email, and Message.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('danger', 'Please provide a valid email address.');
    } else {
        try {
            // 1. Save directly to MySQL contact_messages table
            $db = getDb();
            $msgId = 'MSG-' . date('Ymd') . '-' . rand(100, 999);
            
            $stmt = $db->prepare("
                INSERT INTO `contact_messages` (`id`, `sender_name`, `email`, `phone`, `subject`, `message`, `status`, `received_at`)
                VALUES (:id, :sender_name, :email, :phone, :subject, :message, 'New', NOW())
            ");
            $stmt->execute([
                ':id'          => $msgId,
                ':sender_name' => $name,
                ':email'       => $email,
                ':phone'       => $phone,
                ':subject'     => $subject,
                ':message'     => $message,
            ]);

            // 2. Dispatch Emails via Gmail SMTP
            $emailSent = false;
            try {
                $mailer = new SmtpMailer();

                // (A) Admin / Business Notification to mitramsolutions@gmail.com
                $businessRecipient = 'mitramsolutions@gmail.com';
                $adminSubject = "[New Inquiry - {$msgId}] {$subject} from {$name}";
                $adminHtml = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; color: #333; line-height: 1.6; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;'>
                  <div style='background: #1b2e25; color: #d4a855; padding: 18px 24px;'>
                    <h2 style='margin: 0; font-size: 1.25rem;'>" . e(APP_NAME) . " — New Customer Inquiry</h2>
                    <p style='margin: 4px 0 0 0; font-size: 0.85rem; color: #cfd8d3;'>Message ID: " . e($msgId) . "</p>
                  </div>
                  <div style='padding: 24px; background: #ffffff;'>
                    <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px;'>
                      <tr><td style='padding: 8px 0; width: 130px; font-weight: bold; color: #555;'>From:</td><td>" . e($name) . "</td></tr>
                      <tr><td style='padding: 8px 0; font-weight: bold; color: #555;'>Email:</td><td><a href='mailto:" . e($email) . "'>" . e($email) . "</a></td></tr>
                      <tr><td style='padding: 8px 0; font-weight: bold; color: #555;'>Phone:</td><td>" . e($phone ?: 'Not provided') . "</td></tr>
                      <tr><td style='padding: 8px 0; font-weight: bold; color: #555;'>Subject:</td><td>" . e($subject) . "</td></tr>
                      <tr><td style='padding: 8px 0; font-weight: bold; color: #555;'>Received:</td><td>" . date('Y-m-d H:i:s T') . "</td></tr>
                    </table>
                    <div style='background: #f8f9fa; border-left: 4px solid #d4a855; padding: 16px; border-radius: 4px;'>
                      <p style='margin: 0; font-weight: bold; color: #333;'>Message:</p>
                      <p style='margin: 8px 0 0 0; white-space: pre-wrap;'>" . nl2br(e($message)) . "</p>
                    </div>
                    <p style='margin-top: 24px; font-size: 0.85rem; color: #888;'>Reply directly to this email to respond to " . e($name) . " (" . e($email) . ").</p>
                  </div>
                </div>";

                // Send to business with customer Reply-To
                $mailer->send($businessRecipient, $adminSubject, $adminHtml, $email, $name);

                // (B) Confirmation Auto-Reply to the Customer
                $customerSubject = "We received your message · " . APP_NAME . " [{$msgId}]";
                $customerHtml = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; color: #333; line-height: 1.6; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;'>
                  <div style='background: #1b2e25; color: #d4a855; padding: 20px 24px; text-align: center;'>
                    <h1 style='margin: 0; font-size: 1.4rem; font-family: Georgia, serif;'>" . e(APP_NAME) . "</h1>
                    <p style='margin: 4px 0 0 0; font-size: 0.85rem; color: #cfd8d3; letter-spacing: 1px;'>PRE-LOVED FASHION, NEW STORIES</p>
                  </div>
                  <div style='padding: 24px; background: #ffffff;'>
                    <p>Hi <strong>" . e($name) . "</strong>,</p>
                    <p>Thank you for reaching out to " . e(APP_NAME) . "! We have received your inquiry and our team will get back to you shortly.</p>
                    
                    <div style='background: #f9fbf9; border: 1px solid #e4eae5; padding: 16px; border-radius: 6px; margin: 20px 0;'>
                      <p style='margin: 0 0 8px 0; font-weight: bold; font-size: 0.9rem; color: #1b2e25;'>Summary of your inquiry:</p>
                      <p style='margin: 0; font-size: 0.85rem; color: #666;'><strong>Reference ID:</strong> " . e($msgId) . "</p>
                      <p style='margin: 4px 0; font-size: 0.85rem; color: #666;'><strong>Subject:</strong> " . e($subject) . "</p>
                      <p style='margin: 8px 0 0 0; font-size: 0.85rem; color: #444; border-top: 1px dashed #ddd; padding-top: 8px; white-space: pre-wrap;'>" . nl2br(e($message)) . "</p>
                    </div>

                    <p style='font-size: 0.9rem;'>In the meantime, feel free to browse our latest circular fashion drops at <a href='https://anshthrifts.in/shop.php' style='color: #1b2e25; font-weight: bold;'>anshthrifts.in</a>.</p>
                    
                    <p style='margin-top: 24px; margin-bottom: 0;'>Warm regards,<br><strong>The " . e(APP_NAME) . " Team</strong></p>
                  </div>
                  <div style='background: #f5f5f0; padding: 12px 24px; text-align: center; font-size: 0.75rem; color: #888;'>
                    &copy; " . date('Y') . " " . e(APP_NAME) . ". Sustainable pre-loved fashion across India.
                  </div>
                </div>";

                // Send to customer with business Reply-To
                $mailer->send($email, $customerSubject, $customerHtml, $businessRecipient, APP_NAME);

                $emailSent = true;
            } catch (\Throwable $mailErr) {
                // Log delivery failure without leaking private passwords
                error_log("Contact Form Email Delivery Error [{$msgId}]: " . $mailErr->getMessage());
            }

            if ($emailSent) {
                setFlash('success', 'Thank you! Your message has been received. A confirmation has been sent to your email and our team will respond shortly.');
            } else {
                setFlash('success', 'Thank you! Your message has been saved in our system (' . e($msgId) . '). Our team will review and reply shortly.');
            }

            redirect('/contact.php');
        } catch (Throwable $e) {
            error_log("Contact Form Submission Error: " . $e->getMessage());
            setFlash('danger', 'Unable to submit your message at this moment. Please try again later.');
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
