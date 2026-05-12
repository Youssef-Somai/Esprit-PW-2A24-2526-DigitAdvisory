<?php /* Back Office — Digit Advisory style */ ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Back Office | Gestion Missions</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Inter',sans-serif;background:#f4f6fb;display:flex;min-height:100vh;color:#1e293b}
        .sidebar{width:260px;min-height:100vh;background:#fff;border-right:1px solid #e8edf4;display:flex;flex-direction:column;position:fixed;top:0;left:0;z-index:200;box-shadow:2px 0 12px rgba(0,0,0,.04)}
        .sidebar-logo{padding:1.4rem 1.5rem;border-bottom:1px solid #f0f4f9;display:flex;align-items:center;gap:.6rem}
        .logo-icon{width:36px;height:36px;background:linear-gradient(135deg,#2563eb,#1d4ed8);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1rem}
        .logo-text{font-size:1.05rem;font-weight:700;color:#1a3c5e;letter-spacing:-.3px}
        .logo-text span{color:#2563eb}
        .sidebar-section{padding:.6rem 1rem .3rem;font-size:.7rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em}
        .sidebar-nav{flex:1;padding:.5rem 0;overflow-y:auto}
        .nav-item{display:flex;align-items:center;gap:.75rem;padding:.65rem 1.5rem;font-size:.875rem;font-weight:500;color:#64748b;text-decoration:none;border-left:3px solid transparent;transition:all .15s}
        .nav-item:hover{background:#f8fafc;color:#2563eb}
        .nav-item.active{background:#eff6ff;color:#2563eb;border-left-color:#2563eb;font-weight:600}
        .nav-item i{width:18px;text-align:center;font-size:.95rem}
        .sidebar-user{padding:1rem 1.5rem;border-top:1px solid #f0f4f9;display:flex;align-items:center;gap:.75rem}
        .user-avatar{width:36px;height:36px;background:#e0e7ff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:700;color:#3730a3}
        .user-name{font-size:.85rem;font-weight:600;color:#1e293b}
        .user-role{font-size:.72rem;color:#94a3b8}
        .btn-logout{margin-left:auto;background:none;border:none;color:#94a3b8;cursor:pointer;font-size:1rem;transition:color .15s}
        .btn-logout:hover{color:#ef4444}
        .main-wrap{margin-left:260px;flex:1;display:flex;flex-direction:column;min-height:100vh}
        .topbar{background:#fff;border-bottom:1px solid #e8edf4;padding:.9rem 2rem;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100}
        .topbar-title{font-size:1rem;font-weight:700;color:#1a3c5e}
        .topbar-right{display:flex;align-items:center;gap:1rem}
        .topbar-date{font-size:.8rem;color:#94a3b8}
        .topbar-avatar{width:34px;height:34px;background:linear-gradient(135deg,#2563eb,#1d4ed8);border-radius:50%;color:#fff;font-size:.8rem;font-weight:700;display:flex;align-items:center;justify-content:center}
        .page-content{padding:1.5rem 2rem;flex:1}
        .tabs-bar{display:flex;align-items:center;gap:0;border-bottom:2px solid #e8edf4;margin-bottom:1.5rem}
        .tab-btn{padding:.7rem 1.4rem;font-size:.875rem;font-weight:500;color:#64748b;border:none;background:none;border-bottom:2px solid transparent;margin-bottom:-2px;cursor:pointer;display:flex;align-items:center;gap:.4rem;transition:all .15s}
        .tab-btn:hover{color:#2563eb}
        .tab-btn.active{color:#2563eb;border-bottom-color:#2563eb;font-weight:600}
        .tab-search{margin-left:auto;display:flex;align-items:center;gap:.5rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:.4rem .9rem;font-size:.85rem;color:#94a3b8}
        .tab-search input{border:none;background:none;outline:none;font-size:.85rem;color:#1e293b;width:160px}
        .table-card{background:#fff;border-radius:14px;box-shadow:0 1px 8px rgba(0,0,0,.06);overflow:hidden}
        .table-header{padding:1.1rem 1.5rem;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #f0f4f9}
        .table-header h5{font-size:.95rem;font-weight:700;color:#1a3c5e;margin:0}
        .table-actions{display:flex;gap:.5rem;align-items:center}
        .sort-select{font-size:.8rem;border:1px solid #e2e8f0;border-radius:8px;padding:.35rem .8rem;color:#64748b;background:#f8fafc;cursor:pointer}
        .btn-export{background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;border-radius:8px;padding:.35rem .9rem;font-size:.8rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:.3rem;transition:background .15s;text-decoration:none}
        .btn-export:hover{background:#dbeafe}
        .btn-add{background:#2563eb;color:#fff;border:none;border-radius:8px;padding:.4rem .9rem;font-size:.85rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:.3rem;text-decoration:none;transition:background .15s}
        .btn-add:hover{background:#1d4ed8;color:#fff}
        .btn-alertes-ia{background:linear-gradient(135deg,#7c3aed,#2563eb);color:#fff;border:none;border-radius:8px;padding:.4rem .9rem;font-size:.8rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:.3rem;text-decoration:none;transition:opacity .15s}
        .btn-alertes-ia:hover{opacity:.88;color:#fff}
        .data-table{width:100%;border-collapse:collapse}
        .data-table thead th{padding:.75rem 1.2rem;font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;background:#fafbfc;border-bottom:1px solid #f0f4f9}
        .data-table tbody td{padding:.85rem 1.2rem;font-size:.875rem;border-bottom:1px solid #f8fafc;vertical-align:middle}
        .data-table tbody tr:last-child td{border-bottom:none}
        .data-table tbody tr:hover td{background:#fafbff}
        .id-badge{font-size:.78rem;font-weight:700;color:#64748b}
        .statut-badge{padding:.22rem .7rem;border-radius:20px;font-size:.75rem;font-weight:600;display:inline-block}
        .s-en-cours{background:#dcfce7;color:#16a34a}
        .s-terminee{background:#dbeafe;color:#2563eb}
        .s-suspendue{background:#fef3c7;color:#d97706}
        .s-default{background:#f1f5f9;color:#64748b}
        .criteres-link{color:#2563eb;font-size:.8rem;font-weight:600;text-decoration:none;display:flex;align-items:center;gap:.3rem}
        .criteres-link:hover{text-decoration:underline}
        .action-group{display:flex;align-items:center;gap:.4rem}
        .btn-icon{width:32px;height:32px;border:1.5px solid #e2e8f0;border-radius:8px;background:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:.8rem;transition:all .15s;text-decoration:none}
        .btn-icon.edit{color:#2563eb;border-color:#bfdbfe}
        .btn-icon.edit:hover{background:#eff6ff}
        .btn-icon.del{color:#ef4444;border-color:#fecaca}
        .btn-icon.del:hover{background:#fef2f2}
        .btn-icon.ai{color:#7c3aed;border-color:#ddd6fe}
        .btn-icon.ai:hover{background:#f5f3ff}
        .btn-gerer{background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;border-radius:8px;padding:.28rem .75rem;font-size:.78rem;font-weight:600;cursor:pointer;text-decoration:none;display:flex;align-items:center;gap:.3rem;transition:background .15s;white-space:nowrap}
        .btn-gerer:hover{background:#dbeafe;color:#1d4ed8}
        .success-toast{background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:.75rem 1rem;color:#15803d;font-size:.875rem;display:flex;align-items:center;gap:.5rem;margin-bottom:1.2rem}
        .tab-content-section{display:none}
        .tab-content-section.active{display:block}
        .empty-state{text-align:center;padding:3rem 1rem;color:#94a3b8}
        .empty-state i{font-size:2.5rem;margin-bottom:.8rem;display:block}
        /* CHATBOT */
        .chatbot-trigger{position:fixed;bottom:1.5rem;right:1.5rem;width:52px;height:52px;background:linear-gradient(135deg,#2563eb,#1d4ed8);border-radius:50%;border:none;color:#fff;font-size:1.3rem;cursor:pointer;box-shadow:0 4px 20px rgba(37,99,235,.4);display:flex;align-items:center;justify-content:center;z-index:1000;transition:transform .2s}
        .chatbot-trigger:hover{transform:scale(1.08)}
        .chatbot-window{position:fixed;bottom:5rem;right:1.5rem;width:340px;background:#fff;border-radius:16px;box-shadow:0 8px 40px rgba(0,0,0,.15);z-index:999;display:none;flex-direction:column;overflow:hidden;border:1px solid #e2e8f0}
        .chatbot-window.open{display:flex}
        .chatbot-header{background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;padding:.9rem 1.1rem;display:flex;align-items:center;gap:.7rem}
        .bot-avatar{width:36px;height:36px;background:rgba(255,255,255,.2);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1rem}
        .bot-name{font-weight:700;font-size:.95rem}
        .bot-status{font-size:.72rem;opacity:.8;display:flex;align-items:center;gap:.3rem}
        .bot-online{width:7px;height:7px;background:#4ade80;border-radius:50%;display:inline-block}
        .chatbot-close{margin-left:auto;background:none;border:none;color:#fff;font-size:1rem;cursor:pointer;opacity:.8}
        .chatbot-close:hover{opacity:1}
        .chatbot-messages{flex:1;padding:1rem;overflow-y:auto;max-height:320px;display:flex;flex-direction:column;gap:.7rem;background:#f8fafc}
        .msg{max-width:85%;font-size:.82rem;line-height:1.5}
        .msg-bot{background:#fff;border:1px solid #e2e8f0;border-radius:12px 12px 12px 3px;padding:.6rem .9rem;color:#334155;align-self:flex-start}
        .msg-user{background:#2563eb;color:#fff;border-radius:12px 12px 3px 12px;padding:.6rem .9rem;align-self:flex-end}
        .chatbot-input-row{padding:.8rem 1rem;border-top:1px solid #e8edf4;display:flex;gap:.5rem;align-items:center;background:#fff}
        .chatbot-input{flex:1;border:1px solid #e2e8f0;border-radius:8px;padding:.5rem .8rem;font-size:.82rem;outline:none;color:#1e293b}
        .chatbot-input:focus{border-color:#2563eb}
        .btn-send{width:34px;height:34px;background:#2563eb;border:none;border-radius:8px;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.85rem}
        .btn-send:hover{background:#1d4ed8}
        .msg-loading{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:.6rem .9rem;color:#94a3b8;font-size:.8rem;align-self:flex-start;display:none;margin:.5rem 1rem}
        .msg-loading.visible{display:block}
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon"><i class="fas fa-chart-line"></i></div>
        <div class="logo-text">Digit <span>Advisory</span></div>
    </div>
    <div class="sidebar-nav">
        <div class="sidebar-section">Menu</div>
        <a href="#" class="nav-item"><i class="fas fa-users"></i> Gestion Utilisateurs</a>
        <a href="#" class="nav-item"><i class="fas fa-list-check"></i> Gestion Quiz</a>
        <a href="#" class="nav-item"><i class="fas fa-folder-open"></i> Gestion Portfolios</a>
        <a href="#" class="nav-item"><i class="fas fa-briefcase"></i> Gestion Offres</a>
        <a href="index.php?action=back_list" class="nav-item active"><i class="fas fa-diagram-project"></i> Gestion Missions</a>
        <a href="#" class="nav-item"><i class="fas fa-envelope"></i> Gestion Messagerie</a>
    </div>
    <div class="sidebar-user">
        <div class="user-avatar">AD</div>
        <div>
            <div class="user-name">Admin Système</div>
            <div class="user-role">Admin</div>
        </div>
        <button class="btn-logout" title="Déconnexion"><i class="fas fa-right-from-bracket"></i></button>
    </div>
</aside>

<div class="main-wrap">
    <div class="topbar">
        <span class="topbar-title">Back Office | Gestion Missions</span>
        <div class="topbar-right">
            <span class="topbar-date"><?php echo date('d/m/Y H:i'); ?></span>
            <div class="topbar-avatar">AD</div>
        </div>
    </div>

    <div class="page-content">

        <?php if (isset($_GET['success'])): ?>
        <div class="success-toast" id="successToast">
            <i class="fas fa-circle-check"></i>
            <?php echo match($_GET['success']) {
                'created'          => 'Mission créée avec succès !',
                'updated'          => 'Mission mise à jour avec succès !',
                'deleted'          => 'Mission supprimée avec succès !',
                'livrable_created' => 'Livrable ajouté avec succès !',
                'livrable_updated' => 'Livrable mis à jour !',
                'livrable_deleted' => 'Livrable supprimé !',
                default            => 'Opération réussie !'
            }; ?>
        </div>
        <?php endif; ?>

        <div class="tabs-bar">
            <button class="tab-btn active" onclick="switchTab('missions')">
                <i class="fas fa-diagram-project"></i> Missions
            </button>
            <button class="tab-btn" onclick="switchTab('livrables')">
                <i class="fas fa-file-alt"></i> Livrables
            </button>
            <div class="tab-search">
                <i class="fas fa-search" style="color:#94a3b8;font-size:.8rem"></i>
                <input type="text" id="searchInput" placeholder="Rechercher..." oninput="filterTable()">
            </div>
        </div>

        <!-- TAB MISSIONS -->
        <div class="tab-content-section active" id="tab-missions">
            <div class="table-card">
                <div class="table-header">
                    <h5>Liste des Missions</h5>
                    <div class="table-actions">
                        <select class="sort-select" onchange="sortTable(this.value)">
                            <option value="">-- Trier par --</option>
                            <option value="titre">Titre</option>
                            <option value="statut">Statut</option>
                            <option value="date">Date</option>
                        </select>
                        <a href="index.php?action=metier2_alertes" class="btn-alertes-ia">
                            <i class="fas fa-robot"></i> Alertes IA
                        </a>
                        <button class="btn-export" onclick="exportCSV()">
                            <i class="fas fa-file-export"></i> Exporter
                        </button>
                        <a href="index.php?action=back_create" class="btn-add">
                            <i class="fas fa-plus"></i>
                        </a>
                    </div>
                </div>
                <table class="data-table" id="missionsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Date Début</th>
                            <th>Statut</th>
                            <th>Livrables liés</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($missions)): ?>
                        <tr><td colspan="6"><div class="empty-state"><i class="fas fa-folder-open"></i>Aucune mission trouvée.</div></td></tr>
                    <?php else: ?>
                        <?php foreach ($missions as $m):
                            $sClass = match($m['statut']) {
                                'En cours'  => 's-en-cours',
                                'Terminée'  => 's-terminee',
                                'Suspendue' => 's-suspendue',
                                default     => 's-default'
                            };
                            $livsMission = array_filter($livrables, fn($l) => $l['mission_id'] == $m['id']);
                            $nbLivs = count($livsMission);
                        ?>
                        <tr>
                            <td><span class="id-badge">#<?php echo $m['id']; ?></span></td>
                            <td style="font-weight:600;color:#1e293b"><?php echo htmlspecialchars($m['titre']); ?></td>
                            <td style="color:#64748b"><?php echo date('d/m/Y', strtotime($m['date_debut'])); ?></td>
                            <td><span class="statut-badge <?php echo $sClass; ?>"><?php echo htmlspecialchars($m['statut']); ?></span></td>
                            <td>
                                <a href="index.php?action=front_detail&id=<?php echo $m['id']; ?>" class="criteres-link">
                                    <i class="fas fa-link"></i> <?php echo $nbLivs; ?> livrable<?php echo $nbLivs>1?'s':''; ?>
                                </a>
                            </td>
                            <td>
                                <div class="action-group">
                                    <a href="index.php?action=back_edit&id=<?php echo $m['id']; ?>" class="btn-icon edit" title="Modifier"><i class="fas fa-pen"></i></a>
                                    <a href="index.php?action=back_delete&id=<?php echo $m['id']; ?>" class="btn-icon del" title="Supprimer" onclick="return confirm('Supprimer cette mission ?')"><i class="fas fa-trash"></i></a>
                                    <a href="index.php?action=back_livrable_create&id=<?php echo $m['id']; ?>" class="btn-gerer"><i class="fas fa-list"></i> Gérer Livrables</a>
                                    <a href="index.php?action=metier1_rapport&id=<?php echo $m['id']; ?>" class="btn-icon ai" title="Rapport IA"><i class="fas fa-robot"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB LIVRABLES -->
        <div class="tab-content-section" id="tab-livrables">
            <div class="table-card">
                <div class="table-header">
                    <h5>Liste des Livrables</h5>
                    <div class="table-actions">
                        <a href="index.php?action=back_livrable_create&id=0" class="btn-add"><i class="fas fa-plus"></i></a>
                    </div>
                </div>
                <table class="data-table">
                    <thead>
                        <tr><th>ID</th><th>Nom Fichier</th><th>Mission</th><th>Date Remise</th><th>État</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php if (empty($livrables)): ?>
                        <tr><td colspan="6"><div class="empty-state"><i class="fas fa-file-circle-xmark"></i>Aucun livrable trouvé.</div></td></tr>
                    <?php else: ?>
                        <?php foreach ($livrables as $l):
                            $eClass = match($l['etat']) { 'Validé'=>'s-en-cours','Rejeté'=>'s-suspendue',default=>'s-default' };
                            $mTitle = '';
                            foreach ($missions as $mi) { if ($mi['id']==$l['mission_id']) { $mTitle=$mi['titre']; break; } }
                        ?>
                        <tr>
                            <td><span class="id-badge">#<?php echo $l['id']; ?></span></td>
                            <td style="font-weight:600"><i class="fas fa-file-pdf" style="color:#ef4444;margin-right:.4rem"></i><?php echo htmlspecialchars($l['nom_fichier']); ?></td>
                            <td style="color:#64748b;font-size:.82rem"><?php echo htmlspecialchars($mTitle); ?></td>
                            <td style="color:#64748b"><?php echo date('d/m/Y', strtotime($l['date_remise'])); ?></td>
                            <td><span class="statut-badge <?php echo $eClass; ?>"><?php echo htmlspecialchars($l['etat']); ?></span></td>
                            <td>
                                <div class="action-group">
                                    <a href="index.php?action=back_livrable_edit&id=<?php echo $l['id']; ?>" class="btn-icon edit"><i class="fas fa-pen"></i></a>
                                    <a href="index.php?action=back_livrable_delete&id=<?php echo $l['id']; ?>" class="btn-icon del" onclick="return confirm('Supprimer ce livrable ?')"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- DIGITBOT -->
<button class="chatbot-trigger" onclick="toggleBot()"><i class="fas fa-robot"></i></button>
<div class="chatbot-window" id="chatbotWindow">
    <div class="chatbot-header">
        <div class="bot-avatar"><i class="fas fa-robot"></i></div>
        <div>
            <div class="bot-name">DigitBot IA</div>
            <div class="bot-status"><span class="bot-online"></span> Assistant En ligne</div>
        </div>
        <button class="chatbot-close" onclick="toggleBot()"><i class="fas fa-chevron-down"></i></button>
    </div>
    <div class="chatbot-messages" id="chatMessages">
        <div class="msg msg-bot">Bonjour ! Je suis <strong>DigitBot IA</strong>, votre assistant pour la gestion des missions. Comment puis-je vous aider ?</div>
    </div>
    <div id="msgLoading" class="msg-loading">L'IA réfléchit...</div>
    <div class="chatbot-input-row">
        <input type="text" class="chatbot-input" id="chatInput" placeholder="Posez n'importe quelle question..." onkeydown="if(event.key==='Enter') sendMessage()">
        <button class="btn-send" onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
    </div>
</div>

<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script>
function switchTab(tab){
    document.querySelectorAll('.tab-btn').forEach((b,i)=>b.classList.toggle('active',(i===0&&tab==='missions')||(i===1&&tab==='livrables')));
    document.getElementById('tab-missions').classList.toggle('active',tab==='missions');
    document.getElementById('tab-livrables').classList.toggle('active',tab==='livrables');
}
function filterTable(){
    const q=document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('#missionsTable tbody tr').forEach(r=>r.style.display=r.textContent.toLowerCase().includes(q)?'':'none');
}
function sortTable(key){
    const tbody=document.querySelector('#missionsTable tbody');
    const rows=Array.from(tbody.querySelectorAll('tr'));
    const col={titre:1,statut:3,date:2}[key]??0;
    if(!col)return;
    rows.sort((a,b)=>(a.cells[col]?.textContent.trim()??'').localeCompare(b.cells[col]?.textContent.trim()??'','fr'));
    rows.forEach(r=>tbody.appendChild(r));
}
function exportCSV(){
    const rows=document.querySelectorAll('#missionsTable tr');
    const csv=Array.from(rows).map(r=>Array.from(r.querySelectorAll('th,td')).slice(0,5).map(c=>'"'+c.textContent.trim().replace(/"/g,'""')+'"').join(',')).join('\n');
    const a=document.createElement('a');
    a.href='data:text/csv;charset=utf-8,\uFEFF'+encodeURIComponent(csv);
    a.download='missions_export.csv';a.click();
}
setTimeout(()=>{document.getElementById('successToast')?.remove();},4000);
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
    const missionsData=<?php echo json_encode(array_map(fn($m)=>['id'=>$m['id'],'titre'=>$m['titre'],'statut'=>$m['statut'],'date_debut'=>$m['date_debut']],$missions)); ?>;
    const context=`Tu es DigitBot IA, assistant de la plateforme Digit Advisory pour les missions consulting. Missions actuelles: ${JSON.stringify(missionsData)}. Réponds en français, sois concis.`;
    try{
        const messages=[{role:'user',content:context+'\n\nQuestion: '+chatHistory[0].content},...chatHistory.slice(1)];
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
