<?php
session_start();
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['descriptor']) || !is_array($data['descriptor'])) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

$descriptor = json_encode($data['descriptor']);
$email = $_SESSION['user']['email'];

try {
    $db = config::getConnexion();
    $sql = 'UPDATE user SET face_descriptor = :descriptor WHERE email = :email';
    $stmt = $db->prepare($sql);
    $stmt->execute(['descriptor' => $descriptor, 'email' => $email]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
