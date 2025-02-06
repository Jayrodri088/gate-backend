<?php
header('Content-Type: application/json');
require_once 'config/dbconfig.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Decode JSON input
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing user ID']);
    exit();
}

$userId = intval($data['user_id']); // Ensure it's an integer

try {
    // Get user's full name and role
    $stmt = $conn->prepare("SELECT full_name, role FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }

    $userName = $user['full_name'];
    $userRole = $user['role'];

    // Get pending check-in requests
    if ($userRole === 'gate') {
        // If role is 'gate', fetch 3 latest pending entries (any role)
        $stmt = $conn->prepare("SELECT id, full_name, visit_purpose, code, reception, created_at FROM check_ins WHERE status = 'pending' ORDER BY created_at DESC LIMIT 3");
    } else {
        // If not 'gate', fetch 3 latest pending entries for the user's role
        $stmt = $conn->prepare("SELECT id, full_name, visit_purpose, code, reception, created_at FROM check_ins WHERE status = 'pending' AND role = :user_role ORDER BY created_at DESC LIMIT 3");
        $stmt->bindParam(':user_role', $userRole, PDO::PARAM_STR);
    }
    $stmt->execute();
    $pendingEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get 3 latest check-in entries (Recent Visitors)
    $stmt = $conn->prepare("SELECT id, full_name, visit_purpose, code, reception, created_at FROM check_ins WHERE status = 'approved' ORDER BY created_at DESC LIMIT 3");
    $stmt->execute();
    $recentVisitors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format date and create combined code (id + code)
    function formatEntries($entries) {
        foreach ($entries as &$entry) {
            // Format date as "Oct. 6, 2024"
            $entry['formatted_date'] = date('M. j, Y', strtotime($entry['created_at']));
            // Extract time (HH:MM AM/PM)
            $entry['formatted_time'] = date('h:i A', strtotime($entry['created_at']));
            // Combine ID and Code
            $entry['combined_code'] = $entry['id'] . $entry['code'];
            // Use reception as location
            $entry['location'] = $entry['reception'];
        }
        return $entries;
    }

    $pendingEntries = formatEntries($pendingEntries);
    $recentVisitors = formatEntries($recentVisitors);

    // Send response
    echo json_encode([
        'success' => true,
        'full_name' => $userName,
        'role' => $userRole,
        'pending_entries' => $pendingEntries,
        'recent_visitors' => $recentVisitors
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
