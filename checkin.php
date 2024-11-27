<?php
header('Content-Type: application/json');
require_once 'config/dbconfig.php';

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset(
    $data['full_name'], 
    $data['email'], 
    $data['address'], 
    $data['phone'], 
    $data['visit_intent'], 
    $data['personal_effect'], 
    $data['visit_purpose'], 
    $data['appointment_details']
)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

// Extract input data
$full_name = trim($data['full_name']);
$email = trim($data['email']);
$address = trim($data['address']);
$phone = trim($data['phone']);
$visit_intent = trim($data['visit_intent']);
$personal_effect = trim($data['personal_effect']);
$visit_purpose = trim($data['visit_purpose']);
$appointment_details = trim($data['appointment_details']);

// Basic validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit();
}

if (!preg_match('/^[0-9]{10,15}$/', $phone)) {
    echo json_encode(['success' => false, 'message' => 'Invalid phone number']);
    exit();
}

try {
    // Insert check-in record into database
    $stmt = $conn->prepare("INSERT INTO check_ins (
        full_name, email, address, phone, visit_intent, personal_effect, visit_purpose, appointment_details
    ) VALUES (
        :full_name, :email, :address, :phone, :visit_intent, :personal_effect, :visit_purpose, :appointment_details
    )");

    $stmt->bindParam(':full_name', $full_name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':visit_intent', $visit_intent);
    $stmt->bindParam(':personal_effect', $personal_effect);
    $stmt->bindParam(':visit_purpose', $visit_purpose);
    $stmt->bindParam(':appointment_details', $appointment_details);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Check-in recorded successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to record check-in']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
