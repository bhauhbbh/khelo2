<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
if (!$email) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare('INSERT INTO subscriptions (email) VALUES (:email)');
    $stmt->execute(['email' => $email]);
    
    echo json_encode(['success' => true, 'message' => 'Successfully subscribed!']);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) { // Duplicate entry
        echo json_encode(['error' => 'Email already subscribed']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Server error']);
    }
}
?>