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
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    
    // Handle file upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadDir = '../assets/images/';
        $image = time() . '_' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image);
    }
    
    if (isset($_POST['course_id'])) {
        // Update existing course
        $courseId = $_POST['course_id'];
        if ($image) {
            $stmt = $pdo->prepare("UPDATE courses SET title = ?, description = ?, category = ?, image = ? WHERE id = ?");
            $stmt->execute([$title, $description, $category, $image, $courseId]);
        } else {
            $stmt = $pdo->prepare("UPDATE courses SET title = ?, description = ?, category = ? WHERE id = ?");
            $stmt->execute([$title, $description, $category, $courseId]);
        }
        $_SESSION['message'] = "Course updated successfully!";
    } else {
        // Insert new course
        $stmt = $pdo->prepare("INSERT INTO courses (title, description, category, image) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $description, $category, $image]);
        $_SESSION['message'] = "Course added successfully!";
    }
    
    header("Location: manage_courses.php");
    exit();
}

// Handle edit request
$course = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch all courses
$stmt = $pdo->query("SELECT * FROM courses ORDER BY created_at DESC");
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - PrimeVision Studios</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="admin-container">
        <div class="admin-header">
            <h1><?php echo $course ? 'Edit Course' : 'Add New Course'; ?></h1>
            <a href="dashboard.php" class="btn btn-teal">Back to Dashboard</a>
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="success-message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        
        <div class="admin-content">
            <!-- Course Form -->
            <div class="form-container" style="max-width: 100%;">
                <form method="POST" enctype="multipart/form-data">
                    <?php if ($course): ?>
                        <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="title">Course Title</label>
                        <input type="text" id="title" name="title" class="form-control" 
                               value="<?php echo $course ? htmlspecialchars($course['title']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="5" required><?php echo $course ? htmlspecialchars($course['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" class="form-control" required>
                            <option value="">Select Category</option>
                            <option value="reality" <?php echo $course && $course['category'] == 'reality' ? 'selected' : ''; ?>>Reality TV</option>
                            <option value="documentary" <?php echo $course && $course['category'] == 'documentary' ? 'selected' : ''; ?>>Documentaries</option>
                            <option value="educational" <?php echo $course && $course['category'] == 'educational' ? 'selected' : ''; ?>>Educational</option>
                            <option value="production" <?php echo $course && $course['category'] == 'production' ? 'selected' : ''; ?>>Production Courses</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Course Image</label>
                        <input type="file" id="image" name="image" class="form-control" accept="image/*">
                        <?php if ($course && $course['image']): ?>
                            <p>Current image: <?php echo htmlspecialchars($course['image']); ?></p>
                            <img src="../assets/images/<?php echo $course['image']; ?>" alt="Current image" style="max-width: 200px; margin-top: 10px;">
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn"><?php echo $course ? 'Update Course' : 'Add Course'; ?></button>
                        <a href="manage_courses.php" class="btn btn-teal">Cancel</a>
                    </div>
                </form>
            </div>
            
            <!-- Courses List -->
            <h2 style="margin-top: 50px;">Existing Courses</h2>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($courses as $courseItem): ?>
                    <tr>
                        <td><?php echo $courseItem['id']; ?></td>
                        <td>
                            <?php if($courseItem['image']): ?>
                                <img src="../assets/images/<?php echo $courseItem['image']; ?>" alt="Course image" style="width: 50px; height: 50px; object-fit: cover;">
                            <?php else: ?>
                                <div style="width: 50px; height: 50px; background: #eee; display: flex; align-items: center; justify-content: center;">No Image</div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($courseItem['title']); ?></td>
                        <td><?php echo htmlspecialchars($courseItem['category']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($courseItem['created_at'])); ?></td>
                        <td>
                            <a href="manage_courses.php?action=edit&id=<?php echo $courseItem['id']; ?>" class="btn btn-small btn-warning">Edit</a>
                            <a href="dashboard.php?action=delete&table=courses&id=<?php echo $courseItem['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Are you sure you want to delete this course?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>