<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    $errors = [];
    
    if (empty($username) || empty($email) || empty($password)) {
        $errors[] = "All fields are required.";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }
    
    // Check if username or email already exists
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Username or email already exists.";
    }
    
    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert admin
        $stmt = $pdo->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
        if ($stmt->execute([$username, $email, $hashed_password])) {
            $_SESSION['success'] = "Registration successful. You can now login.";
            header("Location: login.php");
            exit();
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration - PrimeVision Studios</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Admin Registration</h1>
        </div>
        
        <div class="admin-content">
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php foreach($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="admin-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">Register</button>
                    <a href="login.php" class="btn btn-teal">Back to Login</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>