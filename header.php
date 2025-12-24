<?php

?>
<?php
/*
  © 2025 B-TECH
  All code written and maintained by B-TECH.
*/
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iClick Classic</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <a href="index.php" ><!--<img src="iclick.png" height ="100" width="100"></img>-->iClick Classic</a>
                </div>
                <div class="nav-menu">
                    <a href="index.php" class="nav-link">Home</a>
                    <a href="courses.php" class="nav-link">Courses/Shows</a>
                    <a href="about.php" class="nav-link">About</a>
                    <a href="gallery.php" class="nav-link">Gallery</a>
                    <a href="clients.php" class="nav-link">Clients</a>
                    <a href="join.php" class="nav-link">Join Us</a>
                    <a href="contact.php" class="nav-link">Contact</a>
                    <?php if(isset($_SESSION['admin_logged_in'])): ?>
                        <a href="admin/dashboard.php" class="nav-link admin-link">Admin Dashboard</a>
                        <a href="admin/logout.php" class="nav-link">Logout</a>
                    <?php else: ?>
                        <a href="admin/login.php" class="nav-link admin-link">Admin Login</a>
                    <?php endif; ?>
                </div>
                <div class="hamburger">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
            </div>
        </nav>
    </header>