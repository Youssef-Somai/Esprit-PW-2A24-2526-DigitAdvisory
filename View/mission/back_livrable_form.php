<?php
$isEdit = isset($livrable['id']);
$pageTitle = $isEdit ? "Modifier le Livrable" : "Ajouter un Livrable";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?> — BackOffice</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
<div class="sidebar">
    <div class="brand"><i class="fas fa-briefcase me-2"></i>Consulting</div>
    <a href="index.php?action=back_list" class="active"><i class="fas fa-tasks me-2"></i>Missions</a>
    <a href="index.php?action=front_list"><i class="fas fa-globe me-2"></i>FrontOffice</a>
</div>
<div class="main">
    <div class="topbar">
        <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-file-alt me-2"></i><?= htmlspecialchars($pageTitle) ?></h5>
        <a href="index.php?action=back_list" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Retour</a>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="error-box">
        <strong><i class="fas fa-exclamation-triangle me-1"></i>Erreurs de saisie :</strong>
        <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>

    <div class="card-form">
        <form method="POST" action="" novalidate>
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= (int)$livrable['id'] ?>">
            <?php endif; ?>

            <div class="mb-4">
                <label class="form-label">Mission associée</label>
                <select name="mission_id" class="form-select" <?= $isEdit ? 'disabled' : '' ?>>
                    <option value="">-- Choisir une mission --</option>
                    <?php foreach ($missions as $m): ?>
                    <option value="<?= (int)$m['id'] ?>"
                        <?= ((int)($livrable['mission_id'] ?? 0) === (int)$m['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($m['titre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($isEdit): ?>
                <input type="hidden" name="mission_id" value="<?= (int)$livrable['mission_id'] ?>">
                <?php endif; ?>
            </div>

            <div class="mb-4">
                <label class="form-label">Nom du fichier / Rapport <span class="text-danger">*</span></label>
                <input type="text" name="nom_fichier" class="form-control"
                       placeholder="ex: rapport_final.pdf"
                       value="<?= htmlspecialchars($livrable['nom_fichier'] ?? '') ?>">
                <div class="field-error">Doit inclure une extension (ex: .pdf, .docx)</div>
            </div>

            <div class="mb-4">
                <label class="form-label">Date de remise <span class="text-danger">*</span></label>
                <input type="text" name="date_remise" class="form-control date-picker"
                       placeholder="YYYY-MM-DD"
                       value="<?= htmlspecialchars($livrable['date_remise'] ?? '') ?>">
            </div>

            <div class="mb-4">
                <label class="form-label">État <span class="text-danger">*</span></label>
                <select name="etat" class="form-select">
                    <option value="">-- Choisir un état --</option>
                    <?php foreach (['En attente','Validé','Rejeté'] as $e): ?>
                    <option value="<?= $e ?>" <?= (($livrable['etat'] ?? '') === $e) ? 'selected' : '' ?>>
                        <?= $e ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-save">
                    <i class="fas fa-save me-2"></i><?= $isEdit ? 'Mettre à jour' : 'Enregistrer' ?>
                </button>
                <a href="index.php?action=back_list" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script>
// JS Validation — no HTML5 validation used
document.querySelector('form').addEventListener('submit', function(e) {
    let errors = [];
    const nomFichier = document.querySelector('[name="nom_fichier"]').value.trim();
    const dateRemise = document.querySelector('[name="date_remise"]').value.trim();
    const etat       = document.querySelector('[name="etat"]').value;
    const missionId  = document.querySelector('[name="mission_id"]')?.value;

    if (nomFichier.length < 2) errors.push("Le nom du fichier est trop court.");
    if (!/\.[a-zA-Z0-9]{2,5}$/.test(nomFichier)) errors.push("Le nom doit avoir une extension (ex: .pdf).");
    if (!dateRemise.match(/^\d{4}-\d{2}-\d{2}$/)) errors.push("La date doit être au format YYYY-MM-DD.");
    if (!etat) errors.push("Veuillez sélectionner un état.");
    if (missionId !== undefined && !missionId) errors.push("Veuillez sélectionner une mission.");

    if (errors.length > 0) {
        e.preventDefault();
        alert("Erreurs :\n• " + errors.join("\n• "));
    }
});
</script>
</body>
</html>
