<?php
header('Content-Type: application/json');
require_once 'config/dbconfig.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Check if role is passed (optional)
$role = isset($_GET['role']) ? trim($_GET['role']) : null;

try {
    if ($role) {
        // Count unread notifications for a specific role
        $stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE read_status = 0 AND role = :role");
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);
    } else {
        // Count all unread notifications
        $stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE read_status = 0");
    }

    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'unread_count' => $result['unread_count']]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
