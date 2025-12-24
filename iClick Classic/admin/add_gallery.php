<?php
session_start();
require '../includes/db.php';

// Redirect if admin not logged in
if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}

$message = '';

if($_POST){
    $title = trim($_POST['title']);
    $category = trim($_POST['category']);

    // Handle file upload
    if(isset($_FILES['file']) && $_FILES['file']['error'] == 0){
        $allowed = ['jpg','jpeg','png','gif','mp4'];
        $file_name = $_FILES['file']['name'];
        $file_tmp = $_FILES['file']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if(in_array($file_ext, $allowed)){
            $new_name = time().'_'.$file_name;
            $upload_dir = '../assets/images/';
            if(move_uploaded_file($file_tmp, $upload_dir.$new_name)){
                // Insert into database
                $stmt = $pdo->prepare("INSERT INTO gallery (title, category, file_path) VALUES (?,?,?)");
                if($stmt->execute([$title, $category, $new_name])){
                    $message = "Gallery item added successfully!";
                } else {
                    $message = "Database error!";
                }
            } else {
                $message = "Failed to move uploaded file.";
            }
        } else {
            $message = "Invalid file type. Only JPG, PNG, GIF, MP4 allowed.";
        }
    } else {
        $message = "Please select a file to upload.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Gallery Item</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
    body { font-family: 'Poppins', sans-serif; padding: 20px; background: #F5F5F5; color: #004D4D; }
    h1 { color: #008080; }
    form { max-width: 500px; margin-top: 20px; }
    label { display: block; margin-bottom: 5px; font-weight: bold; }
    input[type="text"], select, input[type="file"] { width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #ccc; }
    .btn { background: #FF6F3C; color: #fff; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; transition: 0.3s; }
    .btn:hover { background: #e65c2a; }
    .message { margin-bottom: 20px; padding: 10px; background: #008080; color: #fff; border-radius: 5px; }
</style>
</head>
<body>

<h1>Add Gallery Item</h1>

<?php if($message): ?>
    <div class="message"><?= $message; ?></div>
<?php endif; ?>

<form action="" method="POST" enctype="multipart/form-data">
    <label for="title">Title</label>
    <input type="text" name="title" id="title" required>

    <label for="category">Category</label>
    <select name="category" id="category" required>
        <option value="">Select Category</option>
        <option value="Shows">Shows</option>
        <option value="Events">Events</option>
        <option value="Behind the Scenes">Behind the Scenes</option>
        <option value="Promos">Promos</option>
    </select>

    <label for="file">Image/Video</label>
    <input type="file" name="file" id="file" accept=".jpg,.jpeg,.png,.gif,.mp4" required>

    <button type="submit" class="btn">Add Item</button>
</form>

</body>
</html>
