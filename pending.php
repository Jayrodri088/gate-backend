<?php
header('Content-Type: application/json');
require_once 'config/dbconfig.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get role from request (if provided)
$role = isset($_GET['role']) ? trim($_GET['role']) : null;

try {
    if ($role) {
        // Check if any pending entries exist for this role
        $stmt = $conn->prepare("SELECT COUNT(*) FROM check_ins WHERE status = 'pending' AND role = :role");
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);
        $stmt->execute();
        $roleExists = $stmt->fetchColumn() > 0;

        if ($roleExists) {
            // Fetch only entries that match the given role
            $stmt = $conn->prepare("SELECT id, code, full_name, selfie_path, visit_purpose, created_at FROM check_ins WHERE status = 'pending' AND role = :role");
            $stmt->bindParam(':role', $role, PDO::PARAM_STR);
        } else {
            // If no matching role entries exist, fetch all pending entries
            $stmt = $conn->prepare("SELECT id, code, full_name, selfie_path, visit_purpose, created_at FROM check_ins WHERE status = 'pending'");
        }
    } else {
        // Fetch all pending entries if no role is provided
        $stmt = $conn->prepare("SELECT id, code, full_name, selfie_path, visit_purpose, created_at FROM check_ins WHERE status = 'pending'");
    }

    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format data
    $formattedResults = array_map(function($entry) {
        // Combine id and code to form id_number
        $entry['id_number'] = $entry['id'] . $entry['code'];

        // Extract date components from created_at
        $timestamp = strtotime($entry['created_at']);
        $entry['day'] = date('l', $timestamp); // Day of the week
        $entry['date'] = date('M d, Y', $timestamp); // Month day, Year
        $entry['time'] = date('h:i A', $timestamp); // Hour:Minute AM/PM

        unset($entry['id'], $entry['code'], $entry['created_at']); // Remove unnecessary fields
        return $entry;
    }, $results);

    echo json_encode(['success' => true, 'data' => $formattedResults]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
