<?php
header('Content-Type: application/json');
require_once 'config/dbconfig.php';

// Get the JSON input
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['email'], $data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Missing email or password']);
    exit();
}

// Validate inputs
$email = trim($data['email']);
$password = trim($data['password']);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit();
}

try {
    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT id, full_name, password FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit();
    }

    // Verify the password
    if (password_verify($password, $user['password'])) {
        // Authentication successful
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'id' => $user['id'],
                'full_name' => $user['full_name'],
                'email' => $email
            ]
        ]);
    } else {
        // Invalid password
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
