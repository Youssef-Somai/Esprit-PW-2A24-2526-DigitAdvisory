<?php
require_once __DIR__ . '/../../Controller/utilisateur_controller.php';

$controller = new UtilisateurController();
$id_user = isset($_GET['id_user']) ? (int) $_GET['id_user'] : 0;
$user = $controller->getUserById($id_user);

if (!$user) {
    die('Utilisateur introuvable');
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail utilisateur</title>
</head>
<body>
    <h2>Détail utilisateur</h2>

    <p><strong>ID :</strong> <?php echo $user['id_user']; ?></p>
    <p><strong>Email :</strong> <?php echo htmlspecialchars($user['email']); ?></p>
    <p><strong>Rôle :</strong> <?php echo htmlspecialchars($user['role']); ?></p>
    <p><strong>Statut :</strong> <?php echo htmlspecialchars($user['statut_compte']); ?></p>

    <?php if ($user['role'] === 'expert'): ?>
        <p><strong>Nom :</strong> <?php echo htmlspecialchars($user['nom']); ?></p>
        <p><strong>Prénom :</strong> <?php echo htmlspecialchars($user['prenom']); ?></p>
        <p><strong>Domaine :</strong> <?php echo htmlspecialchars($user['domaine']); ?></p>
        <p><strong>Niveau expérience :</strong> <?php echo htmlspecialchars($user['niveau_experience']); ?></p>
        <p><strong>Tarif journalier :</strong> <?php echo htmlspecialchars((string)$user['tarif_journalier']); ?></p>
    <?php else: ?>
        <p><strong>Nom entreprise :</strong> <?php echo htmlspecialchars($user['nom_entreprise']); ?></p>
        <p><strong>Secteur activité :</strong> <?php echo htmlspecialchars($user['secteur_activite']); ?></p>
        <p><strong>Adresse :</strong> <?php echo htmlspecialchars($user['adresse']); ?></p>
        <p><strong>Téléphone :</strong> <?php echo htmlspecialchars($user['telephone']); ?></p>
    <?php endif; ?>

    <p><a href="listUtilisateurs.php">Retour</a></p>
</body>
</html>