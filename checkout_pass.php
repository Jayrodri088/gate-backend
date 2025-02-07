<?php
header('Content-Type: application/json');
require_once 'config/dbconfig.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

if (!isset($_GET['id_number'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameter: id_number']);
    exit();
}

$idNumber = $_GET['id_number'];

// Extract the first numeric part (ID)
preg_match('/^\d+/', $idNumber, $matches);
if (!isset($matches[0])) {
    echo json_encode(['success' => false, 'message' => 'Invalid id_number format']);
    exit();
}

$id = intval($matches[0]);

try {
    // Fetch visitor details using extracted ID
    $stmt = $conn->prepare("SELECT * FROM check_ins WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Combine id and code into id_number again
        $result['id_number'] = $result['id'] . $result['code'];

        echo json_encode(['success' => true, 'data' => $result]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Record not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
