<?php
session_start();
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['descriptor']) || !is_array($data['descriptor'])) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

$loginDescriptor = $data['descriptor'];

function euclideanDistance($vec1, $vec2) {
    if (count($vec1) !== count($vec2)) return 999;
    $sum = 0;
    for ($i = 0; $i < count($vec1); $i++) {
        $sum += pow($vec1[$i] - $vec2[$i], 2);
    }
    return sqrt($sum);
}

try {
    $db = config::getConnexion();
    $sql = 'SELECT * FROM user WHERE face_descriptor IS NOT NULL';
    $stmt = $db->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $bestMatch = null;
    $minDistance = 0.50; // Seuil de tolérance (plus bas = plus strict)

    foreach ($users as $user) {
        $dbDesc = json_decode($user['face_descriptor'], true);
        if (is_array($dbDesc)) {
            $distance = euclideanDistance($loginDescriptor, $dbDesc);
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $bestMatch = $user;
            }
        }
    }

    if ($bestMatch) {
        if ($bestMatch['statut_compte'] !== 'actif') {
            echo json_encode(['success' => false, 'message' => 'Compte désactivé.']);
            exit;
        }

        $userRole = strtolower(trim($bestMatch['role'] ?? ''));

        $_SESSION['user'] = [
            'id_user' => $bestMatch['id_user'],
            'email' => $bestMatch['email'],
            'role' => $userRole,
            'nom' => $bestMatch['nom'] ?? null,
            'prenom' => $bestMatch['prenom'] ?? null,
        ];
        
        $redirectUrl = '../FrontOffice/front-entreprise-dashboard.php';
        if ($userRole === 'admin') {
            $redirectUrl = '../BackOffice/back-utilisateur.php';
        } elseif ($userRole === 'expert') {
            $redirectUrl = '../FrontOffice/front-expert-dashboard.php';
        }
        
        echo json_encode(['success' => true, 'redirect' => $redirectUrl, 'nom' => $bestMatch['prenom'] ?? 'Utilisateur']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Visage non reconnu ou non enregistré.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
