/* ═══════════════════════════════════════════════════════
   AI ENGINES — Digit Advisory
   ⚠️  Aucune logique IA côté JS.
   Tous les appels passent par AIController.php (OpenAI).
═══════════════════════════════════════════════════════ */
'use strict';

const AI_ENDPOINT = '../../Controller/AIController.php';

async function callAI(action, data) {
    const res = await fetch(`${AI_ENDPOINT}?action=${action}`, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify(data),
    });
    const json = await res.json();
    if (!json.success) throw new Error(json.error || 'Erreur OpenAI.');
    return json;
}

window.generateBioAI    = d => callAI('generate_bio',    d).then(r => r.bio);
window.analyzeProfileAI = d => callAI('analyze_profile', d).then(r => r.analysis);
window.getSkillGapAI    = d => callAI('skill_gap',       d).then(r => r.skill_gap);
window.getCareerPathAI  = d => callAI('career_path',     d).then(r => r.career_path);
window.estimateSalaryAI = d => callAI('salary_estimate', d).then(r => r.salary);
