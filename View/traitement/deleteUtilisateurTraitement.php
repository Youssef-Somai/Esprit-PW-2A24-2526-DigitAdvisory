<?php

require_once __DIR__ . '/../../Controller/utilisateur_controller.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_user = isset($_POST['id_user']) ? (int) $_POST['id_user'] : 0;

    if ($id_user > 0) {
        $controller = new UtilisateurController();
        $controller->supprimerUtilisateur($id_user);
    }

    header('Location: ../BackOffice/back-utilisateur.php');
    exit;
}