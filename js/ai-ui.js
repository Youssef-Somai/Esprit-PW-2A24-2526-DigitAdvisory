/* ═══════════════════════════════════════════════════════
   AI UI — Dashboard, Modals, PDF, Templates
   Digit Advisory
   (C) 2026
═══════════════════════════════════════════════════════ */
'use strict';

function wireAIFeatures() {
    const btnBio = byId('btn-gen-bio');
    const btnPdf = byId('btn-export-pdf');
    const btnTpl = byId('btn-tpl-select');

    if(btnBio) btnBio.addEventListener('click', handleGenBio);
    if(btnPdf) btnPdf.addEventListener('click', handleExportPDF);
    if(btnTpl) btnTpl.addEventListener('click', () => { 
        renderTemplateGrid(); 
        if(window.showOv) window.showOv('ov-template'); 
    });

    ['ov-template','ov-skillgap','ov-career','ov-salary'].forEach(id => {
        const el = byId(id);
        if(el) el.addEventListener('click', e => { if(e.target === el) if(window.hideOv) window.hideOv(id); });
    });
}

async function renderAIDashboard(p) {
    const host = byId('ai-dashboard-host');
    if(!host) return;
    if(!p) { host.innerHTML = ''; return; }

    // Afficher spinner pendant l'appel OpenAI
    host.innerHTML = `<div style="text-align:center;padding:3rem;"><span class="spin" style="width:40px;height:40px;border-width:4px;"></span><div style="margin-top:1rem;color:#64748b;font-size:.9rem;">Analyse IA en cours <b>OpenAI ChatGPT</b>...</div></div>`;

    let scores, market, recs;
    try {
        const analysis = await window.analyzeProfileAI(p);
        scores = {
            total: analysis.score_global || 0,
            skills: analysis.score_competences || 0,
            experience: analysis.score_experience || 0,
            certifications: analysis.score_certifications || 0,
            completeness: analysis.score_coherence || 0
        };
        market = analysis.compatibilite_marche || {};
        recs = (analysis.recommandations || []).map(r => ({
            icon: r.icone || 'fa-lightbulb',
            text: r.texte,
            priority: r.priorite === 'haute' ? 'high' : r.priorite === 'moyenne' ? 'med' : 'low'
        }));
    } catch(e) {
        host.innerHTML = `<p style="color:#ef4444;text-align:center;padding:2rem;">Erreur OpenAI : ${esc(e.message)}</p>`;
        return;
    }
    const circumference = 2 * Math.PI * 70;
    const offset = circumference - (scores.total / 100) * circumference;
    const gradeColor = scores.total >= 80 ? '#10b981' : scores.total >= 60 ? '#06b6d4' : scores.total >= 40 ? '#f59e0b' : '#ef4444';
    const gradeLabel = scores.total >= 80 ? 'Excellent' : scores.total >= 60 ? 'Bon' : scores.total >= 40 ? 'A am\u00E9liorer' : 'Insuffisant';

    const detailCards = [
        { cls:'sk', icon:'fa-code', name:'Comp\u00E9tences', val:scores.skills, fill:'c1' },
        { cls:'xp', icon:'fa-briefcase', name:'Exp\u00E9rience', val:scores.experience, fill:'c2' },
        { cls:'ct', icon:'fa-award', name:'Certifications', val:scores.certifications, fill:'c3' },
        { cls:'co', icon:'fa-puzzle-piece', name:'Coh\u00E9rence', val:scores.completeness, fill:'c4' }
    ];

    const marketHTML = Object.entries(market).map(([s,v],i) =>
        `<div class="market-row"><span class="market-lbl">${esc(s)}</span><div class="market-track"><div class="market-fill" style="width:${v}%;background:${['#06b6d4','#7c3aed','#f59e0b','#10b981','#ef4444'][i%5]}"></div></div><span class="market-pct">${v}%</span></div>`
    ).join('');

    const recoHTML = recs.map(r =>
        `<div class="reco-item"><div class="reco-ic"><i class="fa-solid ${r.icon}"></i></div><div><div class="reco-txt">${esc(r.text)}</div><span class="reco-pri ${r.priority==='high'?'high':r.priority==='med'?'med':'low'}">${r.priority==='high'?'Priorit\u00E9 haute':r.priority==='med'?'Priorit\u00E9 moyenne':'Suggestion'}</span></div></div>`
    ).join('');

    host.innerHTML = `
    <div class="ai-dashboard fade-in">
        <div class="ai-score-main">
            <div class="gauge-wrap">
                <svg class="gauge-svg" viewBox="0 0 160 160">
                    <circle class="gauge-bg" cx="80" cy="80" r="70"/>
                    <circle class="gauge-fill" cx="80" cy="80" r="70" stroke="${gradeColor}" stroke-dasharray="${circumference}" stroke-dashoffset="${offset}"/>
                </svg>
                <div class="gauge-score"><div class="gauge-num" style="color:${gradeColor}">${scores.total}</div><div class="gauge-label">${gradeLabel}</div></div>
            </div>
            <div class="ai-score-title">Score Global du Profil</div>
        </div>
        <div class="ai-right-panel">
            <div class="ai-detail-grid">
                ${detailCards.map(d => `
                <div class="ai-detail-card">
                    <div class="ai-detail-hdr"><div class="ai-detail-icon ${d.cls}"><i class="fa-solid ${d.icon}"></i></div><div><div class="ai-detail-name">${d.name}</div><div class="ai-detail-val">${d.val}/100</div></div></div>
                    <div class="ai-prog-track"><div class="ai-prog-fill ${d.fill}" style="width:${d.val}%"></div></div>
                </div>`).join('')}
            </div>
            <div class="ai-card"><h3><i class="fa-solid fa-chart-bar" style="color:#06b6d4"></i> Compatibilit\u00E9 March\u00E9</h3>${marketHTML}</div>
        </div>
    </div>
    <div class="ai-card fade-in" style="margin-top:1.5rem;"><h3><i class="fa-solid fa-robot" style="color:#06b6d4"></i> Recommandations IA</h3>${recoHTML || '<p style="color:#64748b;font-size:.85rem">Aucune recommandation \u2014 votre profil est excellent !</p>'}</div>
    <div class="ai-actions fade-in">
        <button class="ai-abtn" onclick="window.openSkillGap()"><i class="fa-solid fa-magnifying-glass-chart"></i> Skill Gap Analysis</button>
        <button class="ai-abtn" onclick="window.openCareerPath()"><i class="fa-solid fa-route"></i> Trajectoire de Carri\u00E8re</button>
        <button class="ai-abtn" onclick="window.openSalaryEstimator()"><i class="fa-solid fa-coins"></i> Estimateur de Salaire</button>
    </div>
    <div class="ai-stats fade-in">
        <div class="ai-card"><h3><i class="fa-solid fa-chart-pie" style="color:#06b6d4"></i> Radar de Comp\u00E9tences</h3><div class="radar-wrap"><canvas id="radar-canvas" width="280" height="280"></canvas></div></div>
        <div class="ai-card"><h3><i class="fa-solid fa-chart-line" style="color:#7c3aed"></i> Synth\u00E8se du Profil</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.8rem;margin-top:.5rem;">
                <div style="text-align:center;padding:1rem;background:#f8fafc;border-radius:12px;"><div style="font-family:Poppins,sans-serif;font-size:1.8rem;font-weight:800;color:#06b6d4;">${(p.skills||[]).length}</div><div style="font-size:.72rem;color:#64748b;text-transform:uppercase;font-weight:600;">Comp\u00E9tences</div></div>
                <div style="text-align:center;padding:1rem;background:#f8fafc;border-radius:12px;"><div style="font-family:Poppins,sans-serif;font-size:1.8rem;font-weight:800;color:#7c3aed;">${(p.experiences||[]).length}</div><div style="font-size:.72rem;color:#64748b;text-transform:uppercase;font-weight:600;">Exp\u00E9riences</div></div>
                <div style="text-align:center;padding:1rem;background:#f8fafc;border-radius:12px;"><div style="font-family:Poppins,sans-serif;font-size:1.8rem;font-weight:800;color:#f59e0b;">${(p.certifications||[]).length}</div><div style="font-size:.72rem;color:#64748b;text-transform:uppercase;font-weight:600;">Certifications</div></div>
                <div style="text-align:center;padding:1rem;background:#f8fafc;border-radius:12px;"><div style="font-family:Poppins,sans-serif;font-size:1.8rem;font-weight:800;color:#10b981;">${scores.total}%</div><div style="font-size:.72rem;color:#64748b;text-transform:uppercase;font-weight:600;">Score Global</div></div>
            </div>
        </div>
    </div>`;
    setTimeout(() => drawRadar(scores, market), 300);
}
window.renderAIDashboard = renderAIDashboard;

function drawRadar(scores, market) {
    const canvas = byId('radar-canvas');
    if(!canvas) return;
    const ctx = canvas.getContext('2d');
    const cx=140, cy=140, r=110;
    const mKeys = Object.keys(market || {});
    const mAvg = mKeys.length > 0 ? Object.values(market).reduce((a,b)=>a+b,0) / mKeys.length : 0;
    const values = [scores.skills || 0, scores.experience || 0, scores.certifications || 0, scores.completeness || 0, mAvg];
    const labels = ['Compétences','Expérience','Certifications','Cohérence','Marché'];

    ctx.clearRect(0,0,280,280);
    ctx.strokeStyle = '#e2e8f0'; ctx.lineWidth = 1;
    for(let i=1; i<=4; i++){
        ctx.beginPath();
        for(let j=0; j<5; j++){
            const angle = (j*2*Math.PI/5) - Math.PI/2;
            const x = cx + (r*i/4)*Math.cos(angle), y = cy + (r*i/4)*Math.sin(angle);
            if(j===0) ctx.moveTo(x,y); else ctx.lineTo(x,y);
        }
        ctx.closePath(); ctx.stroke();
    }
    for(let i=0; i<5; i++){
        const angle = (i*2*Math.PI/5) - Math.PI/2;
        ctx.beginPath(); ctx.moveTo(cx,cy); ctx.lineTo(cx+r*Math.cos(angle), cy+r*Math.sin(angle)); ctx.stroke();
    }
    ctx.beginPath(); ctx.fillStyle = 'rgba(6,182,212,0.2)'; ctx.strokeStyle = '#06b6d4'; ctx.lineWidth = 2;
    for(let i=0; i<5; i++){
        const angle = (i*2*Math.PI/5) - Math.PI/2;
        const v = (values[i]/100)*r;
        const x = cx+v*Math.cos(angle), y = cy+v*Math.sin(angle);
        if(i===0) ctx.moveTo(x,y); else ctx.lineTo(x,y);
    }
    ctx.closePath(); ctx.fill(); ctx.stroke();
    for(let i=0; i<5; i++){
        const angle = (i*2*Math.PI/5) - Math.PI/2;
        const v = (values[i]/100)*r;
        const x = cx+v*Math.cos(angle), y = cy+v*Math.sin(angle);
        ctx.beginPath(); ctx.arc(x,y,4,0,Math.PI*2); ctx.fillStyle='#06b6d4'; ctx.fill();
        const lx = cx+(r+22)*Math.cos(angle), ly = cy+(r+22)*Math.sin(angle);
        ctx.fillStyle='#64748b'; ctx.font='600 10px Inter,sans-serif'; ctx.textAlign='center'; ctx.textBaseline='middle';
        ctx.fillText(labels[i],lx,ly);
    }
}

async function openSkillGap() {
    const p = (window.portfolios || []).find(x => x.id_portfolio == window.previewId);
    if(!p) return;
    const body = byId('skillgap-body');
    body.innerHTML = `<div style="text-align:center;padding:2rem;"><span class="spin"></span><div style="margin-top:.8rem;color:#64748b;font-size:.85rem;">Analyse OpenAI en cours...</div></div>`;
    if(window.showOv) window.showOv('ov-skillgap');
    try {
        const gap = await window.getSkillGapAI(p);
        const sp = gap.secteur_principal;
        let html = `<div class="gap-sec"><div class="gap-sec-ttl"><i class="fa-solid fa-industry" style="color:#06b6d4"></i> ${esc(sp.nom)} &mdash; ${sp.couverture_pct}% couverture</div>`;
        (sp.competences_possedees||[]).forEach(s => { html += `<div class="gap-row"><span class="gap-name">${esc(s)}</span><div class="gap-track"><div class="gap-have" style="width:100%"></div></div><span class="gap-st">&#10003;</span></div>`; });
        (sp.competences_manquantes||[]).forEach(s => { html += `<div class="gap-row"><span class="gap-name">${esc(s)}</span><div class="gap-track"><div class="gap-have" style="width:0%"></div></div><span class="gap-st" style="color:#ef4444">&#10007;</span></div>`; });
        html += '</div>';
        if(gap.plan_formation && gap.plan_formation.length) {
            html += `<div class="gap-sec"><div class="gap-sec-ttl"><i class="fa-solid fa-graduation-cap" style="color:#7c3aed"></i> Plan de formation recommandé par l'IA</div>`;
            gap.plan_formation.forEach(f => { html += `<div class="gap-row"><span class="gap-name">${esc(f.competence)}</span><span class="reco-pri ${f.priorite==='haute'?'high':f.priorite==='moyenne'?'med':'low'}" style="margin-left:auto;">${esc(f.duree_estimee)}</span></div>`; });
            html += '</div>';
        }
        html += `<div style="text-align:center;margin-top:1rem;font-size:.72rem;color:#94a3b8;"><i class="fa-solid fa-robot"></i> Analyse générée par OpenAI ChatGPT</div>`;
        body.innerHTML = html;
    } catch(e) { body.innerHTML = `<p style="color:#ef4444;">Erreur IA : ${esc(e.message)}</p>`; }
}
window.openSkillGap = openSkillGap;

async function openCareerPath() {
    const p = (window.portfolios || []).find(x => x.id_portfolio == window.previewId);
    if(!p) return;
    const body = byId('career-body');
    body.innerHTML = `<div style="text-align:center;padding:2rem;"><span class="spin"></span><div style="margin-top:.8rem;color:#64748b;font-size:.85rem;">OpenAI analyse votre trajectoire...</div></div>`;
    if(window.showOv) window.showOv('ov-career');
    try {
        const cp = await window.getCareerPathAI(p);
        const steps = cp.etapes || [];
        let html = '';
        steps.forEach((node, i) => {
            const isCurrent = node.statut === 'actuel';
            const isPast    = node.statut === 'passé';
            const dotIcon   = isPast ? '&#10003;' : isCurrent ? '&#9733;' : (i+1);
            const dotCls    = isPast ? 'past' : isCurrent ? 'now' : 'fut';
            html += `<div class="career-node"><div class="career-dot-col"><div class="career-dot ${dotCls}">${dotIcon}</div>${i<steps.length-1?`<div class="career-line${isPast?' done':''}"></div>`:''}</div><div class="career-body${isCurrent?' now':''}"><div class="career-role">${esc(node.role)}</div><div class="career-yrs"><i class="fa-regular fa-clock"></i> ${esc(node.duree)}</div><div class="career-sal"><i class="fa-solid fa-coins"></i> ${esc(node.fourchette_salaire)}</div>${node.conseil?`<div style="font-size:.78rem;color:#64748b;margin-top:.4rem;font-style:italic;">${esc(node.conseil)}</div>`:''}</div></div>`;
        });
        if(cp.message_motivation) html += `<div style="margin-top:1.2rem;padding:1rem;background:linear-gradient(135deg,#06b6d415,#7c3aed15);border-radius:12px;font-size:.85rem;color:#334155;font-style:italic;"><i class="fa-solid fa-robot" style="color:#7c3aed;"></i> ${esc(cp.message_motivation)}</div>`;
        html += `<div style="text-align:center;margin-top:1rem;font-size:.72rem;color:#94a3b8;"><i class="fa-solid fa-robot"></i> Trajectoire générée par OpenAI ChatGPT</div>`;
        body.innerHTML = html;
    } catch(e) { body.innerHTML = `<p style="color:#ef4444;">Erreur IA : ${esc(e.message)}</p>`; }
}
window.openCareerPath = openCareerPath;

async function openSalaryEstimator() {
    const p = (window.portfolios || []).find(x => x.id_portfolio == window.previewId);
    if(!p) return;
    const body = byId('salary-body');
    body.innerHTML = `<div style="text-align:center;padding:2rem;"><span class="spin"></span><div style="margin-top:.8rem;color:#64748b;font-size:.85rem;">OpenAI estime votre salaire...</div></div>`;
    if(window.showOv) window.showOv('ov-salary');
    try {
        const sal = await window.estimateSalaryAI(p);
        const fmtK = v => (v/1000).toFixed(0) + 'K€';
        const tendIcon = sal.tendance_marche === 'en hausse' ? '↗️' : sal.tendance_marche === 'en baisse' ? '↘️' : '➡️';
        body.innerHTML = `
        <div class="sal-display">
            <div style="font-size:.75rem;color:#64748b;text-transform:uppercase;font-weight:700;letter-spacing:.05em;margin-bottom:.5rem;">Salaire Annuel Estimé par OpenAI</div>
            <div class="sal-amount">${fmtK(sal.salaire_median)}</div>
            <div class="sal-range">
                <div class="sal-bound"><div class="sal-bound-lbl">Minimum</div><div class="sal-bound-val">${fmtK(sal.salaire_min)}</div></div>
                <div class="sal-bound"><div class="sal-bound-lbl">Médian</div><div class="sal-bound-val" style="color:#06b6d4">${fmtK(sal.salaire_median)}</div></div>
                <div class="sal-bound"><div class="sal-bound-lbl">Maximum</div><div class="sal-bound-val">${fmtK(sal.salaire_max)}</div></div>
            </div>
            ${sal.justification?`<div style="margin-top:1rem;padding:.8rem;background:#f8fafc;border-radius:10px;font-size:.82rem;color:#475569;font-style:italic;">${esc(sal.justification)}</div>`:''}
            <div style="margin-top:.8rem;font-size:.8rem;color:#64748b;">Tendance marché : <b>${tendIcon} ${esc(sal.tendance_marche||'stable')}</b></div>
        </div>
        <div style="text-align:center;margin-top:1rem;font-size:.72rem;color:#94a3b8;"><i class="fa-solid fa-robot"></i> Estimation générée par OpenAI ChatGPT</div>`;
    } catch(e) { body.innerHTML = `<p style="color:#ef4444;">Erreur IA : ${esc(e.message)}</p>`; }
}
window.openSalaryEstimator = openSalaryEstimator;

async function handleGenBio() {
    const btn = byId('btn-gen-bio');
    const title = byId('f-title') ? byId('f-title').value.trim() : '';
    const level = byId('f-level') ? byId('f-level').value : 'junior';
    const skills = window.currentSkills || [];
    const experiences = window.currentExperiences || [];
    const certifications = window.currentCertifications || [];
    if(!title) { toast('err','Remplissez d\'abord le titre professionnel.'); return; }
    if(btn) { btn.disabled = true; btn.innerHTML = '<span class="spin"></span> OpenAI...'; }
    try {
        const bio = await window.generateBioAI({
            professional_title: title,
            experience_level: level || 'junior',
            full_name: byId('f-name') ? byId('f-name').value : '',
            preferred_industry: byId('f-industry') ? byId('f-industry').value : '',
            location: byId('f-location') ? byId('f-location').value : '',
            skills, experiences, certifications
        });
        if(byId('f-bio')) byId('f-bio').value = bio;
        toast('ok', 'Bio générée par OpenAI !');
    } catch(e) {
        toast('err', 'Erreur OpenAI : ' + e.message);
    } finally {
        if(btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Générer Bio IA'; }
    }
}
window.handleGenBio = handleGenBio;

function handleExportPDF() {
    const el = byId('cv-doc-content');
    if(!el) return;
    const btn = byId('btn-export-pdf');
    btn.disabled = true; btn.innerHTML = '<span class="spin"></span> Export...';
    // Use scale 1 to prevent memory crash (black screen) on mobile devices
    html2pdf().set({ margin:0, filename:'CV-Expert.pdf', image:{type:'jpeg',quality:0.98}, html2canvas:{scale:1,useCORS:true}, jsPDF:{unit:'mm',format:'a4',orientation:'portrait'} }).from(el).save().then(() => {
        toast('ok','PDF export\u00E9 !');
    }).finally(() => {
        btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-file-pdf"></i> Export PDF';
    });
}
window.handleExportPDF = handleExportPDF;

const TEMPLATES = [
    { id:'classic', name:'Classique', desc:'Design professionnel 2 colonnes', icon:'fa-solid fa-file-lines', color:'#0f172a' },
    { id:'modern', name:'Moderne', desc:'\u00C9pur\u00E9 avec accents d\u00E9grad\u00E9s', icon:'fa-solid fa-wand-magic-sparkles', color:'#06b6d4' },
    { id:'creative', name:'Cr\u00E9atif', desc:'Layout dynamique et visuel', icon:'fa-solid fa-palette', color:'#7c3aed' }
];

function renderTemplateGrid() {
    const current = (typeof window.currentTemplate !== 'undefined') ? window.currentTemplate : 'classic';
    const grid = byId('tpl-grid');
    if(!grid) return;
    grid.innerHTML = TEMPLATES.map(t =>
        `<div class="tpl-opt${current===t.id?' sel':''}" onclick="window.selectTemplate('${t.id}')">
            <div class="tpl-icon" style="background:${t.color}15;color:${t.color}"><i class="${t.icon}"></i></div>
            <div class="tpl-name">${t.name}</div><div class="tpl-desc">${t.desc}</div></div>`
    ).join('');
}
window.renderTemplateGrid = renderTemplateGrid;

function selectTemplate(id) {
    if(typeof window.currentTemplate !== 'undefined') window.currentTemplate = id;
    renderTemplateGrid();
    if(window.previewId) { 
        if(window.hideOv) window.hideOv('ov-template'); 
        if(window.openPreview) window.openPreview(window.previewId); 
    }
}
window.selectTemplate = selectTemplate;
