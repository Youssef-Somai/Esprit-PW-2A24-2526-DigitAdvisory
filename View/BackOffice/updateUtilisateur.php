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
    <title>Modifier utilisateur</title>
</head>
<body>
    <h2>Modifier utilisateur</h2>

    <form method="POST" action="../traitement/updateUtilisateurTraitement.php" onsubmit="return validateUpdateForm();">
        <input type="hidden" name="id_user" value="<?php echo $user['id_user']; ?>">
        <input type="hidden" name="role" value="<?php echo htmlspecialchars($user['role']); ?>">

        <div>
            <label>Email</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>">
        </div>

        <div>
            <label>Mot de passe</label>
            <input type="text" name="password" id="password" value="<?php echo htmlspecialchars($user['password']); ?>">
        </div>

        <div>
            <label>Statut</label>
            <select name="statut_compte">
                <option value="actif" <?php if($user['statut_compte']==='actif') echo 'selected'; ?>>actif</option>
                <option value="bloque" <?php if($user['statut_compte']==='bloque') echo 'selected'; ?>>bloque</option>
                <option value="en_attente" <?php if($user['statut_compte']==='en_attente') echo 'selected'; ?>>en_attente</option>
            </select>
        </div>

        <?php if ($user['role'] === 'expert'): ?>
            <div>
                <label>Nom</label>
                <input type="text" name="nom" id="nom" value="<?php echo htmlspecialchars($user['nom']); ?>">
            </div>
            <div>
                <label>Prénom</label>
                <input type="text" name="prenom" id="prenom" value="<?php echo htmlspecialchars($user['prenom']); ?>">
            </div>
            <div>
                <label>Domaine</label>
                <input type="text" name="domaine" id="domaine" value="<?php echo htmlspecialchars($user['domaine']); ?>">
            </div>
            <div>
                <label>Niveau expérience</label>
                <input type="text" name="niveau_experience" id="niveau_experience" value="<?php echo htmlspecialchars($user['niveau_experience']); ?>">
            </div>
            <div>
                <label>Tarif journalier</label>
                <input type="number" step="0.01" name="tarif_journalier" id="tarif_journalier" value="<?php echo htmlspecialchars((string)$user['tarif_journalier']); ?>">
            </div>
        <?php else: ?>
            <div>
                <label>Nom entreprise</label>
                <input type="text" name="nom_entreprise" id="nom_entreprise" value="<?php echo htmlspecialchars($user['nom_entreprise']); ?>">
            </div>
            <div>
                <label>Secteur activité</label>
                <input type="text" name="secteur_activite" id="secteur_activite" value="<?php echo htmlspecialchars($user['secteur_activite']); ?>">
            </div>
            <div>
                <label>Adresse</label>
                <input type="text" name="adresse" id="adresse" value="<?php echo htmlspecialchars($user['adresse']); ?>">
            </div>
            <div>
                <label>Téléphone</label>
                <input type="text" name="telephone" id="telephone" value="<?php echo htmlspecialchars($user['telephone']); ?>">
            </div>
        <?php endif; ?>

        <button type="submit">Enregistrer</button>
    </form>

    <script>
        function validateUpdateForm() {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();

            if (email === '' || !email.includes('@')) {
                alert('Email invalide');
                return false;
            }

            if (password.length < 6) {
                alert('Le mot de passe doit contenir au moins 6 caractères');
                return false;
            }

            const nom = document.getElementById('nom');
            const nomEntreprise = document.getElementById('nom_entreprise');

            if (nom && nom.value.trim() === '') {
                alert('Le nom est obligatoire');
                return false;
            }

            if (nomEntreprise && nomEntreprise.value.trim() === '') {
                alert("Le nom de l'entreprise est obligatoire");
                return false;
            }

            const telephone = document.getElementById('telephone');
            if (telephone && telephone.value.trim() !== '' && !/^[0-9+\s-]{8,20}$/.test(telephone.value.trim())) {
                alert('Téléphone invalide');
                return false;
            }

            const tarif = document.getElementById('tarif_journalier');
            if (tarif && tarif.value.trim() !== '' && parseFloat(tarif.value) < 0) {
                alert('Le tarif journalier doit être positif');
                return false;
            }

            return true;
        }
    </script>
</body>
</html>