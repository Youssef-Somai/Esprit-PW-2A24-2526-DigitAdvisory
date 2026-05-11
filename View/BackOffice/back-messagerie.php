<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../FrontOffice/login.php');
    exit;
}
$apiUrl = '../traitement/messageAPI.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back Office | Gestion Messagerie</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .sidebar { background: var(--dark); color: white; }
        .sidebar .menu-item { color: var(--gray-light); }
        .sidebar .menu-item:hover, .sidebar .menu-item.active { background: rgba(255,255,255,.1); color: white; border-left-color: var(--accent); }
        .sidebar-header { border-bottom: 1px solid rgba(255,255,255,.1); }
        .sidebar-header .logo { color: white; }
        .user-profile-widget { background: rgba(0,0,0,.2); border-top: 1px solid rgba(255,255,255,.1); }
        .dashboard-container { display: flex; min-height: 100vh; }
        .sidebar { width: 280px; display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100; }
        .sidebar-header { padding: 1.5rem; display: flex; align-items: center; }
        .sidebar-menu { padding: 1rem 0; flex: 1; overflow-y: auto; }
        .menu-item { padding: .75rem 1.5rem; display: flex; align-items: center; gap: 1rem; font-weight: 500; cursor: pointer; transition: var(--transition); border-left: 3px solid transparent; text-decoration: none; }
        .menu-item i { width: 20px; text-align: center; font-size: 1.1rem; }
        .user-profile-widget { padding: 1rem 1.5rem; display: flex; align-items: center; gap: 1rem; }
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--accent); color: white; display: flex; justify-content: center; align-items: center; font-weight: 600; }
        .main-content { flex: 1; margin-left: 280px; padding: 2rem; background: #f1f5f9; min-height: 100vh; }
        .top-navbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; background: white; padding: 1rem 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); }
        .card { background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-sm); margin-bottom: 2rem; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.25rem 1.5rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 1rem; }
        .stat-icon { width: 50px; height: 50px; border-radius: var(--radius); display: flex; justify-content: center; align-items: center; font-size: 1.4rem; flex-shrink: 0; }
        .stat-icon.blue { background: rgba(37,99,235,.1); color: var(--primary); }
        .stat-icon.cyan { background: rgba(14,165,233,.1); color: var(--secondary); }
        .stat-icon.green { background: rgba(16,185,129,.1); color: var(--success); }
        .stat-icon.amber { background: rgba(245,158,11,.1); color: var(--warning); }
        .stat-val { font-size: 1.6rem; font-weight: 700; font-family: 'Poppins',sans-serif; line-height: 1; }
        .stat-label { font-size: .82rem; color: var(--gray); margin-top: .2rem; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: .9rem 1rem; text-align: left; border-bottom: 1px solid var(--gray-light); font-size: .88rem; }
        .data-table th { color: var(--gray); font-weight: 500; background: #f8fafc; }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table tr:hover td { background: #f8fafc; }
        .badge { padding: .2rem .65rem; border-radius: 999px; font-size: .78rem; font-weight: 600; display: inline-block; }
        .badge.expert { background: rgba(14,165,233,.1); color: var(--secondary); }
        .badge.entreprise { background: rgba(37,99,235,.1); color: var(--primary); }
        .badge.success { background: rgba(16,185,129,.1); color: var(--success); }
        .btn-sm { padding: .35rem .75rem; font-size: .82rem; border-radius: 6px; }
        .search-bar { display: flex; align-items: center; gap: .75rem; margin-bottom: 1.25rem; }
        .search-bar input { flex: 1; max-width: 320px; padding: .5rem .9rem; border: 1px solid var(--gray-light); border-radius: 8px; font-size: .88rem; font-family: var(--font-main); outline: none; }
        .search-bar input:focus { border-color: var(--accent); }

        /* Message viewer modal */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 999; align-items: center; justify-content: center; }
        .modal-overlay.open { display: flex; }
        .modal-box { background: white; border-radius: var(--radius-lg); width: 600px; max-width: 95vw; max-height: 85vh; display: flex; flex-direction: column; box-shadow: var(--shadow-lg); overflow: hidden; }
        .modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--gray-light); display: flex; align-items: center; justify-content: space-between; }
        .modal-header h3 { margin: 0; font-size: 1rem; }
        .modal-messages { flex: 1; overflow-y: auto; padding: 1.25rem; display: flex; flex-direction: column; gap: .65rem; background: #f8fafc; }
        .admin-msg { background: white; border-radius: 10px; padding: .75rem 1rem; box-shadow: var(--shadow-sm); font-size: .88rem; }
        .admin-msg .msg-sender { font-weight: 600; font-size: .82rem; color: var(--primary); margin-bottom: .3rem; }
        .admin-msg .msg-time { font-size: .72rem; color: var(--gray); margin-top: .3rem; }
        .admin-msg.deleted { opacity: .5; font-style: italic; }
        .modal-footer { padding: 1rem 1.5rem; border-top: 1px solid var(--gray-light); display: flex; justify-content: flex-end; gap: .75rem; }
        #loading { display: none; text-align: center; padding: 3rem 0; color: var(--gray); }
    </style>
</head>
<body class="admin-theme">
<div class="dashboard-container">
    <aside class="sidebar admin-sidebar">
        <div class="sidebar-header">
            <div class="logo"><i class="fa-solid fa-user-shield text-accent"></i> Admin Panel</div>
        </div>
        <div class="sidebar-menu">
            <a href="back-utilisateur.php" class="menu-item"><i class="fa-solid fa-users"></i> Gestion Utilisateurs</a>
            <a href="back-quiz.php" class="menu-item"><i class="fa-solid fa-list-check"></i> Gestion Quiz</a>
            <a href="back-portfolio.php" class="menu-item"><i class="fa-solid fa-folder-open"></i> Gestion Portfolios</a>
            <a href="back-offres.php" class="menu-item"><i class="fa-solid fa-briefcase"></i> Gestion Offres</a>
            <a href="back-certification.php" class="menu-item"><i class="fa-solid fa-award"></i> Gestion Certifications</a>
            <a href="back-messagerie.php" class="menu-item active"><i class="fa-solid fa-comments"></i> Gestion Messagerie</a>
        </div>
        <div class="user-profile-widget">
            <div class="user-avatar"><i class="fa-solid fa-user-shield"></i></div>
            <div>
                <h4 style="font-size:.95rem;margin-bottom:.2rem;color:white;">Admin Système</h4>
                <span style="font-size:.8rem;color:var(--gray-light);">Superviseur</span>
            </div>
            <a href="../FrontOffice/login.php" style="margin-left:auto;color:var(--danger);"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
        </div>
    </aside>

    <main class="main-content">
        <div class="top-navbar">
            <h2 style="margin:0;font-size:1.5rem;">Administration — Messagerie</h2>
            <span class="badge" style="background:rgba(245,158,11,.1);color:var(--warning);font-size:.9rem;padding:.35rem .9rem;">
                <i class="fa-solid fa-lock"></i> Espace Sécurisé Admin
            </span>
        </div>

        <section class="fade-in-up">
            <!-- Stats -->
            <div class="stats-grid" id="statsGrid">
                <div class="stat-card"><div class="stat-icon blue"><i class="fa-solid fa-message"></i></div>
                    <div><div class="stat-val" id="st-msgs">—</div><div class="stat-label">Messages envoyés</div></div></div>
                <div class="stat-card"><div class="stat-icon cyan"><i class="fa-solid fa-comments"></i></div>
                    <div><div class="stat-val" id="st-convs">—</div><div class="stat-label">Conversations</div></div></div>
                <div class="stat-card"><div class="stat-icon green"><i class="fa-solid fa-calendar-day"></i></div>
                    <div><div class="stat-val" id="st-today">—</div><div class="stat-label">Messages aujourd'hui</div></div></div>
                <div class="stat-card"><div class="stat-icon amber"><i class="fa-solid fa-users"></i></div>
                    <div><div class="stat-val" id="st-active">—</div><div class="stat-label">Utilisateurs actifs (7j)</div></div></div>
            </div>

            <!-- Conversations table -->
            <div class="card">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
                    <h3 style="margin:0;">Historique des Conversations</h3>
                    <button class="btn btn-outline btn-sm" onclick="loadConvs()"><i class="fa-solid fa-rotate"></i> Actualiser</button>
                </div>
                <div class="search-bar">
                    <i class="fa-solid fa-magnifying-glass" style="color:var(--gray);"></i>
                    <input type="text" id="searchInput" placeholder="Rechercher participants..." oninput="filterConvs(this.value)">
                </div>
                <div id="loading"><i class="fa-solid fa-spinner fa-spin fa-2x"></i><p style="margin-top:.75rem;">Chargement...</p></div>
                <div style="overflow-x:auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Participants</th>
                                <th>Rôles</th>
                                <th>Messages</th>
                                <th>Dernier échange</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="convTableBody">
                            <tr><td colspan="6" style="text-align:center;color:var(--gray);padding:2rem;">Chargement...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</div>

<!-- Message Viewer Modal -->
<div class="modal-overlay" id="msgModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalTitle"><i class="fa-solid fa-comments"></i> Historique</h3>
            <button onclick="closeModal()" style="background:none;border:none;font-size:1.25rem;color:var(--gray);cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-messages" id="modalMessages">
            <div style="text-align:center;color:var(--gray);padding:2rem;"><i class="fa-solid fa-spinner fa-spin fa-2x"></i></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline btn-sm" onclick="closeModal()">Fermer</button>
            <button class="btn btn-sm" id="deleteConvBtn" style="background:var(--danger);color:white;border:none;border-radius:6px;cursor:pointer;">
                <i class="fa-solid fa-trash"></i> Supprimer la conversation
            </button>
        </div>
    </div>
</div>

<script>
const API = '<?= $apiUrl ?>';
let allConvs = [], currentAdminConvId = null;

document.addEventListener('DOMContentLoaded', () => { loadStats(); loadConvs(); });

// ─── Stats ────────────────────────────────────────────────────────────────────
function loadStats() {
    fetch(API + '?action=admin_get_stats')
        .then(r => r.json())
        .then(s => {
            document.getElementById('st-msgs').textContent   = s.total_messages ?? 0;
            document.getElementById('st-convs').textContent  = s.total_conversations ?? 0;
            document.getElementById('st-today').textContent  = s.messages_today ?? 0;
            document.getElementById('st-active').textContent = s.active_users ?? 0;
        }).catch(() => {});
}

// ─── Conversations ────────────────────────────────────────────────────────────
function loadConvs() {
    document.getElementById('loading').style.display = 'block';
    document.getElementById('convTableBody').innerHTML = '';
    fetch(API + '?action=admin_get_all')
        .then(r => r.json())
        .then(data => {
            allConvs = data;
            renderConvs(data);
            document.getElementById('loading').style.display = 'none';
        }).catch(() => {
            document.getElementById('loading').style.display = 'none';
            document.getElementById('convTableBody').innerHTML =
                '<tr><td colspan="6" style="text-align:center;color:var(--danger);">Erreur de chargement</td></tr>';
        });
}

function renderConvs(convs) {
    const tbody = document.getElementById('convTableBody');
    if (!convs.length) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--gray);padding:2rem;">Aucune conversation</td></tr>';
        return;
    }
    tbody.innerHTML = convs.map(c => {
        const lastAt = c.last_at ? formatDateTime(c.last_at) : formatDateTime(c.created_at);
        const preview = c.last_content ? escHtml(c.last_content.substring(0, 50)) + (c.last_content.length > 50 ? '…' : '') : (c.last_type === 'file' ? '📎 Fichier' : c.last_type === 'audio' ? '🎤 Audio' : '—');
        return `<tr>
            <td style="color:var(--gray);font-size:.8rem;">#${c.id_conversation}</td>
            <td>
                <div style="font-weight:600;font-size:.88rem;">${escHtml(c.name_user1)}</div>
                <div style="font-size:.8rem;color:var(--gray);">${escHtml(c.name_user2)}</div>
                ${c.last_content || c.last_type ? `<div style="font-size:.78rem;color:var(--gray);margin-top:.2rem;font-style:italic;">${preview}</div>` : ''}
            </td>
            <td>
                <span class="badge ${c.role_user1}">${c.role_user1 || '—'}</span><br>
                <span class="badge ${c.role_user2}" style="margin-top:.25rem;">${c.role_user2 || '—'}</span>
            </td>
            <td><span class="badge success">${c.msg_count ?? 0} msg</span></td>
            <td style="font-size:.82rem;color:var(--gray);">${lastAt}</td>
            <td>
                <button class="btn btn-outline btn-sm" onclick="viewConv(${c.id_conversation}, '${escHtml(c.name_user1)} ↔ ${escHtml(c.name_user2)}')">
                    <i class="fa-solid fa-eye"></i> Voir
                </button>
            </td>
        </tr>`;
    }).join('');
}

function filterConvs(q) {
    q = q.toLowerCase();
    renderConvs(allConvs.filter(c =>
        c.name_user1.toLowerCase().includes(q) || c.name_user2.toLowerCase().includes(q)
    ));
}

// ─── Modal ────────────────────────────────────────────────────────────────────
function viewConv(id, title) {
    currentAdminConvId = id;
    document.getElementById('modalTitle').innerHTML = `<i class="fa-solid fa-comments"></i> ${escHtml(title)}`;
    document.getElementById('msgModal').classList.add('open');
    document.getElementById('modalMessages').innerHTML =
        '<div style="text-align:center;color:var(--gray);padding:2rem;"><i class="fa-solid fa-spinner fa-spin fa-2x"></i></div>';

    document.getElementById('deleteConvBtn').onclick = () => adminDeleteConv(id);

    fetch(API + '?action=admin_get_messages&id_conversation=' + id)
        .then(r => r.json())
        .then(msgs => {
            const box = document.getElementById('modalMessages');
            if (!msgs.length) {
                box.innerHTML = '<div style="text-align:center;color:var(--gray);padding:2rem;">Aucun message dans cette conversation.</div>';
                return;
            }
            box.innerHTML = msgs.map(m => {
                const cls = m.is_deleted ? 'admin-msg deleted' : 'admin-msg';
                let content = '';
                if (m.is_deleted) {
                    content = '<i class="fa-solid fa-ban"></i> Message supprimé';
                } else if (m.type === 'text') {
                    content = escHtml(m.content || '');
                } else if (m.type === 'audio') {
                    content = '<i class="fa-solid fa-file-audio"></i> Message vocal';
                } else if (m.type === 'file') {
                    content = `<i class="fa-solid fa-paperclip"></i> ${escHtml(m.file_name || 'Fichier')}`;
                }
                const edited = m.is_edited ? ' <span style="font-size:.7rem;color:var(--gray);">(modifié)</span>' : '';
                return `<div class="${cls}">
                    <div class="msg-sender"><i class="fa-solid fa-user-circle"></i> ${escHtml(m.sender_name)}</div>
                    <div>${content}${edited}</div>
                    <div class="msg-time"><i class="fa-solid fa-clock"></i> ${formatDateTime(m.created_at)}</div>
                </div>`;
            }).join('');
            box.scrollTop = box.scrollHeight;
        });
}

function closeModal() {
    document.getElementById('msgModal').classList.remove('open');
    currentAdminConvId = null;
}

function adminDeleteConv(id) {
    if (!confirm('Supprimer définitivement cette conversation et tous ses messages ?')) return;
    const fd = new FormData();
    fd.append('action', 'admin_delete_conv');
    fd.append('id_conversation', id);
    fetch(API, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                closeModal();
                loadConvs();
                loadStats();
            } else {
                alert('Erreur lors de la suppression.');
            }
        });
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
function formatDateTime(dt) {
    if (!dt) return '—';
    const d = new Date(dt.replace(' ', 'T'));
    return d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' })
         + ' ' + d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
}

function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Close modal on overlay click
document.getElementById('msgModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
</body>
</html>
