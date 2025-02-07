<?php
header('Content-Type: application/json');
require_once 'config/dbconfig.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get notification ID from request
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['notification_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing notification ID']);
    exit();
}

$notification_id = intval($data['notification_id']);

try {
    // Delete notification
    $stmt = $conn->prepare("DELETE FROM notifications WHERE id = :notification_id");
    $stmt->bindParam(':notification_id', $notification_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Notification deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete notification']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
