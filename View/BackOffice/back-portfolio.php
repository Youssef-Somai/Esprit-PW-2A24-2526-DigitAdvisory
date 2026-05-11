<?php
/**
 * back-portfolio.php — Digit Advisory
 * Gestion des Portfolios CV (interface admin / back-office)
 *
 * Tables utilisées : `portfolio` + `element_portfolio`
 * Le chargement serveur utilise PDO + ElementPortfolioController.
 * Les mutations (create) passent par fetch() → PortfolioController (API JSON).
 */
require_once '../../config.php';
require_once '../../Model/Portfolio.php';
require_once '../../Model/ElementPortfolio.php';
require_once '../../Controller/ElementPortfolioController.php';

$db       = config::getConnexion();
$elemCtrl = new ElementPortfolioController();


// ─── Suppression (GET) ───────────────────────────────────────
if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete') {
    $stmt = $db->prepare('DELETE FROM portfolio WHERE id_portfolio = :id');
    $stmt->execute([':id' => (int)$_GET['id']]);
    // Les éléments sont supprimés en CASCADE par la FK
    header('Location: back-portfolio.php');
    exit();
}

// ─── Chargement des portfolios (JOIN element_portfolio) ──────
$stmt = $db->query('SELECT * FROM portfolio ORDER BY created_at DESC');
$rawPortfolios = $stmt->fetchAll();

$portfolios = [];
foreach ($rawPortfolios as $pf) {
    $elements = $elemCtrl->listElements((int)$pf['id_portfolio']);
    $stats    = $elemCtrl->countByType((int)$pf['id_portfolio']);
    $pf['skills']         = [];
    $pf['experiences']    = [];
    $pf['certifications'] = [];
    foreach ($elements as $el) {
        if ($el['type_element'] === 'skill') {
            $pf['skills'][] = ['id_element' => $el['id_element'], 'skill_name' => $el['titre'], 'niveau' => $el['niveau']];
        } elseif ($el['type_element'] === 'experience') {
            $pf['experiences'][] = ['id_element' => $el['id_element'], 'job_title' => $el['titre'], 'company' => $el['description'], 'start_date' => $el['date_debut'], 'end_date' => $el['date_fin']];
        } elseif ($el['type_element'] === 'certification') {
            $pf['certifications'][] = ['id_element' => $el['id_element'], 'cert_name' => $el['titre'], 'issuer' => $el['description']];
        }
    }
    $pf['skills_count'] = $stats['skill'];
    $pf['exp_count']    = $stats['experience'];
    $pf['certs_count']  = $stats['certification'];
    $portfolios[] = $pf;
}

$total        = count($portfolios);
$totalSkills  = array_sum(array_column($portfolios, 'skills_count'));
$totalCerts   = array_sum(array_column($portfolios, 'certs_count'));
$portfoliosJson = json_encode($portfolios, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$apiUrl = '../../Controller/PortfolioController.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Portfolios CV — Digit Advisory</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root{--amber:#f59e0b;--navy:#1e293b;--sidebar-w:268px;}
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Inter',sans-serif;background:#f1f5f9;color:var(--navy);}

        /* ── SIDEBAR ── */
        .sidebar{width:var(--sidebar-w);background:var(--navy);display:flex;flex-direction:column;position:fixed;height:100vh;z-index:100;box-shadow:4px 0 20px rgba(0,0,0,.2);}
        .sb-head{padding:1.5rem 1.4rem;border-bottom:1px solid rgba(255,255,255,.08);display:flex;align-items:center;gap:.75rem;}
        .sb-logo{color:#fff;font-family:'Poppins',sans-serif;font-weight:700;font-size:1.1rem;text-decoration:none;display:flex;align-items:center;gap:.55rem;}
        .sb-logo i{color:var(--amber);}
        .sb-nav{flex:1;padding:.75rem 0;overflow-y:auto;}
        .sb-item{padding:.82rem 1.4rem;display:flex;align-items:center;gap:.9rem;color:rgba(255,255,255,.5);font-weight:500;font-size:.875rem;text-decoration:none;border-left:3px solid transparent;transition:all .18s;}
        .sb-item i{width:18px;text-align:center;}
        .sb-item:hover{background:rgba(255,255,255,.05);color:#fff;}
        .sb-item.active{background:rgba(245,158,11,.12);color:var(--amber);border-left-color:var(--amber);}
        .sb-footer{padding:1rem 1.4rem;border-top:1px solid rgba(255,255,255,.08);display:flex;align-items:center;gap:.85rem;}
        .sb-av{width:38px;height:38px;border-radius:50%;background:var(--amber);color:#fff;display:flex;justify-content:center;align-items:center;font-weight:700;font-size:.85rem;flex-shrink:0;}

        /* ── MAIN ── */
        .main{margin-left:var(--sidebar-w);padding:2rem;min-height:100vh;}
        .topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;background:#fff;padding:1.25rem 1.75rem;border-radius:14px;box-shadow:0 2px 10px rgba(0,0,0,.06);}
        .topbar h1{font-family:'Poppins',sans-serif;font-size:1.3rem;font-weight:700;color:var(--navy);}
        .topbar p{font-size:.78rem;color:#94a3b8;margin-top:.2rem;}

        /* ── KPI ── */
        .kpi-row{display:grid;grid-template-columns:repeat(3,1fr);gap:1.25rem;margin-bottom:2rem;}
        .kpi{background:#fff;padding:1.25rem 1.5rem;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.06);display:flex;align-items:center;gap:1rem;}
        .kpi-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;}
        .kpi-icon.blue{background:#eff6ff;color:#2563eb;}
        .kpi-icon.amber{background:#fef3c7;color:#d97706;}
        .kpi-icon.green{background:#f0fdf4;color:#16a34a;}
        .kpi-n{font-family:'Poppins',sans-serif;font-size:1.8rem;font-weight:700;line-height:1;}
        .kpi-l{font-size:.75rem;color:#94a3b8;margin-top:.3rem;}

        /* ── TABLE CARD ── */
        .card{background:#fff;border-radius:14px;box-shadow:0 2px 10px rgba(0,0,0,.06);overflow:hidden;margin-bottom:2rem;}
        .card-head{display:flex;justify-content:space-between;align-items:center;padding:1.25rem 1.75rem;border-bottom:1px solid #f1f5f9;}
        .card-head h2{font-family:'Poppins',sans-serif;font-size:1rem;font-weight:700;color:var(--navy);display:flex;align-items:center;gap:.6rem;}
        .tbl{width:100%;border-collapse:collapse;}
        .tbl th{padding:.85rem 1.25rem;text-align:left;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;border-bottom:1px solid #f1f5f9;white-space:nowrap;}
        .tbl td{padding:.85rem 1.25rem;font-size:.875rem;border-bottom:1px solid #f8fafc;vertical-align:middle;}
        .tbl tbody tr:hover{background:#f8fafc;}
        .tbl tbody tr:last-child td{border-bottom:none;}
        .empty-msg{text-align:center;padding:3.5rem;color:#94a3b8;}
        .empty-msg i{font-size:2.5rem;margin-bottom:.75rem;display:block;}

        /* ── BADGES ── */
        .badge{display:inline-flex;align-items:center;padding:.22rem .65rem;border-radius:20px;font-size:.72rem;font-weight:600;}
        .b-junior{background:rgba(16,185,129,.12);color:#059669;}
        .b-mid{background:rgba(6,182,212,.12);color:#0891b2;}
        .b-senior{background:rgba(139,92,246,.12);color:#7c3aed;}
        .b-expert{background:rgba(245,158,11,.12);color:#d97706;}
        .chip{display:inline-flex;align-items:center;gap:.3rem;padding:.2rem .55rem;background:#f1f5f9;border-radius:20px;font-size:.72rem;color:#64748b;font-weight:600;}

        /* ── AVAILABILITY ── */
        .av-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;}
        .av-immediate .av-dot{background:#16a34a;}.av-immediate{color:#15803d;}
        .av-one_month .av-dot{background:#d97706;}.av-one_month{color:#b45309;}
        .av-three_months .av-dot{background:#ca8a04;}.av-three_months{color:#a16207;}
        .av-unavailable .av-dot{background:#dc2626;}.av-unavailable{color:#dc2626;}
        .av-row{display:flex;align-items:center;gap:.4rem;font-size:.8rem;font-weight:600;}

        /* ── BUTTONS ── */
        .btn{display:inline-flex;align-items:center;gap:.4rem;padding:.55rem 1.1rem;border-radius:8px;border:none;cursor:pointer;font-family:'Inter',sans-serif;font-size:.84rem;font-weight:600;text-decoration:none;transition:all .18s;}
        .btn-primary{background:#2563eb;color:#fff;box-shadow:0 4px 12px rgba(37,99,235,.25);}
        .btn-primary:hover{background:#1d4ed8;}
        .btn-danger{background:#ef4444;color:#fff;}.btn-danger:hover{background:#dc2626;}
        .btn-ghost{background:#f8fafc;color:#64748b;border:1px solid #e2e8f0;}.btn-ghost:hover{background:#f1f5f9;}
        .btn-sm{padding:.35rem .8rem;font-size:.78rem;}

        /* ── MODAL ── */
        .modal-ov{position:fixed;inset:0;z-index:1000;background:rgba(15,23,42,.72);display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:opacity .3s;backdrop-filter:blur(4px);}
        .modal-ov.open{opacity:1;pointer-events:all;}
        .modal-box{background:#fff;border-radius:18px;width:92%;max-width:860px;max-height:90vh;display:flex;flex-direction:column;overflow:hidden;transform:translateY(22px);transition:transform .32s;box-shadow:0 20px 60px rgba(0,0,0,.18);}
        .modal-ov.open .modal-box{transform:translateY(0);}
        .m-head{padding:1.25rem 1.75rem;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#fff;}
        .m-head h3{font-family:'Poppins',sans-serif;font-size:1.05rem;font-weight:700;}
        .btn-x{background:transparent;border:none;font-size:1.1rem;color:#94a3b8;cursor:pointer;padding:.3rem;border-radius:6px;transition:all .18s;}
        .btn-x:hover{color:#ef4444;background:#fef2f2;}
        .m-body{padding:1.5rem;overflow-y:auto;flex:1;background:#f8fafc;}
        .m-foot{padding:1rem 1.75rem;border-top:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#fff;}

        /* Form */
        .form-row{display:grid;gap:1rem;margin-bottom:1rem;}
        .grid-2{grid-template-columns:1fr 1fr;}
        .f-group{display:flex;flex-direction:column;gap:.4rem;}
        .f-group label{font-size:.75rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;}
        .f-input,.f-select,.f-txt{padding:.65rem .9rem;border:1.5px solid #e2e8f0;border-radius:9px;font-family:'Inter',sans-serif;font-size:.875rem;background:#fff;width:100%;color:var(--navy);}
        .f-input:focus,.f-select:focus,.f-txt:focus{outline:none;border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.1);}
        .f-section{background:#fff;padding:1.4rem;border-radius:12px;border:1px solid #e2e8f0;margin-bottom:1rem;}
        .f-sec-title{font-weight:700;font-size:.9rem;color:var(--navy);margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;}
        .f-sec-title i{color:#2563eb;}
        .d-block{background:#f8fafc;border:1px dashed #cbd5e1;border-radius:8px;padding:.9rem;margin-bottom:.7rem;position:relative;}
        .d-x{position:absolute;right:.6rem;top:.6rem;background:none;border:none;color:#94a3b8;cursor:pointer;font-size:.9rem;padding:.2rem;border-radius:4px;}
        .d-x:hover{color:#ef4444;}
        .tz{display:flex;flex-wrap:wrap;gap:.4rem;padding:.5rem;border:1.5px solid #e2e8f0;border-radius:9px;background:#fff;min-height:44px;cursor:text;}
        .tz:focus-within{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.1);}
        .tz-chip{background:#eff6ff;color:#2563eb;padding:.22rem .6rem;border-radius:20px;font-size:.78rem;display:inline-flex;align-items:center;gap:.3rem;font-weight:500;}
        .tz-chip button{background:none;border:none;color:inherit;cursor:pointer;font-size:.7rem;padding:0;}
        .tz input{border:none;outline:none;flex:1;min-width:90px;font-size:.875rem;font-family:'Inter',sans-serif;}

        /* Step */
        .step-bar{display:flex;align-items:center;padding:1rem 1.75rem;background:#f8fafc;border-bottom:1px solid #e2e8f0;gap:0;flex-shrink:0;}
        .s-item{display:flex;align-items:center;gap:.5rem;}
        .s-dot{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;flex-shrink:0;transition:all .2s;}
        .s-dot.inactive{background:#e2e8f0;color:#94a3b8;}
        .s-dot.active{background:#2563eb;color:#fff;box-shadow:0 3px 10px rgba(37,99,235,.35);}
        .s-dot.done{background:#16a34a;color:#fff;}
        .s-lbl{font-size:.78rem;font-weight:600;color:#94a3b8;}
        .s-lbl.active{color:#2563eb;}
        .s-lbl.done{color:#16a34a;}
        .s-line{flex:1;height:2px;background:#e2e8f0;margin:0 .5rem;min-width:20px;transition:background .3s;}
        .s-line.done{background:#16a34a;}
        .step-panel{display:none;}
        .step-panel.active{display:block;animation:fadeUp .28s ease;}
        @keyframes fadeUp{from{opacity:0;transform:translateY(8px);}to{opacity:1;transform:translateY(0);}}

        /* Toast */
        #toast-host{position:fixed;right:1.25rem;bottom:1.25rem;z-index:9999;display:flex;flex-direction:column;gap:.5rem;}
        .toast{padding:.85rem 1.2rem;background:#fff;box-shadow:0 8px 25px rgba(0,0,0,.12);border-radius:10px;border-left:4px solid #16a34a;font-size:.875rem;font-weight:500;animation:tIn .28s ease;}
        .toast.err{border-left-color:#ef4444;}
        @keyframes tIn{from{transform:translateX(100%);opacity:0;}to{transform:translateX(0);opacity:1;}}
    </style>
</head>
<body>

<div style="display:flex;min-height:100vh;">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sb-head">
            <a href="../../View/FrontOffice/index.php" class="sb-logo"><i class="fa-solid fa-shield-halved"></i> Admin Panel</a>
        </div>
        <nav class="sb-nav">
            <a href="back-utilisateur.php"   class="sb-item"><i class="fa-solid fa-users"></i> Utilisateurs</a>
            <a href="back-quiz.php"          class="sb-item"><i class="fa-solid fa-list-check"></i> Quiz</a>
            <a href="back-portfolio.php"     class="sb-item active"><i class="fa-solid fa-id-card"></i> Portfolios CV</a>
            <a href="back-offres.php"        class="sb-item"><i class="fa-solid fa-briefcase"></i> Offres</a>
            <a href="back-certification.php" class="sb-item"><i class="fa-solid fa-award"></i> Certifications</a>
            <a href="back-messagerie.php"    class="sb-item"><i class="fa-solid fa-comments"></i> Messagerie</a>
        </nav>
        <div class="sb-footer">
            <div class="sb-av">AD</div>
            <div>
                <div style="font-size:.875rem;font-weight:600;color:#fff;">Admin Système</div>
                <div style="font-size:.72rem;color:rgba(255,255,255,.4);">Administrateur</div>
            </div>
            <a href="../../View/FrontOffice/login.php" style="margin-left:auto;color:rgba(255,255,255,.3);"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
        </div>
    </aside>

    <!-- MAIN -->
    <main class="main">
        <!-- Top Bar -->
        <div class="topbar">
            <div>
                <h1><i class="fa-solid fa-id-card" style="color:var(--amber);margin-right:.5rem;"></i>Gestion des Portfolios CV</h1>
                <p>Portfolios (<code>portfolio</code>) et éléments joints (<code>element_portfolio</code>).</p>
            </div>
            <div style="display:flex;gap:.75rem;align-items:center;">
                <span style="background:#fef3c7;color:#92400e;padding:.4rem 1rem;border-radius:8px;font-size:.8rem;font-weight:600;"><i class="fa-solid fa-lock"></i> Zone Admin</span>
                <button class="btn btn-primary" onclick="openBuilder()"><i class="fa-solid fa-plus"></i> Ajouter un CV</button>
            </div>
        </div>

        <!-- KPI Row -->
        <div class="kpi-row">
            <div class="kpi">
                <div class="kpi-icon blue"><i class="fa-solid fa-id-card"></i></div>
                <div>
                    <div class="kpi-n"><?= $total ?></div>
                    <div class="kpi-l">Portfolios (table <code>portfolio</code>)</div>
                </div>
            </div>
            <div class="kpi">
                <div class="kpi-icon amber"><i class="fa-solid fa-code"></i></div>
                <div>
                    <div class="kpi-n"><?= $totalSkills ?></div>
                    <div class="kpi-l">Compétences (table <code>element_portfolio</code>)</div>
                </div>
            </div>
            <div class="kpi">
                <div class="kpi-icon green"><i class="fa-solid fa-award"></i></div>
                <div>
                    <div class="kpi-n"><?= $totalCerts ?></div>
                    <div class="kpi-l">Certifications (table <code>element_portfolio</code>)</div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card">
            <div class="card-head">
                <h2><i class="fa-solid fa-table-list" style="color:#2563eb;"></i> Profils CV <span class="chip"><?= $total ?> profils</span></h2>
                <input id="searchInput" type="text" placeholder="Rechercher un nom…" class="f-input" style="width:220px;padding:.45rem .8rem;font-size:.84rem;" oninput="filterTable(this.value)">
            </div>
            <table class="tbl">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Consultant (portfolio)</th>
                        <th>Titre / Niveau</th>
                        <th>Éléments liés (element_portfolio)</th>
                        <th>Disponibilité</th>
                        <th>Créé le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tbl-body">
                <?php foreach ($portfolios as $ep): ?>
                <tr data-name="<?= strtolower(htmlspecialchars($ep['full_name'])) ?>">
                    <td style="color:#94a3b8;font-size:.8rem;">#<?= $ep['id_portfolio'] ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:.75rem;">
                            <div style="width:38px;height:38px;border-radius:50%;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0;">
                                <?= strtoupper(substr($ep['full_name'], 0, 2)) ?>
                            </div>
                            <div>
                                <div style="font-weight:600;"><?= htmlspecialchars($ep['full_name']) ?></div>
                                <div style="font-size:.75rem;color:#94a3b8;"><?= htmlspecialchars($ep['preferred_industry'] ?: '—') ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="font-size:.82rem;color:#64748b;margin-bottom:.3rem;"><?= htmlspecialchars(substr($ep['professional_title'],0,30)) ?><?= strlen($ep['professional_title'])>30?'…':'' ?></div>
                        <?php $lCss=['junior'=>'b-junior','mid'=>'b-mid','senior'=>'b-senior','expert'=>'b-expert']; ?>
                        <span class="badge <?= $lCss[$ep['experience_level']] ?? '' ?>"><?= ucfirst($ep['experience_level']) ?></span>
                    </td>
                    <td>
                        <div style="display:flex;gap:.3rem;flex-wrap:wrap;">
                            <span class="chip"><i class="fa-solid fa-code"></i> <?= (int)$ep['skills_count'] ?> compét.</span>
                            <span class="chip"><i class="fa-solid fa-briefcase"></i> <?= (int)$ep['exp_count'] ?> exp.</span>
                            <?php if ($ep['certs_count'] > 0): ?>
                            <span class="chip"><i class="fa-solid fa-award"></i> <?= (int)$ep['certs_count'] ?> cert.</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <?php $avMap = ['immediate'=>'Immédiate','one_month'=>'1 Mois','three_months'=>'3 Mois','unavailable'=>'Indisponible']; ?>
                        <div class="av-row av-<?= $ep['availability'] ?>">
                            <div class="av-dot"></div>
                            <?= $avMap[$ep['availability']] ?? $ep['availability'] ?>
                        </div>
                    </td>
                    <td style="font-size:.78rem;color:#94a3b8;"><?= date('d/m/Y', strtotime($ep['created_at'])) ?></td>
                    <td>
                        <div style="display:flex;gap:.4rem;">
                            <button class="btn btn-ghost btn-sm" onclick="viewProfile(<?= $ep['id_portfolio'] ?>)" title="Voir détails"><i class="fa-solid fa-eye"></i></button>
                            <a href="?action=delete&id=<?= $ep['id_portfolio'] ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Supprimer le portfolio de <?= htmlspecialchars(addslashes($ep['full_name'])) ?> ?\n\nTous ses éléments (compétences, expériences, certifications) seront supprimés.')">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if ($total === 0): ?>
                <tr><td colspan="7">
                    <div class="empty-msg">
                        <i class="fa-regular fa-folder-open"></i>
                        Aucun profil CV enregistré. <button class="btn btn-primary" style="margin-top:1rem;" onclick="openBuilder()"><i class="fa-solid fa-plus"></i> Créer le premier profil</button>
                    </div>
                </td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- TOAST HOST -->
<div id="toast-host"></div>

<!-- MODAL DETAIL VIEW -->
<div class="modal-ov" id="ov-detail">
    <div class="modal-box" style="max-width:700px;">
        <div class="m-head">
            <h3><i class="fa-solid fa-eye" style="color:#2563eb;margin-right:.4rem;"></i> Détail du Profil CV</h3>
            <button class="btn-x" onclick="closeModal('ov-detail')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="m-body" id="detail-body" style="background:#fff;"></div>
        <div class="m-foot">
            <button class="btn btn-ghost" onclick="closeModal('ov-detail')">Fermer</button>
        </div>
    </div>
</div>

<!-- MODAL CREATE (2 STEPS) -->
<div class="modal-ov" id="ov-builder">
    <div class="modal-box">
        <div class="m-head">
            <h3><i class="fa-solid fa-id-card" style="color:#2563eb;margin-right:.4rem;"></i> Ajouter un Profil CV</h3>
            <button class="btn-x" onclick="closeModal('ov-builder')"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <div class="step-bar">
            <div class="s-item">
                <div class="s-dot active" id="sd-1">1</div>
                <div class="s-lbl active" id="sl-1">Informations</div>
            </div>
            <div class="s-line" id="sline-1"></div>
            <div class="s-item">
                <div class="s-dot inactive" id="sd-2">2</div>
                <div class="s-lbl" id="sl-2">Compétences & Exp.</div>
            </div>
        </div>

        <div class="m-body">
            <!-- STEP 1 -->
            <div class="step-panel active" id="sp-1">
                <div class="f-section">
                    <div class="f-sec-title"><i class="fa-solid fa-address-card"></i> Informations Personnelles</div>
                    <div class="form-row grid-2">
                        <div class="f-group">
                            <label>Nom complet <span style="color:#ef4444;">*</span></label>
                            <input id="f-name" class="f-input" placeholder="Ex : Alice Martin">
                            <span style="font-size:.72rem;color:#ef4444;display:none;" id="e-name">Requis</span>
                        </div>
                        <div class="f-group">
                            <label>Titre Professionnel <span style="color:#ef4444;">*</span></label>
                            <input id="f-title" class="f-input" placeholder="Ex : Consultant IT Senior">
                            <span style="font-size:.72rem;color:#ef4444;display:none;" id="e-title">Requis</span>
                        </div>
                        <div class="f-group">
                            <label>Niveau d'expérience <span style="color:#ef4444;">*</span></label>
                            <select id="f-level" class="f-select">
                                <option value="">— Sélectionner —</option>
                                <option value="junior">Junior (0–3 ans)</option>
                                <option value="mid">Mid-level (3–6 ans)</option>
                                <option value="senior">Senior (6–12 ans)</option>
                                <option value="expert">Expert (12+ ans)</option>
                            </select>
                        </div>
                        <div class="f-group">
                            <label>Disponibilité <span style="color:#ef4444;">*</span></label>
                            <select id="f-avail" class="f-select">
                                <option value="">— Sélectionner —</option>
                                <option value="immediate">Immédiate</option>
                                <option value="one_month">1 Mois</option>
                                <option value="three_months">3 Mois</option>
                                <option value="unavailable">Indisponible</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="f-group">
                            <label>Secteur Préféré</label>
                            <select id="f-industry" class="f-select">
                                <option value="">— Tous secteurs —</option>
                                <option value="IT">Technologie & IT</option>
                                <option value="Finance">Finance & Banque</option>
                                <option value="Industry">Industrie</option>
                                <option value="Healthcare">Santé</option>
                                <option value="Consulting">Conseil</option>
                                <option value="Telecom">Télécoms</option>
                                <option value="Energy">Énergie</option>
                                <option value="Other">Autre</option>
                            </select>
                        </div>
                        <div class="f-group">
                            <label>Localisation</label>
                            <input id="f-location" class="f-input" placeholder="Ex : Paris, France">
                        </div>
                    </div>
                    <div class="f-group">
                        <label>Bio / Résumé Professionnel</label>
                        <textarea id="f-bio" class="f-txt" rows="3" placeholder="Décrivez le parcours du consultant…"></textarea>
                    </div>
                </div>
            </div>

            <!-- STEP 2 -->
            <div class="step-panel" id="sp-2">
                <div class="f-section">
                    <div class="f-sec-title"><i class="fa-solid fa-code"></i> Compétences (→ table element_portfolio, type='skill')</div>
                    <div class="tz" id="zone-tags"><input type="text" id="inp-tags" placeholder="Taper et appuyer sur Entrée…"></div>
                    <p style="font-size:.72rem;color:#94a3b8;margin-top:.4rem;"><i class="fa-solid fa-circle-info"></i> Entrée ou virgule pour valider chaque compétence.</p>
                </div>

                <div class="f-section">
                    <div class="f-sec-title"><i class="fa-solid fa-briefcase"></i> Expériences (→ table element_portfolio, type='experience')</div>
                    <div id="exp-host"></div>
                    <button type="button" class="btn btn-ghost" style="width:100%;border:1.5px dashed #cbd5e1;background:transparent;color:#64748b;" onclick="addExp()"><i class="fa-solid fa-plus"></i> Ajouter un Poste</button>
                </div>

                <div class="f-section">
                    <div class="f-sec-title"><i class="fa-solid fa-award"></i> Certifications (→ table element_portfolio, type='certification')</div>
                    <div id="cert-host"></div>
                    <button type="button" class="btn btn-ghost" style="width:100%;border:1.5px dashed #cbd5e1;background:transparent;color:#64748b;" onclick="addCert()"><i class="fa-solid fa-plus"></i> Ajouter une Certification</button>
                </div>
            </div>
        </div>

        <div class="m-foot">
            <button class="btn btn-ghost" id="btn-back" style="display:none;" onclick="goStep(1)"><i class="fa-solid fa-arrow-left"></i> Précédent</button>
            <div style="flex:1;"></div>
            <button class="btn btn-ghost" onclick="closeModal('ov-builder')" style="margin-right:.5rem;">Annuler</button>
            <button class="btn btn-primary" id="btn-next" onclick="nextStep()">Suivant <i class="fa-solid fa-arrow-right"></i></button>
            <button class="btn btn-primary" id="btn-save" style="display:none;" onclick="saveCV()"><i class="fa-solid fa-floppy-disk"></i> Enregistrer</button>
        </div>
    </div>
</div>

<script>
const API = '<?= $apiUrl ?>';
let portfolios = <?= $portfoliosJson ?>;
let skillsArr  = [];
let currentStep = 1;

/* ── TABLE FILTER ── */
function filterTable(q) {
    document.querySelectorAll('#tbl-body tr[data-name]').forEach(r => {
        r.style.display = r.dataset.name.includes(q.toLowerCase()) ? '' : 'none';
    });
}

/* ── DETAIL VIEW ── */
function viewProfile(id) {
    const p = portfolios.find(x => x.id_portfolio == id);
    if (!p) return;

    const avMap = {immediate:'Immédiate',one_month:'1 Mois',three_months:'3 Mois',unavailable:'Indisponible'};
    const lvMap = {junior:'Junior',mid:'Mid-level',senior:'Senior',expert:'Expert'};

    const tags  = (p.skills||[]).map(s => `<span style="background:#eff6ff;color:#2563eb;padding:.2rem .6rem;border-radius:20px;font-size:.78rem;font-weight:500;">${esc(s.skill_name)}</span>`).join('');
    const exps  = (p.experiences||[]).map(e => `
        <div style="padding:.75rem;background:#f8fafc;border-radius:8px;border-left:3px solid #2563eb;margin-bottom:.6rem;">
            <strong>${esc(e.job_title)}</strong> — <span style="color:#64748b;">${esc(e.company)}</span>
            <div style="font-size:.75rem;color:#94a3b8;margin-top:.2rem;">${esc(e.start_date||'')} → ${esc(e.end_date||'En cours')}</div>
        </div>`).join('');
    const certs = (p.certifications||[]).map(c => `
        <div style="display:flex;gap:.5rem;padding:.5rem 0;border-bottom:1px solid #f1f5f9;">
            <i class="fa-solid fa-award" style="color:#d97706;"></i>
            <div><strong>${esc(c.cert_name)}</strong> <span style="color:#94a3b8;font-size:.78rem;">— ${esc(c.issuer||'')}</span></div>
        </div>`).join('');

    document.getElementById('detail-body').innerHTML = `
        <div style="padding:1.5rem;">
            <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;padding:1.25rem;background:#f8fafc;border-radius:12px;">
                <div style="width:56px;height:56px;border-radius:50%;background:#2563eb;color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.3rem;font-weight:700;flex-shrink:0;">${initials(p.full_name)}</div>
                <div>
                    <h3 style="font-size:1.15rem;font-family:'Poppins',sans-serif;">${esc(p.full_name)}</h3>
                    <p style="color:#64748b;font-size:.875rem;">${esc(p.professional_title)}</p>
                    <div style="display:flex;gap:.5rem;margin-top:.5rem;flex-wrap:wrap;">
                        <span style="background:#eff6ff;color:#2563eb;padding:.2rem .6rem;border-radius:20px;font-size:.72rem;font-weight:600;">${lvMap[p.experience_level]||p.experience_level}</span>
                        <span style="background:#f0fdf4;color:#16a34a;padding:.2rem .6rem;border-radius:20px;font-size:.72rem;font-weight:600;">${avMap[p.availability]||p.availability}</span>
                    </div>
                </div>
            </div>
            ${p.bio ? `<div style="background:#f0f9ff;border-left:4px solid #2563eb;padding:.9rem 1.1rem;border-radius:0 8px 8px 0;margin-bottom:1.25rem;font-size:.875rem;color:#1e293b;line-height:1.65;">${esc(p.bio)}</div>` : ''}
            ${tags ? `<div style="margin-bottom:1.25rem;"><h4 style="font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;font-weight:700;margin-bottom:.65rem;">Compétences (element_portfolio)</h4><div style="display:flex;gap:.4rem;flex-wrap:wrap;">${tags}</div></div>` : ''}
            ${exps ? `<div style="margin-bottom:1.25rem;"><h4 style="font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;font-weight:700;margin-bottom:.65rem;">Expériences (element_portfolio)</h4>${exps}</div>` : ''}
            ${certs ? `<div><h4 style="font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;font-weight:700;margin-bottom:.65rem;">Certifications (element_portfolio)</h4>${certs}</div>` : ''}
        </div>`;

    document.getElementById('ov-detail').classList.add('open');
}

function initials(name) {
    if (!name) return '?';
    return name.split(' ').slice(0,2).map(w => w[0]).join('').toUpperCase();
}

/* ── BUILDER ── */
function openBuilder() {
    resetForm();
    goStep(1);
    document.getElementById('ov-builder').classList.add('open');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('open');
}

function goStep(step) {
    currentStep = step;
    document.getElementById('sp-1').classList.toggle('active', step === 1);
    document.getElementById('sp-2').classList.toggle('active', step === 2);

    document.getElementById('sd-1').className = 's-dot ' + (step === 1 ? 'active' : 'done');
    document.getElementById('sl-1').className = 's-lbl ' + (step === 1 ? 'active' : 'done');
    document.getElementById('sd-2').className = 's-dot ' + (step === 2 ? 'active' : 'inactive');
    document.getElementById('sl-2').className = 's-lbl ' + (step === 2 ? 'active' : '');
    document.getElementById('sline-1').className = 's-line' + (step > 1 ? ' done' : '');

    document.getElementById('btn-back').style.display  = step === 1 ? 'none' : 'inline-flex';
    document.getElementById('btn-next').style.display  = step === 1 ? 'inline-flex' : 'none';
    document.getElementById('btn-save').style.display  = step === 2 ? 'inline-flex' : 'none';
}

function nextStep() {
    let ok = true;
    ['name','title'].forEach(f => {
        const v = document.getElementById('f-' + f).value.trim();
        document.getElementById('e-' + f).style.display = v ? 'none' : 'block';
        if (!v) ok = false;
    });
    if (ok) goStep(2);
}

/* ── TAG INPUT ── */
document.addEventListener('DOMContentLoaded', () => {
    const inp = document.getElementById('inp-tags');
    inp.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            const v = inp.value.trim().replace(/,/g, '');
            if (v && !skillsArr.find(s => s.skill_name.toLowerCase() === v.toLowerCase())) {
                skillsArr.push({skill_name: v, niveau: 'intermediate'});
                renderTags();
            }
            inp.value = '';
        }
    });

    document.querySelectorAll('.modal-ov').forEach(ov => {
        ov.addEventListener('click', e => { if (e.target === ov) closeModal(ov.id); });
    });
});

function renderTags() {
    const tz  = document.getElementById('zone-tags');
    const inp = document.getElementById('inp-tags');
    tz.innerHTML = '';
    skillsArr.forEach((s, idx) => {
        const chip = document.createElement('div');
        chip.className = 'tz-chip';
        chip.innerHTML = `<span>${esc(s.skill_name)}</span><button type="button" onclick="rmTag(${idx})"><i class="fa-solid fa-xmark"></i></button>`;
        tz.appendChild(chip);
    });
    tz.appendChild(inp);
    inp.focus();
}

function rmTag(idx) { skillsArr.splice(idx, 1); renderTags(); }

/* ── DYNAMIC BLOCKS ── */
function addExp() {
    const d = document.createElement('div');
    d.className = 'd-block';
    d.innerHTML = `
        <button type="button" class="d-x" onclick="this.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button>
        <div class="form-row grid-2">
            <input class="f-input exp-tit" placeholder="Titre du poste">
            <input class="f-input exp-cy" placeholder="Entreprise">
            <input type="date" class="f-input exp-sd" title="Date de début">
            <input type="date" class="f-input exp-ed" title="Date de fin (vide = En cours)">
        </div>`;
    document.getElementById('exp-host').appendChild(d);
}

function addCert() {
    const d = document.createElement('div');
    d.className = 'd-block';
    d.innerHTML = `
        <button type="button" class="d-x" onclick="this.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button>
        <div class="form-row grid-2">
            <input class="f-input cert-nm" placeholder="Nom certification (ex: ISO 27001)">
            <input class="f-input cert-is" placeholder="Émetteur / Organisme">
        </div>`;
    document.getElementById('cert-host').appendChild(d);
}

/* ── SAVE (via PortfolioController API — insère dans les 2 tables) ── */
async function saveCV() {
    const btn = document.getElementById('btn-save');
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Enregistrement…';
    btn.disabled = true;

    const expsArr = [];
    document.querySelectorAll('#exp-host .d-block').forEach(b => {
        const jt = b.querySelector('.exp-tit').value.trim();
        if (jt) expsArr.push({job_title: jt, company: b.querySelector('.exp-cy').value.trim(), start_date: b.querySelector('.exp-sd').value, end_date: b.querySelector('.exp-ed').value});
    });

    const certsArr = [];
    document.querySelectorAll('#cert-host .d-block').forEach(b => {
        const cn = b.querySelector('.cert-nm').value.trim();
        if (cn) certsArr.push({cert_name: cn, issuer: b.querySelector('.cert-is').value.trim()});
    });

    const payload = {
        full_name:         document.getElementById('f-name').value.trim(),
        professional_title:document.getElementById('f-title').value.trim(),
        experience_level:  document.getElementById('f-level').value || 'junior',
        availability:      document.getElementById('f-avail').value || 'immediate',
        preferred_industry:document.getElementById('f-industry').value,
        location:          document.getElementById('f-location').value.trim(),
        bio:               document.getElementById('f-bio').value.trim(),
        skills:            skillsArr,      // → element_portfolio (type='skill')
        experiences:       expsArr,        // → element_portfolio (type='experience')
        certifications:    certsArr,       // → element_portfolio (type='certification')
    };

    try {
        const res  = await fetch(`${API}?action=create`, {method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(payload)});
        const data = await res.json();
        if (data.success) {
            toast('✅ Profil CV créé ! (portfolio + element_portfolio)');
            closeModal('ov-builder');
            setTimeout(() => location.reload(), 1200);
        } else {
            toast('❌ ' + (data.error || 'Erreur inconnue'), true);
        }
    } catch (e) {
        toast('❌ Erreur réseau.', true);
    }

    btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Enregistrer';
    btn.disabled = false;
}

function resetForm() {
    ['f-name','f-title','f-bio','f-location'].forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
    ['f-level','f-avail','f-industry'].forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
    skillsArr = [];
    if (document.getElementById('zone-tags')) renderTags();
    document.getElementById('exp-host').innerHTML  = '';
    document.getElementById('cert-host').innerHTML = '';
}

/* ── TOAST ── */
function toast(msg, err = false) {
    const t = document.createElement('div');
    t.className = err ? 'toast err' : 'toast';
    t.textContent = msg;
    document.getElementById('toast-host').appendChild(t);
    setTimeout(() => t.remove(), 3800);
}

function esc(s) {
    if (s === null || s === undefined) return '';
    return String(s).replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>
</body>
</html>