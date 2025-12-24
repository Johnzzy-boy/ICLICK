<?php
include 'includes/header.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'includes/db.php';
    
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];
    $subject = isset($_POST['subject']) ? $_POST['subject'] : 'General Inquiry';
    
    // Insert into database
    $stmt = $pdo->prepare("INSERT INTO contacts (name, email, message, subject) VALUES (?, ?, ?, ?)");
    $success = $stmt->execute([$name, $email, $message, $subject]);
    
    if ($success) {
        $message = "Thank you for your message! We will get back to you within 24 hours.";
        $messageClass = "success-message";
        
        // Optional: Send email notification
        $to = "info@primevision.com";
        $headers = "From: $email\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        $emailBody = "
        <h2>New Contact Form Submission</h2>
        <p><strong>Name:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Subject:</strong> $subject</p>
        <p><strong>Message:</strong></p>
        <p>$message</p>
        ";
        
        @mail($to, "New Contact Form Submission: $subject", $emailBody, $headers);
    } else {
        $message = "There was an error sending your message. Please try again.";
        $messageClass = "error-message";
    }
}
?>

<section class="form-section">
    <div class="container">
        <h2 class="section-title">Contact Us</h2>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 50px; align-items: start;">
            <!-- Contact Form -->
            <div class="form-container">
                <?php if (isset($message)): ?>
                    <div class="<?php echo $messageClass; ?>"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <form id="contact-form" method="POST" action="contact.php">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                        <div id="name-error" class="error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                        <div id="email-error" class="error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <select id="subject" name="subject" class="form-control">
                            <option value="General Inquiry">General Inquiry</option>
                            <option value="Partnership">Partnership Opportunity</option>
                            <option value="Career">Career Opportunity</option>
                            <option value="Production Inquiry">Production Inquiry</option>
                            <option value="Technical Support">Technical Support</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message *</label>
                        <textarea id="message" name="message" class="form-control" rows="5" required></textarea>
                        <div id="message-error" class="error"></div>
                    </div>
                    
                    <button type="submit" class="btn">Send Message</button>
                </form>
            </div>
            
            <!-- Contact Information -->
            <div class="contact-info" style="background: white; padding: 40px; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <h3 style="color: var(--teal); margin-bottom: 30px;">Get In Touch</h3>
                
                <div class="contact-item" style="display: flex; align-items: start; margin-bottom: 25px;">
                    <div style="background: var(--orange); color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; flex-shrink: 0;">
                        📍
                    </div>
                    <div>
                        <h4 style="color: var(--teal); margin-bottom: 5px;">Our Studio</h4>
                        <p>123 Media Lane<br>Studio City, CA 91604</p>
                    </div>
                </div>
                
                <div class="contact-item" style="display: flex; align-items: start; margin-bottom: 25px;">
                    <div style="background: var(--orange); color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; flex-shrink: 0;">
                        📞
                    </div>
                    <div>
                        <h4 style="color: var(--teal); margin-bottom: 5px;">Phone</h4>
                        <p>+1 (555) 123-4567<br>Mon-Fri: 9AM-6PM PST</p>
                    </div>
                </div>
                
                <div class="contact-item" style="display: flex; align-items: start; margin-bottom: 25px;">
                    <div style="background: var(--orange); color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; flex-shrink: 0;">
                        ✉️
                    </div>
                    <div>
                        <h4 style="color: var(--teal); margin-bottom: 5px;">Email</h4>
                        <p>info@primevision.com<br>support@primevision.com</p>
                    </div>
                </div>
                
                <div class="contact-item" style="display: flex; align-items: start;">
                    <div style="background: var(--orange); color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; flex-shrink: 0;">
                        🕒
                    </div>
                    <div>
                        <h4 style="color: var(--teal); margin-bottom: 5px;">Business Hours</h4>
                        <p>Monday - Friday: 9:00 AM - 6:00 PM<br>Saturday: 10:00 AM - 4:00 PM<br>Sunday: Closed</p>
                    </div>
                </div>
                
                <!-- Embedded Map -->
                <div style="margin-top: 30px; border-radius: 8px; overflow: hidden;">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3304.453123238287!2d-118.396071324686!3d34.08388807314358!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x80c2beb5d35d5d5d%3A0x5b47c8c0e0d5b5b5!2sStudio%20City%2C%20Los%20Angeles%2C%20CA!5e0!3m2!1sen!2sus!4v1234567890123!5m2!1sen!2sus" 
                            width="100%" 
                            height="200" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>