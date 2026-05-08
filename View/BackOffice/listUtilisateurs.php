<?php
session_start();

if (!isset($_SESSION['user']['id_user']) || strtolower($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: ../FrontOffice/login.php');
    exit;
}

require_once __DIR__ . '/../../Controller/utilisateur_controller.php';

$controller = new UtilisateurController();
$users = $controller->listeUsers();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste Utilisateurs</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        a, button { margin-right: 5px; }
    </style>
</head>
<body>
    <h2>Liste des utilisateurs</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Nom / Entreprise</th>
                <th>Domaine / Secteur</th>
                <th>Téléphone / Tarif</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?php echo $u['id_user']; ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td><?php echo htmlspecialchars($u['role']); ?></td>
                <td>
                    <?php
                    echo $u['role'] === 'expert'
                        ? htmlspecialchars(($u['nom'] ?? '') . ' ' . ($u['prenom'] ?? ''))
                        : htmlspecialchars($u['nom_entreprise'] ?? '');
                    ?>
                </td>
                <td>
                    <?php
                    echo $u['role'] === 'expert'
                        ? htmlspecialchars($u['domaine'] ?? '')
                        : htmlspecialchars($u['secteur_activite'] ?? '');
                    ?>
                </td>
                <td>
                    <?php
                    echo $u['role'] === 'expert'
                        ? htmlspecialchars((string)($u['tarif_journalier'] ?? ''))
                        : htmlspecialchars($u['telephone'] ?? '');
                    ?>
                </td>
                <td><?php echo htmlspecialchars($u['statut_compte']); ?></td>
                <td>
                    <a href="readUtilisateur.php?id_user=<?php echo $u['id_user']; ?>">Voir</a>
                    <a href="updateUtilisateur.php?id_user=<?php echo $u['id_user']; ?>">Modifier</a>

                    <form action="../traitement/deleteUtilisateurTraitement.php" method="POST" style="display:inline;">
                        <input type="hidden" name="id_user" value="<?php echo $u['id_user']; ?>">
                        <button type="submit" onclick="return confirm('Supprimer cet utilisateur ?')">Supprimer</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>