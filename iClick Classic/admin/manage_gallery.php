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
    $type = $_POST['type'];
    $description = $_POST['description'] ?? '';
    
    // Handle file upload
    $file_path = '';
    if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] == 0) {
        $uploadDir = $type === 'image' ? '../assets/images/gallery/' : '../assets/videos/';
        $file_path = time() . '_' . basename($_FILES['media_file']['name']);
        move_uploaded_file($_FILES['media_file']['tmp_name'], $uploadDir . $file_path);
    }
    
    if (isset($_POST['gallery_id'])) {
        // Update existing gallery item
        $galleryId = $_POST['gallery_id'];
        if ($file_path) {
            // Delete old file
            $stmt = $pdo->prepare("SELECT file_path, type FROM gallery WHERE id = ?");
            $stmt->execute([$galleryId]);
            $oldItem = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($oldItem && file_exists('../assets/' . ($oldItem['type'] === 'image' ? 'images/gallery/' : 'videos/') . $oldItem['file_path'])) {
                unlink('../assets/' . ($oldItem['type'] === 'image' ? 'images/gallery/' : 'videos/') . $oldItem['file_path']);
            }
            
            $stmt = $pdo->prepare("UPDATE gallery SET title = ?, description = ?, type = ?, file_path = ? WHERE id = ?");
            $stmt->execute([$title, $description, $type, $file_path, $galleryId]);
        } else {
            $stmt = $pdo->prepare("UPDATE gallery SET title = ?, description = ?, type = ? WHERE id = ?");
            $stmt->execute([$title, $description, $type, $galleryId]);
        }
        $_SESSION['message'] = "Gallery item updated successfully!";
    } else {
        // Insert new gallery item
        if (!$file_path) {
            $_SESSION['error'] = "Please select a file to upload.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO gallery (title, description, type, file_path) VALUES (?, ?, ?, ?)");
            $stmt->execute([$title, $description, $type, $file_path]);
            $_SESSION['message'] = "Gallery item added successfully!";
        }
    }
    
    header("Location: manage_gallery.php");
    exit();
}

// Handle edit request
$galleryItem = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM gallery WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $galleryItem = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle delete request
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    // Get file path before deleting
    $stmt = $pdo->prepare("SELECT file_path, type FROM gallery WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $itemToDelete = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($itemToDelete) {
        // Delete file from server
        $filePath = '../assets/' . ($itemToDelete['type'] === 'image' ? 'images/gallery/' : 'videos/') . $itemToDelete['file_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $_SESSION['message'] = "Gallery item deleted successfully!";
    }
    
    header("Location: manage_gallery.php");
    exit();
}

// Fetch all gallery items
$stmt = $pdo->query("SELECT * FROM gallery ORDER BY created_at DESC");
$galleryItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Gallery - PrimeVision Studios</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .media-preview {
            max-width: 200px;
            max-height: 150px;
            margin: 10px 0;
            border-radius: 4px;
            border: 2px solid var(--teal-light);
        }
        .file-info {
            background: var(--light-gray);
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
            font-size: 0.9rem;
        }
        .type-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .type-image { background: var(--teal-light); color: white; }
        .type-video { background: var(--orange); color: white; }
        .thumbnail-cell { width: 100px; }
        .actions-cell { width: 150px; }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="admin-container">
        <div class="admin-header">
            <h1><?php echo $galleryItem ? 'Edit Gallery Item' : 'Add New Gallery Item'; ?></h1>
            <a href="dashboard.php" class="btn btn-teal">Back to Dashboard</a>
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="success-message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <div class="admin-content">
            <!-- Gallery Item Form -->
            <div class="form-container" style="max-width: 100%;">
                <form method="POST" enctype="multipart/form-data" id="galleryForm">
                    <?php if ($galleryItem): ?>
                        <input type="hidden" name="gallery_id" value="<?php echo $galleryItem['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="title">Title *</label>
                        <input type="text" id="title" name="title" class="form-control" 
                               value="<?php echo $galleryItem ? htmlspecialchars($galleryItem['title']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="3"><?php echo $galleryItem ? htmlspecialchars($galleryItem['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="type">Media Type *</label>
                        <select id="type" name="type" class="form-control" required>
                            <option value="">Select Type</option>
                            <option value="image" <?php echo $galleryItem && $galleryItem['type'] == 'image' ? 'selected' : ''; ?>>Image</option>
                            <option value="video" <?php echo $galleryItem && $galleryItem['type'] == 'video' ? 'selected' : ''; ?>>Video</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="media_file">
                            <?php echo $galleryItem ? 'Replace Media File' : 'Media File *'; ?>
                        </label>
                        <input type="file" id="media_file" name="media_file" class="form-control" 
                               accept="<?php echo $galleryItem && $galleryItem['type'] ? ($galleryItem['type'] == 'image' ? 'image/*' : 'video/*') : '*/*'; ?>"
                               <?php echo !$galleryItem ? 'required' : ''; ?>>
                        
                        <?php if ($galleryItem && $galleryItem['file_path']): ?>
                            <div class="file-info">
                                <strong>Current File:</strong> <?php echo htmlspecialchars($galleryItem['file_path']); ?>
                                <br>
                                <strong>Uploaded:</strong> <?php echo date('M j, Y g:i A', strtotime($galleryItem['created_at'])); ?>
                            </div>
                            
                            <div id="currentMediaPreview">
                                <?php if ($galleryItem['type'] == 'image'): ?>
                                    <img src="../assets/images/gallery/<?php echo $galleryItem['file_path']; ?>" 
                                         alt="Current image" 
                                         class="media-preview">
                                <?php else: ?>
                                    <video controls class="media-preview">
                                        <source src="../assets/videos/<?php echo $galleryItem['file_path']; ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div id="filePreview" style="display: none; margin-top: 10px;">
                            <strong>New File Preview:</strong>
                            <div id="previewContent"></div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn"><?php echo $galleryItem ? 'Update Item' : 'Add Item'; ?></button>
                        <a href="manage_gallery.php" class="btn btn-teal">Cancel</a>
                        <?php if ($galleryItem): ?>
                            <a href="?action=delete&id=<?php echo $galleryItem['id']; ?>" 
                               class="btn btn-danger" 
                               onclick="return confirm('Are you sure you want to delete this gallery item? This action cannot be undone.')">
                                Delete Item
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <!-- Gallery Items List -->
            <h2 style="margin-top: 50px;">Existing Gallery Items (<?php echo count($galleryItems); ?>)</h2>
            
            <?php if (empty($galleryItems)): ?>
                <div style="text-align: center; padding: 40px; background: var(--light-gray); border-radius: 8px;">
                    <h3 style="color: var(--teal);">No gallery items yet</h3>
                    <p>Add your first gallery item using the form above.</p>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th class="thumbnail-cell">Preview</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>File</th>
                                <th>Created</th>
                                <th class="actions-cell">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($galleryItems as $item): ?>
                            <tr>
                                <td>
                                    <?php if($item['type'] == 'image'): ?>
                                        <img src="../assets/images/gallery/<?php echo $item['file_path']; ?>" 
                                             alt="Thumbnail" 
                                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                                    <?php else: ?>
                                        <div style="width: 60px; height: 60px; background: #000; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                                            <span style="color: white; font-size: 1.5rem;">▶</span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($item['title']); ?></td>
                                <td>
                                    <span class="type-badge <?php echo 'type-' . $item['type']; ?>">
                                        <?php echo ucfirst($item['type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    if ($item['description']) {
                                        echo strlen($item['description']) > 50 
                                            ? htmlspecialchars(substr($item['description'], 0, 50)) . '...' 
                                            : htmlspecialchars($item['description']);
                                    } else {
                                        echo '<span style="color: #999;">No description</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <small style="font-family: monospace;"><?php echo htmlspecialchars($item['file_path']); ?></small>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($item['created_at'])); ?></td>
                                <td>
                                    <a href="manage_gallery.php?action=edit&id=<?php echo $item['id']; ?>" 
                                       class="btn btn-small btn-warning">Edit</a>
                                    <a href="manage_gallery.php?action=delete&id=<?php echo $item['id']; ?>" 
                                       class="btn btn-small btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this gallery item?')">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Bulk Actions -->
                <div style="margin-top: 20px; padding: 15px; background: var(--light-gray); border-radius: 4px;">
                    <h4>Bulk Actions</h4>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <select id="bulkAction" class="form-control" style="width: auto;">
                            <option value="">Choose action...</option>
                            <option value="delete">Delete selected items</option>
                        </select>
                        <button type="button" id="applyBulkAction" class="btn btn-small btn-danger" disabled>Apply</button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // File preview functionality
        document.getElementById('media_file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const previewDiv = document.getElementById('filePreview');
            const previewContent = document.getElementById('previewContent');
            const typeSelect = document.getElementById('type');
            
            if (file) {
                previewDiv.style.display = 'block';
                previewContent.innerHTML = '';
                
                // Auto-detect type if not set
                if (!typeSelect.value) {
                    if (file.type.startsWith('image/')) {
                        typeSelect.value = 'image';
                    } else if (file.type.startsWith('video/')) {
                        typeSelect.value = 'video';
                    }
                }
                
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewContent.innerHTML = `<img src="${e.target.result}" class="media-preview" alt="Preview">`;
                    };
                    reader.readAsDataURL(file);
                } else if (file.type.startsWith('video/')) {
                    const url = URL.createObjectURL(file);
                    previewContent.innerHTML = `
                        <video controls class="media-preview">
                            <source src="${url}" type="${file.type}">
                            Your browser does not support the video tag.
                        </video>
                    `;
                } else {
                    previewContent.innerHTML = `<div class="file-info">File type not supported for preview</div>`;
                }
            } else {
                previewDiv.style.display = 'none';
            }
        });
        
        // Update file input accept attribute when type changes
        document.getElementById('type').addEventListener('change', function() {
            const fileInput = document.getElementById('media_file');
            if (this.value === 'image') {
                fileInput.setAttribute('accept', 'image/*');
            } else if (this.value === 'video') {
                fileInput.setAttribute('accept', 'video/*');
            } else {
                fileInput.removeAttribute('accept');
            }
        });
        
        // Form validation
        document.getElementById('galleryForm').addEventListener('submit', function(e) {
            const type = document.getElementById('type').value;
            const file = document.getElementById('media_file').files[0];
            const isEdit = <?php echo $galleryItem ? 'true' : 'false'; ?>;
            
            if (!type) {
                e.preventDefault();
                alert('Please select a media type.');
                return;
            }
            
            if (!isEdit && !file) {
                e.preventDefault();
                alert('Please select a file to upload.');
                return;
            }
            
            if (file) {
                if (type === 'image' && !file.type.startsWith('image/')) {
                    e.preventDefault();
                    alert('Please select an image file.');
                    return;
                }
                
                if (type === 'video' && !file.type.startsWith('video/')) {
                    e.preventDefault();
                    alert('Please select a video file.');
                    return;
                }
                
                // Check file size (max 10MB)
                if (file.size > 100 * 1024 * 1024) {
                    e.preventDefault();
                    alert('File size must be less than 10MB.');
                    return;
                }
            }
        });
        
        // Bulk actions functionality
        document.addEventListener('DOMContentLoaded', function() {
            const bulkActionSelect = document.getElementById('bulkAction');
            const applyBulkActionBtn = document.getElementById('applyBulkAction');
            
            if (bulkActionSelect && applyBulkActionBtn) {
                bulkActionSelect.addEventListener('change', function() {
                    applyBulkActionBtn.disabled = !this.value;
                });
                
                applyBulkActionBtn.addEventListener('click', function() {
                    const action = bulkActionSelect.value;
                    
                    if (action === 'delete') {
                        if (confirm('Are you sure you want to delete all gallery items? This action cannot be undone.')) {
                        
                            alert('Bulk delete functionality would be implemented here. In a real system, this would delete all selected items.');
                        }
                    }
                });
            }
        });
    </script>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>