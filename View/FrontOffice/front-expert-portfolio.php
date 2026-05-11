<?php
/**
 * View · front-expert-portfolio.php
 * Digit Advisory — Expert Portfolio & CV Builder
 *
 * Initial data loaded server-side; all mutations via JS fetch()
 * pointing to Controller/ExpertPortfolioController.php
 */
require_once '../../config.php';
require_once '../../Model/ExpertPortfolioModel.php';

$db    = config::getConnexion();
$model = new ExpertPortfolioModel($db);
$userId = 1; // Mock — replace with $_SESSION['user_id'] in production

$portfolios = $model->getAllByUser($userId);
foreach ($portfolios as &$p) {
    $p['skills']         = $model->getSkills($p['id_portfolio']);
    $p['certifications'] = $model->getCertifications($p['id_portfolio']);
    $p['experiences']    = $model->getExperiences($p['id_portfolio']);
}
unset($p);

$portfoliosJson = json_encode($portfolios, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digit Advisory | Portfolio &amp; CV Expert</title>
    <meta name="description" content="Gérez votre profil CV expert sur Digit Advisory. Créez, éditez et partagez votre portfolio professionnel.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        /* ═══════════════════════════════════════════════════════
           ROOT & LAYOUT
        ═══════════════════════════════════════════════════════ */
        :root {
            --ex-navy:   #0f172a;
            --ex-cyan:   #06b6d4;
            --ex-cyan2:  #0891b2;
            --ex-cyan3:  #cffafe;
            --ex-purple: #7c3aed;
            --ex-gold:   #f59e0b;
            --ex-green:  #10b981;
            --ex-red:    #ef4444;
            --ex-gray:   #64748b;
            --ex-light:  #f0f9ff;
            --card-r:    18px;
            --shadow-sm: 0 2px 8px rgba(0,0,0,.06);
            --shadow-md: 0 6px 24px rgba(0,0,0,.10);
            --shadow-lg: 0 16px 48px rgba(0,0,0,.16);
            --modal-bg:  rgba(15,23,42,.72);
            --trans:     all .22s ease;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: var(--ex-light); color: #1e293b; }
        .da-container { display: flex; min-height: 100vh; }

        /* ═══════════════════════════════════════════════════════
           SIDEBAR
        ═══════════════════════════════════════════════════════ */
        .da-sidebar {
            width: 268px; background: var(--ex-navy);
            display: flex; flex-direction: column;
            position: fixed; height: 100vh; z-index: 200;
            box-shadow: 4px 0 20px rgba(0,0,0,.18);
        }
        .da-sb-header {
            padding: 1.6rem 1.4rem;
            border-bottom: 1px solid rgba(255,255,255,.08);
            display: flex; align-items: center; gap: .75rem;
        }
        .da-logo-icon {
            width: 36px; height: 36px; border-radius: 10px;
            background: linear-gradient(135deg, var(--ex-cyan), var(--ex-cyan2));
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: .95rem; flex-shrink: 0;
        }
        .da-logo-text {
            font-family: 'Poppins', sans-serif; font-weight: 700;
            font-size: 1.15rem; color: #fff; text-decoration: none;
        }
        .da-sb-menu { flex: 1; padding: .75rem 0; overflow-y: auto; }
        .da-menu-item {
            display: flex; align-items: center; gap: .9rem;
            padding: .8rem 1.4rem;
            color: rgba(255,255,255,.55); font-weight: 500; font-size: .875rem;
            text-decoration: none; border-left: 3px solid transparent;
            transition: var(--trans); cursor: pointer;
        }
        .da-menu-item i { width: 18px; text-align: center; font-size: 1rem; }
        .da-menu-item:hover { background: rgba(255,255,255,.05); color: #fff; }
        .da-menu-item.active {
            background: rgba(6,182,212,.12); color: var(--ex-cyan);
            border-left-color: var(--ex-cyan);
        }
        .da-sb-footer {
            padding: 1rem 1.4rem;
            border-top: 1px solid rgba(255,255,255,.08);
            display: flex; align-items: center; gap: .85rem;
        }
        .da-sb-avatar {
            width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
            background: linear-gradient(135deg, var(--ex-cyan), var(--ex-cyan2));
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 700; font-size: .85rem;
        }
        .da-sb-name { font-size: .875rem; font-weight: 600; color: #fff; }
        .da-sb-role { font-size: .72rem; color: rgba(255,255,255,.4); }
        .da-logout  { margin-left: auto; color: rgba(255,255,255,.35); font-size: 1.05rem; text-decoration: none; transition: var(--trans); }
        .da-logout:hover { color: var(--ex-red); }

        /* ═══════════════════════════════════════════════════════
           MAIN CONTENT
        ═══════════════════════════════════════════════════════ */
        .da-main { flex: 1; margin-left: 268px; padding: 2rem; min-height: 100vh; }

        /* Top bar */
        .da-topbar {
            display: flex; justify-content: space-between; align-items: center;
            background: #fff; padding: 1.25rem 1.75rem;
            border-radius: var(--card-r); box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
        }
        .da-topbar h1 {
            font-family: 'Poppins', sans-serif; font-size: 1.4rem;
            font-weight: 700; color: var(--ex-navy);
        }
        .da-topbar p { font-size: .8rem; color: var(--ex-gray); margin-top: .2rem; }

        /* ═══════════════════════════════════════════════════════
           BUTTONS
        ═══════════════════════════════════════════════════════ */
        .btn {
            display: inline-flex; align-items: center; gap: .45rem;
            padding: .6rem 1.2rem; border: none; border-radius: 10px;
            font-family: 'Inter', sans-serif; font-weight: 600; font-size: .875rem;
            cursor: pointer; text-decoration: none; transition: var(--trans);
            white-space: nowrap;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--ex-cyan), var(--ex-cyan2));
            color: #fff; box-shadow: 0 4px 14px rgba(6,182,212,.35);
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(6,182,212,.45); }
        .btn-outline { background: #fff; color: var(--ex-cyan); border: 2px solid var(--ex-cyan); }
        .btn-outline:hover { background: var(--ex-cyan3); }
        .btn-ghost { background: #f8fafc; color: var(--ex-gray); border: 1px solid #e2e8f0; }
        .btn-ghost:hover { background: #f1f5f9; }
        .btn-danger { background: var(--ex-red); color: #fff; }
        .btn-danger:hover { background: #dc2626; }
        .btn-sm { padding: .38rem .85rem; font-size: .8rem; }

        /* ═══════════════════════════════════════════════════════
           PORTFOLIO CARD GRID
        ═══════════════════════════════════════════════════════ */
        .portfolio-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(370px, 1fr));
            gap: 1.5rem;
        }
        .pf-card {
            background: #fff; border-radius: 20px; overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: transform .22s, box-shadow .22s;
        }
        .pf-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-lg); }

        /* Card header strip */
        .pf-card-hdr {
            background: linear-gradient(135deg, var(--ex-navy) 0%, #164e63 100%);
            padding: 1.5rem; display: flex; align-items: center; gap: 1rem;
            position: relative; overflow: hidden;
        }
        .pf-card-hdr::before {
            content: ''; position: absolute; right: -20px; top: -20px;
            width: 90px; height: 90px; border-radius: 50%;
            background: rgba(255,255,255,.05);
        }
        .pf-card-hdr::after {
            content: ''; position: absolute; right: 10px; bottom: -50px;
            width: 120px; height: 120px; border-radius: 50%;
            background: rgba(6,182,212,.08);
        }
        .pf-avatar {
            width: 60px; height: 60px; border-radius: 50%; flex-shrink: 0;
            background: linear-gradient(135deg, var(--ex-cyan), #0284c7);
            display: flex; align-items: center; justify-content: center;
            font-family: 'Poppins', sans-serif; font-weight: 800;
            font-size: 1.3rem; color: #fff;
            border: 3px solid rgba(255,255,255,.25);
            box-shadow: 0 4px 14px rgba(0,0,0,.2);
        }
        .pf-identity { flex: 1; min-width: 0; }
        .pf-name {
            font-family: 'Poppins', sans-serif; font-weight: 700;
            font-size: 1.05rem; color: #fff; margin-bottom: .2rem;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .pf-title { font-size: .8rem; color: rgba(255,255,255,.65); margin-bottom: .5rem; }
        .lvl-badge {
            display: inline-flex; align-items: center; gap: .3rem;
            padding: .22rem .55rem; border-radius: 20px;
            font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em;
        }
        .lvl-junior  { background: rgba(16,185,129,.25);  color: #6ee7b7; }
        .lvl-mid     { background: rgba(6,182,212,.25);   color: #67e8f9; }
        .lvl-senior  { background: rgba(139,92,246,.25);  color: #c4b5fd; }
        .lvl-expert  { background: rgba(245,158,11,.25);  color: #fcd34d; }

        /* Card body */
        .pf-card-body { padding: 1.4rem; }
        .pf-stats { display: flex; gap: .75rem; margin-bottom: 1.2rem; }
        .pf-stat { flex: 1; text-align: center; padding: .65rem .5rem; background: #f8fafc; border-radius: 10px; }
        .pf-stat-n { font-family: 'Poppins', sans-serif; font-size: 1.2rem; font-weight: 700; color: var(--ex-cyan); }
        .pf-stat-l { font-size: .65rem; color: var(--ex-gray); text-transform: uppercase; letter-spacing: .05em; margin-top: .1rem; }

        /* Skill tags */
        .pf-tags { display: flex; flex-wrap: wrap; gap: .45rem; margin-bottom: 1.2rem; }
        .pf-tag {
            padding: .28rem .65rem; border-radius: 20px;
            font-size: .72rem; font-weight: 500;
        }
        .pf-tag.tech    { background: rgba(6,182,212,.1);   color: var(--ex-cyan2); }
        .pf-tag.exp     { background: rgba(124,58,237,.1);  color: var(--ex-purple); }
        .pf-tag.plus    { background: #f1f5f9;              color: var(--ex-gray); }

        .pf-meta {
            display: flex; align-items: center; gap: .75rem; flex-wrap: wrap;
            padding-top: .9rem; border-top: 1px solid #f1f5f9;
            font-size: .75rem; color: var(--ex-gray);
        }
        .pf-meta-item { display: flex; align-items: center; gap: .3rem; }
        .avail-pill {
            margin-left: auto; padding: .22rem .6rem; border-radius: 12px;
            font-size: .7rem; font-weight: 600;
        }
        .avail-immediate    { background: rgba(16,185,129,.1);  color: #059669; }
        .avail-one_month    { background: rgba(245,158,11,.1);  color: #d97706; }
        .avail-three_months { background: rgba(234,179,8,.1);   color: #ca8a04; }
        .avail-unavailable  { background: rgba(239,68,68,.1);   color: #dc2626; }

        .pf-card-actions {
            display: flex; gap: .6rem; padding: 1rem 1.4rem;
            border-top: 1px solid #f8fafc; background: #fafafa;
        }

        /* ═══════════════════════════════════════════════════════
           EMPTY STATE
        ═══════════════════════════════════════════════════════ */
        .empty-state {
            text-align: center; padding: 5rem 2rem;
            background: #fff; border-radius: 20px; box-shadow: var(--shadow-sm);
        }
        .empty-icon {
            width: 96px; height: 96px; border-radius: 50%; margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, var(--ex-cyan3), #e0f2fe);
            display: flex; align-items: center; justify-content: center;
            font-size: 2.5rem; color: var(--ex-cyan);
        }
        .empty-state h2 {
            font-family: 'Poppins', sans-serif; font-size: 1.45rem;
            color: var(--ex-navy); margin-bottom: .75rem;
        }
        .empty-state p { color: var(--ex-gray); max-width: 400px; margin: 0 auto 2rem; font-size: .9rem; line-height: 1.6; }

        /* ═══════════════════════════════════════════════════════
           MODAL OVERLAY (shared)
        ═══════════════════════════════════════════════════════ */
        .modal-ov {
            position: fixed; inset: 0; z-index: 1000;
            background: var(--modal-bg); backdrop-filter: blur(5px);
            display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none;
            transition: opacity .3s;
        }
        .modal-ov.open { opacity: 1; pointer-events: all; }
        .modal-ov.open .modal-box,
        .modal-ov.open .cv-preview-box,
        .modal-ov.open .confirm-box { transform: scale(1) translateY(0); }

        /* ═══════════════════════════════════════════════════════
           BUILDER MODAL
        ═══════════════════════════════════════════════════════ */
        .modal-box {
            background: #fff; border-radius: 22px;
            width: 92%; max-width: 800px; max-height: 92vh;
            display: flex; flex-direction: column; overflow: hidden;
            transform: scale(.95) translateY(18px); transition: transform .3s;
            box-shadow: var(--shadow-lg);
        }
        .mb-header {
            padding: 1.4rem 2rem;
            background: linear-gradient(135deg, var(--ex-navy), #164e63);
            display: flex; justify-content: space-between; align-items: center;
            flex-shrink: 0;
        }
        .mb-header h2 {
            font-family: 'Poppins', sans-serif; color: #fff;
            font-size: 1.2rem; font-weight: 700;
        }
        .mb-header p { color: rgba(255,255,255,.55); font-size: .8rem; margin-top: .15rem; }
        .btn-x {
            width: 34px; height: 34px; border-radius: 50%; border: none;
            background: rgba(255,255,255,.12); color: #fff; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: .95rem; transition: var(--trans);
        }
        .btn-x:hover { background: rgba(255,255,255,.22); }

        /* Step indicators */
        .step-bar {
            display: flex; align-items: center; padding: 1rem 2rem;
            background: #f8fafc; border-bottom: 1px solid #e2e8f0;
            flex-shrink: 0; overflow-x: auto; gap: 0;
        }
        .step-ind { display: flex; align-items: center; flex-shrink: 0; gap: .4rem; }
        .step-dot {
            width: 30px; height: 30px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: .75rem; font-weight: 700; cursor: pointer; transition: var(--trans);
        }
        .step-dot.inactive { background: #e2e8f0; color: var(--ex-gray); }
        .step-dot.active   { background: var(--ex-cyan); color: #fff; box-shadow: 0 3px 10px rgba(6,182,212,.4); }
        .step-dot.done     { background: var(--ex-green); color: #fff; }
        .step-lbl { font-size: .72rem; color: var(--ex-gray); font-weight: 500; display: none; }
        @media(min-width:560px){ .step-lbl { display: block; } }
        .step-line { flex: 1; height: 2px; background: #e2e8f0; min-width: 16px; transition: background .3s; }
        .step-line.done { background: var(--ex-green); }

        /* Form panels */
        .mb-body { padding: 2rem; overflow-y: auto; flex: 1; min-height: 0; }
        .step-panel { display: none; }
        .step-panel.active { display: block; animation: fadeUp .28s ease; }
        @keyframes fadeUp { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }

        .panel-title {
            display: flex; align-items: center; gap: .85rem; margin-bottom: 1.5rem;
        }
        .panel-icon {
            width: 40px; height: 40px; border-radius: 12px; flex-shrink: 0;
            background: linear-gradient(135deg, var(--ex-cyan3), #e0f2fe);
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; color: var(--ex-cyan);
        }
        .panel-title h3 {
            font-family: 'Poppins', sans-serif; font-size: 1.05rem;
            color: var(--ex-navy); margin-bottom: .15rem;
        }
        .panel-title p { font-size: .78rem; color: var(--ex-gray); }

        /* Form grid */
        .frow { display: grid; gap: 1rem; margin-bottom: 1rem; }
        .frow-2 { grid-template-columns: 1fr 1fr; }
        .frow-3 { grid-template-columns: 1fr 1fr 1fr; }
        .frow-1 { grid-template-columns: 1fr; }
        @media(max-width:580px) { .frow-2, .frow-3 { grid-template-columns: 1fr; } }

        .fgroup { display: flex; flex-direction: column; gap: .35rem; }
        .fgroup label {
            font-size: .74rem; font-weight: 700; color: #374151;
            text-transform: uppercase; letter-spacing: .05em;
        }
        .fgroup label .req { color: var(--ex-cyan); margin-left: .2rem; }
        .finput, .fselect, .ftextarea {
            padding: .72rem .95rem; border: 2px solid #e2e8f0; border-radius: 10px;
            font-family: 'Inter', sans-serif; font-size: .875rem; color: #1e293b;
            background: #fafafa; width: 100%;
            transition: border-color .2s, box-shadow .2s;
        }
        .finput:focus, .fselect:focus, .ftextarea:focus {
            outline: none; border-color: var(--ex-cyan); background: #fff;
            box-shadow: 0 0 0 3px rgba(6,182,212,.12);
        }
        .finput.err { border-color: var(--ex-red); }
        .ftextarea { resize: vertical; min-height: 90px; }
        .fselect { appearance: none; cursor: pointer; }
        .ferr { font-size: .72rem; color: var(--ex-red); display: none; }
        .ferr.show { display: block; }

        /* Checkbox */
        .cb-row {
            display: flex; align-items: center; gap: .8rem;
            padding: .72rem 1rem; background: #f8fafc; border-radius: 10px; cursor: pointer;
        }
        .cb-box {
            width: 20px; height: 20px; border-radius: 5px;
            border: 2px solid #cbd5e1; display: flex; align-items: center;
            justify-content: center; flex-shrink: 0; transition: var(--trans);
        }
        .cb-box.on { background: var(--ex-cyan); border-color: var(--ex-cyan); }
        .cb-box.on::after { content: '✓'; color: #fff; font-size: .7rem; font-weight: 700; }
        .cb-lbl { font-size: .875rem; color: #374151; font-weight: 500; }

        /* Tag input */
        .tag-zone {
            border: 2px solid #e2e8f0; border-radius: 10px;
            padding: .45rem; min-height: 48px;
            display: flex; flex-wrap: wrap; gap: .38rem; align-items: flex-start;
            background: #fafafa; cursor: text; transition: var(--trans);
        }
        .tag-zone:focus-within { border-color: var(--ex-cyan); background: #fff; box-shadow: 0 0 0 3px rgba(6,182,212,.12); }
        .tag-chip {
            display: inline-flex; align-items: center; gap: .3rem;
            padding: .28rem .6rem; border-radius: 20px; font-size: .78rem; font-weight: 500;
        }
        .tag-chip.tech { background: rgba(6,182,212,.15);  color: var(--ex-cyan2); }
        .tag-chip.exp  { background: rgba(124,58,237,.15); color: var(--ex-purple); }
        .tag-rm {
            border: none; background: none; color: inherit; cursor: pointer;
            font-size: .7rem; opacity: .6; padding: 0; line-height: 1;
        }
        .tag-rm:hover { opacity: 1; }
        .tag-txt {
            border: none; outline: none; background: transparent;
            font-family: 'Inter', sans-serif; font-size: .85rem; flex: 1;
            min-width: 100px; padding: .28rem .35rem; color: #1e293b;
        }
        .tag-hint { font-size: .72rem; color: #94a3b8; margin-top: .3rem; }

        /* Dynamic blocks (experience / cert) */
        .dyn-block {
            background: #f8fafc; border: 1px solid #e2e8f0;
            border-radius: 14px; padding: 1.2rem; margin-bottom: .9rem;
        }
        .dyn-block-hdr {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 1rem;
        }
        .dyn-block-title {
            font-size: .82rem; font-weight: 700; color: var(--ex-navy);
            display: flex; align-items: center; gap: .45rem;
        }
        .dyn-block-title i { color: var(--ex-cyan); }
        .btn-rm-block {
            width: 28px; height: 28px; border-radius: 50%; border: none;
            background: rgba(239,68,68,.1); color: var(--ex-red); cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: .75rem; transition: var(--trans);
        }
        .btn-rm-block:hover { background: rgba(239,68,68,.22); }
        .btn-add-block {
            width: 100%; padding: .72rem; border: 2px dashed #cbd5e1;
            border-radius: 12px; background: transparent; color: var(--ex-gray);
            cursor: pointer; font-size: .85rem; font-weight: 500;
            display: flex; align-items: center; justify-content: center; gap: .5rem;
            transition: var(--trans);
        }
        .btn-add-block:hover { border-color: var(--ex-cyan); color: var(--ex-cyan); background: rgba(6,182,212,.04); }
        .dyn-empty {
            text-align: center; padding: 1.75rem; color: #94a3b8;
            background: #f8fafc; border-radius: 12px; margin-bottom: .9rem;
        }
        .dyn-empty i { font-size: 1.8rem; display: block; margin-bottom: .5rem; }

        /* Modal footer */
        .mb-footer {
            flex-shrink: 0; padding: 1.1rem 2rem;
            border-top: 1px solid #f1f5f9; background: #fafafa;
            display: flex; justify-content: space-between; align-items: center;
        }
        .mb-footer-counter { font-size: .8rem; color: var(--ex-gray); }
        .mb-footer-btns { display: flex; gap: .65rem; }

        /* ═══════════════════════════════════════════════════════
           CV PREVIEW MODAL
        ═══════════════════════════════════════════════════════ */
        .cv-preview-box {
            background: #fff; border-radius: 22px;
            width: 95%; max-width: 920px; max-height: 92vh;
            display: flex; flex-direction: column; overflow: hidden;
            transform: scale(.95) translateY(18px); transition: transform .3s;
            box-shadow: var(--shadow-lg);
        }
        .cv-prev-hdr {
            display: flex; justify-content: space-between; align-items: center;
            padding: 1.1rem 1.75rem; border-bottom: 1px solid #f1f5f9; flex-shrink: 0;
        }
        .cv-prev-hdr h3 { font-family: 'Poppins', sans-serif; font-size: 1.1rem; color: var(--ex-navy); }
        .cv-prev-body { overflow-y: auto; flex: 1; }

        /* Two-column CV layout */
        .cv-doc { display: grid; grid-template-columns: 270px 1fr; min-height: 100%; }
        .cv-left {
            background: linear-gradient(165deg, var(--ex-navy) 0%, #164e63 100%);
            padding: 2.4rem 1.6rem; color: #fff;
        }
        .cv-right { padding: 2.4rem 2rem; background: #fff; }

        /* CV left elements */
        .cv-av-big {
            width: 88px; height: 88px; border-radius: 50%; margin: 0 auto 1.1rem;
            background: linear-gradient(135deg, var(--ex-cyan), #0284c7);
            display: flex; align-items: center; justify-content: center;
            font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 2rem; color: #fff;
            border: 3px solid rgba(255,255,255,.2);
        }
        #cv-name  { text-align: center; font-family: 'Poppins', sans-serif; font-size: 1.1rem; font-weight: 700; color: #fff; margin-bottom: .25rem; }
        #cv-title { text-align: center; font-size: .78rem; color: rgba(255,255,255,.6); margin-bottom: .85rem; }
        #cv-level { text-align: center; margin-bottom: 1.6rem; }
        .cv-sec-ttl {
            font-size: .65rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .1em; color: var(--ex-cyan);
            margin-bottom: .65rem; padding-bottom: .35rem;
            border-bottom: 1px solid rgba(255,255,255,.1);
        }
        .cv-sec-ttl.dark { color: var(--ex-navy); border-bottom-color: #e2e8f0; }
        .cv-contact-item {
            display: flex; align-items: center; gap: .55rem;
            font-size: .78rem; color: rgba(255,255,255,.7); margin-bottom: .45rem;
        }
        .cv-contact-item i { color: var(--ex-cyan); width: 13px; font-size: .8rem; }
        .cv-contact-item a { color: rgba(255,255,255,.7); text-decoration: none; }
        .cv-contact-item a:hover { color: var(--ex-cyan); }

        .cv-skill-entry { margin-bottom: .65rem; }
        .cv-skill-lbl { font-size: .76rem; color: rgba(255,255,255,.8); margin-bottom: .22rem; }
        .cv-skill-track { height: 5px; background: rgba(255,255,255,.1); border-radius: 3px; overflow: hidden; }
        .cv-skill-fill { height: 100%; border-radius: 3px; background: linear-gradient(90deg, var(--ex-cyan), #0284c7); }

        .cv-cert-entry { display: flex; align-items: flex-start; gap: .55rem; margin-bottom: .55rem; }
        .cv-cert-entry i { color: #fbbf24; font-size: .78rem; margin-top: .1rem; }
        .cv-cert-nm { font-size: .78rem; color: rgba(255,255,255,.85); }
        .cv-cert-iss { font-size: .68rem; color: rgba(255,255,255,.4); margin-top: .1rem; }

        /* CV right elements */
        .cv-r-section { margin-bottom: 1.75rem; }
        .cv-r-section:last-child { margin-bottom: 0; }
        .cv-r-name { font-family: 'Poppins', sans-serif; font-size: 1.5rem; font-weight: 700; color: var(--ex-navy); }
        .cv-r-subtitle { font-size: .875rem; color: var(--ex-gray); margin-bottom: .35rem; }

        .cv-bio-box {
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
            border-left: 4px solid var(--ex-cyan);
            padding: .9rem 1.1rem; border-radius: 0 10px 10px 0;
            font-size: .85rem; color: #1e293b; line-height: 1.65;
        }
        /* Timeline */
        .cv-timeline { position: relative; }
        .cv-tl-item { padding-left: 1.4rem; position: relative; margin-bottom: 1.25rem; }
        .cv-tl-item::before {
            content: ''; position: absolute; left: 0; top: 5px;
            width: 9px; height: 9px; border-radius: 50%;
            background: var(--ex-cyan); border: 2px solid #fff;
            box-shadow: 0 0 0 2px var(--ex-cyan);
        }
        .cv-tl-item::after {
            content: ''; position: absolute; left: 3.5px; top: 16px;
            width: 2px; bottom: -14px; background: #e2e8f0;
        }
        .cv-tl-item:last-child::after { display: none; }
        .cv-tl-title { font-weight: 700; color: var(--ex-navy); font-size: .9rem; }
        .cv-tl-company { color: var(--ex-cyan2); font-weight: 600; font-size: .82rem; }
        .cv-tl-dates { font-size: .72rem; color: #94a3b8; margin: .18rem 0 .35rem; }
        .cv-tl-desc { font-size: .78rem; color: var(--ex-gray); line-height: 1.5; }

        .cv-exp-tags { display: flex; flex-wrap: wrap; gap: .45rem; }
        .cv-exp-tag {
            padding: .28rem .75rem; border-radius: 20px;
            font-size: .72rem; font-weight: 500;
            background: rgba(124,58,237,.08); color: var(--ex-purple);
            border: 1px solid rgba(124,58,237,.18);
        }
        .cv-avail-row { display: flex; gap: .75rem; flex-wrap: wrap; align-items: center; }

        /* ═══════════════════════════════════════════════════════
           DELETE CONFIRM MODAL
        ═══════════════════════════════════════════════════════ */
        .confirm-box {
            background: #fff; border-radius: 20px; padding: 2.25rem;
            width: 90%; max-width: 400px; text-align: center;
            transform: scale(.95) translateY(18px); transition: transform .3s;
            box-shadow: var(--shadow-lg);
        }
        .confirm-box .ci { font-size: 2.8rem; margin-bottom: .9rem; }
        .confirm-box h3 { font-family: 'Poppins', sans-serif; font-size: 1.2rem; margin-bottom: .5rem; color: var(--ex-navy); }
        .confirm-box p { color: var(--ex-gray); font-size: .85rem; margin-bottom: 1.5rem; line-height: 1.55; }
        .confirm-btns { display: flex; gap: .75rem; justify-content: center; }

        /* ═══════════════════════════════════════════════════════
           TOASTS
        ═══════════════════════════════════════════════════════ */
        #toast-host {
            position: fixed; right: 1.5rem; bottom: 1.5rem;
            z-index: 9999; display: flex; flex-direction: column; gap: .65rem;
        }
        .toast {
            display: flex; align-items: center; gap: .75rem;
            padding: .9rem 1.15rem; background: #fff; border-radius: 12px;
            box-shadow: var(--shadow-md); max-width: 310px;
            animation: toastIn .3s ease;
        }
        .toast.success { border-left: 4px solid var(--ex-green); }
        .toast.error   { border-left: 4px solid var(--ex-red); }
        .toast-ic { font-size: 1rem; }
        .toast.success .toast-ic { color: var(--ex-green); }
        .toast.error   .toast-ic { color: var(--ex-red); }
        .toast-msg { font-size: .855rem; color: #1e293b; font-weight: 500; }
        @keyframes toastIn { from { opacity:0; transform:translateX(60px); } to { opacity:1; transform:translateX(0); } }

        /* Loader spinner */
        .spin { display: inline-block; width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,.5); border-top-color: #fff;
            border-radius: 50%; animation: rotating .7s linear infinite;
        }
        @keyframes rotating { to { transform: rotate(360deg); } }

        /* ═══════════════════════════════════════════════════════
           AI DASHBOARD
        ═══════════════════════════════════════════════════════ */
        .ai-dashboard { margin-top:2rem; display:grid; grid-template-columns:320px 1fr; gap:1.5rem; }
        @media(max-width:900px){ .ai-dashboard { grid-template-columns:1fr; } }
        .ai-score-main {
            background:linear-gradient(135deg,var(--ex-navy) 0%,#164e63 100%);
            border-radius:22px; padding:2rem; text-align:center; color:#fff;
            position:relative; overflow:hidden;
        }
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
        .ai-detail-card { background:#fff; border-radius:16px; padding:1.1rem; box-shadow:var(--shadow-sm); transition:var(--trans); }
        .ai-detail-card:hover { transform:translateY(-3px); box-shadow:var(--shadow-md); }
        .ai-detail-hdr { display:flex; align-items:center; gap:.55rem; margin-bottom:.7rem; }
        .ai-detail-icon { width:34px; height:34px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:.85rem; flex-shrink:0; }
        .ai-detail-icon.sk { background:rgba(6,182,212,.12); color:var(--ex-cyan); }
        .ai-detail-icon.xp { background:rgba(124,58,237,.12); color:var(--ex-purple); }
        .ai-detail-icon.ct { background:rgba(245,158,11,.12); color:var(--ex-gold); }
        .ai-detail-icon.co { background:rgba(16,185,129,.12); color:var(--ex-green); }
        .ai-detail-name { font-size:.75rem; font-weight:600; color:var(--ex-gray); }
        .ai-detail-val { font-family:'Poppins',sans-serif; font-size:1.2rem; font-weight:700; color:var(--ex-navy); }
        .ai-prog-track { height:5px; background:#f1f5f9; border-radius:3px; overflow:hidden; margin-top:.45rem; }
        .ai-prog-fill { height:100%; border-radius:3px; transition:width 1.2s ease; }
        .ai-prog-fill.c1 { background:linear-gradient(90deg,var(--ex-cyan),#0284c7); }
        .ai-prog-fill.c2 { background:linear-gradient(90deg,var(--ex-purple),#6d28d9); }
        .ai-prog-fill.c3 { background:linear-gradient(90deg,var(--ex-gold),#d97706); }
        .ai-prog-fill.c4 { background:linear-gradient(90deg,var(--ex-green),#059669); }

        /* Market matching */
        .ai-card { background:#fff; border-radius:16px; padding:1.3rem; box-shadow:var(--shadow-sm); }
        .ai-card h3 { font-family:'Poppins',sans-serif; font-size:.92rem; font-weight:700; color:var(--ex-navy); margin-bottom:.9rem; display:flex; align-items:center; gap:.5rem; }
        .market-row { display:flex; align-items:center; gap:.75rem; margin-bottom:.7rem; }
        .market-lbl { width:95px; font-size:.76rem; font-weight:600; color:var(--ex-gray); flex-shrink:0; }
        .market-track { flex:1; height:7px; background:#f1f5f9; border-radius:4px; overflow:hidden; }
        .market-fill { height:100%; border-radius:4px; transition:width 1.5s ease; }
        .market-pct { width:38px; text-align:right; font-size:.76rem; font-weight:700; color:var(--ex-navy); }

        /* Recommendations */
        .reco-item { display:flex; align-items:flex-start; gap:.7rem; padding:.7rem .8rem; background:#f8fafc; border-radius:10px; margin-bottom:.5rem; border-left:3px solid var(--ex-cyan); transition:var(--trans); }
        .reco-item:hover { background:#f0f9ff; }
        .reco-ic { width:26px; height:26px; border-radius:8px; background:rgba(6,182,212,.1); color:var(--ex-cyan); display:flex; align-items:center; justify-content:center; font-size:.7rem; flex-shrink:0; }
        .reco-txt { font-size:.8rem; color:#374151; line-height:1.4; }
        .reco-pri { font-size:.62rem; font-weight:700; text-transform:uppercase; padding:.12rem .35rem; border-radius:6px; margin-top:.25rem; display:inline-block; }
        .reco-pri.high { background:rgba(239,68,68,.1); color:var(--ex-red); }
        .reco-pri.med { background:rgba(245,158,11,.1); color:var(--ex-gold); }
        .reco-pri.low { background:rgba(16,185,129,.1); color:var(--ex-green); }

        /* AI action buttons */
        .ai-actions { display:flex; flex-wrap:wrap; gap:.7rem; margin-top:1.5rem; }
        .ai-abtn { display:inline-flex; align-items:center; gap:.45rem; padding:.65rem 1.1rem; border:2px solid #e2e8f0; border-radius:12px; background:#fff; font-family:'Inter',sans-serif; font-size:.8rem; font-weight:600; color:var(--ex-navy); cursor:pointer; transition:var(--trans); }
        .ai-abtn:hover { border-color:var(--ex-cyan); color:var(--ex-cyan); background:rgba(6,182,212,.04); transform:translateY(-2px); box-shadow:var(--shadow-sm); }
        .ai-abtn i { font-size:.9rem; color:var(--ex-cyan); }

        /* Stats section */
        .ai-stats { margin-top:2rem; display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; }
        @media(max-width:700px){ .ai-stats { grid-template-columns:1fr; } }
        .radar-wrap { display:flex; justify-content:center; padding:.5rem 0; }

        /* ═══════════════════════════════════════════════════════
           AI MODALS (shared)
        ═══════════════════════════════════════════════════════ */
        .ai-mbox { background:#fff; border-radius:22px; width:92%; max-width:720px; max-height:90vh; display:flex; flex-direction:column; overflow:hidden; transform:scale(.95) translateY(18px); transition:transform .3s; box-shadow:var(--shadow-lg); }
        .modal-ov.open .ai-mbox { transform:scale(1) translateY(0); }
        .ai-mhdr { padding:1.2rem 1.7rem; background:linear-gradient(135deg,var(--ex-navy),#164e63); display:flex; justify-content:space-between; align-items:center; flex-shrink:0; }
        .ai-mhdr h3 { font-family:'Poppins',sans-serif; font-size:1.05rem; font-weight:700; color:#fff; }
        .ai-mhdr p { font-size:.75rem; color:rgba(255,255,255,.5); margin-top:.12rem; }
        .ai-mbody { padding:1.6rem; overflow-y:auto; flex:1; }

        /* Skill gap */
        .gap-sec { margin-bottom:1.4rem; }
        .gap-sec-ttl { font-family:'Poppins',sans-serif; font-size:.88rem; font-weight:700; color:var(--ex-navy); margin-bottom:.75rem; display:flex; align-items:center; gap:.45rem; }
        .gap-row { display:flex; align-items:center; gap:.7rem; margin-bottom:.55rem; }
        .gap-name { width:120px; font-size:.76rem; font-weight:500; color:#374151; flex-shrink:0; }
        .gap-track { flex:1; height:7px; background:#f1f5f9; border-radius:4px; position:relative; overflow:hidden; }
        .gap-have { height:100%; border-radius:4px; background:var(--ex-cyan); position:absolute; left:0; top:0; transition:width 1s ease; }
        .gap-need { height:100%; border-radius:4px; background:rgba(239,68,68,.15); width:100%; }
        .gap-st { width:22px; text-align:center; font-size:.8rem; flex-shrink:0; }
        .gap-miss { margin-top:.8rem; display:flex; flex-wrap:wrap; gap:.35rem; }
        .gap-miss-tag { padding:.22rem .55rem; border-radius:20px; font-size:.7rem; font-weight:600; background:rgba(239,68,68,.08); color:var(--ex-red); border:1px solid rgba(239,68,68,.15); }
        .gap-have-tag { padding:.22rem .55rem; border-radius:20px; font-size:.7rem; font-weight:600; background:rgba(16,185,129,.08); color:var(--ex-green); border:1px solid rgba(16,185,129,.15); }

        /* Career timeline */
        .career-node { display:flex; gap:1rem; margin-bottom:1.3rem; position:relative; }
        .career-dot-col { display:flex; flex-direction:column; align-items:center; flex-shrink:0; width:38px; }
        .career-dot { width:38px; height:38px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.8rem; font-weight:700; transition:var(--trans); }
        .career-dot.past { background:var(--ex-green); color:#fff; box-shadow:0 3px 10px rgba(16,185,129,.3); }
        .career-dot.now { background:var(--ex-cyan); color:#fff; box-shadow:0 3px 10px rgba(6,182,212,.4); animation:pulseDot 2s infinite; }
        .career-dot.fut { background:#e2e8f0; color:var(--ex-gray); }
        @keyframes pulseDot { 0%,100%{box-shadow:0 3px 10px rgba(6,182,212,.4)} 50%{box-shadow:0 3px 20px rgba(6,182,212,.6)} }
        .career-line { flex:1; width:2px; background:#e2e8f0; margin:4px 0; }
        .career-line.done { background:var(--ex-green); }
        .career-body { flex:1; background:#f8fafc; border-radius:14px; padding:.9rem 1.1rem; border:1px solid #e2e8f0; }
        .career-body.now { background:rgba(6,182,212,.05); border-color:rgba(6,182,212,.2); }
        .career-role { font-family:'Poppins',sans-serif; font-size:.88rem; font-weight:700; color:var(--ex-navy); }
        .career-yrs { font-size:.72rem; color:var(--ex-gray); margin:.15rem 0 .35rem; }
        .career-sal { font-size:.76rem; font-weight:600; color:var(--ex-green); }
        .career-tips { margin-top:.4rem; display:flex; flex-wrap:wrap; gap:.3rem; }
        .career-tip { padding:.18rem .45rem; border-radius:20px; font-size:.65rem; font-weight:500; background:rgba(6,182,212,.08); color:var(--ex-cyan2); }

        /* Salary */
        .sal-display { text-align:center; padding:1.5rem 0; }
        .sal-amount { font-family:'Poppins',sans-serif; font-size:2.8rem; font-weight:800; background:linear-gradient(135deg,var(--ex-cyan),var(--ex-purple)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
        .sal-range { display:flex; justify-content:center; gap:2rem; margin-top:.8rem; }
        .sal-bound { text-align:center; }
        .sal-bound-lbl { font-size:.65rem; font-weight:700; text-transform:uppercase; color:var(--ex-gray); letter-spacing:.05em; }
        .sal-bound-val { font-family:'Poppins',sans-serif; font-size:1.15rem; font-weight:700; color:var(--ex-navy); }
        .sal-factors { display:grid; grid-template-columns:1fr 1fr; gap:.7rem; margin-top:1.3rem; }
        @media(max-width:500px){ .sal-factors { grid-template-columns:1fr; } }
        .sal-factor { display:flex; align-items:center; gap:.55rem; padding:.6rem .8rem; background:#f8fafc; border-radius:10px; }
        .sal-fic { width:28px; height:28px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:.7rem; flex-shrink:0; }
        .sal-flbl { font-size:.76rem; color:#374151; }
        .sal-fval { margin-left:auto; font-size:.72rem; font-weight:700; }
        .sal-fval.pos { color:var(--ex-green); }
        .sal-fval.neu { color:var(--ex-gray); }

        /* Template selector */
        .tpl-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:.85rem; margin:1rem 0; }
        @media(max-width:550px){ .tpl-grid { grid-template-columns:1fr; } }
        .tpl-opt { border:2px solid #e2e8f0; border-radius:14px; padding:.9rem; text-align:center; cursor:pointer; transition:var(--trans); }
        .tpl-opt:hover { border-color:var(--ex-cyan); transform:translateY(-3px); box-shadow:var(--shadow-sm); }
        .tpl-opt.sel { border-color:var(--ex-cyan); background:rgba(6,182,212,.04); box-shadow:0 0 0 3px rgba(6,182,212,.15); }
        .tpl-icon { width:48px; height:48px; border-radius:12px; margin:0 auto .6rem; display:flex; align-items:center; justify-content:center; font-size:1.3rem; }
        .tpl-name { font-size:.82rem; font-weight:700; color:var(--ex-navy); margin-bottom:.2rem; }
        .tpl-desc { font-size:.7rem; color:var(--ex-gray); }

        /* Bio generator button */
        .ai-gen-btn { display:inline-flex; align-items:center; gap:.4rem; padding:.4rem .8rem; border:1.5px dashed var(--ex-cyan); border-radius:8px; background:rgba(6,182,212,.04); color:var(--ex-cyan); font-size:.75rem; font-weight:600; cursor:pointer; transition:var(--trans); margin-top:.3rem; font-family:'Inter',sans-serif; }
        .ai-gen-btn:hover { background:rgba(6,182,212,.1); border-style:solid; }

        /* Animated counter */
        @keyframes fadeInUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
        .fade-in { animation:fadeInUp .5s ease forwards; }
    </style>
</head>
<body>
<div class="da-container">

    <!-- ═══ SIDEBAR ═══════════════════════════════════════════ -->
    <aside class="da-sidebar">
        <div class="da-sb-header">
            <div class="da-logo-icon"><i class="fa-solid fa-chart-pie"></i></div>
            <a href="index.php" class="da-logo-text">Digit Advisory</a>
        </div>
        <nav class="da-sb-menu">
            <a href="front-expert-dashboard.php"    class="da-menu-item"><i class="fa-solid fa-house"></i><span>Vue d'ensemble</span></a>
            <a href="front-expert-profil.php"       class="da-menu-item"><i class="fa-solid fa-user"></i><span>Mon Profil</span></a>
            <a href="front-expert-portfolio.php"    class="da-menu-item active"><i class="fa-solid fa-id-card"></i><span>Portfolio &amp; CV</span></a>
            <a href="front-expert-offres.php"       class="da-menu-item"><i class="fa-solid fa-briefcase"></i><span>Explorer les Offres</span></a>
            <a href="front-expert-candidatures.php" class="da-menu-item"><i class="fa-solid fa-file-contract"></i><span>Mes Candidatures</span></a>
            <a href="front-expert-messagerie.php"   class="da-menu-item"><i class="fa-solid fa-comments"></i><span>Messagerie</span></a>
        </nav>
        <div class="da-sb-footer">
            <div class="da-sb-avatar" id="sb-initials">AL</div>
            <div>
                <div class="da-sb-name">Alice Martin</div>
                <div class="da-sb-role">Consultant Expert</div>
            </div>
            <a href="login.php" class="da-logout" title="Se déconnecter"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
        </div>
    </aside>

    <!-- ═══ MAIN ═══════════════════════════════════════════════ -->
    <main class="da-main">

        <div class="da-topbar">
            <div>
                <h1><i class="fa-solid fa-id-card" style="color:var(--ex-cyan);margin-right:.5rem;"></i>Portfolio &amp; CV Professionnel</h1>
                <p>Mettez en valeur vos compétences pour attirer les meilleures missions</p>
            </div>
            <button class="btn btn-primary" id="btn-open-builder">
                <i class="fa-solid fa-plus"></i> Créer mon Portfolio
            </button>
        </div>

        <!-- Portfolio grid populated by JS -->
        <div id="portfolio-host"></div>

        <!-- ═══ AI DASHBOARD (rendered by JS when portfolio exists) ═══ -->
        <div id="ai-dashboard-host"></div>
    </main>
</div>

<!-- ═══════════════════════════════════════════════════════════
     TOAST HOST
══════════════════════════════════════════════════════════════ -->
<div id="toast-host"></div>

<!-- ═══════════════════════════════════════════════════════════
     BUILDER MODAL
══════════════════════════════════════════════════════════════ -->
<div class="modal-ov" id="ov-builder" role="dialog" aria-modal="true" aria-labelledby="builder-title">
    <div class="modal-box">
        <div class="mb-header">
            <div>
                <h2 id="builder-title">Créer mon Portfolio Expert</h2>
                <p id="builder-sub">Construisez votre profil CV professionnel étape par étape</p>
            </div>
            <button class="btn-x" id="btn-close-builder" aria-label="Fermer"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <!-- Step bar -->
        <div class="step-bar" id="step-bar"></div>

        <!-- ── FORM BODY ───────────────────────────────────── -->
        <div class="mb-body">

            <!-- STEP 1 · Personal Info -->
            <div class="step-panel" id="sp-1">
                <div class="panel-title">
                    <div class="panel-icon"><i class="fa-solid fa-user"></i></div>
                    <div><h3>Informations Personnelles</h3><p>Votre identité professionnelle</p></div>
                </div>
                <div class="frow frow-2">
                    <div class="fgroup">
                        <label>Nom Complet <span class="req">*</span></label>
                        <input class="finput" id="f-name" placeholder="Ex : Alice Martin">
                        <span class="ferr" id="e-name">Ce champ est obligatoire</span>
                    </div>
                    <div class="fgroup">
                        <label>Titre Professionnel <span class="req">*</span></label>
                        <input class="finput" id="f-title" placeholder="Ex : Consultant Senior IT">
                        <span class="ferr" id="e-title">Ce champ est obligatoire</span>
                    </div>
                </div>
                <div class="frow frow-2">
                    <div class="fgroup">
                        <label>Niveau d'Expérience <span class="req">*</span></label>
                        <select class="fselect" id="f-level">
                            <option value="">— Sélectionner —</option>
                            <option value="junior">🟢 Junior (0–3 ans)</option>
                            <option value="mid">🔵 Mid-level (3–6 ans)</option>
                            <option value="senior">🟣 Senior (6–12 ans)</option>
                            <option value="expert">🌟 Expert (12+ ans)</option>
                        </select>
                        <span class="ferr" id="e-level">Veuillez sélectionner un niveau</span>
                    </div>
                    <div class="fgroup">
                        <label>Objectif de Carrière <span class="req">*</span></label>
                        <select class="fselect" id="f-obj">
                            <option value="">— Sélectionner —</option>
                            <option value="employment">💼 CDI / Emploi permanent</option>
                            <option value="freelance">🚀 Freelance</option>
                            <option value="consulting">🎯 Missions de Conseil</option>
                            <option value="open">🌐 Ouvert à tout</option>
                        </select>
                        <span class="ferr" id="e-obj">Veuillez sélectionner un objectif</span>
                    </div>
                </div>
                <div class="frow frow-1">
                    <div class="fgroup">
                        <label>Bio / Résumé Professionnnel</label>
                        <textarea class="ftextarea" id="f-bio" placeholder="Décrivez votre parcours, vos valeurs, vos spécialités…"></textarea>
                        <button type="button" class="ai-gen-btn" id="btn-gen-bio"><i class="fa-solid fa-wand-magic-sparkles"></i> Générer avec l'IA</button>
                    </div>
                </div>
            </div>

            <!-- STEP 2 · Skills -->
            <div class="step-panel" id="sp-2">
                <div class="panel-title">
                    <div class="panel-icon"><i class="fa-solid fa-code"></i></div>
                    <div><h3>Compétences &amp; Expertises</h3><p>Appuyez sur Entrée ou virgule pour ajouter</p></div>
                </div>
                <div class="fgroup" style="margin-bottom:1.25rem;">
                    <label><i class="fa-solid fa-microchip" style="color:var(--ex-cyan);"></i> Compétences Techniques</label>
                    <div class="tag-zone" id="zone-tech">
                        <input class="tag-txt" id="inp-tech" placeholder="Ex : PHP, Docker, MySQL…">
                    </div>
                    <p class="tag-hint"><i class="fa-regular fa-lightbulb"></i> Entrée ou virgule pour valider chaque compétence</p>
                </div>
                <div class="fgroup">
                    <label><i class="fa-solid fa-star" style="color:var(--ex-purple);"></i> Domaines d'Expertise Métier</label>
                    <div class="tag-zone" id="zone-exp">
                        <input class="tag-txt" id="inp-exp" placeholder="Ex : Cybersécurité, Finance, Management…">
                    </div>
                    <p class="tag-hint">Vos secteurs et domaines de spécialisation</p>
                </div>
            </div>

            <!-- STEP 3 · Work Experience -->
            <div class="step-panel" id="sp-3">
                <div class="panel-title">
                    <div class="panel-icon"><i class="fa-solid fa-briefcase"></i></div>
                    <div><h3>Expériences Professionnelles</h3><p>Ajoutez vos postes et missions</p></div>
                </div>
                <div id="exp-host"></div>
                <button type="button" class="btn-add-block" id="btn-add-exp">
                    <i class="fa-solid fa-plus"></i> Ajouter une expérience
                </button>
            </div>

            <!-- STEP 4 · Certifications -->
            <div class="step-panel" id="sp-4">
                <div class="panel-title">
                    <div class="panel-icon"><i class="fa-solid fa-award"></i></div>
                    <div><h3>Certifications &amp; Formations</h3><p>ISO, PMP, AWS, Scrum, et autres</p></div>
                </div>
                <div id="cert-host"></div>
                <button type="button" class="btn-add-block" id="btn-add-cert">
                    <i class="fa-solid fa-plus"></i> Ajouter une certification
                </button>
            </div>

            <!-- STEP 5 · Preferences -->
            <div class="step-panel" id="sp-5">
                <div class="panel-title">
                    <div class="panel-icon"><i class="fa-solid fa-sliders"></i></div>
                    <div><h3>Préférences &amp; Disponibilité</h3><p>Localisation, liens et disponibilité</p></div>
                </div>
                <div class="frow frow-2">
                    <div class="fgroup">
                        <label>Secteur Préféré</label>
                        <select class="fselect" id="f-industry">
                            <option value="">— Tous secteurs —</option>
                            <option value="Finance">Finance &amp; Banque</option>
                            <option value="IT">Technologie &amp; IT</option>
                            <option value="Industry">Industrie</option>
                            <option value="Healthcare">Santé</option>
                            <option value="Consulting">Conseil</option>
                            <option value="Telecom">Télécoms</option>
                            <option value="Energy">Énergie</option>
                            <option value="Retail">Commerce &amp; Retail</option>
                            <option value="Other">Autre</option>
                        </select>
                    </div>
                    <div class="fgroup">
                        <label>Disponibilité <span class="req">*</span></label>
                        <select class="fselect" id="f-avail">
                            <option value="">— Sélectionner —</option>
                            <option value="immediate">🟢 Disponible immédiatement</option>
                            <option value="one_month">🟡 Dans 1 mois</option>
                            <option value="three_months">🟠 Dans 3 mois</option>
                            <option value="unavailable">🔴 Non disponible</option>
                        </select>
                        <span class="ferr" id="e-avail">Veuillez indiquer votre disponibilité</span>
                    </div>
                </div>
                <div class="frow frow-2">
                    <div class="fgroup">
                        <label><i class="fa-solid fa-location-dot" style="color:var(--ex-cyan);"></i> Localisation</label>
                        <input class="finput" id="f-location" placeholder="Ex : Paris, France">
                    </div>
                    <div class="fgroup">
                        <label>Options de travail</label>
                        <label class="cb-row" id="remote-toggle">
                            <div class="cb-box" id="cb-remote"></div>
                            <span class="cb-lbl"><i class="fa-solid fa-wifi" style="color:var(--ex-cyan);"></i> Télétravail accepté</span>
                        </label>
                    </div>
                </div>
                <div class="frow frow-3">
                    <div class="fgroup">
                        <label><i class="fa-brands fa-linkedin" style="color:#0077b5;"></i> LinkedIn</label>
                        <input class="finput" id="f-linkedin" type="url" placeholder="linkedin.com/in/…">
                    </div>
                    <div class="fgroup">
                        <label><i class="fa-brands fa-github"></i> GitHub</label>
                        <input class="finput" id="f-github" type="url" placeholder="github.com/…">
                    </div>
                    <div class="fgroup">
                        <label><i class="fa-solid fa-globe" style="color:var(--ex-cyan);"></i> Site Web</label>
                        <input class="finput" id="f-website" type="url" placeholder="https://…">
                    </div>
                </div>
            </div>

        </div><!-- /mb-body -->

        <!-- Footer navigation -->
        <div class="mb-footer">
            <span class="mb-footer-counter" id="step-counter"></span>
            <div class="mb-footer-btns">
                <button class="btn btn-ghost" id="btn-prev" style="display:none;"><i class="fa-solid fa-arrow-left"></i> Précédent</button>
                <button class="btn btn-primary" id="btn-next">Suivant <i class="fa-solid fa-arrow-right"></i></button>
                <button class="btn btn-primary" id="btn-save" style="display:none;"><i class="fa-solid fa-floppy-disk"></i> Sauvegarder</button>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     CV PREVIEW MODAL
══════════════════════════════════════════════════════════════ -->
<div class="modal-ov" id="ov-preview" role="dialog" aria-modal="true" aria-labelledby="prev-title">
    <div class="cv-preview-box">
        <div class="cv-prev-hdr">
            <h3 id="prev-title"><i class="fa-solid fa-eye" style="color:var(--ex-cyan);margin-right:.4rem;"></i>Aperçu du CV Professionnel</h3>
            <div style="display:flex;gap:.65rem;">
                <button class="btn btn-outline btn-sm" id="btn-tpl-select"><i class="fa-solid fa-palette"></i> Template</button>
                <button class="btn btn-primary btn-sm" id="btn-export-pdf"><i class="fa-solid fa-file-pdf"></i> Export PDF</button>
                <button class="btn btn-outline btn-sm" id="btn-edit-from-prev"><i class="fa-solid fa-pen"></i> Modifier</button>
                <button class="btn-x" id="btn-close-prev" style="background:#f1f5f9;color:var(--ex-gray);" aria-label="Fermer"><i class="fa-solid fa-xmark"></i></button>
            </div>
        </div>
        <div class="cv-prev-body">
            <div class="cv-doc" id="cv-doc-content"><!-- JS renders here --></div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     DELETE CONFIRM MODAL
══════════════════════════════════════════════════════════════ -->
<div class="modal-ov" id="ov-delete" role="dialog" aria-modal="true">
    <div class="confirm-box">
        <div class="ci">🗑️</div>
        <h3>Supprimer ce portfolio ?</h3>
        <p>Cette action est irréversible. Toutes les compétences, certifications et expériences associées seront supprimées définitivement.</p>
        <div class="confirm-btns">
            <button class="btn btn-ghost" id="btn-cancel-del">Annuler</button>
            <button class="btn btn-danger" id="btn-confirm-del"><i class="fa-solid fa-trash"></i> Supprimer</button>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     TEMPLATE SELECTOR MODAL
══════════════════════════════════════════════════════════════ -->
<div class="modal-ov" id="ov-template" role="dialog" aria-modal="true">
    <div class="ai-mbox" style="max-width:550px;">
        <div class="ai-mhdr">
            <div><h3><i class="fa-solid fa-palette" style="margin-right:.4rem;"></i>Choisir un Template</h3><p>Sélectionnez le design de votre CV</p></div>
            <button class="btn-x" onclick="closeOv('ov-template')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="ai-mbody">
            <div class="tpl-grid" id="tpl-grid"></div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     SKILL GAP ANALYSIS MODAL
══════════════════════════════════════════════════════════════ -->
<div class="modal-ov" id="ov-skillgap" role="dialog" aria-modal="true">
    <div class="ai-mbox">
        <div class="ai-mhdr">
            <div><h3><i class="fa-solid fa-magnifying-glass-chart" style="margin-right:.4rem;"></i>Analyse des Écarts de Compétences</h3><p>Comparaison avec les exigences du marché</p></div>
            <button class="btn-x" onclick="closeOv('ov-skillgap')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="ai-mbody" id="skillgap-body"></div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     CAREER PATH PREDICTION MODAL
══════════════════════════════════════════════════════════════ -->
<div class="modal-ov" id="ov-career" role="dialog" aria-modal="true">
    <div class="ai-mbox">
        <div class="ai-mhdr">
            <div><h3><i class="fa-solid fa-route" style="margin-right:.4rem;"></i>Prédiction de Trajectoire de Carrière</h3><p>Votre chemin professionnel estimé par l'IA</p></div>
            <button class="btn-x" onclick="closeOv('ov-career')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="ai-mbody" id="career-body"></div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     SALARY ESTIMATOR MODAL
══════════════════════════════════════════════════════════════ -->
<div class="modal-ov" id="ov-salary" role="dialog" aria-modal="true">
    <div class="ai-mbox">
        <div class="ai-mhdr">
            <div><h3><i class="fa-solid fa-coins" style="margin-right:.4rem;"></i>Estimateur de Salaire IA</h3><p>Estimation basée sur votre profil et le marché</p></div>
            <button class="btn-x" onclick="closeOv('ov-salary')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="ai-mbody" id="salary-body"></div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     JAVASCRIPT — All dynamic logic
══════════════════════════════════════════════════════════════ -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="../../js/ai-engines.js"></script>
<script src="../../js/ai-ui.js"></script>
<script>
'use strict';

/* ── Config ──────────────────────────────────────────── */
const API = '../../Controller/ExpertPortfolioController.php';
const TOTAL_STEPS = 5;

const STEP_META = [
    { icon:'fa-user',     label:'Identité'      },
    { icon:'fa-code',     label:'Compétences'   },
    { icon:'fa-briefcase',label:'Expériences'   },
    { icon:'fa-award',    label:'Certifications'},
    { icon:'fa-sliders',  label:'Préférences'   },
];

const LEVEL_LABELS  = { junior:'Junior', mid:'Mid-level', senior:'Séniore', expert:'Expert' };
const AVAIL_LABELS  = { immediate:'Disponible immédiatement', one_month:'Dans 1 mois', three_months:'Dans 3 mois', unavailable:'Non disponible' };
const OBJ_LABELS    = { employment:'CDI / Emploi', freelance:'Freelance', consulting:'Missions Conseil', open:'Ouvert à tout' };
const SKILL_LEVELS  = { beginner:20, intermediate:50, advanced:75, expert:95 };

/* ── App State ───────────────────────────────────────── */
let portfolios     = <?= $portfoliosJson ?>;
let currentStep    = 1;
let editingId      = null;   // null = create, int = edit
let deleteTargetId = null;
let previewId      = null;

// Live form state
let techTags  = [];   // { name: string }
let expTags   = [];   // { name: string }
let expBlocks = [];   // experience objects
let certBlocks= [];   // certification objects
let remoteSel = false;
let currentTemplate = 'classic'; // 'classic' | 'modern' | 'creative'

/* ── Bootstrap ───────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    renderGrid();
    buildStepBar();
    wireBuilder();
    wirePreview();
    wireDelete();
    wireTagInputs();
    wireAIFeatures();
    renderAIDashboard();
});

/* ══════════════════════════════════════════════════════
   PORTFOLIO GRID
══════════════════════════════════════════════════════ */
function renderGrid() {
    const host = document.getElementById('portfolio-host');
    const addBtn = document.getElementById('btn-open-builder');

    if (portfolios.length === 0) {
        addBtn.style.display = 'inline-flex';
        host.innerHTML = `
        <div class="empty-state">
            <div class="empty-icon"><i class="fa-solid fa-id-card"></i></div>
            <h2>Aucun portfolio créé</h2>
            <p>Construisez votre CV expert pour valoriser vos compétences et décrocher vos meilleures missions.</p>
            <button class="btn btn-primary" id="empty-cta"><i class="fa-solid fa-plus"></i> Créer mon Portfolio</button>
        </div>`;
        document.getElementById('empty-cta').addEventListener('click', openCreate);
        return;
    }

    // Hide the topbar button when a portfolio exists (one per consultant)
    addBtn.style.display = 'none';
    host.innerHTML = `<div class="portfolio-grid">${portfolios.map(cardHTML).join('')}</div>`;

    // Wire action buttons
    portfolios.forEach(p => {
        byId(`btn-view-${p.id_portfolio}`).addEventListener('click', () => openPreview(p.id_portfolio));
        byId(`btn-edit-${p.id_portfolio}`).addEventListener('click', () => openEdit(p.id_portfolio));
        byId(`btn-del-${p.id_portfolio}`) .addEventListener('click', () => triggerDelete(p.id_portfolio));
    });
    renderAIDashboard();
}

function cardHTML(p) {
    const initials = getInitials(p.full_name);
    const skills   = p.skills         || [];
    const certs    = p.certifications || [];
    const exps     = p.experiences    || [];
    const tech  = skills.filter(s => s.skill_type === 'technical').slice(0, 4);
    const exp   = skills.filter(s => s.skill_type === 'expertise').slice(0, 3);
    const extra = skills.length > 7 ? `<span class="pf-tag plus">+${skills.length - 7}</span>` : '';

    const tagsHtml = [
        ...tech.map(s => `<span class="pf-tag tech">${esc(s.skill_name)}</span>`),
        ...exp .map(s => `<span class="pf-tag exp">${esc(s.skill_name)}</span>`),
    ].join('') + extra;

    return `
    <div class="pf-card" id="card-${p.id_portfolio}">
        <div class="pf-card-hdr">
            <div class="pf-avatar">${initials}</div>
            <div class="pf-identity">
                <div class="pf-name">${esc(p.full_name)}</div>
                <div class="pf-title">${esc(p.professional_title)}</div>
                <span class="lvl-badge lvl-${p.experience_level}">⭐ ${LEVEL_LABELS[p.experience_level] || p.experience_level}</span>
            </div>
        </div>
        <div class="pf-card-body">
            <div class="pf-stats">
                <div class="pf-stat"><div class="pf-stat-n">${skills.length}</div><div class="pf-stat-l">Compétences</div></div>
                <div class="pf-stat"><div class="pf-stat-n">${certs.length}</div><div class="pf-stat-l">Certifications</div></div>
                <div class="pf-stat"><div class="pf-stat-n">${exps.length}</div><div class="pf-stat-l">Expériences</div></div>
            </div>
            ${skills.length ? `<div class="pf-tags">${tagsHtml}</div>` :
              '<p style="font-size:.78rem;color:#94a3b8;margin-bottom:1.1rem;"><i class="fa-solid fa-info-circle"></i> Aucune compétence ajoutée</p>'}
            <div class="pf-meta">
                ${p.location ? `<span class="pf-meta-item"><i class="fa-solid fa-location-dot"></i> ${esc(p.location)}</span>` : ''}
                ${p.remote_option == 1 ? `<span class="pf-meta-item" style="color:var(--ex-green);"><i class="fa-solid fa-wifi"></i> Remote</span>` : ''}
                <span class="pf-meta-item"><i class="fa-solid fa-bullseye"></i> ${OBJ_LABELS[p.career_objective] || p.career_objective}</span>
                <span class="avail-pill avail-${p.availability}">${AVAIL_LABELS[p.availability] || p.availability}</span>
            </div>
        </div>
        <div class="pf-card-actions">
            <button class="btn btn-outline btn-sm" id="btn-view-${p.id_portfolio}"><i class="fa-solid fa-eye"></i> Voir CV</button>
            <button class="btn btn-ghost btn-sm"   id="btn-edit-${p.id_portfolio}"><i class="fa-solid fa-pen"></i> Modifier</button>
            <button class="btn btn-danger btn-sm"  id="btn-del-${p.id_portfolio}"  style="margin-left:auto;"><i class="fa-solid fa-trash"></i></button>
        </div>
    </div>`;
}

/* ══════════════════════════════════════════════════════
   STEP BAR
══════════════════════════════════════════════════════ */
function buildStepBar() {
    const bar = byId('step-bar');
    bar.innerHTML = STEP_META.map((s, i) => {
        const n = i + 1;
        const line = i < STEP_META.length - 1 ? `<div class="step-line" id="sl-${n}"></div>` : '';
        return `
        <div class="step-ind">
            <div class="step-dot inactive" id="sd-${n}" title="${s.label}"><i class="fa-solid ${s.icon}"></i></div>
            <span class="step-lbl">${s.label}</span>
        </div>${line}`;
    }).join('');
}

function refreshStepUI() {
    for (let i = 1; i <= TOTAL_STEPS; i++) {
        const panel = byId(`sp-${i}`);
        const dot   = byId(`sd-${i}`);
        const line  = byId(`sl-${i}`);
        panel.classList.toggle('active', i === currentStep);
        dot.className = 'step-dot ' + (i < currentStep ? 'done' : i === currentStep ? 'active' : 'inactive');
        if (line) line.classList.toggle('done', i < currentStep);
    }
    byId('btn-prev').style.display = currentStep > 1 ? 'inline-flex' : 'none';
    const isLast = currentStep === TOTAL_STEPS;
    byId('btn-next').style.display = isLast ? 'none' : 'inline-flex';
    byId('btn-save').style.display = isLast ? 'inline-flex' : 'none';
    byId('step-counter').textContent = `Étape ${currentStep} sur ${TOTAL_STEPS}`;
}

/* ══════════════════════════════════════════════════════
   BUILDER MODAL — open / close
══════════════════════════════════════════════════════ */
function openCreate() {
    editingId = null;
    resetForm();
    byId('builder-title').textContent = 'Créer mon Portfolio Expert';
    byId('builder-sub').textContent   = 'Construisez votre profil CV professionnel';
    currentStep = 1;
    refreshStepUI();
    openOv('ov-builder');
}

function openEdit(id) {
    const p = portfolios.find(p => p.id_portfolio == id);
    if (!p) return;
    editingId = id;
    resetForm();
    fillForm(p);
    byId('builder-title').textContent = 'Modifier le Portfolio';
    byId('builder-sub').textContent   = p.full_name;
    currentStep = 1;
    refreshStepUI();
    openOv('ov-builder');
}

function fillForm(p) {
    setVal('f-name',     p.full_name || '');
    setVal('f-title',    p.professional_title || '');
    setVal('f-level',    p.experience_level || '');
    setVal('f-obj',      p.career_objective || '');
    setVal('f-bio',      p.bio || '');
    setVal('f-industry', p.preferred_industry || '');
    setVal('f-avail',    p.availability || '');
    setVal('f-location', p.location || '');
    setVal('f-linkedin', p.linkedin_url || '');
    setVal('f-github',   p.github_url || '');
    setVal('f-website',  p.website_url || '');

    remoteSel = p.remote_option == 1;
    byId('cb-remote').classList.toggle('on', remoteSel);

    techTags = (p.skills || []).filter(s => s.skill_type === 'technical').map(s => s.skill_name);
    expTags  = (p.skills || []).filter(s => s.skill_type === 'expertise').map(s => s.skill_name);
    renderTags('zone-tech', 'inp-tech', techTags, 'tech');
    renderTags('zone-exp',  'inp-exp',  expTags,  'exp');

    expBlocks = (p.experiences || []).map(e => ({
        job_title: e.job_title, company: e.company,
        start_date: e.start_date || '', end_date: e.end_date || '',
        is_current: e.is_current == 1,
        description: e.description || '', location: e.location || '',
    }));
    certBlocks = (p.certifications || []).map(c => ({
        cert_name: c.cert_name, issuer: c.issuer || '',
        issue_date: c.issue_date || '', expiry_date: c.expiry_date || '',
        cert_url: c.cert_url || '',
    }));
    renderExpBlocks();
    renderCertBlocks();
}

function wireBuilder() {
    byId('btn-open-builder').addEventListener('click', openCreate);
    byId('btn-close-builder').addEventListener('click', () => closeOv('ov-builder'));
    byId('ov-builder').addEventListener('click', e => { if (e.target === byId('ov-builder')) closeOv('ov-builder'); });
    byId('btn-next').addEventListener('click', () => { if (validateStep()) { currentStep++; refreshStepUI(); } });
    byId('btn-prev').addEventListener('click', () => { currentStep--; refreshStepUI(); });
    byId('btn-save').addEventListener('click', submitForm);
    byId('remote-toggle').addEventListener('click', () => {
        remoteSel = !remoteSel;
        byId('cb-remote').classList.toggle('on', remoteSel);
    });
    byId('btn-add-exp') .addEventListener('click', () => { expBlocks.push({ job_title:'', company:'', start_date:'', end_date:'', is_current:false, description:'', location:'' }); renderExpBlocks(); });
    byId('btn-add-cert').addEventListener('click', () => { certBlocks.push({ cert_name:'', issuer:'', issue_date:'', expiry_date:'', cert_url:'' }); renderCertBlocks(); });
}

/* ══════════════════════════════════════════════════════
   VALIDATION (JS only — no HTML required / pattern)
══════════════════════════════════════════════════════ */
function validateStep() {
    let ok = true;
    if (currentStep === 1) {
        ok = vField('f-name',  'e-name',  v => v.trim() !== '') && ok;
        ok = vField('f-title', 'e-title', v => v.trim() !== '') && ok;
        ok = vField('f-level', 'e-level', v => v !== '')        && ok;
        ok = vField('f-obj',   'e-obj',   v => v !== '')        && ok;
    }
    if (currentStep === 5) {
        ok = vField('f-avail', 'e-avail', v => v !== '') && ok;
    }
    return ok;
}

function vField(inputId, errId, fn) {
    const el  = byId(inputId);
    const err = byId(errId);
    const ok  = fn(el.value);
    el.classList.toggle('err', !ok);
    if (err) err.classList.toggle('show', !ok);
    return ok;
}

/* ══════════════════════════════════════════════════════
   TAG INPUTS
══════════════════════════════════════════════════════ */
function wireTagInputs() {
    wireTagZone('inp-tech', 'zone-tech', techTags, 'tech');
    wireTagZone('inp-exp',  'zone-exp',  expTags,  'exp');
}

function wireTagZone(inputId, zoneId, arr, cssType) {
    const input = byId(inputId);
    const commit = () => { addTags(input.value, arr, cssType, zoneId, inputId); };
    input.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ',') { e.preventDefault(); commit(); } });
    input.addEventListener('blur', commit);
    byId(zoneId).addEventListener('click', () => byId(inputId) && byId(inputId).focus());
}

function addTags(raw, arr, cssType, zoneId, inputId) {
    raw.split(',').map(t => t.trim()).filter(t => t.length > 0).forEach(t => {
        if (!arr.includes(t)) arr.push(t);
    });
    if (byId(inputId)) byId(inputId).value = '';
    renderTags(zoneId, inputId, arr, cssType);
}

function removeTag(arr, idx, cssType, zoneId, inputId) {
    arr.splice(idx, 1);
    renderTags(zoneId, inputId, arr, cssType);
}

function renderTags(zoneId, inputId, arr, cssType) {
    const zone = byId(zoneId);
    const chips = arr.map((t, i) => `
        <span class="tag-chip ${cssType}">
            ${esc(t)}
            <button class="tag-rm" onclick="removeTag(${cssType === 'tech' ? 'techTags' : 'expTags'},${i},'${cssType}','${zoneId}','${inputId}')" tabindex="-1">✕</button>
        </span>`).join('');
    zone.innerHTML = chips + `<input class="tag-txt" id="${inputId}" placeholder="${arr.length === 0 ? (cssType === 'tech' ? 'Ex : PHP, Docker…' : 'Ex : Cybersécurité…') : ''}">`;
    wireTagZone(inputId, zoneId, arr, cssType);
}

/* ══════════════════════════════════════════════════════
   DYNAMIC BLOCKS — Experiences
══════════════════════════════════════════════════════ */
function renderExpBlocks() {
    const host = byId('exp-host');
    if (expBlocks.length === 0) {
        host.innerHTML = `<div class="dyn-empty"><i class="fa-solid fa-briefcase"></i>Aucune expérience ajoutée. Cliquez sur le bouton ci-dessous.</div>`;
        return;
    }
    host.innerHTML = expBlocks.map((b, i) => `
    <div class="dyn-block" id="eb-${i}">
        <div class="dyn-block-hdr">
            <div class="dyn-block-title"><i class="fa-solid fa-briefcase"></i> Expérience ${i + 1}</div>
            <button class="btn-rm-block" onclick="removeExp(${i})"><i class="fa-solid fa-times"></i></button>
        </div>
        <div class="frow frow-2">
            <div class="fgroup"><label>Intitulé du Poste</label>
                <input class="finput" value="${esc(b.job_title)}" placeholder="Chef de Projet IT" onchange="expBlocks[${i}].job_title=this.value"></div>
            <div class="fgroup"><label>Entreprise</label>
                <input class="finput" value="${esc(b.company)}" placeholder="TechCorp SAS" onchange="expBlocks[${i}].company=this.value"></div>
        </div>
        <div class="frow frow-3">
            <div class="fgroup"><label>Début</label>
                <input type="date" class="finput" value="${b.start_date||''}" onchange="expBlocks[${i}].start_date=this.value"></div>
            <div class="fgroup"><label>Fin</label>
                <input type="date" class="finput" id="ee-${i}" value="${b.end_date||''}" onchange="expBlocks[${i}].end_date=this.value" ${b.is_current?'disabled':''}></div>
            <div class="fgroup"><label>Poste actuel</label>
                <label class="cb-row" onclick="toggleCurExp(${i})">
                    <div class="cb-box ${b.is_current?'on':''}" id="ecc-${i}"></div>
                    <span class="cb-lbl">En cours</span>
                </label></div>
        </div>
        <div class="frow frow-2">
            <div class="fgroup"><label>Lieu</label>
                <input class="finput" value="${esc(b.location)}" placeholder="Paris" onchange="expBlocks[${i}].location=this.value"></div>
        </div>
        <div class="frow frow-1">
            <div class="fgroup"><label>Description</label>
                <textarea class="ftextarea" placeholder="Vos responsabilités…" onchange="expBlocks[${i}].description=this.value">${esc(b.description)}</textarea>
            </div>
        </div>
    </div>`).join('');
}

function removeExp(i) { expBlocks.splice(i, 1); renderExpBlocks(); }
function toggleCurExp(i) { expBlocks[i].is_current = !expBlocks[i].is_current; renderExpBlocks(); }

/* ══════════════════════════════════════════════════════
   DYNAMIC BLOCKS — Certifications
══════════════════════════════════════════════════════ */
function renderCertBlocks() {
    const host = byId('cert-host');
    if (certBlocks.length === 0) {
        host.innerHTML = `<div class="dyn-empty"><i class="fa-solid fa-award"></i>Aucune certification ajoutée.</div>`;
        return;
    }
    host.innerHTML = certBlocks.map((c, i) => `
    <div class="dyn-block" id="cb-${i}">
        <div class="dyn-block-hdr">
            <div class="dyn-block-title"><i class="fa-solid fa-award" style="color:var(--ex-gold);"></i> Certification ${i + 1}</div>
            <button class="btn-rm-block" onclick="removeCert(${i})"><i class="fa-solid fa-times"></i></button>
        </div>
        <div class="frow frow-2">
            <div class="fgroup"><label>Nom de la Certification</label>
                <input class="finput" value="${esc(c.cert_name)}" placeholder="ISO 27001, PMP, AWS…" onchange="certBlocks[${i}].cert_name=this.value"></div>
            <div class="fgroup"><label>Organisme / Émetteur</label>
                <input class="finput" value="${esc(c.issuer)}" placeholder="PMI, Bureau Veritas…" onchange="certBlocks[${i}].issuer=this.value"></div>
        </div>
        <div class="frow frow-3">
            <div class="fgroup"><label>Date d'obtention</label>
                <input type="date" class="finput" value="${c.issue_date||''}" onchange="certBlocks[${i}].issue_date=this.value"></div>
            <div class="fgroup"><label>Date d'expiration</label>
                <input type="date" class="finput" value="${c.expiry_date||''}" onchange="certBlocks[${i}].expiry_date=this.value"></div>
            <div class="fgroup"><label>URL / Preuve</label>
                <input type="url" class="finput" value="${esc(c.cert_url)}" placeholder="https://…" onchange="certBlocks[${i}].cert_url=this.value"></div>
        </div>
    </div>`).join('');
}

function removeCert(i) { certBlocks.splice(i, 1); renderCertBlocks(); }

/* ══════════════════════════════════════════════════════
   SUBMIT (Create / Update)
══════════════════════════════════════════════════════ */
async function submitForm() {
    if (!validateStep()) return;

    // Flush any pending tag input
    ['inp-tech','inp-exp'].forEach(id => {
        const el = byId(id);
        if (el && el.value.trim()) {
            if (id === 'inp-tech') addTags(el.value, techTags, 'tech', 'zone-tech', 'inp-tech');
            else                  addTags(el.value, expTags,  'exp',  'zone-exp',  'inp-exp');
        }
    });

    const payload = {
        full_name:          byId('f-name').value.trim(),
        professional_title: byId('f-title').value.trim(),
        experience_level:   byId('f-level').value,
        career_objective:   byId('f-obj').value,
        bio:                byId('f-bio').value.trim(),
        preferred_industry: byId('f-industry').value,
        availability:       byId('f-avail').value,
        location:           byId('f-location').value.trim(),
        remote_option:      remoteSel ? 1 : 0,
        linkedin_url:       byId('f-linkedin').value.trim(),
        github_url:         byId('f-github').value.trim(),
        website_url:        byId('f-website').value.trim(),
        skills: [
            ...techTags.map(n => ({ skill_name: n, skill_type: 'technical',  skill_level: 'intermediate' })),
            ...expTags .map(n => ({ skill_name: n, skill_type: 'expertise', skill_level: 'advanced'     })),
        ],
        experiences:    expBlocks.map(b => ({...b, is_current: b.is_current ? 1 : 0})).filter(b => b.job_title.trim()),
        certifications: certBlocks.filter(c => c.cert_name.trim()),
    };
    if (editingId) payload.id_portfolio = editingId;

    const action = editingId ? 'update' : 'create';
    const btn    = byId('btn-save');
    btn.disabled = true;
    btn.innerHTML = '<span class="spin"></span> Sauvegarde…';

    try {
        const res  = await fetch(`${API}?action=${action}`, {
            method : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body   : JSON.stringify(payload),
        });
        const data = await res.json();
        if (data.success) {
            if (editingId) {
                const idx = portfolios.findIndex(p => p.id_portfolio == editingId);
                if (idx !== -1) portfolios[idx] = data.portfolio;
            } else {
                portfolios.push(data.portfolio);
            }
            renderGrid();
            closeOv('ov-builder');
            toast('success', data.message || 'Sauvegardé avec succès !');
        } else {
            toast('error', data.error || 'Une erreur est survenue');
        }
    } catch (_) {
        toast('error', 'Erreur de connexion. Veuillez réessayer.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Sauvegarder';
    }
}

/* ══════════════════════════════════════════════════════
   CV PREVIEW MODAL
══════════════════════════════════════════════════════ */
function wirePreview() {
    byId('btn-close-prev').addEventListener('click', () => closeOv('ov-preview'));
    byId('ov-preview').addEventListener('click', e => { if (e.target === byId('ov-preview')) closeOv('ov-preview'); });
}

function openPreview(id) {
    previewId = id;
    const p   = portfolios.find(p => p.id_portfolio == id);
    if (!p) return;

    const skills  = p.skills         || [];
    const certs   = p.certifications || [];
    const exps    = p.experiences    || [];
    const tech    = skills.filter(s => s.skill_type === 'technical');
    const expSk   = skills.filter(s => s.skill_type === 'expertise');
    const init    = getInitials(p.full_name);

    const skillBars = tech.map(s => `
        <div class="cv-skill-entry">
            <div class="cv-skill-lbl">${esc(s.skill_name)}</div>
            <div class="cv-skill-track"><div class="cv-skill-fill" style="width:${SKILL_LEVELS[s.skill_level]||50}%"></div></div>
        </div>`).join('');

    const certList = certs.map(c => `
        <div class="cv-cert-entry">
            <i class="fa-solid fa-star"></i>
            <div><div class="cv-cert-nm">${esc(c.cert_name)}</div>${c.issuer?`<div class="cv-cert-iss">${esc(c.issuer)}</div>`:''}</div>
        </div>`).join('');

    const expList = exps.map(e => `
        <div class="cv-tl-item">
            <div class="cv-tl-title">${esc(e.job_title)}</div>
            <div class="cv-tl-company">${esc(e.company)}</div>
            <div class="cv-tl-dates"><i class="fa-regular fa-calendar"></i> ${fmtDate(e.start_date)} — ${e.is_current==1?'Présent':fmtDate(e.end_date)}</div>
            ${e.description?`<div class="cv-tl-desc">${esc(e.description)}</div>`:''}
        </div>`).join('');

    const expTagList = expSk.map(s => `<span class="cv-exp-tag">${esc(s.skill_name)}</span>`).join('');

    byId('cv-doc-content').innerHTML = `
    <div class="cv-left">
        <div class="cv-av-big">${init}</div>
        <div id="cv-name">${esc(p.full_name)}</div>
        <div id="cv-title">${esc(p.professional_title)}</div>
        <div id="cv-level"><span class="lvl-badge lvl-${p.experience_level}">${LEVEL_LABELS[p.experience_level]||p.experience_level}</span></div>

        <div class="cv-sec-ttl">Contact</div>
        ${p.location  ? `<div class="cv-contact-item"><i class="fa-solid fa-location-dot"></i>${esc(p.location)}</div>` : ''}
        ${p.remote_option==1 ? `<div class="cv-contact-item"><i class="fa-solid fa-wifi"></i>Télétravail OK</div>` : ''}
        ${p.linkedin_url ? `<div class="cv-contact-item"><i class="fa-brands fa-linkedin"></i><a href="${esc(p.linkedin_url)}" target="_blank">LinkedIn</a></div>` : ''}
        ${p.github_url   ? `<div class="cv-contact-item"><i class="fa-brands fa-github"></i><a href="${esc(p.github_url)}" target="_blank">GitHub</a></div>` : ''}
        ${p.website_url  ? `<div class="cv-contact-item"><i class="fa-solid fa-globe"></i><a href="${esc(p.website_url)}" target="_blank">Site Web</a></div>` : ''}

        ${tech.length ? `<div class="cv-sec-ttl" style="margin-top:1.5rem;">Compétences</div>${skillBars}` : ''}
        ${certs.length ? `<div class="cv-sec-ttl" style="margin-top:1.5rem;">Certifications</div>${certList}` : ''}
    </div>

    <div class="cv-right">
        <div class="cv-r-section">
            <div class="cv-r-name">${esc(p.full_name)}</div>
            <div class="cv-r-subtitle">${esc(p.professional_title)}${p.preferred_industry?' · '+esc(p.preferred_industry):''}</div>
        </div>
        ${p.bio ? `<div class="cv-r-section"><div class="cv-sec-ttl dark">À Propos</div><div class="cv-bio-box">${esc(p.bio)}</div></div>` : ''}
        ${exps.length ? `<div class="cv-r-section"><div class="cv-sec-ttl dark">Expériences Professionnelles</div><div class="cv-timeline">${expList}</div></div>` : ''}
        ${expSk.length ? `<div class="cv-r-section"><div class="cv-sec-ttl dark">Domaines d'Expertise</div><div class="cv-exp-tags">${expTagList}</div></div>` : ''}
        <div class="cv-r-section">
            <div class="cv-sec-ttl dark">Disponibilité &amp; Objectif</div>
            <div class="cv-avail-row">
                <span class="avail-pill avail-${p.availability}" style="font-size:.82rem;padding:.35rem .85rem;">${AVAIL_LABELS[p.availability]||p.availability}</span>
                <span class="pf-tag exp">${OBJ_LABELS[p.career_objective]||p.career_objective}</span>
            </div>
        </div>
    </div>`;

    byId('btn-edit-from-prev').onclick = () => { closeOv('ov-preview'); openEdit(id); };
    openOv('ov-preview');
}

/* ══════════════════════════════════════════════════════
   DELETE
══════════════════════════════════════════════════════ */
function wireDelete() {
    byId('btn-cancel-del').addEventListener('click', () => closeOv('ov-delete'));
    byId('ov-delete').addEventListener('click', e => { if (e.target === byId('ov-delete')) closeOv('ov-delete'); });
    byId('btn-confirm-del').addEventListener('click', performDelete);
}

function triggerDelete(id) {
    deleteTargetId = id;
    openOv('ov-delete');
}

async function performDelete() {
    if (!deleteTargetId) return;
    const btn = byId('btn-confirm-del');
    btn.disabled = true;
    btn.innerHTML = '<span class="spin"></span>';
    try {
        const res  = await fetch(`${API}?action=delete`, {
            method : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body   : JSON.stringify({ id_portfolio: deleteTargetId }),
        });
        const data = await res.json();
        if (data.success) {
            portfolios = portfolios.filter(p => p.id_portfolio != deleteTargetId);
            renderGrid();
            closeOv('ov-delete');
            toast('success', 'Portfolio supprimé avec succès.');
        } else {
            toast('error', data.error || 'Erreur lors de la suppression');
        }
    } catch (_) {
        toast('error', 'Erreur de connexion.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-trash"></i> Supprimer';
    }
}

/* ══════════════════════════════════════════════════════
   FORM RESET
══════════════════════════════════════════════════════ */
function resetForm() {
    ['f-name','f-title','f-bio','f-location','f-linkedin','f-github','f-website'].forEach(id => setVal(id, ''));
    ['f-level','f-obj','f-industry','f-avail'].forEach(id => setVal(id, ''));
    document.querySelectorAll('.ferr').forEach(el => el.classList.remove('show'));
    document.querySelectorAll('.finput, .fselect').forEach(el => el.classList.remove('err'));
    techTags = []; expTags = []; expBlocks = []; certBlocks = [];
    remoteSel = false;
    byId('cb-remote').classList.remove('on');
    renderTags('zone-tech', 'inp-tech', techTags, 'tech');
    renderTags('zone-exp',  'inp-exp',  expTags,  'exp');
    renderExpBlocks();
    renderCertBlocks();
}

/* ══════════════════════════════════════════════════════
   MODAL OVERLAY HELPERS
══════════════════════════════════════════════════════ */
function openOv(id)  { byId(id).classList.add('open');    document.body.style.overflow = 'hidden'; }
function closeOv(id) { byId(id).classList.remove('open'); document.body.style.overflow = ''; }

/* ══════════════════════════════════════════════════════
   TOAST
══════════════════════════════════════════════════════ */
function toast(type, msg) {
    const host = byId('toast-host');
    const el   = document.createElement('div');
    el.className = `toast ${type}`;
    el.innerHTML = `<span class="toast-ic"><i class="fa-solid ${type==='success'?'fa-circle-check':'fa-circle-exclamation'}"></i></span><span class="toast-msg">${msg}</span>`;
    host.appendChild(el);
    setTimeout(() => el.remove(), 4200);
}

/* ══════════════════════════════════════════════════════
   UTILS
══════════════════════════════════════════════════════ */
function byId(id)   { return document.getElementById(id); }
function setVal(id, v) { const el = byId(id); if (el) el.value = v; }
function esc(str)   {
    if (str === null || str === undefined) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function getInitials(name) {
    if (!name) return '?';
    return name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase();
}
function fmtDate(d) {
    if (!d) return '—';
    const dt = new Date(d);
    return isNaN(dt) ? d : dt.toLocaleDateString('fr-FR', { year: 'numeric', month: 'short' });
}
</script>
</body>
</html>
