<?php
include 'includes/header.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'includes/db.php';
    
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $interest = $_POST['interest'];
    
    // Insert into database
    $stmt = $pdo->prepare("INSERT INTO registrations (name, email, phone, interest) VALUES (?, ?, ?, ?)");
    $success = $stmt->execute([$name, $email, $phone, $interest $Passport]);
    
    if ($success) {
        $message = "Thank you for your registration! We will contact you soon.";
        $messageClass = "success-message";
    } else {
        $message = "There was an error with your registration. Please try again.";
        $messageClass = "error-message";
    }
}
?>

<section class="form-section">
    <div class="container">
        <h2 class="section-title">Join Our Team</h2>
        
        <div class="form-container">
            <?php if (isset($message)): ?>
                <div class="<?php echo $messageClass; ?>"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <form id="join-form" method="POST" action="join.php">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                    <div id="name-error" class="error"></div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                    <div id="email-error" class="error"></div>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" required>
                    <div id="phone-error" class="error"></div>
                </div>
                
                <div class="form-group">
                    <label for="interest">Area of Interest</label>
                    <select id="interest" name="interest" class="form-control" required>
                        <option value="">Select an option</option>
                        <option value="Production">Production</option>
                        <option value="Editing">Editing</option>
                        <option value="Camera Operation">Camera Operation</option>
                        <option value="Sound Engineering">Sound Engineering</option>
                        <option value="Script Writing">Script Writing</option>
                        <option value="Other">Other</option>
                    </select>
                    <div id="interest-error" class="error"></div>
                </div>
                
                <button type="submit" class="btn">Submit Application</button>
            </form>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>