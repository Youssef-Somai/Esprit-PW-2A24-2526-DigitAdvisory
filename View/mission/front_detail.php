<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($mission['titre']) ?> - Eduleb Consulting</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark" style="background:#1a3c5e;">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">🎓 Eduleb Consulting</a>
        <div>
            <a href="index.php?action=front_list" class="btn btn-outline-light btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Retour aux missions
            </a>
        </div>
    </div>
</nav>

<section class="hero-section">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-3">
                <li class="breadcrumb-item"><a href="index.php?action=front_list" class="text-white-50">Missions</a></li>
                <li class="breadcrumb-item active text-white"><?= htmlspecialchars($mission['titre']) ?></li>
            </ol>
        </nav>
        <h1 class="display-6 fw-bold mb-4"><?= htmlspecialchars($mission['titre']) ?></h1>
        <div>
            <span class="info-badge"><i class="fas fa-calendar-alt me-2"></i>Début : <?= date('d/m/Y', strtotime($mission['date_debut'])) ?></span>
            <span class="info-badge"><i class="fas fa-flag me-2"></i>Statut : <?= htmlspecialchars($mission['statut']) ?></span>
            <span class="info-badge"><i class="fas fa-paperclip me-2"></i><?= count($livrables) ?> livrable(s)</span>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <h4 class="fw-bold mb-4" style="color:#1a3c5e;">
            <i class="fas fa-file-alt me-2"></i>Livrables de la mission
        </h4>

        <?php if (empty($livrables)): ?>
            <div class="alert alert-info text-center py-5">
                <i class="fas fa-inbox fa-3x mb-3 d-block text-muted"></i>
                Aucun livrable associé à cette mission.
            </div>
        <?php else: ?>
            <?php foreach ($livrables as $l): ?>
            <?php
                $etatClass = match($l['etat_validation']) {
                    'Validé'     => 'etat-valide',
                    'Rejeté'     => 'etat-rejete',
                    default      => 'etat-attente'
                };
                $icon = match($l['etat_validation']) {
                    'Validé'  => 'fa-check-circle text-success',
                    'Rejeté'  => 'fa-times-circle text-danger',
                    default   => 'fa-hourglass-half text-warning'
                };
            ?>
            <div class="livrable-row d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                    <i class="fas <?= $icon ?> fa-lg"></i>
                    <div>
                        <div class="fw-semibold"><?= htmlspecialchars($l['nom_fichier']) ?></div>
                        <div class="text-muted small">
                            <i class="fas fa-calendar-check me-1"></i>
                            Date de remise : <?= date('d/m/Y', strtotime($l['date_remise'])) ?>
                        </div>
                    </div>
                </div>
                <span class="badge-etat <?= $etatClass ?>"><?= htmlspecialchars($l['etat_validation']) ?></span>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<footer class="py-4 text-center" style="background:#1a3c5e;">
    <p class="mb-0 text-white-50">© <?= date('Y') ?> Eduleb Consulting — Module 5 : Gestion des Missions</p>
</footer>

<script src="assets/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
