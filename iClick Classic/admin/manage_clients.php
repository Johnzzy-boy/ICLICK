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
    $testimonial = $_POST['testimonial'] ?? '';
    $website = $_POST['website'] ?? '';
    $contact_person = $_POST['contact_person'] ?? '';
    $contact_email = $_POST['contact_email'] ?? '';
    
    // Handle file upload
    $logo = '';
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $uploadDir = '../assets/images/clients/';
        $logo = time() . '_' . basename($_FILES['logo']['name']);
        move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $logo);
    }
    
    if (isset($_POST['client_id'])) {
        // Update existing client
        $clientId = $_POST['client_id'];
        if ($logo) {
            // Delete old logo
            $stmt = $pdo->prepare("SELECT logo FROM clients WHERE id = ?");
            $stmt->execute([$clientId]);
            $oldClient = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($oldClient && $oldClient['logo'] && file_exists('../assets/images/clients/' . $oldClient['logo'])) {
                unlink('../assets/images/clients/' . $oldClient['logo']);
            }
            
            $stmt = $pdo->prepare("UPDATE clients SET name = ?, testimonial = ?, website = ?, contact_person = ?, contact_email = ?, logo = ? WHERE id = ?");
            $stmt->execute([$name, $testimonial, $website, $contact_person, $contact_email, $logo, $clientId]);
        } else {
            $stmt = $pdo->prepare("UPDATE clients SET name = ?, testimonial = ?, website = ?, contact_person = ?, contact_email = ? WHERE id = ?");
            $stmt->execute([$name, $testimonial, $website, $contact_person, $contact_email, $clientId]);
        }
        $_SESSION['message'] = "Client updated successfully!";
    } else {
        // Insert new client
        if (!$logo) {
            $_SESSION['error'] = "Please upload a logo for the client.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO clients (name, testimonial, website, contact_person, contact_email, logo) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $testimonial, $website, $contact_person, $contact_email, $logo]);
            $_SESSION['message'] = "Client added successfully!";
        }
    }
    
    header("Location: manage_clients.php");
    exit();
}

// Handle edit request
$client = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle delete request
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    // Get logo path before deleting
    $stmt = $pdo->prepare("SELECT logo FROM clients WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $clientToDelete = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($clientToDelete && $clientToDelete['logo'] && file_exists('../assets/images/clients/' . $clientToDelete['logo'])) {
        unlink('../assets/images/clients/' . $clientToDelete['logo']);
    }
    
    $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $_SESSION['message'] = "Client deleted successfully!";
    
    header("Location: manage_clients.php");
    exit();
}

// Handle toggle featured status
if (isset($_GET['action']) && $_GET['action'] == 'toggle_featured' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("UPDATE clients SET featured = NOT featured WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $_SESSION['message'] = "Client featured status updated!";
    
    header("Location: manage_clients.php");
    exit();
}

// Fetch all clients
$stmt = $pdo->query("SELECT * FROM clients ORDER BY featured DESC, name ASC");
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count statistics
$totalClients = count($clients);
$featuredClients = array_filter($clients, function($client) {
    return $client['featured'] == 1;
});
$clientsWithTestimonials = array_filter($clients, function($client) {
    return !empty($client['testimonial']);
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Clients - PrimeVision Studios</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .logo-preview {
            max-width: 150px;
            max-height: 100px;
            margin: 10px 0;
            border: 2px solid var(--teal-light);
            border-radius: 4px;
            padding: 5px;
            background: white;
        }
        .client-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--teal);
            margin-bottom: 10px;
        }
        .featured-badge {
            background: var(--orange);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            margin-left: 10px;
        }
        .actions-cell { 
            width: 200px; 
            text-align: center;
        }
        .thumbnail-cell { 
            width: 120px; 
            text-align: center;
        }
        .testimonial-preview {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .form-section {
            background: var(--light-gray);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="admin-container">
        <div class="admin-header">
            <h1><?php echo $client ? 'Edit Client' : 'Add New Client'; ?></h1>
            <a href="dashboard.php" class="btn btn-teal">Back to Dashboard</a>
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="success-message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <!-- Client Statistics -->
        <div class="client-stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalClients; ?></div>
                <div>Total Clients</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($featuredClients); ?></div>
                <div>Featured Clients</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($clientsWithTestimonials); ?></div>
                <div>With Testimonials</div>
            </div>
        </div>
        
        <div class="admin-content">
            <!-- Client Form -->
            <div class="form-container" style="max-width: 100%;">
                <form method="POST" enctype="multipart/form-data" id="clientForm">
                    <?php if ($client): ?>
                        <input type="hidden" name="client_id" value="<?php echo $client['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-section">
                        <h3>Basic Information</h3>
                        <div class="form-group">
                            <label for="name">Client Name *</label>
                            <input type="text" id="name" name="name" class="form-control" 
                                   value="<?php echo $client ? htmlspecialchars($client['name']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="website">Website URL</label>
                            <input type="url" id="website" name="website" class="form-control" 
                                   value="<?php echo $client ? htmlspecialchars($client['website']) : ''; ?>" 
                                   placeholder="https://example.com">
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3>Logo</h3>
                        <div class="form-group">
                            <label for="logo">
                                <?php echo $client ? 'Replace Logo' : 'Client Logo *'; ?>
                                <small>(Recommended: 300x200px, PNG/JPG)</small>
                            </label>
                            <input type="file" id="logo" name="logo" class="form-control" 
                                   accept="image/*" <?php echo !$client ? 'required' : ''; ?>>
                            
                            <?php if ($client && $client['logo']): ?>
                                <div class="file-info">
                                    <strong>Current Logo:</strong> <?php echo htmlspecialchars($client['logo']); ?>
                                    <br>
                                    <strong>Uploaded:</strong> <?php echo date('M j, Y', strtotime($client['created_at'])); ?>
                                </div>
                                
                                <div id="currentLogoPreview">
                                    <img src="../assets/images/clients/<?php echo $client['logo']; ?>" 
                                         alt="Current logo" 
                                         class="logo-preview">
                                </div>
                            <?php endif; ?>
                            
                            <div id="filePreview" style="display: none; margin-top: 10px;">
                                <strong>New Logo Preview:</strong>
                                <div id="previewContent"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3>Testimonial (Optional)</h3>
                        <div class="form-group">
                            <label for="testimonial">Client Testimonial</label>
                            <textarea id="testimonial" name="testimonial" class="form-control" rows="4" 
                                      placeholder="What the client said about working with us..."><?php echo $client ? htmlspecialchars($client['testimonial']) : ''; ?></textarea>
                            <small>Leave empty if no testimonial is available.</small>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3>Contact Information (Optional)</h3>
                        <div class="form-group">
                            <label for="contact_person">Contact Person</label>
                            <input type="text" id="contact_person" name="contact_person" class="form-control" 
                                   value="<?php echo $client ? htmlspecialchars($client['contact_person']) : ''; ?>" 
                                   placeholder="John Doe">
                        </div>
                        
                        <div class="form-group">
                            <label for="contact_email">Contact Email</label>
                            <input type="email" id="contact_email" name="contact_email" class="form-control" 
                                   value="<?php echo $client ? htmlspecialchars($client['contact_email']) : ''; ?>" 
                                   placeholder="john@clientcompany.com">
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn"><?php echo $client ? 'Update Client' : 'Add Client'; ?></button>
                        <a href="manage_clients.php" class="btn btn-teal">Cancel</a>
                        <?php if ($client): ?>
                            <a href="?action=delete&id=<?php echo $client['id']; ?>" 
                               class="btn btn-danger" 
                               onclick="return confirm('Are you sure you want to delete this client? This action cannot be undone.')">
                                Delete Client
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <!-- Clients List -->
            <h2 style="margin-top: 50px;">Existing Clients</h2>
            
            <?php if (empty($clients)): ?>
                <div style="text-align: center; padding: 40px; background: var(--light-gray); border-radius: 8px;">
                    <h3 style="color: var(--teal);">No clients yet</h3>
                    <p>Add your first client using the form above.</p>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th class="thumbnail-cell">Logo</th>
                                <th>Client Name</th>
                                <th>Website</th>
                                <th>Testimonial</th>
                                <th>Contact</th>
                                <th>Featured</th>
                                <th>Created</th>
                                <th class="actions-cell">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($clients as $clientItem): ?>
                            <tr>
                                <td class="thumbnail-cell">
                                    <?php if($clientItem['logo']): ?>
                                        <img src="../assets/images/clients/<?php echo $clientItem['logo']; ?>" 
                                             alt="Client logo" 
                                             style="width: 60px; height: 40px; object-fit: contain;">
                                    <?php else: ?>
                                        <div style="width: 60px; height: 40px; background: #eee; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; color: #999;">No Logo</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($clientItem['name']); ?></strong>
                                    <?php if($clientItem['featured']): ?>
                                        <span class="featured-badge">Featured</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($clientItem['website']): ?>
                                        <a href="<?php echo htmlspecialchars($clientItem['website']); ?>" target="_blank" style="font-size: 0.9rem;">
                                            Visit Site
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #999;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="testimonial-preview" title="<?php echo htmlspecialchars($clientItem['testimonial']); ?>">
                                        <?php 
                                        if ($clientItem['testimonial']) {
                                            echo strlen($clientItem['testimonial']) > 50 
                                                ? htmlspecialchars(substr($clientItem['testimonial'], 0, 50)) . '...' 
                                                : htmlspecialchars($clientItem['testimonial']);
                                        } else {
                                            echo '<span style="color: #999;">No testimonial</span>';
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if($clientItem['contact_person'] || $clientItem['contact_email']): ?>
                                        <small>
                                            <?php if($clientItem['contact_person']): ?>
                                                <div><?php echo htmlspecialchars($clientItem['contact_person']); ?></div>
                                            <?php endif; ?>
                                            <?php if($clientItem['contact_email']): ?>
                                                <div><?php echo htmlspecialchars($clientItem['contact_email']); ?></div>
                                            <?php endif; ?>
                                        </small>
                                    <?php else: ?>
                                        <span style="color: #999;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <a href="?action=toggle_featured&id=<?php echo $clientItem['id']; ?>" 
                                       class="btn btn-small <?php echo $clientItem['featured'] ? 'btn-warning' : 'btn-teal'; ?>">
                                        <?php echo $clientItem['featured'] ? 'Unfeature' : 'Feature'; ?>
                                    </a>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($clientItem['created_at'])); ?></td>
                                <td class="actions-cell">
                                    <a href="manage_clients.php?action=edit&id=<?php echo $clientItem['id']; ?>" 
                                       class="btn btn-small btn-warning">Edit</a>
                                    <a href="manage_clients.php?action=delete&id=<?php echo $clientItem['id']; ?>" 
                                       class="btn btn-small btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this client?')">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Export Options -->
                <div style="margin-top: 30px; padding: 20px; background: var(--light-gray); border-radius: 8px;">
                    <h4>Export Client Data</h4>
                    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <a href="export_clients.php?format=csv" class="btn btn-small btn-teal">Export as CSV</a>
                        <a href="export_clients.php?format=json" class="btn btn-small btn-teal">Export as JSON</a>
                        <button type="button" id="printClientList" class="btn btn-small btn-teal">Print Client List</button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Logo preview functionality
        document.getElementById('logo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const previewDiv = document.getElementById('filePreview');
            const previewContent = document.getElementById('previewContent');
            
            if (file) {
                previewDiv.style.display = 'block';
                previewContent.innerHTML = '';
                
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewContent.innerHTML = `<img src="${e.target.result}" class="logo-preview" alt="Logo preview">`;
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewContent.innerHTML = '<div class="error-message">Please select an image file (PNG, JPG, etc.)</div>';
                    this.value = '';
                }
            } else {
                previewDiv.style.display = 'none';
            }
        });
        
        // Form validation
        document.getElementById('clientForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value;
            const logo = document.getElementById('logo').files[0];
            const isEdit = <?php echo $client ? 'true' : 'false'; ?>;
            
            if (!name.trim()) {
                e.preventDefault();
                alert('Please enter a client name.');
                return;
            }
            
            if (!isEdit && !logo) {
                e.preventDefault();
                alert('Please upload a logo for the client.');
                return;
            }
            
            if (logo) {
                if (!logo.type.startsWith('image/')) {
                    e.preventDefault();
                    alert('Please select an image file for the logo.');
                    return;
                }
                
                // Check file size (max 5MB for logos)
                if (logo.size > 5 * 1024 * 1024) {
                    e.preventDefault();
                    alert('Logo file size must be less than 5MB.');
                    return;
                }
            }
            
            // Validate website URL if provided
            const website = document.getElementById('website').value;
            if (website && !isValidUrl(website)) {
                e.preventDefault();
                alert('Please enter a valid website URL (including http:// or https://).');
                return;
            }
            
            // Validate email if provided
            const contactEmail = document.getElementById('contact_email').value;
            if (contactEmail && !isValidEmail(contactEmail)) {
                e.preventDefault();
                alert('Please enter a valid contact email address.');
                return;
            }
        });
        
        // Utility functions
        function isValidUrl(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        }
        
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        
        // Print functionality
        document.getElementById('printClientList')?.addEventListener('click', function() {
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Client List - PrimeVision Studios</title>
                        <style>
                            body { font-family: Arial, sans-serif; margin: 20px; }
                            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                            th { background-color: #f2f2f2; }
                            .header { text-align: center; margin-bottom: 30px; }
                            .timestamp { text-align: right; font-size: 0.9em; color: #666; }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <h1>PrimeVision Studios - Client List</h1>
                            <div class="timestamp">Generated on: ${new Date().toLocaleDateString()}</div>
                        </div>
                        ${document.querySelector('.admin-table').outerHTML}
                    </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        });
        
        // Quick search functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Add search input if there are clients
            if (document.querySelector('.admin-table tbody')) {
                const searchHtml = `
                    <div style="margin-bottom: 20px;">
                        <input type="text" id="clientSearch" placeholder="Search clients by name..." 
                               style="padding: 8px 12px; width: 300px; max-width: 100%; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                `;
                document.querySelector('h2[style*="margin-top: 50px"]').insertAdjacentHTML('afterend', searchHtml);
                
                const searchInput = document.getElementById('clientSearch');
                const tableRows = document.querySelectorAll('.admin-table tbody tr');
                
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    
                    tableRows.forEach(row => {
                        const clientName = row.cells[1].textContent.toLowerCase();
                        if (clientName.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }
        });
    </script>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>