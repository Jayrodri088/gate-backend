<?php
header('Content-Type: application/json');
require_once 'config/dbconfig.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Validate user role (Optional: If a role is passed, filter notifications by role)
$data = json_decode(file_get_contents('php://input'), true);
$role = isset($data['role']) ? trim($data['role']) : null;

try {
    // Fetch notifications for a specific role if provided, otherwise fetch all
    if ($role) {
        $stmt = $conn->prepare("SELECT id, message, selfie_path, created_at, read_status FROM notifications WHERE role = :role ORDER BY created_at DESC");
        $stmt->bindParam(':role', $role);
    } else {
        $stmt = $conn->prepare("SELECT id, message, selfie_path, created_at, read_status FROM notifications ORDER BY created_at DESC");
    }

    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Function to format time (e.g., "20 mins ago", "Yesterday")
    function formatTime($timestamp) {
        $time = strtotime($timestamp);
        $diff = time() - $time;

        if ($diff < 60) {
            return "$diff seconds ago";
        } elseif ($diff < 3600) {
            return floor($diff / 60) . " mins ago";
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . " hours ago";
        } elseif ($diff < 172800) {
            return "Yesterday";
        } else {
            return date('M d, Y', $time);
        }
    }

    // Format notifications
    foreach ($notifications as &$notification) {
        $notification['formatted_time'] = formatTime($notification['created_at']);

        // Ensure the image path is correctly formatted for Flutter
        if (!empty($notification['selfie_path'])) {
            $notification['selfie_path'] = "http://10.10.2.34/gate-backend/" . ltrim($notification['selfie_path'], './');
        }
    }

    echo json_encode(['success' => true, 'data' => $notifications]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
