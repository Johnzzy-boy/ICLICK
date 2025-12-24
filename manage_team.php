<?php
session_start();
include '../includes/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $position = $_POST['position'];
    $bio = $_POST['bio'] ?? '';
    $linkedin_url = $_POST['linkedin_url'] ?? '';
    $twitter_url = $_POST['twitter_url'] ?? '';
    $instagram_url = $_POST['instagram_url'] ?? '';
    $display_order = $_POST['display_order'] ?? 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Handle file upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadDir = '../assets/images/team/';
        $image = time() . '_' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image);
    }
    
    if (isset($_POST['team_id'])) {
        // Update existing team member
        $teamId = $_POST['team_id'];
        if ($image) {
            // Delete old image
            $stmt = $pdo->prepare("SELECT image FROM team_members WHERE id = ?");
            $stmt->execute([$teamId]);
            $oldMember = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($oldMember && $oldMember['image'] && file_exists('../assets/images/team/' . $oldMember['image'])) {
                unlink('../assets/images/team/' . $oldMember['image']);
            }
            
            $stmt = $pdo->prepare("UPDATE team_members SET name = ?, position = ?, bio = ?, linkedin_url = ?, twitter_url = ?, instagram_url = ?, image = ?, display_order = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$name, $position, $bio, $linkedin_url, $twitter_url, $instagram_url, $image, $display_order, $is_active, $teamId]);
        } else {
            $stmt = $pdo->prepare("UPDATE team_members SET name = ?, position = ?, bio = ?, linkedin_url = ?, twitter_url = ?, instagram_url = ?, display_order = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$name, $position, $bio, $linkedin_url, $twitter_url, $instagram_url, $display_order, $is_active, $teamId]);
        }
        $_SESSION['message'] = "Team member updated successfully!";
    } else {
        // Insert new team member
        $stmt = $pdo->prepare("INSERT INTO team_members (name, position, bio, linkedin_url, twitter_url, instagram_url, image, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $position, $bio, $linkedin_url, $twitter_url, $instagram_url, $image, $display_order, $is_active]);
        $_SESSION['message'] = "Team member added successfully!";
    }
    
    header("Location: manage_team.php");
    exit();
}

// Handle edit request
$teamMember = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM team_members WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $teamMember = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle delete request
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    // Get image path before deleting
    $stmt = $pdo->prepare("SELECT image FROM team_members WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $memberToDelete = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($memberToDelete && $memberToDelete['image'] && file_exists('../assets/images/team/' . $memberToDelete['image'])) {
        unlink('../assets/images/team/' . $memberToDelete['image']);
    }
    
    $stmt = $pdo->prepare("DELETE FROM team_members WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $_SESSION['message'] = "Team member deleted successfully!";
    
    header("Location: manage_team.php");
    exit();
}

// Fetch all team members
$stmt = $pdo->query("SELECT * FROM team_members ORDER BY display_order, name");
$teamMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Team - PrimeVision Studios</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="admin-container">
        <div class="admin-header">
            <h1><?php echo $teamMember ? 'Edit Team Member' : 'Add Team Member'; ?></h1>
            <a href="dashboard.php" class="btn btn-teal">Back to Dashboard</a>
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="success-message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        
        <div class="admin-content">
            <!-- Team Member Form -->
            <div class="form-container" style="max-width: 100%;">
                <form method="POST" enctype="multipart/form-data">
                    <?php if ($teamMember): ?>
                        <input type="hidden" name="team_id" value="<?php echo $teamMember['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?php echo $teamMember ? htmlspecialchars($teamMember['name']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="position">Position *</label>
                        <input type="text" id="position" name="position" class="form-control" 
                               value="<?php echo $teamMember ? htmlspecialchars($teamMember['position']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="bio">Bio</label>
                        <textarea id="bio" name="bio" class="form-control" rows="4"><?php echo $teamMember ? htmlspecialchars($teamMember['bio']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Profile Photo</label>
                        <input type="file" id="image" name="image" class="form-control" accept="image/*">
                        <?php if ($teamMember && $teamMember['image']): ?>
                            <div style="margin-top: 10px;">
                                <img src="../assets/images/team/<?php echo $teamMember['image']; ?>" alt="Current photo" style="max-width: 150px;">
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="linkedin_url">LinkedIn URL</label>
                        <input type="url" id="linkedin_url" name="linkedin_url" class="form-control" 
                               value="<?php echo $teamMember ? htmlspecialchars($teamMember['linkedin_url']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="twitter_url">Twitter URL</label>
                        <input type="url" id="twitter_url" name="twitter_url" class="form-control" 
                               value="<?php echo $teamMember ? htmlspecialchars($teamMember['twitter_url']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="instagram_url">Instagram URL</label>
                        <input type="url" id="instagram_url" name="instagram_url" class="form-control" 
                               value="<?php echo $teamMember ? htmlspecialchars($teamMember['instagram_url']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="display_order">Display Order</label>
                        <input type="number" id="display_order" name="display_order" class="form-control" 
                               value="<?php echo $teamMember ? $teamMember['display_order'] : '0'; ?>" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_active" value="1" 
                                   <?php echo $teamMember && $teamMember['is_active'] ? 'checked' : 'checked'; ?>> 
                            Active (show on website)
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn"><?php echo $teamMember ? 'Update Member' : 'Add Member'; ?></button>
                        <a href="manage_team.php" class="btn btn-teal">Cancel</a>
                    </div>
                </form>
            </div>
            
            <!-- Team Members List -->
            <h2 style="margin-top: 50px;">Team Members</h2>
            
            <?php if (empty($teamMembers)): ?>
                <div style="text-align: center; padding: 40px;">
                    <h3 style="color: var(--teal);">No team members yet</h3>
                    <p>Add your first team member using the form above.</p>
                </div>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Status</th>
                            <th>Order</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($teamMembers as $member): ?>
                        <tr>
                            <td>
                                <?php if($member['image']): ?>
                                    <img src="../assets/images/team/<?php echo $member['image']; ?>" alt="Profile" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                                <?php else: ?>
                                    <div style="width: 50px; height: 50px; background: #eee; border-radius: 50%; display: flex; align-items: center; justify-content: center;">👤</div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($member['name']); ?></td>
                            <td><?php echo htmlspecialchars($member['position']); ?></td>
                            <td>
                                <span style="color: <?php echo $member['is_active'] ? 'green' : 'red'; ?>;">
                                    <?php echo $member['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td><?php echo $member['display_order']; ?></td>
                            <td>
                                <a href="manage_team.php?action=edit&id=<?php echo $member['id']; ?>" class="btn btn-small btn-warning">Edit</a>
                                <a href="manage_team.php?action=delete&id=<?php echo $member['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>