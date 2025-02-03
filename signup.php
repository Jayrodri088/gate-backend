<?php
header('Content-Type: application/json');
require_once 'config/dbconfig.php';

// Get the JSON input
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['full_name'], $data['email'], $data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

// Validate inputs
$full_name = trim($data['full_name']);
$email = trim($data['email']);
$password = trim($data['password']);
$role = isset($data['role']) && in_array($data['role'], ['gate', 'goss', 'admin']) ? $data['role'] : 'gate'; // Default to 'gate'

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit();
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit();
}

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    // Insert user into the database
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (:full_name, :email, :password, :role)");
    $stmt->bindParam(':full_name', $full_name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashed_password);
    $stmt->bindParam(':role', $role);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'User registered successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to register user']);
    }
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
