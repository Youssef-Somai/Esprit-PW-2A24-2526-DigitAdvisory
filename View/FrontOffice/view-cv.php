<?php
/**
 * view-cv.php — Page CV dédiée mobile (accessible via QR Code)
 */
require_once '../../config.php';
require_once '../../Model/Portfolio.php';
require_once '../../Model/ElementPortfolio.php';
require_once '../../Controller/ElementPortfolioController.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { echo '<h2 style="text-align:center;padding:2rem;font-family:sans-serif;">Portfolio introuvable.</h2>'; exit; }

$db       = config::getConnexion();
$elemCtrl = new ElementPortfolioController();

$stmt = $db->prepare('SELECT * FROM portfolio WHERE id_portfolio = :id LIMIT 1');
$stmt->execute([':id' => $id]);
$p = $stmt->fetch();

if (!$p) { echo '<h2 style="text-align:center;padding:2rem;font-family:sans-serif;">Portfolio introuvable.</h2>'; exit; }

$elements = $elemCtrl->listElements($id);
$skills = $experiences = $certifications = [];
foreach ($elements as $el) {
    if ($el['type_element'] === 'skill')         $skills[]         = $el;
    elseif ($el['type_element'] === 'experience')  $experiences[]    = $el;
    elseif ($el['type_element'] === 'certification') $certifications[] = $el;
}

function initials($n){ if(!$n) return '?'; $parts=explode(' ',trim($n)); return strtoupper(substr($parts[0],0,1).(count($parts)>1?substr($parts[1],0,1):'')); }
function fmtDate($d){ if(!$d) return ''; try{ $dt=new DateTime($d); return $dt->format('M Y'); } catch(Exception $e){ return $d; } }
function e($s){ return htmlspecialchars($s??'', ENT_QUOTES,'UTF-8'); }
$lvlMap = ['junior'=>'Junior','mid'=>'Mid-level','senior'=>'Senior','expert'=>'Expert'];
$avlMap = ['immediate'=>'Disponible','one_month'=>'1 Mois','three_months'=>'3 Mois','unavailable'=>'Indisponible'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($p['full_name']) ?> — CV Digit Advisory</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:#f1f5f9;color:#1e293b;min-height:100vh}
.page{max-width:900px;margin:0 auto;background:#fff;min-height:100vh;box-shadow:0 0 40px rgba(0,0,0,.1)}

/* HEADER */
.cv-header{background:linear-gradient(135deg,#0f172a,#1e3a5f);padding:2rem 1.5rem;display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap}
.cv-av{width:80px;height:80px;border-radius:50%;background:#2563eb;display:flex;align-items:center;justify-content:center;font-family:'Poppins',sans-serif;font-size:1.8rem;font-weight:800;color:#fff;border:3px solid rgba(255,255,255,.3);flex-shrink:0}
.cv-hdr-info{flex:1}
.cv-name{font-family:'Poppins',sans-serif;font-size:1.5rem;font-weight:800;color:#fff}
.cv-title-sub{font-size:.9rem;color:rgba(255,255,255,.7);margin-top:.2rem}
.cv-badge{display:inline-block;margin-top:.5rem;padding:.2rem .7rem;border-radius:20px;font-size:.72rem;font-weight:700;background:rgba(59,130,246,.3);color:#93c5fd}
.cv-meta-row{display:flex;gap:1rem;flex-wrap:wrap;margin-top:.6rem}
.cv-meta-item{display:flex;align-items:center;gap:.4rem;font-size:.78rem;color:rgba(255,255,255,.6)}
.cv-meta-item i{color:#60a5fa;font-size:.75rem}

/* BODY */
.cv-body{padding:1.5rem}
.cv-section{margin-bottom:1.8rem}
.cv-sec-title{font-family:'Poppins',sans-serif;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#2563eb;padding-bottom:.4rem;border-bottom:2px solid #eff6ff;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem}
.cv-sec-title i{font-size:.8rem}

/* BIO */
.cv-bio{background:#f0f9ff;border-left:4px solid #3b82f6;padding:1rem;border-radius:0 8px 8px 0;font-size:.875rem;color:#1e293b;line-height:1.7}

/* SKILLS */
.skills-grid{display:flex;flex-wrap:wrap;gap:.5rem}
.skill-tag{padding:.3rem .8rem;background:#eff6ff;color:#2563eb;border-radius:20px;font-size:.8rem;font-weight:500}

/* EXPERIENCES */
.exp-item{padding-left:1.2rem;position:relative;margin-bottom:1.3rem}
.exp-item::before{content:'';position:absolute;left:0;top:6px;width:10px;height:10px;border-radius:50%;background:#2563eb;border:2px solid #fff;box-shadow:0 0 0 2px #2563eb}
.exp-item::after{content:'';position:absolute;left:4px;top:18px;width:2px;bottom:-14px;background:#e2e8f0}
.exp-item:last-child::after{display:none}
.exp-job{font-weight:700;color:#0f172a;font-size:.9rem}
.exp-co{color:#2563eb;font-size:.83rem;font-weight:600;margin-top:.1rem}
.exp-dates{font-size:.73rem;color:#94a3b8;margin-top:.15rem}

/* CERTS */
.cert-item{display:flex;align-items:flex-start;gap:.75rem;padding:.75rem;background:#f8fafc;border-radius:10px;margin-bottom:.6rem}
.cert-icon{width:36px;height:36px;border-radius:10px;background:#fef3c7;color:#d97706;display:flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0}
.cert-name{font-weight:600;font-size:.875rem;color:#0f172a}
.cert-issuer{font-size:.75rem;color:#64748b}

/* FOOTER */
.cv-footer{text-align:center;padding:1.5rem;background:#f8fafc;border-top:1px solid #e2e8f0;font-size:.78rem;color:#94a3b8}
.cv-footer strong{color:#2563eb}

@media(max-width:500px){
  .cv-header{flex-direction:column;align-items:flex-start;text-align:left}
  .cv-name{font-size:1.2rem}
}

/* FLOATING BTN */
.float-btn-wrap { position:fixed; bottom:0; left:0; right:0; padding:1rem; background:linear-gradient(to top, rgba(255,255,255,1) 60%, rgba(255,255,255,0)); text-align:center; z-index:100; }
.btn-vcard { display:inline-flex; align-items:center; gap:.5rem; background:#2563eb; color:#fff; padding:.8rem 1.5rem; border-radius:30px; font-family:'Poppins',sans-serif; font-size:.9rem; font-weight:600; text-decoration:none; box-shadow:0 10px 25px rgba(37,99,235,.3); transition:transform .2s; cursor:pointer; border:none; }
.btn-vcard:active { transform:scale(.96); }
body { padding-bottom: 5rem; } /* Espace pour le bouton */
</style>
</head>
<body>
<div class="page">

  <!-- HEADER -->
  <div class="cv-header">
    <div class="cv-av"><?= e(initials($p['full_name'])) ?></div>
    <div class="cv-hdr-info">
      <div class="cv-name"><?= e($p['full_name']) ?></div>
      <div class="cv-title-sub"><?= e($p['professional_title']) ?></div>
      <span class="cv-badge"><?= e($lvlMap[$p['experience_level']] ?? $p['experience_level']) ?></span>
      <div class="cv-meta-row">
        <?php if($p['location']): ?><div class="cv-meta-item"><i class="fa-solid fa-location-dot"></i><?= e($p['location']) ?></div><?php endif; ?>
        <?php if($p['preferred_industry']): ?><div class="cv-meta-item"><i class="fa-solid fa-industry"></i><?= e($p['preferred_industry']) ?></div><?php endif; ?>
        <div class="cv-meta-item"><i class="fa-solid fa-circle-dot"></i><?= e($avlMap[$p['availability']] ?? $p['availability']) ?></div>
      </div>
    </div>
  </div>

  <div class="cv-body">

    <!-- BIO -->
    <?php if(!empty($p['bio'])): ?>
    <div class="cv-section">
      <div class="cv-sec-title"><i class="fa-solid fa-user"></i> Profil</div>
      <div class="cv-bio"><?= e($p['bio']) ?></div>
    </div>
    <?php endif; ?>

    <!-- SKILLS -->
    <?php if(!empty($skills)): ?>
    <div class="cv-section">
      <div class="cv-sec-title"><i class="fa-solid fa-code"></i> Compétences</div>
      <div class="skills-grid">
        <?php foreach($skills as $sk): ?>
          <span class="skill-tag"><?= e($sk['titre']) ?></span>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- EXPERIENCES -->
    <?php if(!empty($experiences)): ?>
    <div class="cv-section">
      <div class="cv-sec-title"><i class="fa-solid fa-briefcase"></i> Expériences</div>
      <?php foreach($experiences as $exp): ?>
      <div class="exp-item">
        <div class="exp-job"><?= e($exp['titre']) ?></div>
        <div class="exp-co"><?= e($exp['description']) ?></div>
        <div class="exp-dates">
          <?= fmtDate($exp['date_debut']) ?> → <?= $exp['date_fin'] ? fmtDate($exp['date_fin']) : 'En cours' ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- CERTIFICATIONS -->
    <?php if(!empty($certifications)): ?>
    <div class="cv-section">
      <div class="cv-sec-title"><i class="fa-solid fa-award"></i> Certifications</div>
      <?php foreach($certifications as $cert): ?>
      <div class="cert-item">
        <div class="cert-icon"><i class="fa-solid fa-star"></i></div>
        <div>
          <div class="cert-name"><?= e($cert['titre']) ?></div>
          <div class="cert-issuer"><?= e($cert['description']) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div>

  <div class="cv-footer">
    Profil généré via <strong>Digit Advisory</strong> &mdash; Plateforme de Conseil &amp; Expertise
  </div>
</div>

<!-- BOUTON FLOTTANT VCARD -->
<div class="float-btn-wrap">
    <button class="btn-vcard" onclick="downloadVCard()">
        <i class="fa-solid fa-address-book"></i> Enregistrer dans mes contacts
    </button>
</div>

<script>
function downloadVCard() {
    const name = <?= json_encode($p['full_name']) ?>;
    const title = <?= json_encode($p['professional_title']) ?>;
    const skills = <?= json_encode(implode(', ', array_map(function($s){ return $s['titre']; }, $skills))) ?>;
    
    const vcard = `BEGIN:VCARD
VERSION:3.0
FN:${name}
TITLE:${title}
ORG:Digit Advisory
NOTE:Compétences : ${skills}
END:VCARD`;

    const blob = new Blob([vcard], { type: 'text/vcard' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = name.replace(/\s+/g, '_') + '_Contact.vcf';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}
</script>
</body>
</html>
