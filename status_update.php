<?php
header('Content-Type: application/json');
require_once 'config/dbconfig.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Decode JSON input
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id_number'], $data['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$idNumber = $data['id_number'];
$status = strtolower(trim($data['status'])); // Normalize status input

// Validate status to be either 'approved' or 'rejected'
$validStatuses = ['approved', 'rejected'];
if (!in_array($status, $validStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status value']);
    exit();
}

// Extract numeric ID
$id = intval(preg_replace('/[^0-9]/', '', $idNumber));

try {
    // Check if the ID exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM check_ins WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->fetchColumn() == 0) {
        echo json_encode(['success' => false, 'message' => 'Record not found']);
        exit();
    }

    // Update the status in the database
    $stmt = $conn->prepare("UPDATE check_ins SET status = :status WHERE id = :id");
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update status']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
