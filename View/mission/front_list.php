<?php /* Front Office — Digit Advisory style */ ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Espace Missions — Digit Advisory</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Inter',sans-serif;background:#f4f6fb;display:flex;min-height:100vh;color:#1e293b}
        /* SIDEBAR */
        .sidebar{width:260px;min-height:100vh;background:#fff;border-right:1px solid #e8edf4;display:flex;flex-direction:column;position:fixed;top:0;left:0;z-index:200;box-shadow:2px 0 12px rgba(0,0,0,.04)}
        .sidebar-logo{padding:1.4rem 1.5rem;border-bottom:1px solid #f0f4f9;display:flex;align-items:center;gap:.6rem}
        .logo-icon{width:36px;height:36px;background:linear-gradient(135deg,#2563eb,#1d4ed8);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1rem}
        .logo-text{font-size:1.05rem;font-weight:700;color:#1a3c5e;letter-spacing:-.3px}
        .logo-text span{color:#2563eb}
        .sidebar-nav{flex:1;padding:.5rem 0;overflow-y:auto}
        .nav-item{display:flex;align-items:center;gap:.75rem;padding:.65rem 1.5rem;font-size:.875rem;font-weight:500;color:#64748b;text-decoration:none;border-left:3px solid transparent;transition:all .15s}
        .nav-item:hover{background:#f8fafc;color:#2563eb}
        .nav-item.active{background:#eff6ff;color:#2563eb;border-left-color:#2563eb;font-weight:600}
        .nav-item i{width:18px;text-align:center;font-size:.95rem}
        .sidebar-user{padding:1rem 1.5rem;border-top:1px solid #f0f4f9;display:flex;align-items:center;gap:.75rem}
        .user-avatar-sm{width:36px;height:36px;background:#dbeafe;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;color:#1d4ed8}
        .user-name{font-size:.85rem;font-weight:600;color:#1e293b}
        .user-role{font-size:.72rem;color:#94a3b8}
        .btn-logout{margin-left:auto;background:none;border:none;color:#94a3b8;cursor:pointer;font-size:1rem}
        .btn-logout:hover{color:#ef4444}
        /* MAIN */
        .main-wrap{margin-left:260px;flex:1;padding:2rem;min-height:100vh}
        /* PAGE HEADER */
        .page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem}
        .page-header h1{font-size:1.4rem;font-weight:700;color:#1a3c5e}
        .btn-back{background:#fff;border:1.5px solid #e2e8f0;border-radius:10px;padding:.5rem 1.1rem;font-size:.875rem;font-weight:600;color:#1a3c5e;text-decoration:none;display:flex;align-items:center;gap:.4rem;transition:all .15s}
        .btn-back:hover{background:#f8fafc;color:#2563eb;border-color:#bfdbfe}
        /* SEARCH BAR */
        .search-bar{background:#fff;border:1.5px solid #e2e8f0;border-radius:12px;padding:.75rem 1.2rem;display:flex;align-items:center;gap:.8rem;margin-bottom:1.5rem;box-shadow:0 1px 4px rgba(0,0,0,.04)}
        .search-bar i{color:#94a3b8;font-size:1rem}
        .search-bar input{border:none;outline:none;flex:1;font-size:.9rem;color:#1e293b;font-family:'Inter',sans-serif}
        .search-bar input::placeholder{color:#94a3b8}
        /* MISSION CARDS */
        .missions-grid{display:grid;grid-template-columns:1fr;gap:1rem}
        .mission-card{background:#fff;border-radius:14px;border:1.5px solid #e8edf4;overflow:hidden;transition:box-shadow .2s,border-color .2s}
        .mission-card:hover{box-shadow:0 4px 20px rgba(0,0,0,.08);border-color:#bfdbfe}
        .mission-card-inner{padding:1.4rem 1.5rem;display:flex;align-items:flex-start;gap:1rem}
        .mission-icon{width:44px;height:44px;background:#eff6ff;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.1rem;color:#2563eb}
        .mission-body{flex:1}
        .mission-norme{font-size:.72rem;font-weight:700;color:#2563eb;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem;display:flex;align-items:center;gap:.4rem}
        .mission-title{font-size:.95rem;font-weight:700;color:#1e293b;margin-bottom:.3rem}
        .mission-meta{font-size:.8rem;color:#94a3b8;display:flex;align-items:center;gap:1rem;margin-bottom:.8rem}
        .statut-badge{padding:.2rem .65rem;border-radius:20px;font-size:.72rem;font-weight:600}
        .s-en-cours{background:#dcfce7;color:#16a34a}
        .s-terminee{background:#dbeafe;color:#2563eb}
        .s-suspendue{background:#fef3c7;color:#d97706}
        .mission-desc{font-size:.82rem;color:#64748b;line-height:1.6;margin-bottom:1rem}
        .btn-detail{background:none;border:none;color:#2563eb;font-size:.82rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:.4rem;padding:0;text-decoration:none;transition:color .15s}
        .btn-detail:hover{color:#1d4ed8}
        .btn-detail i{font-size:.75rem}
        /* CHATBOT */
        .chatbot-trigger{position:fixed;bottom:1.5rem;right:1.5rem;width:52px;height:52px;background:linear-gradient(135deg,#2563eb,#1d4ed8);border-radius:50%;border:none;color:#fff;font-size:1.3rem;cursor:pointer;box-shadow:0 4px 20px rgba(37,99,235,.4);display:flex;align-items:center;justify-content:center;z-index:1000;transition:transform .2s}
        .chatbot-trigger:hover{transform:scale(1.08)}
        .chatbot-window{position:fixed;bottom:5rem;right:1.5rem;width:340px;background:#fff;border-radius:16px;box-shadow:0 8px 40px rgba(0,0,0,.15);z-index:999;display:none;flex-direction:column;overflow:hidden;border:1px solid #e2e8f0}
        .chatbot-window.open{display:flex}
        .chatbot-header{background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;padding:.9rem 1.1rem;display:flex;align-items:center;gap:.7rem}
        .bot-avatar{width:36px;height:36px;background:rgba(255,255,255,.2);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1rem}
        .bot-name{font-weight:700;font-size:.95rem}
        .bot-sub{font-size:.72rem;opacity:.8}
        .chatbot-close{margin-left:auto;background:none;border:none;color:#fff;font-size:1rem;cursor:pointer;opacity:.8}
        .chatbot-close:hover{opacity:1}
        .chatbot-messages{flex:1;padding:1rem;overflow-y:auto;max-height:300px;display:flex;flex-direction:column;gap:.7rem;background:#f8fafc}
        .msg{max-width:85%;font-size:.82rem;line-height:1.5}
        .msg-bot{background:#fff;border:1px solid #e2e8f0;border-radius:12px 12px 12px 3px;padding:.6rem .9rem;color:#334155;align-self:flex-start}
        .msg-user{background:#2563eb;color:#fff;border-radius:12px 12px 3px 12px;padding:.6rem .9rem;align-self:flex-end}
        .chatbot-input-row{padding:.8rem 1rem;border-top:1px solid #e8edf4;display:flex;gap:.5rem;align-items:center;background:#fff}
        .chatbot-input{flex:1;border:1px solid #e2e8f0;border-radius:8px;padding:.5rem .8rem;font-size:.82rem;outline:none;color:#1e293b}
        .chatbot-input:focus{border-color:#2563eb}
        .btn-send{width:34px;height:34px;background:#2563eb;border:none;border-radius:8px;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.85rem}
        .btn-send:hover{background:#1d4ed8}
        .msg-loading{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:.6rem .9rem;color:#94a3b8;font-size:.8rem;align-self:flex-start;display:none;margin:0 1rem .5rem}
        .msg-loading.visible{display:block}
        .empty-state{text-align:center;padding:3rem;color:#94a3b8}
        .empty-state i{font-size:2.5rem;margin-bottom:.8rem;display:block}
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon"><i class="fas fa-chart-line"></i></div>
        <div class="logo-text">Digit <span>Advisory</span></div>
    </div>
    <div class="sidebar-nav">
        <a href="#" class="nav-item"><i class="fas fa-house"></i> Vue d'ensemble</a>
        <a href="#" class="nav-item"><i class="fas fa-building"></i> Profil Entreprise</a>
        <a href="#" class="nav-item"><i class="fas fa-list-check"></i> Questionnaire</a>
        <a href="#" class="nav-item"><i class="fas fa-folder-open"></i> Mon Portfolio</a>
        <a href="#" class="nav-item"><i class="fas fa-briefcase"></i> Mes Offres de Mission</a>
        <a href="index.php?action=front_list" class="nav-item active"><i class="fas fa-diagram-project"></i> Missions & Projets</a>
        <a href="#" class="nav-item"><i class="fas fa-envelope"></i> Messagerie</a>
    </div>
    <div class="sidebar-user">
        <div class="user-avatar-sm">TC</div>
        <div>
            <div class="user-name">TechCorp SAS</div>
            <div class="user-role">Compte Entreprise</div>
        </div>
        <button class="btn-logout"><i class="fas fa-right-from-bracket"></i></button>
    </div>
</aside>

<div class="main-wrap">

    <div class="page-header">
        <h1>Espace Missions & Projets</h1>
        <a href="index.php?action=back_list" class="btn-back">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    <!-- SEARCH -->
    <div class="search-bar">
        <i class="fas fa-search"></i>
        <input type="text" id="searchMissions" placeholder="Rechercher une mission (ex: Audit, Cloud...)..." oninput="filterMissions()">
    </div>

    <!-- MISSIONS -->
    <?php if (empty($missions)): ?>
    <div class="empty-state">
        <i class="fas fa-folder-open"></i>
        Aucune mission disponible pour le moment.
    </div>
    <?php else: ?>
    <div class="missions-grid" id="missionsGrid">
        <?php foreach ($missions as $m):
            $sClass = match($m['statut']) { 'En cours'=>'s-en-cours','Terminée'=>'s-terminee','Suspendue'=>'s-suspendue',default=>'s-default' };
            $icon   = match($m['statut']) { 'En cours'=>'fa-spinner','Terminée'=>'fa-check-circle','Suspendue'=>'fa-pause-circle',default=>'fa-briefcase' };
        ?>
        <div class="mission-card" data-title="<?php echo strtolower(htmlspecialchars($m['titre'])); ?>">
            <div class="mission-card-inner">
                <div class="mission-icon">
                    <i class="fas <?php echo $icon; ?>"></i>
                </div>
                <div class="mission-body">
                    <div class="mission-norme">
                        <i class="fas fa-shield-halved"></i>
                        Mission #<?php echo $m['id']; ?>
                        <span class="statut-badge <?php echo $sClass; ?>"><?php echo htmlspecialchars($m['statut']); ?></span>
                    </div>
                    <div class="mission-title"><?php echo htmlspecialchars($m['titre']); ?></div>
                    <div class="mission-meta">
                        <span><i class="fas fa-calendar-alt" style="margin-right:.3rem"></i>Début : <?php echo date('d/m/Y', strtotime($m['date_debut'])); ?></span>
                    </div>
                    <div class="mission-desc">
                        Suivez l'avancement de cette mission, consultez les livrables associés et leur état de validation.
                    </div>
                    <a href="index.php?action=front_detail&id=<?php echo $m['id']; ?>" class="btn-detail">
                        <i class="fas fa-rotate"></i> Lancer l'auto-évaluation
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>

<!-- DIGITBOT CERTIF -->
<button class="chatbot-trigger" onclick="toggleBot()"><i class="fas fa-robot"></i></button>
<div class="chatbot-window" id="chatbotWindow">
    <div class="chatbot-header">
        <div class="bot-avatar"><i class="fas fa-robot"></i></div>
        <div>
            <div class="bot-name">DigitBot Certif</div>
            <div class="bot-sub">Assistant Certification</div>
        </div>
        <button class="chatbot-close" onclick="toggleBot()"><i class="fas fa-chevron-down"></i></button>
    </div>
    <div class="chatbot-messages" id="chatMessages">
        <div class="msg msg-bot">Bonjour ! Je suis votre assistant IA. Je réponds à <em>toutes</em> vos questions : certifications ISO, conseils business, rédaction, calculs et bien plus !</div>
    </div>
    <div id="msgLoading" class="msg-loading">L'IA réfléchit...</div>
    <div class="chatbot-input-row">
        <input type="text" class="chatbot-input" id="chatInput" placeholder="Posez n'importe quelle ques..." onkeydown="if(event.key==='Enter') sendMessage()">
        <button class="btn-send" onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
    </div>
</div>

<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script>
function filterMissions(){
    const q=document.getElementById('searchMissions').value.toLowerCase();
    document.querySelectorAll('#missionsGrid .mission-card').forEach(c=>{
        c.style.display=c.dataset.title.includes(q)?'':'none';
    });
}
function toggleBot(){document.getElementById('chatbotWindow').classList.toggle('open');}
const chatHistory=[];
async function sendMessage(){
    const input=document.getElementById('chatInput');
    const msgs=document.getElementById('chatMessages');
    const loading=document.getElementById('msgLoading');
    const text=input.value.trim();
    if(!text)return;
    msgs.innerHTML+=`<div class="msg msg-user">${text}</div>`;
    chatHistory.push({role:'user',content:text});
    input.value='';msgs.scrollTop=msgs.scrollHeight;
    loading.classList.add('visible');
    try{
        const messages=[{role:'user',content:'Tu es DigitBot Certif, assistant IA de la plateforme Digit Advisory. Tu aides les entreprises avec leurs missions consulting, certifications ISO, et tout autre sujet. Réponds en français, sois utile et concis.\n\nQuestion: '+chatHistory[0].content},...chatHistory.slice(1)];
        const res=await fetch('https://api.anthropic.com/v1/messages',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({model:'claude-sonnet-4-20250514',max_tokens:1000,messages})});
        const data=await res.json();
        const reply=data.content?.[0]?.text||"Je n'ai pas pu répondre.";
        chatHistory.push({role:'assistant',content:reply});
        loading.classList.remove('visible');
        msgs.innerHTML+=`<div class="msg msg-bot">${reply.replace(/\n/g,'<br>')}</div>`;
        msgs.scrollTop=msgs.scrollHeight;
    }catch(e){
        loading.classList.remove('visible');
        msgs.innerHTML+=`<div class="msg msg-bot" style="color:#ef4444">Erreur de connexion.</div>`;
    }
}
</script>
</body>
</html>
