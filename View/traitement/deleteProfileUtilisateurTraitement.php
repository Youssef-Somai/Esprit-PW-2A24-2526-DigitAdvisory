<?php
session_start();

require_once __DIR__ . '/../../Controller/utilisateur_controller.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SESSION['user']['id_user'])) {
    $id_user = isset($_POST['id_user']) ? (int) $_POST['id_user'] : 0;
    if ($id_user === (int) $_SESSION['user']['id_user']) {
        $controller = new UtilisateurController();
        $controller->supprimerUtilisateur($id_user);
        session_unset();
        session_destroy();
    }
}

header('Location: ../FrontOffice/login.php');
exit;
