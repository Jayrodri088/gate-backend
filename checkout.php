<?php
header('Content-Type: application/json');
require_once 'config/dbconfig.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id_number'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameter: id_number']);
    exit();
}

$idNumber = $data['id_number'];

// Extract the first numeric part (ID)
preg_match('/^\d+/', $idNumber, $matches);
if (!isset($matches[0])) {
    echo json_encode(['success' => false, 'message' => 'Invalid id_number format']);
    exit();
}

$id = intval($matches[0]);

try {
    // Check if the visitor has already checked out
    $stmt = $conn->prepare("SELECT checked_out FROM check_ins WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Record not found']);
        exit();
    }

    if ($result['checked_out']) {
        echo json_encode(['success' => false, 'message' => 'User already checked out']);
        exit();
    }

    // Update check-out status
    $stmt = $conn->prepare("UPDATE check_ins SET checked_out = 1, check_out_time = NOW() WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Check-out successful']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update check-out status']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
