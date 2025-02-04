<?php
header('Content-Type: application/json');
require_once 'config/dbconfig.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Check if required fields are present
if (!isset($_POST['check_in_id'], $_FILES['id_card'], $_FILES['selfie'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$check_in_id = $_POST['check_in_id'];

// Validate file uploads
function validateFile($file, $fieldName) {
    $allowedExtensions = ['jpg', 'jpeg', 'png'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions)) {
        echo json_encode(['success' => false, 'message' => "$fieldName must be a JPG, JPEG, or PNG file"]);
        exit();
    }
    if ($file['size'] > 2 * 1024 * 1024) { // Limit size to 2MB
        echo json_encode(['success' => false, 'message' => "$fieldName must be less than 2MB"]);
        exit();
    }
}

// Validate both files
validateFile($_FILES['id_card'], 'ID Card');
validateFile($_FILES['selfie'], 'Selfie');

// File upload paths
$uploadDir = './uploads/';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        echo json_encode(['success' => false, 'message' => 'Failed to create uploads directory']);
        exit();
    }
}

// Ensure the uploads directory is writable
if (!is_writable($uploadDir)) {
    echo json_encode(['success' => false, 'message' => 'Uploads directory is not writable']);
    exit();
}

$idCardPath = $uploadDir . 'id_card_' . time() . '_' . basename($_FILES['id_card']['name']);
$selfiePath = $uploadDir . 'selfie_' . time() . '_' . basename($_FILES['selfie']['name']);

// Move uploaded files
if (!move_uploaded_file($_FILES['id_card']['tmp_name'], $idCardPath)) {
    echo json_encode(['success' => false, 'message' => 'Failed to upload ID Card']);
    exit();
}
if (!move_uploaded_file($_FILES['selfie']['tmp_name'], $selfiePath)) {
    echo json_encode(['success' => false, 'message' => 'Failed to upload Selfie']);
    exit();
}

try {
    // Update the check-in record with image paths
    $stmt = $conn->prepare("UPDATE check_ins SET id_card_path = :id_card, selfie_path = :selfie WHERE id = :check_in_id");
    $stmt->bindParam(':id_card', $idCardPath);
    $stmt->bindParam(':selfie', $selfiePath);
    $stmt->bindParam(':check_in_id', $check_in_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Identity verification completed']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update check-in record']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>