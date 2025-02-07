<?php
header('Content-Type: application/json');
require_once 'config/dbconfig.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Decode JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Check if marking all as read or a specific notification
$markAll = isset($data['mark_all']) ? filter_var($data['mark_all'], FILTER_VALIDATE_BOOLEAN) : false;
$notificationId = isset($data['notification_id']) ? intval($data['notification_id']) : null;

try {
    if ($markAll) {
        // Mark all notifications as read
        $stmt = $conn->prepare("UPDATE notifications SET read_status = 1");
    } elseif ($notificationId) {
        // Mark a specific notification as read
        $stmt = $conn->prepare("UPDATE notifications SET read_status = 1 WHERE id = :notification_id");
        $stmt->bindParam(':notification_id', $notificationId, PDO::PARAM_INT);
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit();
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => $markAll ? 'All notifications marked as read' : 'Notification marked as read']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update notification(s)']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
