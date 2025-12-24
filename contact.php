<?php
include 'includes/header.php';

/*
  © 2025 B-TECH
  All code written and maintained by B-TECH.
*/

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
        $to = "aggreyjohn298@gmail.com";
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
                        <p>ACCRA<br>Tetegu, CX 112 5828</p>
                    </div>
                </div>
                
                <div class="contact-item" style="display: flex; align-items: start; margin-bottom: 25px;">
                    <div style="background: var(--orange); color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; flex-shrink: 0;">
                        📞
                    </div>
                    <div>
                        <h4 style="color: var(--teal); margin-bottom: 5px;">Phone</h4>
                        <p>+233 54 954 6079<br>Mon-Fri: 9AM-6PM PST</p>
                    </div>
                </div>
                
                <div class="contact-item" style="display: flex; align-items: start; margin-bottom: 25px;">
                    <div style="background: var(--orange); color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; flex-shrink: 0;">
                        ✉️
                    </div>
                    <div>
                        <h4 style="color: var(--teal); margin-bottom: 5px;">Email</h4>
                        <p>iClickproduction46@gmail.com<br>support@iClickprodution.com</p>
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
                    <iframe src="https://www.bing.com/ck/a?!&&p=13a33d079c80b63b4240df1984cd35786f8624809ac4e3be470ef35090849e03JmltdHM9MTc2NTkyOTYwMA&ptn=3&ver=2&hsh=4&fclid=15dd2972-8281-6806-106a-3fb6832a6947&psq=tetegu+location+link&u=a1aHR0cHM6Ly9tYXBjYXJ0YS5jb20vTjEyMzMwNzM0OTg3Izp-OnRleHQ9VGV0ZWd1JTIwaXMlMjBhJTIwc3VidXJiJTIwaW4lMjBHYSUyMFNvdXRoJTIwTXVuaWNpcGFsLEdiYXdlJTIwYW5kJTIwV2VpZHphLiUyMFBob3RvJTNBJTIwS3dhbWVnaGFuYSUyQyUyMENDJTIwQlktU0ElMjA0LjAu" 
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