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

// Generate a unique 4-character code
function generateCode() {
    $letters = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 2);
    $numbers = substr(str_shuffle("0123456789"), 0, 2);
    return $letters . $numbers;
}

$uniqueCode = generateCode();

try {
    // Check for code uniqueness
    do {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM check_ins WHERE code = :code");
        $stmt->bindParam(':code', $uniqueCode);
        $stmt->execute();
        $exists = $stmt->fetchColumn();
        if ($exists > 0) {
            $uniqueCode = generateCode();
        }
    } while ($exists > 0);

    // Insert check-in record into database
    $stmt = $conn->prepare("INSERT INTO check_ins (
        code, full_name, email, address, phone, visit_intent, personal_effect, visit_purpose, appointment_details, status
    ) VALUES (
        :code, :full_name, :email, :address, :phone, :visit_intent, :personal_effect, :visit_purpose, :appointment_details, 'pending'
    )");

    $stmt->bindParam(':code', $uniqueCode);
    $stmt->bindParam(':full_name', $full_name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':visit_intent', $visit_intent);
    $stmt->bindParam(':personal_effect', $personal_effect);
    $stmt->bindParam(':visit_purpose', $visit_purpose);
    $stmt->bindParam(':appointment_details', $appointment_details);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Check-in recorded successfully', 'code' => $uniqueCode]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to record check-in']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
