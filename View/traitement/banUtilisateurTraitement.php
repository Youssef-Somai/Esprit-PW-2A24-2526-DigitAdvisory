<?php
session_start();

if (!isset($_SESSION['user']['id_user']) || strtolower($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: ../FrontOffice/login.php');
    exit;
}

require_once __DIR__ . '/../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_user'])) {
    $id = $_POST['id_user'];
    $new_status = $_POST['new_status'] ?? 'désactivé';

    try {
        $db = config::getConnexion();
        $sql = "UPDATE user SET statut_compte = :status WHERE id_user = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute(['status' => $new_status, 'id' => $id]);
    } catch (Exception $e) {
        // On gère l'erreur silencieusement ou on pourrait l'afficher
    }
}

header('Location: ../BackOffice/back-utilisateur.php');
exit;
