<?php
/**
 * Module 5 — Gestion des Missions & Projets
 * Front Controller / Router (MVC Pattern)
 */

session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/Model/Mission.php';
require_once __DIR__ . '/Model/Livrable.php';
require_once __DIR__ . '/Controller/MissionController.php';

$action = $_GET['action'] ?? 'front_list';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$controller = new MissionController();

match($action) {
    // ── FRONT OFFICE
    'front_list'           => $controller->frontList(),
    'front_detail'         => $controller->frontDetail($id),

    // ── BACK OFFICE — MISSIONS
    'back_list'            => $controller->backList(),
    'back_create'          => $controller->backCreate(),
    'back_edit'            => $controller->backEdit($id),
    'back_delete'          => $controller->backDelete($id),

    // ── BACK OFFICE — LIVRABLES
    'back_livrable_create' => $controller->backLivrableCreate($id),
    'back_livrable_edit'   => $controller->backLivrableEdit($id),
    'back_livrable_delete' => $controller->backLivrableDelete($id),

    // ── MÉTIERS AVANCÉS IA
    'metier1_rapport'      => $controller->metier1Rapport($id),
    'metier2_alertes'      => $controller->metier2Alertes(),

    default                => $controller->frontList(),
};
