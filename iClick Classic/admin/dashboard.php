<?php
session_start();
include '../includes/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Handle CRUD operations
if (isset($_GET['action']) && isset($_GET['table']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $table = $_GET['table'];
    $id = $_GET['id'];
    
    if ($action == 'delete') {
        $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['message'] = "Item deleted successfully.";
        header("Location: dashboard.php");
        exit();
    }
}

// Get counts for dashboard
$courses_count = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$gallery_count = $pdo->query("SELECT COUNT(*) FROM gallery")->fetchColumn();
$clients_count = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
$registrations_count = $pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();
$contacts_count = $pdo->query("SELECT COUNT(*) FROM contacts")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PrimeVision Studios</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="admin-container">
        <div class="admin-header">
            <h1>Admin Dashboard</h1>
            <p>Welcome, <?php echo $_SESSION['admin_username']; ?>!</p>
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="success-message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        
        <div class="admin-content">
            <h2>Quick Stats</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <div class="client-card">
                    <h3>Courses/Shows</h3>
                    <p style="font-size: 2rem; color: var(--teal);"><?php echo $courses_count; ?></p>
                </div>
                <div class="client-card">
                    <h3>Gallery Items</h3>
                    <p style="font-size: 2rem; color: var(--teal);"><?php echo $gallery_count; ?></p>
                </div>
                <div class="client-card">
                    <h3>Clients</h3>
                    <p style="font-size: 2rem; color: var(--teal);"><?php echo $clients_count; ?></p>
                </div>
                <div class="client-card">
                    <h3>Registrations</h3>
                    <p style="font-size: 2rem; color: var(--teal);"><?php echo $registrations_count; ?></p>
                </div>
                <div class="client-card">
                    <h3>Contact Messages</h3>
                    <p style="font-size: 2rem; color: var(--teal);"><?php echo $contacts_count; ?></p>
                </div>
            </div>
            
            <h2>Manage Content</h2>
            
            <div style="margin-bottom: 30px;">
                <h3>Courses/Shows</h3>
                <a href="manage_courses.php" class="btn">Manage Courses</a>
                <a href="manage_gallery.php" class="btn">Manage Gallery</a>
                <a href="manage_clients.php" class="btn">Manage Client</a>
                <a href="manage_team.php" class="btn">Manage Team</a>
                <?php
                $stmt = $pdo->query("SELECT * FROM courses LIMIT 5");
                $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($courses as $course): ?>
                        <tr>
                            <td><?php echo $course['id']; ?></td>
                            <td><?php echo htmlspecialchars($course['title']); ?></td>
                            <td><?php echo htmlspecialchars($course['category']); ?></td>
                            <td>
                                <a href="manage_courses.php?action=edit&id=<?php echo $course['id']; ?>" class="btn btn-small btn-warning">Edit</a>
                                <a href="dashboard.php?action=delete&table=courses&id=<?php echo $course['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Similar sections for Gallery, Clients, Registrations, and Contacts -->
            
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>