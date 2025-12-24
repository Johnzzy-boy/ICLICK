<?php
session_start();
include '../includes/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$format = $_GET['format'] ?? 'csv';

// Fetch clients data
$stmt = $pdo->query("SELECT name, testimonial, website, contact_person, contact_email, featured, created_at FROM clients ORDER BY name");
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($format === 'csv') {
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=clients_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    fputcsv($output, ['Name', 'Testimonial', 'Website', 'Contact Person', 'Contact Email', 'Featured', 'Created Date']);
    
    // Add data rows
    foreach ($clients as $client) {
        fputcsv($output, [
            $client['name'],
            $client['testimonial'],
            $client['website'],
            $client['contact_person'],
            $client['contact_email'],
            $client['featured'] ? 'Yes' : 'No',
            $client['created_at']
        ]);
    }
    
    fclose($output);
    
} elseif ($format === 'json') {
    // Set headers for JSON download
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename=clients_' . date('Y-m-d') . '.json');
    
    echo json_encode($clients, JSON_PRETTY_PRINT);
}

exit();
?>