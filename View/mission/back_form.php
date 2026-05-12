<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($mission) ? 'Modifier' : 'Créer' ?> une Mission | Admin Eduleb</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-brand">🎓 Eduleb <span>Admin</span></div>
    <ul class="sidebar-menu">
        <li><a href="#"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></li>
        <li class="active"><a href="index.php?action=back_list"><i class="fas fa-briefcase"></i> Missions</a></li>
        <li><a href="index.php?action=back_livrable_list"><i class="fas fa-file-alt"></i> Livrables</a></li>
        <li><a href="index.php?action=front_list"><i class="fas fa-eye"></i> Front Office</a></li>
    </ul>
</aside>

<div class="main-content">
    <div class="topbar">
        <span class="topbar-title">
            <i class="fas fa-<?= isset($mission['id']) ? 'edit' : 'plus-circle' ?> me-2"></i>
            <?= isset($mission['id']) ? 'Modifier la mission' : 'Nouvelle mission' ?>
        </span>
        <a href="index.php?action=back_list" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Retour
        </a>
    </div>

    <div class="page-content">
        <div class="form-card">
            <h4><i class="fas fa-briefcase me-2"></i><?= isset($mission['id']) ? 'Modifier la Mission' : 'Créer une Mission' ?></h4>

            <!-- Server-side errors -->
            <?php if (!empty($errors)): ?>
            <div class="error-summary">
                <strong><i class="fas fa-exclamation-circle me-1"></i> Erreurs :</strong>
                <ul class="mt-1">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <form id="missionForm" method="POST" action="index.php?action=<?= isset($mission['id']) ? 'back_edit&id='.$mission['id'] : 'back_create' ?>" novalidate>

                <!-- TITRE -->
                <div class="mb-4">
                    <label class="form-label" for="titre">Titre de la mission <span class="text-danger">*</span></label>
                    <input type="text" id="titre" name="titre" class="form-control"
                           value="<?= htmlspecialchars($mission['titre'] ?? '') ?>"
                           placeholder="Ex: Audit Digital Entreprise ABC">
                    <div class="invalid-feedback" id="titreError"></div>
                </div>

                <!-- DATE DEBUT -->
                <div class="mb-4">
                    <label class="form-label" for="date_debut">Date de début <span class="text-danger">*</span></label>
                    <input type="text" id="date_debut" name="date_debut" class="form-control"
                           value="<?= htmlspecialchars($mission['date_debut'] ?? '') ?>"
                           placeholder="AAAA-MM-JJ (ex: 2024-06-01)">
                    <div class="invalid-feedback" id="dateError"></div>
                    <small class="text-muted">Format : AAAA-MM-JJ</small>
                </div>

                <!-- STATUT -->
                <div class="mb-4">
                    <label class="form-label" for="statut">Statut <span class="text-danger">*</span></label>
                    <select id="statut" name="statut" class="form-select">
                        <option value="">-- Sélectionner un statut --</option>
                        <?php foreach (['En attente','En cours','Terminée','Annulée'] as $s): ?>
                            <option value="<?= $s ?>" <?= (($mission['statut'] ?? '') === $s) ? 'selected' : '' ?>>
                                <?= $s ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback" id="statutError"></div>
                </div>

                <div class="d-flex gap-3 mt-4">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save me-2"></i><?= isset($mission['id']) ? 'Enregistrer les modifications' : 'Créer la mission' ?>
                    </button>
                    <a href="index.php?action=back_list" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script>
/**
 * JavaScript Validation — No HTML5 required attributes used
 * All validation is done manually in JS (and duplicated server-side in PHP)
 */
document.getElementById('missionForm').addEventListener('submit', function(e) {
    let valid = true;

    // Reset
    ['titre','date_debut','statut'].forEach(id => {
        document.getElementById(id).classList.remove('is-invalid');
    });
    ['titreError','dateError','statutError'].forEach(id => {
        document.getElementById(id).textContent = '';
    });

    // Validate Titre
    const titre = document.getElementById('titre').value.trim();
    if (titre.length < 3) {
        showError('titre', 'titreError', 'Le titre doit contenir au moins 3 caractères.');
        valid = false;
    } else if (titre.length > 255) {
        showError('titre', 'titreError', 'Le titre ne peut pas dépasser 255 caractères.');
        valid = false;
    }

    // Validate Date
    const date = document.getElementById('date_debut').value.trim();
    const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
    if (!dateRegex.test(date)) {
        showError('date_debut', 'dateError', 'Format de date invalide. Utilisez AAAA-MM-JJ.');
        valid = false;
    } else {
        const parsed = new Date(date);
        if (isNaN(parsed.getTime())) {
            showError('date_debut', 'dateError', 'Date invalide.');
            valid = false;
        }
    }

    // Validate Statut
    const statut = document.getElementById('statut').value;
    const allowed = ['En attente', 'En cours', 'Terminée', 'Annulée'];
    if (!allowed.includes(statut)) {
        showError('statut', 'statutError', 'Veuillez sélectionner un statut valide.');
        valid = false;
    }

    if (!valid) e.preventDefault();
});

function showError(fieldId, errorId, message) {
    document.getElementById(fieldId).classList.add('is-invalid');
    document.getElementById(errorId).textContent = message;
}
</script>
</body>
</html>
