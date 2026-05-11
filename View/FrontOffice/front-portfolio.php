<?php
/**
 * front-portfolio.php — Digit Advisory
 * Gestion des Portfolios CV (interface utilisateur / front-office)
 *
 * Tables utilisées : `portfolio` + `element_portfolio`
 * Le chargement serveur utilise ElementPortfolioController.
 * Les mutations (create/update/delete) passent par fetch() → PortfolioController (API JSON).
 */
require_once '../../config.php';
require_once '../../Model/Portfolio.php';
require_once '../../Model/ElementPortfolio.php';
require_once '../../Controller/ElementPortfolioController.php';

$userId   = 1; // Remplacer par $_SESSION['user_id']
$db       = config::getConnexion();
$elemCtrl = new ElementPortfolioController();

// Récupération des portfolios (table `portfolio`)
$stmt = $db->prepare('SELECT * FROM portfolio WHERE user_id = :uid ORDER BY created_at DESC');
$stmt->execute([':uid' => $userId]);
$rawPortfolios = $stmt->fetchAll();

// Pour chaque portfolio, on récupère ses éléments via ElementPortfolioController (table `element_portfolio`)
$portfolios = [];
foreach ($rawPortfolios as $pf) {
    $elements = $elemCtrl->listElements((int)$pf['id_portfolio']);
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
    $portfolios[] = $pf;
}

$portfoliosJson = json_encode($portfolios, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$apiUrl         = '../../Controller/PortfolioController.php';

// Détection automatique de l'IP locale (LAN) pour le QR Code
$serverIp = 'localhost';

// 1. Tenter d'utiliser ipconfig (fonctionne sur toute machine Windows)
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    exec('ipconfig', $catch);
    foreach($catch as $line) {
        // Capture n'importe quelle adresse IPv4 (192.168.x.x, 10.x.x.x, 172.x.x.x)
        if(preg_match('/IPv4.*:\s*(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/i', $line, $matches)) {
            $ip = $matches[1];
            // On ignore localhost et les cartes virtuelles VMware/VirtualBox (souvent en .1)
            if ($ip !== '127.0.0.1' && !str_ends_with($ip, '.1')) {
                $serverIp = $ip;
                break;
            }
        }
    }
}

// 2. Fallback pour Mac/Linux ou si la détection a échoué
if ($serverIp === 'localhost' || $serverIp === '127.0.0.1') {
    $serverIp = getHostByName(getHostName());
}

// 3. Si on l'utilise déjà via une IP dans le navigateur, on garde celle-là
if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $_SERVER['HTTP_HOST'] ?? '')) {
    $serverIp = explode(':', $_SERVER['HTTP_HOST'])[0]; // Enlève le port si présent
}
// Si l'utilisateur y accède déjà via une IP, on privilégie celle-ci
if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $_SERVER['HTTP_HOST'] ?? '')) {
    $serverIp = $_SERVER['HTTP_HOST'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Digit Advisory | Mon Portfolio & CV</title>
<meta name="description" content="Gérez vos portfolios professionnels sur Digit Advisory. Compétences, expériences et certifications.">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:#f1f5f9;color:#1e293b}
a{text-decoration:none}

/* ── LAYOUT ── */
.wrap{display:flex;min-height:100vh}

/* ── SIDEBAR ── */
.sb{width:272px;background:#ffffff;display:flex;flex-direction:column;position:fixed;height:100vh;z-index:200;border-right:1px solid #e2e8f0}
.sb-logo{padding:1.5rem;display:flex;align-items:center;gap:.75rem;border-bottom:1px solid #f1f5f9}
.sb-logo span{color:#1e40af;font-family:'Poppins',sans-serif;font-weight:700;font-size:1.4rem}
.sb-logo i{color:#2563eb;font-size:1.4rem}
.sb-nav{flex:1;padding:1.5rem 0;overflow-y:auto}
.sb-link{display:flex;align-items:center;gap:.85rem;padding:.8rem 1.8rem;color:#64748b;font-size:.9rem;font-weight:600;border-left:4px solid transparent;transition:.15s}
.sb-link:hover{background:#f8fafc;color:#3b82f6}
.sb-link.on{background:#eff6ff;color:#2563eb;border-left-color:#2563eb}
.sb-link i{width:20px;text-align:center;font-size:.95rem}
.sb-foot{padding:1.25rem 1.5rem;border-top:1px solid #f1f5f9;display:flex;align-items:center;gap:.85rem}
.sb-av{width:38px;height:38px;border-radius:50%;background:#2563eb;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0}
.sb-name{font-size:.875rem;font-weight:700;color:#1e293b}
.sb-role{font-size:.72rem;color:#64748b}
.sb-out{margin-left:auto;color:#ef4444;font-size:1.1rem;transition:.2s}
.sb-out:hover{color:#b91c1c}

/* ── MAIN ── */
.main{margin-left:272px;padding:2rem;flex:1;min-height:100vh}

/* ── TOPBAR ── */
.topbar{display:flex;justify-content:space-between;align-items:center;background:#fff;padding:1.25rem 1.75rem;border-radius:14px;box-shadow:0 1px 8px rgba(0,0,0,.07);margin-bottom:2rem}
.topbar-ttl{font-family:'Poppins',sans-serif;font-size:1.3rem;font-weight:700;color:#0f172a}
.topbar-sub{font-size:.78rem;color:#94a3b8;margin-top:.15rem}

/* ── BUTTONS ── */
.btn{display:inline-flex;align-items:center;gap:.45rem;padding:.6rem 1.25rem;border:none;border-radius:9px;font-family:'Inter',sans-serif;font-size:.875rem;font-weight:600;cursor:pointer;transition:.18s}
.btn-blue{background:#3b82f6;color:#fff;box-shadow:0 4px 14px rgba(59,130,246,.3)}
.btn-blue:hover{background:#2563eb}
.btn-red{background:#ef4444;color:#fff}.btn-red:hover{background:#dc2626}
.btn-ghost{background:#f8fafc;color:#64748b;border:1px solid #e2e8f0}.btn-ghost:hover{background:#f1f5f9}
.btn-outline{background:#fff;border:1.5px solid #3b82f6;color:#3b82f6}.btn-outline:hover{background:#eff6ff}
.btn-sm{padding:.35rem .8rem;font-size:.8rem}

/* ── PORTFOLIO GRID ── */
.pf-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:1.5rem}

/* ── PORTFOLIO CARD ── */
.pf-card{background:#fff;border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,.07);overflow:hidden;transition:transform .2s,box-shadow .2s}
.pf-card:hover{transform:translateY(-5px);box-shadow:0 10px 30px rgba(0,0,0,.12)}
.pf-card-hdr{background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 100%);padding:1.4rem;display:flex;align-items:center;gap:1rem;position:relative;overflow:hidden}
.pf-card-hdr::before{content:'';position:absolute;right:-20px;top:-20px;width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,.04)}
.pf-card-av{width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#3b82f6,#1d4ed8);display:flex;align-items:center;justify-content:center;font-family:'Poppins',sans-serif;font-weight:800;font-size:1.15rem;color:#fff;border:3px solid rgba(255,255,255,.2);flex-shrink:0}
.pf-card-name{font-family:'Poppins',sans-serif;font-weight:700;font-size:1rem;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pf-card-title{font-size:.75rem;color:rgba(255,255,255,.6);margin-top:.2rem}
.lvl{display:inline-flex;align-items:center;padding:.18rem .55rem;border-radius:20px;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;margin-top:.45rem}
.lvl-junior{background:rgba(16,185,129,.25);color:#6ee7b7}
.lvl-mid{background:rgba(59,130,246,.25);color:#93c5fd}
.lvl-senior{background:rgba(139,92,246,.25);color:#c4b5fd}
.lvl-expert{background:rgba(245,158,11,.25);color:#fcd34d}

.pf-card-body{padding:1.25rem}
.pf-stats{display:flex;gap:.75rem;margin-bottom:1.1rem}
.pf-stat{flex:1;text-align:center;padding:.6rem .5rem;background:#f8fafc;border-radius:9px}
.pf-stat-n{font-family:'Poppins',sans-serif;font-size:1.1rem;font-weight:700;color:#3b82f6}
.pf-stat-l{font-size:.65rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.04em;margin-top:.1rem}

.pf-tags{display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:1rem;min-height:28px}
.pf-tag{padding:.22rem .6rem;border-radius:20px;font-size:.72rem;font-weight:500;background:#eff6ff;color:#2563eb}

.pf-meta{display:flex;align-items:center;gap:.5rem;font-size:.75rem;color:#64748b;padding-top:.9rem;border-top:1px solid #f1f5f9;flex-wrap:wrap}
.avail-pill{margin-left:auto;padding:.2rem .6rem;border-radius:20px;font-size:.7rem;font-weight:600}
.avail-immediate{background:rgba(16,185,129,.1);color:#059669}
.avail-one_month{background:rgba(245,158,11,.1);color:#d97706}
.avail-three_months{background:rgba(234,179,8,.1);color:#ca8a04}
.avail-unavailable{background:rgba(239,68,68,.1);color:#dc2626}

.pf-card-foot{display:flex;gap:.6rem;padding:1rem 1.25rem;border-top:1px solid #f8fafc;background:#fafafa}

/* ── EMPTY STATE ── */
.empty-box{text-align:center;padding:5rem 2rem;background:#fff;border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,.06)}
.empty-ic{width:90px;height:90px;border-radius:50%;background:#eff6ff;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;font-size:2.2rem;color:#3b82f6}
.empty-box h2{font-family:'Poppins',sans-serif;font-size:1.35rem;color:#0f172a;margin-bottom:.65rem}
.empty-box p{color:#94a3b8;font-size:.9rem;max-width:360px;margin:0 auto 1.75rem;line-height:1.6}

/* ── MODAL OVERLAY ── */
.ov{position:fixed;inset:0;background:rgba(15,23,42,.75);z-index:1000;display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:opacity .28s;backdrop-filter:blur(3px)}
.ov.show{opacity:1;pointer-events:all}
.ov-box{background:#fff;border-radius:20px;width:92%;max-width:820px;max-height:91vh;display:flex;flex-direction:column;overflow:hidden;transform:translateY(24px);transition:transform .3s;box-shadow:0 20px 60px rgba(0,0,0,.2)}
.ov.show .ov-box{transform:translateY(0)}

/* ── MODAL HEAD ── */
.ov-head{display:flex;justify-content:space-between;align-items:center;padding:1.25rem 1.75rem;border-bottom:1px solid #f1f5f9;flex-shrink:0}
.ov-head-ttl{font-family:'Poppins',sans-serif;font-size:1rem;font-weight:700;color:#0f172a}
.ov-close{background:transparent;border:none;cursor:pointer;color:#94a3b8;font-size:1.1rem;padding:.3rem;border-radius:6px;transition:.15s}
.ov-close:hover{background:#fee2e2;color:#ef4444}

/* ── STEP BAR ── */
.step-bar{display:flex;align-items:center;padding:1rem 1.75rem;background:#f8fafc;border-bottom:1px solid #e2e8f0;flex-shrink:0;gap:0}
.stp{display:flex;align-items:center;gap:.5rem}
.stp-dot{width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;flex-shrink:0;transition:.2s}
.stp-dot.inactive{background:#e2e8f0;color:#94a3b8}
.stp-dot.active{background:#3b82f6;color:#fff;box-shadow:0 3px 10px rgba(59,130,246,.4)}
.stp-dot.done{background:#22c55e;color:#fff}
.stp-lbl{font-size:.78rem;font-weight:600;color:#94a3b8}
.stp-lbl.active{color:#3b82f6}
.stp-lbl.done{color:#22c55e}
.stp-line{flex:1;height:2px;background:#e2e8f0;margin:0 .6rem;min-width:20px;transition:background .3s}
.stp-line.done{background:#22c55e}

/* ── MODAL BODY ── */
.ov-body{padding:1.5rem;overflow-y:auto;flex:1;background:#f8fafc}
.panel{display:none}
.panel.on{display:block}

/* ── FORM ELEMENTS ── */
.section-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:1.4rem;margin-bottom:1rem}
.section-ttl{font-size:.9rem;font-weight:700;color:#0f172a;margin-bottom:1.1rem;display:flex;align-items:center;gap:.5rem}
.section-ttl i{color:#3b82f6}
.frow{display:grid;gap:1rem;margin-bottom:1rem}
.frow-2{grid-template-columns:1fr 1fr}
.frow-1{grid-template-columns:1fr}
@media(max-width:580px){.frow-2{grid-template-columns:1fr}}
.fg{display:flex;flex-direction:column;gap:.35rem}
.fg label{font-size:.75rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em}
.fg label .req{color:#ef4444}
.fi,.fs,.fta{width:100%;padding:.65rem .9rem;border:1.5px solid #e2e8f0;border-radius:9px;font-family:'Inter',sans-serif;font-size:.875rem;color:#0f172a;background:#fcfcfd;transition:.15s}
.fi:focus,.fs:focus,.fta:focus{outline:none;border-color:#3b82f6;background:#fff;box-shadow:0 0 0 3px rgba(59,130,246,.1)}
.fi.err,.fs.err{border-color:#ef4444}
.ferr{font-size:.72rem;color:#ef4444;display:none;margin-top:.2rem}
.fta{resize:vertical;min-height:80px}

/* ── TAG ZONE ── */
.tag-zone{display:flex;flex-wrap:wrap;gap:.4rem;padding:.5rem;border:1.5px solid #e2e8f0;border-radius:9px;background:#fcfcfd;min-height:45px;cursor:text;transition:.15s}
.tag-zone:focus-within{border-color:#3b82f6;background:#fff;box-shadow:0 0 0 3px rgba(59,130,246,.1)}
.tag-chip{display:inline-flex;align-items:center;gap:.3rem;background:#eff6ff;color:#2563eb;padding:.22rem .6rem;border-radius:20px;font-size:.78rem;font-weight:500}
.tag-chip button{background:none;border:none;cursor:pointer;color:inherit;font-size:.7rem;padding:0;line-height:1}
.tag-inp{border:none;outline:none;background:transparent;font-family:'Inter',sans-serif;font-size:.875rem;color:#0f172a;flex:1;min-width:120px;padding:.2rem .3rem}

/* ── DYN BLOCKS ── */
.dyn-blk{background:#f8fafc;border:1px dashed #cbd5e1;border-radius:10px;padding:1rem;margin-bottom:.75rem;position:relative}
.dyn-blk-x{position:absolute;right:.7rem;top:.7rem;background:none;border:none;color:#94a3b8;cursor:pointer;font-size:.9rem;padding:.2rem;border-radius:5px;transition:.15s}
.dyn-blk-x:hover{color:#ef4444;background:#fee2e2}
.btn-add-dyn{width:100%;padding:.65rem;border:1.5px dashed #cbd5e1;border-radius:10px;background:transparent;color:#64748b;cursor:pointer;font-family:'Inter',sans-serif;font-size:.85rem;font-weight:500;display:flex;align-items:center;justify-content:center;gap:.5rem;transition:.15s}
.btn-add-dyn:hover{border-color:#3b82f6;color:#3b82f6;background:#eff6ff}

/* ── MODAL FOOT ── */
.ov-foot{display:flex;justify-content:space-between;align-items:center;padding:1rem 1.75rem;border-top:1px solid #f1f5f9;background:#fff;flex-shrink:0}

/* ── CV PREVIEW MODAL ── */
.cv-wrap{display:grid;grid-template-columns:240px 1fr;min-height:500px}
.cv-left{background:linear-gradient(165deg,#0f172a,#1e3a5f);padding:2rem 1.5rem;color:#fff}
.cv-right{padding:2rem;background:#fff}
.cv-av{width:80px;height:80px;border-radius:50%;background:#2563eb;display:flex;align-items:center;justify-content:center;font-family:'Poppins',sans-serif;font-size:2rem;font-weight:800;color:#fff;margin:0 auto 1.2rem;border:3px solid rgba(255,255,255,.3)}
.cv-sec{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#60a5fa;margin:1.2rem 0 .6rem;padding-bottom:.35rem;border-bottom:1px solid rgba(255,255,255,.1)}
.cv-sec.dk{color:#64748b;border-bottom-color:#e2e8f0}
.cv-ci{display:flex;align-items:center;gap:.5rem;font-size:.78rem;color:rgba(255,255,255,.7);margin-bottom:.4rem}
.cv-ci i{color:#60a5fa;width:13px}
.cv-skill-bar{margin-bottom:.6rem}
.cv-skill-nm{font-size:.75rem;color:rgba(255,255,255,.8);margin-bottom:.2rem}
.cv-skill-track{height:4px;background:rgba(255,255,255,.1);border-radius:2px;overflow:hidden}
.cv-skill-fill{height:100%;border-radius:2px;background:linear-gradient(90deg,#3b82f6,#60a5fa)}
.cv-bio-box{background:#f0f9ff;border-left:4px solid #3b82f6;padding:.9rem 1rem;border-radius:0 8px 8px 0;font-size:.84rem;color:#1e293b;line-height:1.65;margin-bottom:1.25rem}
.cv-tl-item{padding-left:1.3rem;position:relative;margin-bottom:1.2rem}
.cv-tl-item::before{content:'';position:absolute;left:0;top:5px;width:9px;height:9px;border-radius:50%;background:#3b82f6;border:2px solid #fff;box-shadow:0 0 0 2px #3b82f6}
.cv-tl-item::after{content:'';position:absolute;left:3.5px;top:16px;width:2px;bottom:-14px;background:#e2e8f0}
.cv-tl-item:last-child::after{display:none}
.cv-tl-job{font-weight:700;color:#0f172a;font-size:.9rem}
.cv-tl-co{color:#2563eb;font-size:.82rem;font-weight:600}
.cv-tl-dates{font-size:.72rem;color:#94a3b8;margin:.15rem 0 .3rem}

/* ── CONFIRM BOX ── */
.cnf-box{background:#fff;border-radius:18px;padding:2.25rem;width:90%;max-width:400px;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.18)}
.cnf-icon{font-size:2.8rem;margin-bottom:.9rem}
.cnf-box h3{font-family:'Poppins',sans-serif;font-size:1.15rem;color:#0f172a;margin-bottom:.5rem}
.cnf-box p{color:#64748b;font-size:.85rem;margin-bottom:1.5rem;line-height:1.55}

/* ── TOAST ── */
#toast-anchor{position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;display:flex;flex-direction:column;gap:.55rem}
.t{display:flex;align-items:center;gap:.75rem;padding:.85rem 1.1rem;background:#fff;border-radius:12px;box-shadow:0 8px 25px rgba(0,0,0,.12);max-width:300px;animation:tslide .3s ease}
.t.ok{border-left:4px solid #22c55e}
.t.err{border-left:4px solid #ef4444}
.t-ic.ok{color:#22c55e}
.t-ic.err{color:#ef4444}
.t-msg{font-size:.855rem;font-weight:500;color:#1e293b}
@keyframes tslide{from{opacity:0;transform:translateX(60px)}to{opacity:1;transform:translateX(0)}}
.spin{display:inline-block;width:16px;height:16px;border:2px solid rgba(255,255,255,.5);border-top-color:#fff;border-radius:50%;animation:rot .7s linear infinite}
@keyframes rot{to{transform:rotate(360deg)}}
/* ═══════════════════════════════════════════════════════
   AI DASHBOARD
═══════════════════════════════════════════════════════ */
.ai-dashboard { margin-top:2rem; display:grid; grid-template-columns:320px 1fr; gap:1.5rem; }
@media(max-width:900px){ .ai-dashboard { grid-template-columns:1fr; } }
.ai-score-main { background:linear-gradient(135deg,#0f172a 0%,#164e63 100%); border-radius:22px; padding:2rem; text-align:center; color:#fff; position:relative; overflow:hidden; }
.ai-score-main::before { content:''; position:absolute; right:-30px; top:-30px; width:120px; height:120px; border-radius:50%; background:rgba(6,182,212,.1); }
.gauge-wrap { width:170px; height:170px; margin:0 auto 1rem; position:relative; }
.gauge-svg { width:100%; height:100%; transform:rotate(-90deg); }
.gauge-bg { fill:none; stroke:rgba(255,255,255,.1); stroke-width:10; }
.gauge-fill { fill:none; stroke-width:10; stroke-linecap:round; transition:stroke-dashoffset 1.5s ease; }
.gauge-score { position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; }
.gauge-num { font-family:'Poppins',sans-serif; font-size:2.6rem; font-weight:800; line-height:1; }
.gauge-label { font-size:.72rem; color:rgba(255,255,255,.55); margin-top:.25rem; }
.ai-score-title { font-family:'Poppins',sans-serif; font-size:1rem; font-weight:700; margin-bottom:.3rem; }
.ai-score-desc { font-size:.78rem; color:rgba(255,255,255,.5); line-height:1.5; }

.ai-right-panel { display:flex; flex-direction:column; gap:1.5rem; }
.ai-detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
@media(max-width:600px){ .ai-detail-grid { grid-template-columns:1fr; } }
.ai-detail-card { background:#fff; border-radius:16px; padding:1.1rem; box-shadow:0 2px 12px rgba(0,0,0,.05); transition:transform .2s; }
.ai-detail-card:hover { transform:translateY(-3px); box-shadow:0 10px 30px rgba(0,0,0,.1); }
.ai-detail-hdr { display:flex; align-items:center; gap:.55rem; margin-bottom:.7rem; }
.ai-detail-icon { width:34px; height:34px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:.85rem; flex-shrink:0; }
.ai-detail-icon.sk { background:rgba(6,182,212,.12); color:#06b6d4; }
.ai-detail-icon.xp { background:rgba(124,58,237,.12); color:#7c3aed; }
.ai-detail-icon.ct { background:rgba(245,158,11,.12); color:#f59e0b; }
.ai-detail-icon.co { background:rgba(16,185,129,.12); color:#10b981; }
.ai-detail-name { font-size:.75rem; font-weight:600; color:#64748b; }
.ai-detail-val { font-family:'Poppins',sans-serif; font-size:1.2rem; font-weight:700; color:#0f172a; }
.ai-prog-track { height:5px; background:#f1f5f9; border-radius:3px; overflow:hidden; margin-top:.45rem; }
.ai-prog-fill { height:100%; border-radius:3px; transition:width 1.2s ease; }
.ai-prog-fill.c1 { background:linear-gradient(90deg,#06b6d4,#0284c7); }
.ai-prog-fill.c2 { background:linear-gradient(90deg,#7c3aed,#6d28d9); }
.ai-prog-fill.c3 { background:linear-gradient(90deg,#f59e0b,#d97706); }
.ai-prog-fill.c4 { background:linear-gradient(90deg,#10b981,#059669); }

.ai-card { background:#fff; border-radius:16px; padding:1.3rem; box-shadow:0 2px 12px rgba(0,0,0,.05); }
.ai-card h3 { font-family:'Poppins',sans-serif; font-size:.92rem; font-weight:700; color:#0f172a; margin-bottom:.9rem; display:flex; align-items:center; gap:.5rem; }
.market-row { display:flex; align-items:center; gap:.75rem; margin-bottom:.7rem; }
.market-lbl { width:95px; font-size:.76rem; font-weight:600; color:#64748b; flex-shrink:0; }
.market-track { flex:1; height:7px; background:#f1f5f9; border-radius:4px; overflow:hidden; }
.market-fill { height:100%; border-radius:4px; transition:width 1.5s ease; }
.market-pct { width:38px; text-align:right; font-size:.76rem; font-weight:700; color:#0f172a; }

.reco-item { display:flex; align-items:flex-start; gap:.7rem; padding:.7rem .8rem; background:#f8fafc; border-radius:10px; margin-bottom:.5rem; border-left:3px solid #06b6d4; transition:background .2s; }
.reco-item:hover { background:#f0f9ff; }
.reco-ic { width:26px; height:26px; border-radius:8px; background:rgba(6,182,212,.1); color:#06b6d4; display:flex; align-items:center; justify-content:center; font-size:.7rem; flex-shrink:0; }
.reco-txt { font-size:.8rem; color:#374151; line-height:1.4; }
.reco-pri { font-size:.62rem; font-weight:700; text-transform:uppercase; padding:.12rem .35rem; border-radius:6px; margin-top:.25rem; display:inline-block; }
.reco-pri.high { background:rgba(239,68,68,.1); color:#ef4444; }
.reco-pri.med { background:rgba(245,158,11,.1); color:#f59e0b; }
.reco-pri.low { background:rgba(16,185,129,.1); color:#10b981; }

.ai-actions { display:flex; flex-wrap:wrap; gap:.7rem; margin-top:1.5rem; }
.ai-abtn { display:inline-flex; align-items:center; gap:.45rem; padding:.65rem 1.1rem; border:2px solid #e2e8f0; border-radius:12px; background:#fff; font-family:'Inter',sans-serif; font-size:.8rem; font-weight:600; color:#0f172a; cursor:pointer; transition:transform .2s, border-color .2s; }
.ai-abtn:hover { border-color:#06b6d4; color:#06b6d4; background:rgba(6,182,212,.04); transform:translateY(-2px); box-shadow:0 4px 12px rgba(0,0,0,.05); }
.ai-abtn i { font-size:.9rem; color:#06b6d4; }

.ai-stats { margin-top:2rem; display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; }
@media(max-width:700px){ .ai-stats { grid-template-columns:1fr; } }
.radar-wrap { display:flex; justify-content:center; padding:.5rem 0; }

/* ═══════════════════════════════════════════════════════
   AI MODALS (shared)
═══════════════════════════════════════════════════════ */
.ai-mbox { background:#fff; border-radius:22px; width:92%; max-width:720px; max-height:90vh; display:flex; flex-direction:column; overflow:hidden; transform:scale(.95) translateY(18px); transition:transform .3s; box-shadow:0 20px 60px rgba(0,0,0,.2); }
.ov.show .ai-mbox { transform:scale(1) translateY(0); }
.ai-mhdr { padding:1.2rem 1.7rem; background:linear-gradient(135deg,#0f172a,#164e63); display:flex; justify-content:space-between; align-items:center; flex-shrink:0; }
.ai-mhdr h3 { font-family:'Poppins',sans-serif; font-size:1.05rem; font-weight:700; color:#fff; }
.ai-mhdr p { font-size:.75rem; color:rgba(255,255,255,.5); margin-top:.12rem; }
.ai-mbody { padding:1.6rem; overflow-y:auto; flex:1; }

.gap-sec { margin-bottom:1.4rem; }
.gap-sec-ttl { font-family:'Poppins',sans-serif; font-size:.88rem; font-weight:700; color:#0f172a; margin-bottom:.75rem; display:flex; align-items:center; gap:.45rem; }
.gap-row { display:flex; align-items:center; gap:.7rem; margin-bottom:.55rem; }
.gap-name { width:120px; font-size:.76rem; font-weight:500; color:#374151; flex-shrink:0; }
.gap-track { flex:1; height:7px; background:#f1f5f9; border-radius:4px; position:relative; overflow:hidden; }
.gap-have { height:100%; border-radius:4px; background:#06b6d4; position:absolute; left:0; top:0; transition:width 1s ease; }
.gap-need { height:100%; border-radius:4px; background:rgba(239,68,68,.15); width:100%; }
.gap-st { width:22px; text-align:center; font-size:.8rem; flex-shrink:0; }
.gap-miss { margin-top:.8rem; display:flex; flex-wrap:wrap; gap:.35rem; }
.gap-miss-tag { padding:.22rem .55rem; border-radius:20px; font-size:.7rem; font-weight:600; background:rgba(239,68,68,.08); color:#ef4444; border:1px solid rgba(239,68,68,.15); }
.gap-have-tag { padding:.22rem .55rem; border-radius:20px; font-size:.7rem; font-weight:600; background:rgba(16,185,129,.08); color:#10b981; border:1px solid rgba(16,185,129,.15); }

.career-node { display:flex; gap:1rem; margin-bottom:1.3rem; position:relative; }
.career-dot-col { display:flex; flex-direction:column; align-items:center; flex-shrink:0; width:38px; }
.career-dot { width:38px; height:38px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.8rem; font-weight:700; transition:all .2s; }
.career-dot.past { background:#10b981; color:#fff; box-shadow:0 3px 10px rgba(16,185,129,.3); }
.career-dot.now { background:#06b6d4; color:#fff; box-shadow:0 3px 10px rgba(6,182,212,.4); animation:pulseDot 2s infinite; }
.career-dot.fut { background:#e2e8f0; color:#64748b; }
@keyframes pulseDot { 0%,100%{box-shadow:0 3px 10px rgba(6,182,212,.4)} 50%{box-shadow:0 3px 20px rgba(6,182,212,.6)} }
.career-line { flex:1; width:2px; background:#e2e8f0; margin:4px 0; }
.career-line.done { background:#10b981; }
.career-body { flex:1; background:#f8fafc; border-radius:14px; padding:.9rem 1.1rem; border:1px solid #e2e8f0; }
.career-body.now { background:rgba(6,182,212,.05); border-color:rgba(6,182,212,.2); }
.career-role { font-family:'Poppins',sans-serif; font-size:.88rem; font-weight:700; color:#0f172a; }
.career-yrs { font-size:.72rem; color:#64748b; margin:.15rem 0 .35rem; }
.career-sal { font-size:.76rem; font-weight:600; color:#10b981; }
.career-tips { margin-top:.4rem; display:flex; flex-wrap:wrap; gap:.3rem; }
.career-tip { padding:.18rem .45rem; border-radius:20px; font-size:.65rem; font-weight:500; background:rgba(6,182,212,.08); color:#0891b2; }

.sal-display { text-align:center; padding:1.5rem 0; }
.sal-amount { font-family:'Poppins',sans-serif; font-size:2.8rem; font-weight:800; background:linear-gradient(135deg,#06b6d4,#7c3aed); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
.sal-range { display:flex; justify-content:center; gap:2rem; margin-top:.8rem; }
.sal-bound { text-align:center; }
.sal-bound-lbl { font-size:.65rem; font-weight:700; text-transform:uppercase; color:#64748b; letter-spacing:.05em; }
.sal-bound-val { font-family:'Poppins',sans-serif; font-size:1.15rem; font-weight:700; color:#0f172a; }
.sal-factors { display:grid; grid-template-columns:1fr 1fr; gap:.7rem; margin-top:1.3rem; }
@media(max-width:500px){ .sal-factors { grid-template-columns:1fr; } }
.sal-factor { display:flex; align-items:center; gap:.55rem; padding:.6rem .8rem; background:#f8fafc; border-radius:10px; }
.sal-fic { width:28px; height:28px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:.7rem; flex-shrink:0; }
.sal-flbl { font-size:.76rem; color:#374151; }
.sal-fval { margin-left:auto; font-size:.72rem; font-weight:700; }
.sal-fval.pos { color:#10b981; }
.sal-fval.neu { color:#64748b; }

.tpl-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:.85rem; margin:1rem 0; }
@media(max-width:550px){ .tpl-grid { grid-template-columns:1fr; } }
.tpl-opt { border:2px solid #e2e8f0; border-radius:14px; padding:.9rem; text-align:center; cursor:pointer; transition:all .2s; }
.tpl-opt:hover { border-color:#06b6d4; transform:translateY(-3px); box-shadow:0 4px 12px rgba(0,0,0,.05); }
.tpl-opt.sel { border-color:#06b6d4; background:rgba(6,182,212,.04); box-shadow:0 0 0 3px rgba(6,182,212,.15); }
.tpl-icon { width:48px; height:48px; border-radius:12px; margin:0 auto .6rem; display:flex; align-items:center; justify-content:center; font-size:1.3rem; }
.tpl-name { font-size:.82rem; font-weight:700; color:#0f172a; margin-bottom:.2rem; }
.tpl-desc { font-size:.7rem; color:#64748b; }

.ai-gen-btn { display:inline-flex; align-items:center; gap:.4rem; padding:.4rem .8rem; border:1.5px dashed #06b6d4; border-radius:8px; background:rgba(6,182,212,.04); color:#06b6d4; font-size:.75rem; font-weight:600; cursor:pointer; transition:all .2s; margin-top:.3rem; font-family:'Inter',sans-serif; }
.ai-gen-btn:hover { background:rgba(6,182,212,.1); border-style:solid; }

@keyframes fadeInUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
.fade-in { animation:fadeInUp .5s ease forwards; }
/* ── TAB NAVIGATION (PREVIEW) ── */
.prev-tabs { display:flex; gap:0; background:#f1f5f9; border-radius:10px; padding:4px; }
.prev-tab { background:transparent; border:none; padding:.45rem 1rem; font-family:'Inter',sans-serif; font-size:.85rem; font-weight:600; color:#64748b; border-radius:8px; cursor:pointer; transition:.2s; display:flex; align-items:center; gap:.4rem; }
.prev-tab:hover { color:#0f172a; }
.prev-tab.active { background:#fff; color:#3b82f6; box-shadow:0 2px 8px rgba(0,0,0,.05); }
.prev-tab-content { display:none; }
.prev-tab-content.active { display:block; animation:fadeInUp .3s ease forwards; }

/* ── CV TEMPLATES ── */
.cv-wrap { transition:all .3s ease; }

/* Classic (Default) */
.tpl-classic .cv-left { background:#0f172a; color:#fff; }
.tpl-classic .cv-right { background:#fff; }
.tpl-classic .cv-av { background:#2563eb; }

/* Modern */
.tpl-modern { display:flex; flex-direction:column; background:#fff; }
.tpl-modern .cv-left { background:#f8fafc; color:#0f172a; display:flex; align-items:center; gap:1.5rem; padding:1.5rem 2rem; border-bottom:1px solid #e2e8f0; }
.tpl-modern .cv-left .cv-av { margin:0; width:70px; height:70px; font-size:1.6rem; background:#06b6d4; }
.tpl-modern .cv-left > div { text-align:left !important; }
.tpl-modern .cv-left .cv-sec { display:none; } /* Hide skills/certs in header */
.tpl-modern .cv-right { padding:2rem; }
.tpl-modern .cv-right .cv-r-section:first-child { display:none; } /* Hide duplicated name */
.tpl-modern .cv-ci { color:#475569; display:inline-flex; margin-right:1rem; }
.tpl-modern .cv-ci i { color:#06b6d4; }
.tpl-modern .cv-sec.dk { color:#06b6d4; border-bottom-color:#06b6d4; }

/* Creative */
.tpl-creative .cv-left { background:#7c3aed; color:#fff; border-right:4px solid #f59e0b; }
.tpl-creative .cv-right { background:#fff; }
.tpl-creative .cv-av { background:#f59e0b; border-color:#fff; color:#0f172a; }
.tpl-creative .cv-bio-box { background:#f3f0ff; border-left-color:#7c3aed; }
.tpl-creative .cv-tl-item::before { background:#f59e0b; box-shadow:0 0 0 2px #f59e0b; }
.tpl-creative .cv-sec.dk { color:#7c3aed; font-size:.8rem; }
</style>
</head>
<body>
<div class="wrap">

<!-- ═══ SIDEBAR ═══════════════════════════════════════════════ -->
<aside class="sb">
    <div class="sb-logo">
        <i class="fa-solid fa-chart-pie"></i>
        <span>Digit Advisory</span>
    </div>
    <nav class="sb-nav">
        <a href="front-entreprise-dashboard.php" class="sb-link"><i class="fa-solid fa-house"></i> Vue d'ensemble</a>
        <a href="front-utilisateur.php"          class="sb-link"><i class="fa-solid fa-building"></i> Profil Entreprise</a>
        <a href="front-quiz.php"                 class="sb-link"><i class="fa-solid fa-list-check"></i> Questionnaire</a>
        <a href="front-portfolio.php"            class="sb-link on"><i class="fa-solid fa-folder-open"></i> Mon Portfolio / CV</a>
        <a href="front-offres.php"               class="sb-link"><i class="fa-solid fa-briefcase"></i> Mes Offres</a>
        <a href="front-certification.php"        class="sb-link"><i class="fa-solid fa-award"></i> Certifications ISO</a>
        <a href="front-messagerie.php"           class="sb-link"><i class="fa-solid fa-comments"></i> Messagerie</a>
    </nav>
    <div class="sb-foot">
        <div class="sb-av">TC</div>
        <div>
            <div class="sb-name">TechCorp SAS</div>
            <div class="sb-role">Compte Entreprise</div>
        </div>
        <a href="login.php" class="sb-out"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
    </div>
</aside>

<!-- ═══ MAIN ════════════════════════════════════════════════ -->
<main class="main">

    <!-- Topbar -->
    <div class="topbar">
        <div>
            <div class="topbar-ttl"><i class="fa-solid fa-id-card" style="color:#3b82f6;margin-right:.5rem"></i>Mes Portfolios & CV</div>
            <div class="topbar-sub">Créez et gérez vos profils professionnels</div>
        </div>
        <button class="btn btn-blue" id="btn-create-main">
            <i class="fa-solid fa-plus"></i> Nouveau Portfolio
        </button>
    </div>

    <!-- Portfolio grid zone -->
    <div id="pf-host"></div>

</main>
</div>

<!-- ═══ TOAST HOST ════════════════════════════════════════════ -->
<div id="toast-anchor"></div>

<!-- ═══ MODAL BUILDER (2 STEPS) ══════════════════════════════ -->
<div class="ov" id="ov-builder">
  <div class="ov-box">
    <div class="ov-head">
        <span class="ov-head-ttl" id="builder-title">Créer mon Portfolio CV</span>
        <div style="display:flex;gap:.75rem;align-items:center;">
            <button class="btn btn-ghost" style="padding:.38rem .9rem;font-size:.8rem;" id="btn-template">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Modèle
            </button>
            <button class="ov-close" id="btn-close-builder"><i class="fa-solid fa-xmark"></i></button>
        </div>
    </div>

    <div class="step-bar">
        <div class="stp">
            <div class="stp-dot active" id="dot-1">1</div>
            <div class="stp-lbl active" id="lbl-1">Informations</div>
        </div>
        <div class="stp-line" id="line-1"></div>
        <div class="stp">
            <div class="stp-dot inactive" id="dot-2">2</div>
            <div class="stp-lbl" id="lbl-2">Compétences & Expériences</div>
        </div>
    </div>

    <div class="ov-body">

      <!-- PANEL 1 -->
      <div class="panel on" id="panel-1">
        <div class="section-card">
            <div class="section-ttl"><i class="fa-solid fa-address-card"></i> Informations Personnelles</div>
            <div class="frow frow-2">
                <div class="fg">
                    <label>Nom complet <span class="req">*</span></label>
                    <input class="fi" id="f-name" placeholder="Ex : Alice Martin">
                    <span class="ferr" id="e-name">Ce champ est obligatoire</span>
                </div>
                <div class="fg">
                    <label>Titre professionnel <span class="req">*</span></label>
                    <input class="fi" id="f-title" placeholder="Ex : Consultant IT Senior">
                    <span class="ferr" id="e-title">Ce champ est obligatoire</span>
                </div>
                <div class="fg">
                    <label>Niveau d'expérience <span class="req">*</span></label>
                    <select class="fs" id="f-level">
                        <option value="">— Sélectionner —</option>
                        <option value="junior">🟢 Junior (0–3 ans)</option>
                        <option value="mid">🔵 Mid-level (3–6 ans)</option>
                        <option value="senior">🟣 Senior (6–12 ans)</option>
                        <option value="expert">🌟 Expert (12+ ans)</option>
                    </select>
                    <span class="ferr" id="e-level">Ce champ est obligatoire</span>
                </div>
                <div class="fg">
                    <label>Disponibilité <span class="req">*</span></label>
                    <select class="fs" id="f-avail">
                        <option value="">— Sélectionner —</option>
                        <option value="immediate">✅ Immédiate</option>
                        <option value="one_month">🟡 1 Mois</option>
                        <option value="three_months">🟠 3 Mois</option>
                        <option value="unavailable">🔴 Indisponible</option>
                    </select>
                    <span class="ferr" id="e-avail">Ce champ est obligatoire</span>
                </div>
                <div class="fg">
                    <label>Secteur préféré</label>
                    <select class="fs" id="f-industry">
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
                <div class="fg">
                    <label>Localisation</label>
                    <input class="fi" id="f-location" placeholder="Ex : Paris, France">
                </div>
            </div>
            <div class="fg">
                <label>Bio / Résumé professionnel</label>
                <textarea class="fta" id="f-bio" placeholder="Décrivez votre parcours, vos valeurs, vos spécialités…"></textarea>
                <button type="button" class="ai-gen-btn" id="btn-gen-bio"><i class="fa-solid fa-wand-magic-sparkles"></i> Générer avec l'IA</button>
            </div>
        </div>
      </div><!-- end panel-1 -->

      <!-- PANEL 2 -->
      <div class="panel" id="panel-2">

        <!-- Skills -->
        <div class="section-card">
            <div class="section-ttl"><i class="fa-solid fa-code"></i> Compétences (Tags)</div>
            <div class="tag-zone" id="tz-skills">
                <input type="text" class="tag-inp" id="inp-skill" placeholder="Appuyez sur Entrée : PHP, Docker, Scrum…">
            </div>
            <p style="font-size:.72rem;color:#94a3b8;margin-top:.5rem"><i class="fa-solid fa-circle-info"></i> Entrée ou virgule pour valider</p>
        </div>

        <!-- Experiences -->
        <div class="section-card">
            <div class="section-ttl"><i class="fa-solid fa-briefcase"></i> Expériences Professionnelles</div>
            <div id="exp-host"></div>
            <button type="button" class="btn-add-dyn" id="btn-add-exp">
                <i class="fa-solid fa-plus"></i> Ajouter une expérience
            </button>
        </div>

        <!-- Certifications -->
        <div class="section-card">
            <div class="section-ttl"><i class="fa-solid fa-award"></i> Certifications</div>
            <div id="cert-host"></div>
            <button type="button" class="btn-add-dyn" id="btn-add-cert">
                <i class="fa-solid fa-plus"></i> Ajouter une certification
            </button>
        </div>

      </div><!-- end panel-2 -->
    </div><!-- end ov-body -->

    <div class="ov-foot">
        <button class="btn btn-ghost" id="btn-prev" style="display:none">
            <i class="fa-solid fa-arrow-left"></i> Précédent
        </button>
        <div style="flex:1"></div>
        <button class="btn btn-ghost" id="btn-cancel-builder" style="margin-right:.6rem">Annuler</button>
        <button class="btn btn-blue" id="btn-next">Suivant <i class="fa-solid fa-arrow-right"></i></button>
        <button class="btn btn-blue" id="btn-save" style="display:none">
            <i class="fa-solid fa-floppy-disk"></i> Enregistrer le CV
        </button>
    </div>
  </div>
</div>

<!-- ═══ MODAL CV PREVIEW ════════════════════════════════════ -->
<div class="ov" id="ov-preview">
  <div class="ov-box" style="max-width:1000px; max-height:95vh;">
    <div class="ov-head">
        <div class="prev-tabs">
            <button class="prev-tab active" id="tab-btn-cv" onclick="switchPrevTab('cv')"><i class="fa-solid fa-eye"></i> Aperçu du CV</button>
            <button class="prev-tab" id="tab-btn-ai" onclick="switchPrevTab('ai')"><i class="fa-solid fa-robot"></i> Analyse IA</button>
        </div>
        <div style="display:flex;gap:.6rem;align-items:center;">
            <div id="cv-actions" style="display:flex;gap:.6rem;">
                <button class="btn btn-outline btn-sm" id="btn-tpl-select"><i class="fa-solid fa-palette"></i> Template</button>
                <button class="btn btn-blue btn-sm" id="btn-export-pdf"><i class="fa-solid fa-file-pdf"></i> Export PDF</button>
            </div>
            <button class="btn btn-ghost btn-sm" id="btn-edit-from-prev"><i class="fa-solid fa-pen"></i> Modifier</button>
            <button class="ov-close" id="btn-close-preview"><i class="fa-solid fa-xmark"></i></button>
        </div>
    </div>
    <div class="ov-body" style="padding:0;background:#f8fafc">
        <!-- TAB CV -->
        <div class="prev-tab-content active" id="tab-cv">
            <div class="cv-wrap tpl-classic" id="cv-doc-content">
                <div class="cv-left" id="cv-left-col"></div>
                <div class="cv-right" id="cv-right-col"></div>
            </div>
        </div>
        <!-- TAB AI -->
        <div class="prev-tab-content" id="tab-ai" style="padding:1.5rem">
            <div id="ai-dashboard-host"></div>
        </div>
    </div>
  </div>
</div>

<!-- ═══ MODAL CONFIRM DELETE ════════════════════════════════ -->
<div class="ov" id="ov-delete">
  <div class="cnf-box">
    <div class="cnf-icon">🗑️</div>
    <h3>Supprimer ce portfolio ?</h3>
    <p>Cette action est irréversible. Le portfolio et tous ses éléments (compétences, expériences, certifications) seront définitivement supprimés.</p>
    <div style="display:flex;gap:.75rem;justify-content:center">
        <button class="btn btn-ghost" id="btn-cancel-del">Annuler</button>
        <button class="btn btn-red" id="btn-confirm-del"><i class="fa-solid fa-trash"></i> Supprimer</button>
    </div>
  </div>
</div>

<!-- ═══ MODAL SKILL GAP ═════════════════════════════════════ -->
<div class="ov" id="ov-skillgap">
  <div class="ai-mbox">
    <div class="ai-mhdr">
        <div><h3><i class="fa-solid fa-magnifying-glass-chart"></i> Skill Gap Analysis</h3><p>Analyse de vos compétences par rapport aux attentes du marché</p></div>
        <button class="ov-close" style="color:#fff" onclick="hideOv('ov-skillgap')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="ai-mbody" id="skillgap-body"></div>
  </div>
</div>

<!-- ═══ MODAL CAREER PATH ═══════════════════════════════════ -->
<div class="ov" id="ov-career">
  <div class="ai-mbox">
    <div class="ai-mhdr">
        <div><h3><i class="fa-solid fa-route"></i> Trajectoire de Carrière</h3><p>Prédiction de votre évolution et conseils stratégiques</p></div>
        <button class="ov-close" style="color:#fff" onclick="hideOv('ov-career')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="ai-mbody" id="career-body"></div>
  </div>
</div>

<!-- ═══ MODAL SALARY ESTIMATOR ══════════════════════════════ -->
<div class="ov" id="ov-salary">
  <div class="ai-mbox" style="max-width:550px">
    <div class="ai-mhdr">
        <div><h3><i class="fa-solid fa-coins"></i> Estimateur de Salaire</h3><p>Calcul basé sur votre niveau, vos certifications et le marché</p></div>
        <button class="ov-close" style="color:#fff" onclick="hideOv('ov-salary')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="ai-mbody" id="salary-body"></div>
  </div>
</div>

<!-- ═══ MODAL TEMPLATE SELECTOR ═════════════════════════════ -->
<div class="ov" id="ov-template">
  <div class="ai-mbox" style="max-width:650px">
    <div class="ai-mhdr">
        <div><h3><i class="fa-solid fa-palette"></i> Style du CV</h3><p>Choisissez l'apparence visuelle de votre export PDF</p></div>
        <button class="ov-close" style="color:#fff" onclick="hideOv('ov-template')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="ai-mbody">
        <div class="tpl-grid" id="tpl-grid"></div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
<script src="../../js/ai-engines.js?v=<?=time()?>"></script>
<script src="../../js/ai-ui.js?v=<?=time()?>"></script>
<script>

/* ─── GLOBALS ─────────────────────────────────────────────── */
var API           = '<?= $apiUrl ?>';
var SERVER_IP     = '<?= $serverIp ?>';
var portfolios    = <?= $portfoliosJson ?>;
var editingId     = null;
var deleteId      = null;
var skills        = [];
var step          = 1;
var currentTemplate = 'classic';
var previewId     = null;

const AVAIL_LBL = {immediate:'Disponible',one_month:'1 Mois',three_months:'3 Mois',unavailable:'Indisponible'};
const LVL_LBL   = {junior:'Junior',mid:'Mid-level',senior:'Senior',expert:'Expert'};

/* ─── UTILS ─────────────────────────────────────────────── */
function byId(id){ return document.getElementById(id); }
function esc(s){ if(s==null) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function initials(n){ if(!n) return '?'; return n.trim().split(/\s+/).slice(0,2).map(w=>w[0]).join('').toUpperCase(); }
function fmtDate(d){ if(!d) return ''; try{ return new Date(d).toLocaleDateString('fr-FR',{year:'numeric',month:'short'}); } catch(e){ return d; } }
function showOv(id){ document.getElementById(id).classList.add('show'); document.body.style.overflow='hidden'; }
function hideOv(id){ document.getElementById(id).classList.remove('show'); document.body.style.overflow=''; }
window.showOv = showOv;
window.hideOv = hideOv;

/* ─── TOAST ─────────────────────────────────────────────── */
function toast(type, msg){
    const host = byId('toast-anchor');
    const t    = document.createElement('div');
    t.className = 't ' + type;
    t.innerHTML = '<span class="t-ic '+type+'"><i class="fa-solid '+(type==='ok'?'fa-circle-check':'fa-circle-exclamation')+'"></i></span><span class="t-msg">'+msg+'</span>';
    host.appendChild(t);
    setTimeout(()=>{ t.style.opacity='0'; t.style.transition='opacity .3s'; setTimeout(()=>t.remove(),350); }, 3800);
}

/* ─── RENDER GRID ────────────────────────────────────────── */
function renderGrid(){
    const host = byId('pf-host');
    if(!host) return;
    if(portfolios.length === 0){
        host.innerHTML = '<div class="empty-box">'
                        +'<div class="empty-ic"><i class="fa-solid fa-id-card"></i></div>'
                        +'<h2>Aucun Portfolio créé</h2>'
                        +'<p>Commencez à builder votre CV professionnel pour attirer les meilleures missions.</p>'
                        +'<button class="btn btn-blue" id="btn-create-empty"><i class="fa-solid fa-plus"></i> Créer mon premier Portfolio</button>'
                        +'</div>';
        const bce = byId('btn-create-empty');
        if(bce) bce.addEventListener('click', openBuilder);
        return;
    }
    const AVCLR  = {immediate:'avail-immediate',one_month:'avail-one_month',three_months:'avail-three_months',unavailable:'avail-unavailable'};
    const LVLCLS = {junior:'lvl-junior',mid:'lvl-mid',senior:'lvl-senior',expert:'lvl-expert'};
    host.innerHTML = '<div class="pf-grid">'+portfolios.map(p=>{
        const sk    = Array.isArray(p.skills)         ? p.skills         : [];
        const exps  = Array.isArray(p.experiences)    ? p.experiences    : [];
        const certs = Array.isArray(p.certifications) ? p.certifications : [];
        const tagHtml = sk.slice(0,5).map(s=>'<span class="pf-tag">'+esc(s.skill_name)+'</span>').join('')
                       +(sk.length>5?'<span class="pf-tag" style="background:#f1f5f9;color:#94a3b8">+'+(sk.length-5)+'</span>':'');
        return '<div class="pf-card" data-id="'+p.id_portfolio+'">'
              +'<div class="pf-card-hdr">'
              +'<div class="pf-card-av">'+esc(initials(p.full_name))+'</div>'
              +'<div style="flex:1;min-width:0">'
              +'<div class="pf-card-name">'+esc(p.full_name)+'</div>'
              +'<div class="pf-card-title">'+esc(p.professional_title)+'</div>'
              +'<div><span class="lvl '+(LVLCLS[p.experience_level]||'')+'">'+esc(LVL_LBL[p.experience_level]||p.experience_level)+'</span></div>'
              +'</div></div>'
              +'<div class="pf-card-body">'
              +'<div class="pf-stats">'
              +'<div class="pf-stat"><div class="pf-stat-n">'+sk.length+'</div><div class="pf-stat-l">Compétences</div></div>'
              +'<div class="pf-stat"><div class="pf-stat-n">'+exps.length+'</div><div class="pf-stat-l">Expériences</div></div>'
              +'<div class="pf-stat"><div class="pf-stat-n">'+certs.length+'</div><div class="pf-stat-l">Certifs.</div></div>'
              +'</div>'
              +'<div class="pf-tags">'+tagHtml+'</div>'
              +'<div class="pf-meta">'
              +(p.location?'<span><i class="fa-solid fa-location-dot"></i> '+esc(p.location)+'</span>':'')
              +'<span class="avail-pill '+(AVCLR[p.availability]||'')+'">'+esc(AVAIL_LBL[p.availability]||p.availability)+'</span>'
              +'</div>'
              +'</div>'
              +'<div class="pf-card-foot">'
              +'<button class="btn btn-outline btn-sm btn-view" data-id="'+p.id_portfolio+'"><i class="fa-solid fa-eye"></i> Voir CV</button>'
              +'<button class="btn btn-ghost btn-sm btn-edit" data-id="'+p.id_portfolio+'" style="margin-left:auto"><i class="fa-solid fa-pen"></i> Modifier</button>'
              +'<button class="btn btn-red btn-sm btn-del" data-id="'+p.id_portfolio+'"><i class="fa-solid fa-trash"></i></button>'
              +'</div>'
              +'</div>';
    }).join('')+'</div>';

    host.querySelectorAll('.btn-view').forEach(b=>b.addEventListener('click',()=>openPreview(+b.dataset.id)));
    host.querySelectorAll('.btn-edit').forEach(b=>b.addEventListener('click',()=>openEdit(+b.dataset.id)));
    host.querySelectorAll('.btn-del' ).forEach(b=>b.addEventListener('click',()=>askDelete(+b.dataset.id)));
    renderAIDashboard();
}

/* ─── BUILDER OPEN/CLOSE ───────────────────────────────── */
function openBuilder(){
    editingId = null;
    resetForm();
    byId('builder-title').textContent = 'Créer mon Portfolio CV';
    goStep(1);
    showOv('ov-builder');
}

function openEdit(id){
    const p = portfolios.find(x=>x.id_portfolio == id);
    if(!p) return;
    editingId = id;
    resetForm();
    byId('builder-title').textContent = 'Modifier le Portfolio';

    byId('f-name').value     = p.full_name           || '';
    byId('f-title').value    = p.professional_title  || '';
    byId('f-level').value    = p.experience_level    || '';
    byId('f-avail').value    = p.availability        || '';
    byId('f-industry').value = p.preferred_industry  || '';
    byId('f-location').value = p.location            || '';
    byId('f-bio').value      = p.bio                 || '';

    skills = (p.skills || []).map(s=>({skill_name:s.skill_name, niveau:s.niveau||'intermediate'}));
    renderSkillTags();

    (p.experiences    || []).forEach(e=>addExpBlock(e));
    (p.certifications || []).forEach(c=>addCertBlock(c));

    goStep(1);
    showOv('ov-builder');
}

function closeBuilder(){ hideOv('ov-builder'); }

/* ─── STEP NAVIGATION ────────────────────────────────────── */
function goStep(s){
    step = s;
    byId('panel-1').className = 'panel' + (s===1?' on':'');
    byId('panel-2').className = 'panel' + (s===2?' on':'');

    byId('dot-1').className = 'stp-dot ' + (s===1?'active':s>1?'done':'inactive');
    byId('lbl-1').className = 'stp-lbl ' + (s===1?'active':s>1?'done':'');
    byId('dot-2').className = 'stp-dot ' + (s===2?'active':'inactive');
    byId('lbl-2').className = 'stp-lbl ' + (s===2?'active':'');
    byId('line-1').className = 'stp-line' + (s>1?' done':'');

    byId('btn-prev').style.display = s===1?'none':'inline-flex';
    byId('btn-next').style.display = s===1?'inline-flex':'none';
    byId('btn-save').style.display = s===2?'inline-flex':'none';
}

function nextStep(){
    let ok = true;
    function vf(fid, eid){ const v=byId(fid).value.trim(); const ok2=v!==''; byId(eid).style.display=ok2?'none':'block'; byId(fid).classList.toggle('err',!ok2); if(!ok2) ok=false; }
    vf('f-name','e-name'); vf('f-title','e-title'); vf('f-level','e-level'); vf('f-avail','e-avail');
    if(ok) goStep(2);
}

/* ─── SKILL TAGS ─────────────────────────────────────────── */
function renderSkillTags(){
    const tz  = byId('tz-skills');
    const inp = byId('inp-skill');
    Array.from(tz.children).forEach(c=>{ if(c!==inp) c.remove(); });
    skills.forEach((sk, idx)=>{
        const chip = document.createElement('div');
        chip.className = 'tag-chip';
        chip.innerHTML = esc(sk.skill_name)+' <button type="button" data-idx="'+idx+'">✕</button>';
        chip.querySelector('button').addEventListener('click', ()=>{ skills.splice(idx,1); renderSkillTags(); });
        tz.insertBefore(chip, inp);
    });
}

function handleSkillInput(e){
    const inp = byId('inp-skill');
    if(e.key==='Enter'||e.key===','){
        e.preventDefault();
        const val = inp.value.trim().replace(/,/g,'');
        if(val && !skills.find(s=>s.skill_name.toLowerCase()===val.toLowerCase())){
            skills.push({skill_name:val, niveau:'intermediate'});
            renderSkillTags();
        }
        inp.value='';
    }
}

/* ─── DYNAMIC EXP/CERT BLOCKS ────────────────────────────── */
function addExpBlock(obj){
    obj = obj||{};
    const div = document.createElement('div');
    div.className = 'dyn-blk';
    div.innerHTML = '<button type="button" class="dyn-blk-x"><i class="fa-solid fa-xmark"></i></button>'
                   +'<div class="frow frow-2">'
                   +'<div class="fg"><label>Titre du poste</label><input class="fi e-job" value="'+esc(obj.job_title||'')+'"></div>'
                   +'<div class="fg"><label>Entreprise</label><input class="fi e-co" value="'+esc(obj.company||'')+'"></div>'
                   +'<div class="fg"><label>Date début</label><input type="date" class="fi e-sd" value="'+esc(obj.start_date||'')+'"></div>'
                   +'<div class="fg"><label>Date fin <small style="color:#94a3b8">(vide = En cours)</small></label><input type="date" class="fi e-ed" value="'+esc(obj.end_date||'')+'"></div>'
                   +'</div>';
    div.querySelector('.dyn-blk-x').addEventListener('click',()=>div.remove());
    byId('exp-host').appendChild(div);
}

function addCertBlock(obj){
    obj = obj||{};
    const div = document.createElement('div');
    div.className = 'dyn-blk';
    div.innerHTML = '<button type="button" class="dyn-blk-x"><i class="fa-solid fa-xmark"></i></button>'
                   +'<div class="frow frow-2">'
                   +'<div class="fg"><label>Nom de la certification</label><input class="fi c-nm" value="'+esc(obj.cert_name||'')+'" placeholder="Ex: ISO 27001, PMP…"></div>'
                   +'<div class="fg"><label>Émetteur / Organisme</label><input class="fi c-is" value="'+esc(obj.issuer||'')+'" placeholder="Ex: Bureau Veritas, PMI…"></div>'
                   +'</div>';
    div.querySelector('.dyn-blk-x').addEventListener('click',()=>div.remove());
    byId('cert-host').appendChild(div);
}

function gatherExps(){
    return Array.from(byId('exp-host').querySelectorAll('.dyn-blk')).map(b=>({
        job_title:  b.querySelector('.e-job').value.trim(),
        company:    b.querySelector('.e-co').value.trim(),
        start_date: b.querySelector('.e-sd').value,
        end_date:   b.querySelector('.e-ed').value,
    })).filter(e=>e.job_title);
}

function gatherCerts(){
    return Array.from(byId('cert-host').querySelectorAll('.dyn-blk')).map(b=>({
        cert_name: b.querySelector('.c-nm').value.trim(),
        issuer:    b.querySelector('.c-is').value.trim(),
    })).filter(c=>c.cert_name);
}

/* ─── FORM RESET ──────────────────────────────────────────── */
function resetForm(){
    ['f-name','f-title','f-bio','f-location'].forEach(id=>{ byId(id).value=''; });
    ['f-level','f-avail','f-industry'].forEach(id=>{ byId(id).value=''; });
    ['e-name','e-title','e-level','e-avail'].forEach(id=>{ byId(id).style.display='none'; });
    ['f-name','f-title','f-level','f-avail'].forEach(id=>{ byId(id).classList.remove('err'); });
    skills = [];
    renderSkillTags();
    byId('exp-host').innerHTML  = '';
    byId('cert-host').innerHTML = '';
}

/* ─── TEMPLATE PREFILL ────────────────────────────────────── */
function prefillTemplate(){
    byId('f-name').value     = 'Jean Dupont';
    byId('f-title').value    = 'Architecte Logiciel Senior';
    byId('f-level').value    = 'expert';
    byId('f-avail').value    = 'immediate';
    byId('f-industry').value = 'IT';
    byId('f-location').value = 'Paris, France';
    byId('f-bio').value      = 'Expert en architecture logicielle avec 12+ années d\'expérience dans des environnements grands comptes.';
    byId('exp-host').innerHTML='';
    addExpBlock({job_title:'Tech Lead',company:'Google France',start_date:'2020-01-01',end_date:'2024-06-01'});
    addExpBlock({job_title:'Senior Developer',company:'BNP Paribas',start_date:'2016-03-01',end_date:'2019-12-31'});
    skills=[{skill_name:'PHP',niveau:'expert'},{skill_name:'React',niveau:'senior'},{skill_name:'Docker',niveau:'intermediate'},{skill_name:'MySQL',niveau:'expert'}];
    renderSkillTags();
    byId('cert-host').innerHTML='';
    addCertBlock({cert_name:'AWS Solutions Architect',issuer:'Amazon Web Services'});
    addCertBlock({cert_name:'PMP',issuer:'PMI'});
    toast('ok','Modèle appliqué — personnalisez selon votre profil !');
}

/* ─── SAVE (via PortfolioController API) ─────────────────── */
async function saveCV(){
    const btn = byId('btn-save');
    btn.disabled = true;
    btn.innerHTML = '<span class="spin"></span> Sauvegarde…';

    const payload = {
        id_portfolio:      editingId,
        full_name:         byId('f-name').value.trim(),
        professional_title:byId('f-title').value.trim(),
        experience_level:  byId('f-level').value || 'junior',
        availability:      byId('f-avail').value || 'immediate',
        preferred_industry:byId('f-industry').value,
        location:          byId('f-location').value.trim(),
        bio:               byId('f-bio').value.trim(),
        skills:            skills,
        experiences:       gatherExps(),
        certifications:    gatherCerts(),
    };

    const action = editingId ? 'update' : 'create';
    try {
        const res  = await fetch(API+'?action='+action, {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
        const data = await res.json();
        if(data.success){
            if(editingId){
                const idx = portfolios.findIndex(p=>p.id_portfolio == editingId);
                if(idx>-1) portfolios[idx]=data.portfolio;
            } else {
                portfolios.unshift(data.portfolio);
            }
            renderGrid();
            closeBuilder();
            toast('ok', editingId ? 'Portfolio mis à jour !' : 'Portfolio créé avec succès !');
        } else {
            toast('err', data.error||'Erreur lors de la sauvegarde.');
        }
    } catch(ex){
        toast('err','Erreur réseau. Vérifiez votre connexion.');
        console.error(ex);
    }
    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Enregistrer le CV';
}

/* ─── DELETE (CASCADE supprime element_portfolio) ────────── */
function askDelete(id){ deleteId=id; showOv('ov-delete'); }

async function confirmDelete(){
    const btn = byId('btn-confirm-del');
    btn.disabled=true;
    btn.innerHTML='<span class="spin"></span>';
    try{
        const res  = await fetch(API+'?action=delete',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id_portfolio:deleteId})});
        const data = await res.json();
        if(data.success){
            portfolios = portfolios.filter(p=>p.id_portfolio != deleteId);
            renderGrid();
            hideOv('ov-delete');
            toast('ok','Portfolio supprimé (compétences & expériences supprimées).');
        } else { toast('err',data.error||'Erreur de suppression.'); }
    } catch(ex){ toast('err','Erreur réseau.'); }
    btn.disabled=false;
    btn.innerHTML='<i class="fa-solid fa-trash"></i> Supprimer';
}

/* ─── CV PREVIEW ─────────────────────────────────────────── */
function switchPrevTab(tab) {
    byId('tab-btn-cv').classList.toggle('active', tab==='cv');
    byId('tab-btn-ai').classList.toggle('active', tab==='ai');
    byId('tab-cv').classList.toggle('active', tab==='cv');
    byId('tab-ai').classList.toggle('active', tab==='ai');
    
    // Hide or show CV actions (Export/Template) based on tab
    if(byId('cv-actions')) {
        byId('cv-actions').style.display = tab === 'cv' ? 'flex' : 'none';
    }
}

function openPreview(id){
    const p = portfolios.find(x=>x.id_portfolio == id);
    if(!p) return;
    previewId = id;
    const sk    = Array.isArray(p.skills)?p.skills:[];
    const exps  = Array.isArray(p.experiences)?p.experiences:[];
    const certs = Array.isArray(p.certifications)?p.certifications:[];

    const skillBars = sk.slice(0,8).map(s=>'<div class="cv-skill-bar"><div class="cv-skill-nm">'+esc(s.skill_name)+'</div><div class="cv-skill-track"><div class="cv-skill-fill" style="width:75%"></div></div></div>').join('');
    const certList  = certs.map(c=>'<div style="display:flex;gap:.5rem;align-items:start;margin-bottom:.45rem"><i class="fa-solid fa-star" style="color:#fbbf24;font-size:.75rem;margin-top:.1rem"></i><div><div style="font-size:.78rem;color:rgba(255,255,255,.85)">'+esc(c.cert_name)+'</div><div style="font-size:.68rem;color:rgba(255,255,255,.4)">'+esc(c.issuer||'')+'</div></div></div>').join('');

    byId('cv-left-col').innerHTML =
        '<div class="cv-av">'+esc(initials(p.full_name))+'</div>'
       +'<div class="cv-r-section" style="text-align:center;font-family:Poppins,sans-serif;font-size:1.05rem;font-weight:700;color:#fff">'+esc(p.full_name)+'</div>'
       +'<div style="text-align:center;font-size:.78rem;color:rgba(255,255,255,.6);margin:.25rem 0 .8rem">'+esc(p.professional_title)+'</div>'
       +(p.location?'<div class="cv-ci"><i class="fa-solid fa-location-dot"></i>'+esc(p.location)+'</div>':'')
       +(p.preferred_industry?'<div class="cv-ci"><i class="fa-solid fa-industry"></i>'+esc(p.preferred_industry)+'</div>':'')
       +(sk.length?'<div class="cv-sec">Compétences</div>'+skillBars:'')
       +(certs.length?'<div class="cv-sec">Certifications</div>'+certList:'')
       +'<div style="margin-top:2rem;text-align:center"><div style="font-size:.65rem;color:rgba(255,255,255,.5);margin-bottom:.5rem;text-transform:uppercase;letter-spacing:1px">Scanner le Profil</div><canvas id="qr-canvas" style="display:block;margin:0 auto;border-radius:8px;padding:6px;background:#ffffff;border:1px solid rgba(255,255,255,.1)"></canvas></div>';

    const tl = exps.map(e=>'<div class="cv-tl-item"><div class="cv-tl-job">'+esc(e.job_title)+'</div><div class="cv-tl-co">'+esc(e.company)+'</div><div class="cv-tl-dates">'+fmtDate(e.start_date)+' → '+(e.end_date?fmtDate(e.end_date):'En cours')+'</div></div>').join('');

    byId('cv-right-col').innerHTML =
        '<div class="cv-r-section" style="margin-bottom:1.5rem"><div style="font-family:Poppins,sans-serif;font-size:1.6rem;font-weight:800;color:#0f172a">'+esc(p.full_name)+'</div><div style="font-size:1rem;color:#3b82f6;font-weight:600">'+esc(p.professional_title)+'</div></div>'
       +(p.bio?'<div style="margin-bottom:1.5rem"><div class="cv-sec dk">Profil</div><div class="cv-bio-box">'+esc(p.bio)+'</div></div>':'')
       +(tl?'<div><div class="cv-sec dk">Expériences</div>'+tl+'</div>':'');

    // Make sure we start on the CV tab
    switchPrevTab('cv');
    
    // Apply the current template CSS class
    byId('cv-doc-content').className = 'cv-wrap tpl-' + currentTemplate;
    
    // Generate QR Code pointing to the dedicated mobile CV page
    setTimeout(() => {
        const qrEl = byId('qr-canvas');
        if(qrEl && typeof QRious !== 'undefined') {
            // Point to view-cv.php — a simple page designed for mobile
            let baseUrl = window.location.origin;
            // Si l'utilisateur est sur localhost, on utilise l'IP locale pour que le téléphone (sur le même Wi-Fi) puisse y accéder
            if (baseUrl.includes('localhost') || baseUrl.includes('127.0.0.1')) {
                baseUrl = window.location.protocol + '//' + SERVER_IP;
            }
            const qrUrl = baseUrl + '/Esprit-PW-2A24-2526-DigitAdvisory/View/FrontOffice/view-cv.php?id=' + p.id_portfolio;

            new QRious({
                element: qrEl,
                value: qrUrl,
                size: 200,
                background: '#ffffff',
                foreground: '#000000',
                level: 'M'
            });
        }
    }, 100);

    // Render the AI dashboard specifically for THIS portfolio
    if(typeof renderAIDashboard === 'function') {
        renderAIDashboard(p);
    }

    showOv('ov-preview');
}
window.showOv = showOv;
window.hideOv = hideOv;
window.switchPrevTab = switchPrevTab;
window.openPreview = openPreview;

/* ─── WIRING ─────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', ()=>{
    renderGrid();
    wireAIFeatures();
    // Note: renderAIDashboard is called per-portfolio from openPreview()

    // Auto-open preview if ID is in URL (for QR Code scanning)
    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.has('id')) {
        setTimeout(() => openPreview(urlParams.get('id')), 100);
    }

    byId('btn-create-main').addEventListener('click', openBuilder);
    byId('btn-close-builder').addEventListener('click', closeBuilder);
    byId('btn-cancel-builder').addEventListener('click', closeBuilder);
    byId('btn-template').addEventListener('click', prefillTemplate);
    byId('btn-next').addEventListener('click', nextStep);
    byId('btn-prev').addEventListener('click', ()=>goStep(1));
    byId('btn-save').addEventListener('click', saveCV);
    byId('btn-cancel-del').addEventListener('click', ()=>hideOv('ov-delete'));
    byId('btn-confirm-del').addEventListener('click', confirmDelete);
    byId('btn-close-preview').addEventListener('click', ()=>hideOv('ov-preview'));
    byId('btn-edit-from-prev').addEventListener('click', ()=>{ hideOv('ov-preview'); if(previewId) openEdit(previewId); });

    byId('inp-skill').addEventListener('keydown', handleSkillInput);
    byId('btn-add-exp').addEventListener('click', ()=>addExpBlock());
    byId('btn-add-cert').addEventListener('click', ()=>addCertBlock());

    // Fermer les overlays en cliquant à l'extérieur
    document.querySelectorAll('.ov').forEach(ov=>{
        ov.addEventListener('click', e=>{ if(e.target===ov) hideOv(ov.id); });
    });
});
</script>
</body>
</html>
