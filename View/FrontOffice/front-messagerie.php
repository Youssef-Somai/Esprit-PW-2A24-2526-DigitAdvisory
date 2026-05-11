<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'entreprise') {
    header('Location: login.php');
    exit;
}
$me       = $_SESSION['user'];
$myId     = (int)$me['id_user'];
$myName   = $me['nom_entreprise'] ?? (trim(($me['prenom'] ?? '') . ' ' . ($me['nom'] ?? '')) ?: $me['email']);
$myInit   = strtoupper(mb_substr($myName, 0, 1)) . strtoupper(mb_substr(strstr($myName, ' ') ?: ' ', 1, 1));
$apiUrl   = '../traitement/messageAPI.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Entreprise | Messagerie</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        body { background: #f1f5f9; }
        .dashboard-container { display: flex; min-height: 100vh; }
        .sidebar { width: 280px; background: white; box-shadow: var(--shadow-md); display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100; }
        .sidebar-header { padding: 1.5rem; border-bottom: 1px solid var(--gray-light); display: flex; align-items: center; }
        .sidebar-menu { padding: 1rem 0; flex: 1; overflow-y: auto; }
        .menu-item { padding: .75rem 1.5rem; display: flex; align-items: center; gap: 1rem; color: var(--gray); font-weight: 500; cursor: pointer; transition: var(--transition); border-left: 3px solid transparent; text-decoration: none; }
        .menu-item:hover, .menu-item.active { background: rgba(37,99,235,.05); color: var(--primary); }
        .menu-item.active { border-left-color: var(--primary); }
        .menu-item i { width: 20px; text-align: center; font-size: 1.1rem; }
        .user-profile-widget { padding: 1rem 1.5rem; border-top: 1px solid var(--gray-light); display: flex; align-items: center; gap: 1rem; background: white; }
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--primary); color: white; display: flex; justify-content: center; align-items: center; font-weight: 600; font-size: .85rem; }
        .main-content { flex: 1; margin-left: 280px; padding: 2rem; }
        .top-navbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; background: white; padding: 1rem 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); }

        /* ── Chat layout ── */
        .chat-wrap { display: flex; height: calc(100vh - 160px); min-height: 500px; border: 1px solid var(--gray-light); border-radius: var(--radius-lg); overflow: hidden; background: white; box-shadow: var(--shadow-sm); }

        /* Sidebar conversations */
        .chat-sidebar { width: 300px; min-width: 300px; border-right: 1px solid var(--gray-light); display: flex; flex-direction: column; background: white; }
        .chat-sidebar-head { padding: .75rem 1rem; border-bottom: 1px solid var(--gray-light); display: flex; align-items: center; gap: .5rem; }
        .chat-sidebar-head h3 { font-size: .95rem; font-weight: 600; flex: 1; margin: 0; }
        .search-box { position: relative; padding: .6rem .75rem; border-bottom: 1px solid var(--gray-light); }
        .search-box input { width: 100%; padding: .45rem .75rem .45rem 2rem; border: 1px solid var(--gray-light); border-radius: 999px; font-size: .85rem; font-family: var(--font-main); outline: none; }
        .search-box input:focus { border-color: var(--primary); }
        .search-box i { position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--gray); font-size: .8rem; }
        .conv-list { flex: 1; overflow-y: auto; }
        .conv-item { padding: .85rem 1rem; border-bottom: 1px solid var(--gray-light); display: flex; gap: .75rem; cursor: pointer; transition: background .15s; align-items: center; position: relative; }
        .conv-item:hover { background: #eff6ff; }
        .conv-item.active { background: #dbeafe; }
        .conv-avatar { width: 42px; height: 42px; min-width: 42px; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-weight: 700; font-size: .85rem; color: white; }
        .conv-info { flex: 1; min-width: 0; }
        .conv-info h4 { font-size: .88rem; margin: 0 0 .15rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .conv-info p { font-size: .78rem; color: var(--gray); margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .conv-meta { display: flex; flex-direction: column; align-items: flex-end; gap: .25rem; min-width: 52px; }
        .conv-time { font-size: .7rem; color: var(--gray); }
        .unread-badge { background: var(--primary); color: white; border-radius: 999px; font-size: .65rem; font-weight: 700; padding: .1rem .45rem; min-width: 18px; text-align: center; }
        .no-convs { padding: 2rem 1rem; text-align: center; color: var(--gray); font-size: .9rem; }

        /* Main chat area */
        .chat-main { flex: 1; display: flex; flex-direction: column; background: #f8fafc; min-width: 0; }
        .chat-header { padding: .85rem 1.25rem; background: white; border-bottom: 1px solid var(--gray-light); display: flex; align-items: center; gap: 1rem; }
        .chat-header-info { flex: 1; }
        .chat-header-info h4 { font-size: .95rem; margin: 0 0 .1rem; }
        .chat-header-info span { font-size: .75rem; color: var(--gray); }
        .chat-messages { flex: 1; padding: 1.25rem; overflow-y: auto; display: flex; flex-direction: column; gap: .75rem; }
        .chat-empty { display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100%; color: var(--gray); gap: 1rem; }
        .chat-empty i { font-size: 3rem; opacity: .3; }
        .chat-empty p { font-size: .9rem; }

        /* Messages */
        .msg-wrapper { display: flex; flex-direction: column; }
        .msg-wrapper.sent { align-items: flex-end; }
        .msg-wrapper.received { align-items: flex-start; }
        .msg-bubble { max-width: 65%; padding: .65rem .9rem; border-radius: 16px; font-size: .9rem; line-height: 1.5; position: relative; word-break: break-word; }
        .msg-wrapper.received .msg-bubble { background: white; box-shadow: var(--shadow-sm); border-bottom-left-radius: 4px; }
        .msg-wrapper.sent .msg-bubble { background: var(--primary); color: white; border-bottom-right-radius: 4px; }
        .msg-deleted { font-style: italic; opacity: .55; }
        .msg-meta { font-size: .68rem; color: var(--gray); margin-top: .2rem; display: flex; align-items: center; gap: .4rem; }
        .msg-wrapper.sent .msg-meta { justify-content: flex-end; }
        .msg-actions { opacity: 0; display: flex; gap: .3rem; transition: opacity .15s; margin-bottom: .2rem; }
        .msg-wrapper:hover .msg-actions { opacity: 1; }
        .msg-actions button { background: white; border: 1px solid var(--gray-light); border-radius: 6px; padding: .2rem .45rem; font-size: .75rem; cursor: pointer; color: var(--gray); transition: all .15s; }
        .msg-actions button:hover { color: var(--primary); border-color: var(--primary); }
        .msg-actions button.del:hover { color: var(--danger); border-color: var(--danger); }
        .msg-edit-input { width: 100%; padding: .4rem .65rem; border: 1px solid var(--primary); border-radius: 8px; font-size: .88rem; font-family: var(--font-main); outline: none; margin-top: .3rem; }

        /* File/audio attachments */
        .msg-file { display: flex; align-items: center; gap: .6rem; padding: .5rem .75rem; background: rgba(255,255,255,.15); border-radius: 8px; margin-top: .3rem; text-decoration: none; color: inherit; font-size: .85rem; }
        .msg-wrapper.received .msg-file { background: #f1f5f9; color: var(--dark); }
        .msg-file i { font-size: 1.1rem; }
        .msg-img { max-width: 220px; border-radius: 10px; display: block; margin-top: .3rem; cursor: pointer; }
        audio.msg-audio { max-width: 240px; margin-top: .3rem; }

        /* Chat input */
        .chat-input-area { padding: .85rem 1.25rem; background: white; border-top: 1px solid var(--gray-light); display: flex; align-items: flex-end; gap: .6rem; }
        .chat-input-area textarea { flex: 1; padding: .65rem .9rem; border: 1px solid var(--gray-light); border-radius: 20px; font-family: var(--font-main); font-size: .9rem; outline: none; resize: none; max-height: 100px; line-height: 1.45; transition: border-color .15s; }
        .chat-input-area textarea:focus { border-color: var(--primary); }
        .input-btn { width: 36px; height: 36px; border: none; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: .9rem; transition: all .15s; flex-shrink: 0; }
        .input-btn.attach { background: var(--gray-light); color: var(--gray); }
        .input-btn.attach:hover { background: #e2e8f0; color: var(--primary); }
        .input-btn.record { background: var(--gray-light); color: var(--gray); }
        .input-btn.record:hover { background: #fee2e2; color: var(--danger); }
        .input-btn.record.recording { background: var(--danger); color: white; animation: pulse 1s infinite; }
        .input-btn.send { background: var(--primary); color: white; width: 38px; height: 38px; }
        .input-btn.send:hover { background: var(--primary-hover); }
        .input-btn.send:disabled { opacity: .5; cursor: not-allowed; }
        .rec-timer { font-size: .78rem; color: var(--danger); font-weight: 600; align-self: center; }
        @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: .6; } }

        /* Modal */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 999; align-items: center; justify-content: center; }
        .modal-overlay.open { display: flex; }
        .modal-box { background: white; border-radius: var(--radius-lg); padding: 1.5rem; width: 440px; max-width: 95vw; max-height: 80vh; display: flex; flex-direction: column; box-shadow: var(--shadow-lg); }
        .modal-box h3 { margin: 0 0 1rem; font-size: 1.1rem; }
        .modal-search { padding: .5rem .75rem; border: 1px solid var(--gray-light); border-radius: 8px; width: 100%; font-size: .9rem; font-family: var(--font-main); outline: none; margin-bottom: .75rem; }
        .modal-search:focus { border-color: var(--primary); }
        .user-list { flex: 1; overflow-y: auto; }
        .user-list-item { display: flex; align-items: center; gap: .75rem; padding: .75rem; border-radius: 8px; cursor: pointer; transition: background .15s; }
        .user-list-item:hover { background: #eff6ff; }
        .user-list-item .ua { width: 40px; height: 40px; min-width: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .85rem; color: white; }
        .modal-close { margin-top: 1rem; }

        /* Image preview modal */
        #imgModal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.8); z-index: 1000; align-items: center; justify-content: center; }
        #imgModal.open { display: flex; }
        #imgModal img { max-width: 90vw; max-height: 90vh; border-radius: 8px; }
        #imgModal .close-img { position: absolute; top: 1rem; right: 1rem; color: white; font-size: 2rem; cursor: pointer; background: none; border: none; }
    </style>
</head>
<body>
<div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="index.php" class="logo" style="text-decoration:none;"><i class="fa-solid fa-chart-pie text-primary"></i> Digit Advisory</a>
        </div>
        <div class="sidebar-menu">
            <a href="front-entreprise-dashboard.php" class="menu-item"><i class="fa-solid fa-house"></i> Vue d'ensemble</a>
            <a href="front-utilisateur.php" class="menu-item"><i class="fa-solid fa-building"></i> Profil Entreprise</a>
            <a href="front-quiz.php" class="menu-item"><i class="fa-solid fa-list-check"></i> Questionnaire</a>
            <a href="front-portfolio.php" class="menu-item"><i class="fa-solid fa-folder-open"></i> Mon Portfolio</a>
            <a href="front-offres.php" class="menu-item"><i class="fa-solid fa-briefcase"></i> Mes Offres de Mission</a>
            <a href="front-certification.php" class="menu-item"><i class="fa-solid fa-award"></i> Certifications ISO</a>
            <a href="front-messagerie.php" class="menu-item active"><i class="fa-solid fa-comments"></i> Messagerie</a>
        </div>
        <div class="user-profile-widget">
            <div class="user-avatar"><?= htmlspecialchars($myInit) ?></div>
            <div>
                <h4 style="font-size:.95rem;margin-bottom:.2rem;"><?= htmlspecialchars($myName) ?></h4>
                <span style="font-size:.8rem;color:var(--gray);">Compte Entreprise</span>
            </div>
            <a href="../traitement/logoutTraitement.php" style="margin-left:auto;color:var(--danger);"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
        </div>
    </aside>

    <!-- Main -->
    <main class="main-content">
        <div class="top-navbar">
            <h2 style="margin:0;font-size:1.5rem;">Messagerie</h2>
            <span style="font-size:.85rem;color:var(--gray);"><i class="fa-solid fa-circle" style="color:var(--success);font-size:.6rem;"></i> En ligne</span>
        </div>

        <section class="fade-in-up">
            <div class="chat-wrap">
                <!-- Conversations sidebar -->
                <div class="chat-sidebar">
                    <div class="chat-sidebar-head">
                        <h3>Conversations</h3>
                        <button class="btn btn-primary" style="padding:.35rem .75rem;font-size:.8rem;border-radius:8px;" onclick="openNewConvModal()" title="Nouvelle conversation">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                    <div class="search-box">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="searchInput" placeholder="Rechercher..." oninput="filterConvs(this.value)">
                    </div>
                    <div class="conv-list" id="convList">
                        <div class="no-convs"><i class="fa-solid fa-spinner fa-spin"></i><br>Chargement...</div>
                    </div>
                </div>

                <!-- Chat main -->
                <div class="chat-main" id="chatMain">
                    <div class="chat-empty" id="chatEmpty">
                        <i class="fa-solid fa-comments"></i>
                        <p>Sélectionnez une conversation ou commencez-en une nouvelle</p>
                        <button class="btn btn-primary" onclick="openNewConvModal()"><i class="fa-solid fa-plus"></i> Nouvelle conversation</button>
                    </div>
                    <!-- Chat zone (hidden until conversation selected) -->
                    <div id="chatZone" style="display:none;flex:1;flex-direction:column;min-width:0;">
                        <div class="chat-header" id="chatHeader"></div>
                        <div class="chat-messages" id="chatMessages"></div>
                        <div class="chat-input-area">
                            <button class="input-btn attach" onclick="document.getElementById('fileInput').click()" title="Joindre un fichier"><i class="fa-solid fa-paperclip"></i></button>
                            <input type="file" id="fileInput" style="display:none" onchange="sendFile(this.files[0])">
                            <button class="input-btn record" id="recBtn" onclick="toggleRecording()" title="Message vocal"><i class="fa-solid fa-microphone"></i></button>
                            <span id="recTimer" class="rec-timer" style="display:none">00:00</span>
                            <textarea id="msgInput" rows="1" placeholder="Écrire un message..." onkeydown="handleKey(event)" oninput="autoResize(this)"></textarea>
                            <button class="input-btn send" onclick="sendText()" title="Envoyer"><i class="fa-solid fa-paper-plane"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>

<!-- New Conversation Modal -->
<div class="modal-overlay" id="newConvModal">
    <div class="modal-box">
        <h3><i class="fa-solid fa-plus-circle" style="color:var(--primary);"></i> Nouvelle conversation</h3>
        <input type="text" class="modal-search" id="userSearch" placeholder="Rechercher un expert..." oninput="filterUsers(this.value)">
        <div class="user-list" id="userList"><div style="text-align:center;color:var(--gray);padding:1rem;"><i class="fa-solid fa-spinner fa-spin"></i></div></div>
        <button class="btn btn-outline modal-close" onclick="closeNewConvModal()">Annuler</button>
    </div>
</div>

<!-- Image Preview Modal -->
<div id="imgModal" onclick="closeImg()">
    <button class="close-img" onclick="closeImg()"><i class="fa-solid fa-xmark"></i></button>
    <img id="imgPreview" src="" alt="">
</div>

<script>
const API   = '<?= $apiUrl ?>';
const MY_ID = <?= $myId ?>;
let currentConvId   = null;
let pollingTimer    = null;
let allConvs        = [];
let allUsers        = [];
let mediaRecorder   = null;
let audioChunks     = [];
let recInterval     = null;
let recSeconds      = 0;

// ─── Init ─────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => { loadConvs(); });

// ─── Conversations ────────────────────────────────────────────────────────────
function loadConvs() {
    fetch(API + '?action=get_conversations')
        .then(r => r.json())
        .then(data => {
            allConvs = data;
            renderConvs(data);
        });
}

function renderConvs(convs) {
    const list = document.getElementById('convList');
    if (!convs.length) {
        list.innerHTML = '<div class="no-convs"><i class="fa-solid fa-comment-slash" style="font-size:2rem;opacity:.3;"></i><br>Aucune conversation</div>';
        return;
    }
    list.innerHTML = convs.map(c => {
        const color = c.other_role === 'expert' ? 'var(--secondary)' : 'var(--accent)';
        const time  = c.last_at ? formatTime(c.last_at) : '';
        const badge = c.unread > 0 ? `<span class="unread-badge">${c.unread}</span>` : '';
        const active = c.id_conversation == currentConvId ? ' active' : '';
        return `<div class="conv-item${active}" data-id="${c.id_conversation}" onclick="openConv(${c.id_conversation}, '${escHtml(c.other_name)}', '${escHtml(c.other_initials)}', '${c.other_role}', ${c.other_id})">
            <div class="conv-avatar" style="background:${color};">${escHtml(c.other_initials)}</div>
            <div class="conv-info">
                <h4>${escHtml(c.other_name)}</h4>
                <p>${escHtml(c.last_preview || 'Démarrer la conversation')}</p>
            </div>
            <div class="conv-meta"><span class="conv-time">${time}</span>${badge}</div>
        </div>`;
    }).join('');
}

function filterConvs(q) {
    q = q.toLowerCase();
    const filtered = allConvs.filter(c =>
        c.other_name.toLowerCase().includes(q) ||
        (c.last_preview || '').toLowerCase().includes(q)
    );
    renderConvs(filtered);
}

// ─── Open conversation ────────────────────────────────────────────────────────
function openConv(id, name, initials, role, otherId) {
    currentConvId = id;
    // Update active state
    document.querySelectorAll('.conv-item').forEach(el => el.classList.toggle('active', el.dataset.id == id));

    // Show chat zone
    document.getElementById('chatEmpty').style.display = 'none';
    const zone = document.getElementById('chatZone');
    zone.style.display = 'flex';

    // Header
    const color = role === 'expert' ? 'var(--secondary)' : 'var(--accent)';
    document.getElementById('chatHeader').innerHTML = `
        <div style="width:38px;height:38px;border-radius:50%;background:${color};color:white;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;">${escHtml(initials)}</div>
        <div class="chat-header-info">
            <h4>${escHtml(name)}</h4>
            <span>${role === 'expert' ? 'Consultant Expert' : 'Entreprise'}</span>
        </div>
        <button onclick="confirmDeleteConv(${id})" style="background:none;border:none;color:var(--gray);cursor:pointer;font-size:1rem;margin-left:auto;" title="Supprimer la conversation">
            <i class="fa-solid fa-trash"></i>
        </button>`;

    loadMessages(id);
    startPolling(id);
}

// ─── Messages ─────────────────────────────────────────────────────────────────
function loadMessages(id) {
    fetch(API + '?action=get_messages&id_conversation=' + id)
        .then(r => r.json())
        .then(msgs => renderMessages(msgs));
}

function renderMessages(msgs) {
    const box = document.getElementById('chatMessages');
    if (!msgs.length) {
        box.innerHTML = '<div style="text-align:center;color:var(--gray);padding:2rem;font-size:.9rem;">Aucun message. Dites bonjour ! 👋</div>';
        return;
    }
    box.innerHTML = msgs.map(m => buildMsgHtml(m)).join('');
    box.scrollTop = box.scrollHeight;
}

function buildMsgHtml(m) {
    const isMine = m.id_sender == MY_ID;
    const side   = isMine ? 'sent' : 'received';
    const time   = formatTime(m.created_at);
    const editedMark = m.is_edited ? '<span title="Modifié"><i class="fa-solid fa-pen" style="font-size:.6rem;"></i></span>' : '';

    let body = '';
    if (m.is_deleted) {
        body = '<span class="msg-deleted"><i class="fa-solid fa-ban"></i> Message supprimé</span>';
    } else if (m.type === 'text') {
        body = `<span class="msg-content" data-id="${m.id_message}">${escHtml(m.content)}</span>`;
    } else if (m.type === 'audio') {
        body = `<audio class="msg-audio" controls src="../../${m.file_path}"></audio>`;
    } else if (m.type === 'file') {
        const isImg = /\.(jpg|jpeg|png|gif|webp)$/i.test(m.file_name || '');
        if (isImg) {
            body = `<img class="msg-img" src="../../${m.file_path}" alt="${escHtml(m.file_name)}" onclick="previewImg('../../${m.file_path}')">`;
        } else {
            const icon = fileIcon(m.file_name);
            const size = m.file_size ? formatSize(m.file_size) : '';
            body = `<a class="msg-file" href="../../${m.file_path}" target="_blank" download="${escHtml(m.file_name)}">
                <i class="${icon}"></i><span>${escHtml(m.file_name)} ${size ? '('+size+')' : ''}</span>
            </a>`;
        }
    }

    const actions = (isMine && !m.is_deleted) ? `
        <div class="msg-actions">
            ${m.type === 'text' ? `<button onclick="startEdit(${m.id_message})" title="Modifier"><i class="fa-solid fa-pen"></i></button>` : ''}
            <button class="del" onclick="deleteMsg(${m.id_message})" title="Supprimer"><i class="fa-solid fa-trash"></i></button>
        </div>` : '';

    return `<div class="msg-wrapper ${side}" data-message-id="${m.id_message}">
        ${isMine ? actions : ''}
        <div class="msg-bubble">${body}</div>
        <div class="msg-meta">${time} ${editedMark}</div>
        ${!isMine ? actions : ''}
    </div>`;
}

// ─── Send message ─────────────────────────────────────────────────────────────
function sendText() {
    const inp = document.getElementById('msgInput');
    const content = inp.value.trim();
    if (!content || !currentConvId) return;
    const fd = new FormData();
    fd.append('action', 'send_message');
    fd.append('id_conversation', currentConvId);
    fd.append('type', 'text');
    fd.append('content', content);
    inp.value = '';
    inp.style.height = '';
    fetch(API, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(() => { loadMessages(currentConvId); loadConvs(); });
}

function handleKey(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendText(); }
}

function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 100) + 'px';
}

function sendFile(file) {
    if (!file || !currentConvId) return;
    const fd = new FormData();
    fd.append('action', 'send_message');
    fd.append('id_conversation', currentConvId);
    fd.append('type', 'file');
    fd.append('file', file);
    document.getElementById('fileInput').value = '';
    fetch(API, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => { if (d.success) { loadMessages(currentConvId); loadConvs(); } else { alert(d.error || 'Erreur envoi fichier'); } });
}

// ─── Voice recording ──────────────────────────────────────────────────────────
function toggleRecording() {
    if (!currentConvId) { alert('Sélectionnez une conversation d\'abord.'); return; }
    if (!mediaRecorder || mediaRecorder.state === 'inactive') {
        startRecording();
    } else {
        stopRecording();
    }
}

function startRecording() {
    navigator.mediaDevices.getUserMedia({ audio: true }).then(stream => {
        audioChunks = [];
        mediaRecorder = new MediaRecorder(stream);
        mediaRecorder.ondataavailable = e => audioChunks.push(e.data);
        mediaRecorder.onstop = () => {
            const blob = new Blob(audioChunks, { type: 'audio/webm' });
            const file = new File([blob], 'voice_' + Date.now() + '.webm', { type: 'audio/webm' });
            sendAudio(file);
            stream.getTracks().forEach(t => t.stop());
        };
        mediaRecorder.start();
        // UI
        document.getElementById('recBtn').classList.add('recording');
        document.getElementById('recBtn').title = 'Arrêter l\'enregistrement';
        document.getElementById('recTimer').style.display = '';
        recSeconds = 0;
        updateRecTimer();
        recInterval = setInterval(() => { recSeconds++; updateRecTimer(); }, 1000);
    }).catch(() => alert('Accès au microphone refusé.'));
}

function stopRecording() {
    if (mediaRecorder && mediaRecorder.state !== 'inactive') mediaRecorder.stop();
    clearInterval(recInterval);
    document.getElementById('recBtn').classList.remove('recording');
    document.getElementById('recBtn').title = 'Message vocal';
    document.getElementById('recTimer').style.display = 'none';
}

function updateRecTimer() {
    const m = String(Math.floor(recSeconds / 60)).padStart(2, '0');
    const s = String(recSeconds % 60).padStart(2, '0');
    document.getElementById('recTimer').textContent = m + ':' + s;
}

function sendAudio(file) {
    const fd = new FormData();
    fd.append('action', 'send_message');
    fd.append('id_conversation', currentConvId);
    fd.append('type', 'audio');
    fd.append('file', file);
    fetch(API, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => { if (d.success) { loadMessages(currentConvId); loadConvs(); } });
}

// ─── Edit / Delete message ────────────────────────────────────────────────────
function startEdit(id) {
    const span = document.querySelector(`[data-message-id="${id}"] .msg-content`);
    if (!span) return;
    const original = span.textContent;
    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'msg-edit-input';
    input.value = original;
    span.replaceWith(input);
    input.focus();
    input.onkeydown = e => {
        if (e.key === 'Enter') confirmEdit(id, input.value, original);
        if (e.key === 'Escape') cancelEdit(id, input, original);
    };
    input.onblur = () => confirmEdit(id, input.value, original);
}

function confirmEdit(id, newVal, original) {
    const input = document.querySelector(`[data-message-id="${id}"] .msg-edit-input`);
    if (!input) return;
    const v = newVal.trim();
    if (!v || v === original) { cancelEdit(id, input, original); return; }
    const fd = new FormData();
    fd.append('action', 'edit_message');
    fd.append('id_message', id);
    fd.append('content', v);
    fetch(API, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(() => loadMessages(currentConvId));
}

function cancelEdit(id, input, original) {
    const span = document.createElement('span');
    span.className = 'msg-content';
    span.dataset.id = id;
    span.textContent = original;
    input.replaceWith(span);
}

function deleteMsg(id) {
    if (!confirm('Supprimer ce message ?')) return;
    const fd = new FormData();
    fd.append('action', 'delete_message');
    fd.append('id_message', id);
    fetch(API, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(() => loadMessages(currentConvId));
}

function confirmDeleteConv(id) {
    if (!confirm('Supprimer cette conversation ? Cette action est irréversible pour vous.')) return;
    const fd = new FormData();
    fd.append('action', 'delete_conversation');
    fd.append('id_conversation', id);
    fetch(API, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(() => {
            currentConvId = null;
            stopPolling();
            document.getElementById('chatZone').style.display = 'none';
            document.getElementById('chatEmpty').style.display = '';
            loadConvs();
        });
}

// ─── Polling ──────────────────────────────────────────────────────────────────
function startPolling(id) {
    stopPolling();
    pollingTimer = setInterval(() => {
        if (currentConvId === id) { loadMessages(id); loadConvs(); }
    }, 3000);
}

function stopPolling() {
    if (pollingTimer) clearInterval(pollingTimer);
    pollingTimer = null;
}

// ─── New Conversation Modal ───────────────────────────────────────────────────
function openNewConvModal() {
    document.getElementById('newConvModal').classList.add('open');
    document.getElementById('userSearch').value = '';
    document.getElementById('userList').innerHTML = '<div style="text-align:center;color:var(--gray);padding:1rem;"><i class="fa-solid fa-spinner fa-spin"></i></div>';
    fetch(API + '?action=get_users')
        .then(r => r.json())
        .then(users => {
            allUsers = users;
            renderUserList(users);
        });
}

function closeNewConvModal() {
    document.getElementById('newConvModal').classList.remove('open');
}

function renderUserList(users) {
    const ul = document.getElementById('userList');
    if (!users.length) {
        ul.innerHTML = '<div style="text-align:center;color:var(--gray);padding:1rem;">Aucun expert disponible</div>';
        return;
    }
    ul.innerHTML = users.map(u => `
        <div class="user-list-item" onclick="startNewConv(${u.id_user})">
            <div class="ua" style="background:var(--secondary);">${escHtml(u.initials)}</div>
            <div>
                <strong style="font-size:.9rem;">${escHtml(u.display_name)}</strong>
                <p style="font-size:.78rem;color:var(--gray);margin:0;">${escHtml(u.domaine || 'Expert')}</p>
            </div>
        </div>`).join('');
}

function filterUsers(q) {
    q = q.toLowerCase();
    renderUserList(allUsers.filter(u => u.display_name.toLowerCase().includes(q) || (u.domaine || '').toLowerCase().includes(q)));
}

function startNewConv(otherId) {
    closeNewConvModal();
    const fd = new FormData();
    fd.append('action', 'create_conversation');
    fd.append('id_other_user', otherId);
    fetch(API, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                loadConvs().then ? loadConvs() : loadConvs();
                // Small delay to let convs load, then open
                setTimeout(() => {
                    fetch(API + '?action=get_conversations')
                        .then(r => r.json())
                        .then(convs => {
                            allConvs = convs;
                            renderConvs(convs);
                            const conv = convs.find(c => c.id_conversation == d.id_conversation);
                            if (conv) openConv(conv.id_conversation, conv.other_name, conv.other_initials, conv.other_role, conv.other_id);
                        });
                }, 200);
            }
        });
}

// ─── Image preview ────────────────────────────────────────────────────────────
function previewImg(src) {
    document.getElementById('imgPreview').src = src;
    document.getElementById('imgModal').classList.add('open');
}

function closeImg() {
    document.getElementById('imgModal').classList.remove('open');
    document.getElementById('imgPreview').src = '';
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
function formatTime(dt) {
    const d = new Date(dt.replace(' ', 'T'));
    const now = new Date();
    const diffDays = Math.floor((now - d) / 86400000);
    if (diffDays === 0) return d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
    if (diffDays === 1) return 'Hier';
    if (diffDays < 7)  return d.toLocaleDateString('fr-FR', { weekday: 'short' });
    return d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' });
}

function formatSize(bytes) {
    if (bytes < 1024) return bytes + ' o';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' Ko';
    return (bytes / 1048576).toFixed(1) + ' Mo';
}

function fileIcon(name) {
    if (!name) return 'fa-solid fa-file';
    const ext = name.split('.').pop().toLowerCase();
    if (['pdf'].includes(ext)) return 'fa-solid fa-file-pdf';
    if (['doc','docx'].includes(ext)) return 'fa-solid fa-file-word';
    if (['xls','xlsx'].includes(ext)) return 'fa-solid fa-file-excel';
    if (['zip','rar','7z'].includes(ext)) return 'fa-solid fa-file-zipper';
    if (['mp3','ogg','wav','webm','m4a'].includes(ext)) return 'fa-solid fa-file-audio';
    if (['jpg','jpeg','png','gif','webp'].includes(ext)) return 'fa-solid fa-file-image';
    return 'fa-solid fa-file';
}

function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Close modal on overlay click
document.getElementById('newConvModal').addEventListener('click', function(e) {
    if (e.target === this) closeNewConvModal();
});
</script>
</body>
</html>
