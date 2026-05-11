<?php
session_start();

if (!isset($_SESSION['user']['id_user']) || strtolower($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: ../FrontOffice/login.php');
    exit;
}

require_once __DIR__ . '/../../Controller/utilisateur_controller.php';
$controller = new UtilisateurController();
$users = $controller->listeUsers();

// --- Statistiques pour le graphique ---
$countExpert = 0;
$countEntreprise = 0;
foreach($users as $user) {
    if (strtolower($user['role'] ?? '') === 'expert') $countExpert++;
    elseif (strtolower($user['role'] ?? '') === 'entreprise') $countEntreprise++;
}
$totalRole = $countExpert + $countEntreprise;
$pctExpert = $totalRole > 0 ? round(($countExpert / $totalRole) * 100) : 0;
$pctEntreprise = $totalRole > 0 ? 100 - $pctExpert : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back Office | Gestion Utilisateurs</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        .sidebar { background: var(--dark); color: white; }
        .sidebar .menu-item { color: var(--gray-light); }
        .sidebar .menu-item:hover, .sidebar .menu-item.active { background: rgba(255,255,255,0.1); color: white; border-left-color: var(--accent); }
        .sidebar-header { border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header .logo { color: white; }
        .user-profile-widget { background: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.1); }
        .dashboard-container { display: flex; min-height: 100vh; }
        .sidebar { width: 280px; display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100; transition: var(--transition); }
        .sidebar-header { padding: 1.5rem; display: flex; align-items: center; }
        .sidebar-menu { padding: 1rem 0; flex: 1; overflow-y: auto; }
        .menu-item { padding: 0.75rem 1.5rem; display: flex; align-items: center; gap: 1rem; font-weight: 500; cursor: pointer; transition: var(--transition); border-left: 3px solid transparent; text-decoration: none; }
        .menu-item i { width: 20px; text-align: center; font-size: 1.1rem; }
        .user-profile-widget { padding: 1rem 1.5rem; display: flex; align-items: center; gap: 1rem; }
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--accent); color: white; display: flex; justify-content: center; align-items: center; font-weight: 600; }
        .main-content { flex: 1; margin-left: 280px; padding: 2rem; background: #f1f5f9; min-height: 100vh; }
        .top-navbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; background: white; padding: 1rem 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); }
        .card { background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-sm); margin-bottom: 2rem; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--gray-light); }
        .data-table th { color: var(--gray); font-weight: 500; }
        .badge { padding: 0.25rem 0.75rem; border-radius: var(--radius-full); font-size: 0.85rem; font-weight: 500; display: inline-block;}
        .badge.success { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .badge.warning { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
        .badge.primary { background: rgba(37, 99, 235, 0.1); color: var(--primary); }
        .btn-sm { padding: 0.4rem 0.8rem; font-size: 0.85rem; }
    </style>
</head>
<body class="admin-theme">
    <div class="dashboard-container">
        <!-- Sidebar ADMIN -->
        <aside class="sidebar admin-sidebar slide-in-right">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fa-solid fa-user-shield text-accent"></i>
                    Admin Panel
                </div>
            </div>
            
            <div class="sidebar-menu">
                <a href="back-utilisateur.php" class="menu-item active"><i class="fa-solid fa-users"></i> Gestion Utilisateurs</a>
                <a href="back-quiz.php" class="menu-item"><i class="fa-solid fa-list-check"></i> Gestion Quiz</a>
                <a href="back-portfolio.php" class="menu-item"><i class="fa-solid fa-folder-open"></i> Gestion Portfolios</a>
                <a href="back-offres.php" class="menu-item"><i class="fa-solid fa-briefcase"></i> Gestion Offres</a>
                <a href="back-certification.php" class="menu-item"><i class="fa-solid fa-award"></i> Gestion Certifications</a>
                <a href="back-messagerie.php" class="menu-item"><i class="fa-solid fa-comments"></i> Gestion Messagerie</a>
            </div>

            <div class="user-profile-widget">
                <div class="user-avatar">AD</div>
                <div>
                    <h4 style="font-size: 0.95rem; margin-bottom: 0.2rem; color: white;">Admin SystÃ¨me</h4>
                    <span style="font-size: 0.8rem; color: var(--gray-light);">Admin</span>
                </div>
                <a href="../../View/FrontOffice/login.php#register" style="margin-left: auto; color: var(--danger);"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
            </div>
        </aside>

        <main class="main-content">
            <div class="top-navbar">
                <h2 style="margin: 0; font-size: 1.5rem;">Administration - RÃ´le Superviseur</h2>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <span class="badge warning" style="font-size: 1rem;"><i class="fa-solid fa-lock"></i> Espace SÃ©curisÃ© Admin</span>
                </div>
            </div>

            <!-- Stats -->
            <div style="display: flex; gap: 2rem; margin-bottom: 2rem;">
                <div class="card admin-card hover-zoom fade-in-up" style="flex: 1; display:flex; align-items:center; gap: 2rem; margin-bottom:0;">
                    <div style="
                        width: 100px; 
                        height: 100px; 
                        border-radius: 50%; 
                        background: conic-gradient(var(--warning) 0% <?php echo $pctExpert; ?>%, var(--primary) <?php echo $pctExpert; ?>% 100%);
                    "></div>
                    <div>
                        <h3 style="margin:0 0 0.5rem 0;">Répartition des rôles</h3>
                        <div style="display:flex; align-items:center; gap: 0.5rem; font-weight:500;">
                            <div style="width:12px;height:12px;background:var(--warning);border-radius:3px;"></div> 
                            Expert: <?php echo $pctExpert; ?>% (<?php echo $countExpert; ?>)
                        </div>
                        <div style="display:flex; align-items:center; gap: 0.5rem; font-weight:500; margin-top:0.25rem;">
                            <div style="width:12px;height:12px;background:var(--primary);border-radius:3px;"></div> 
                            Entreprise: <?php echo $pctEntreprise; ?>% (<?php echo $countEntreprise; ?>)
                        </div>
                    </div>
                </div>
            </div>

            <!-- MODULE: Utilisateur (Admin) -->
            <section class="fade-in-up">
                <div style="display: flex; justify-content: space-between; align-items: center;" class="mb-2">
                    <h2>Gestion des Utilisateurs</h2>
                    <div>
                        <button id="exportPdfBtn" class="btn btn-outline" style="border-color: #dc2626; color: #dc2626; margin-right: 0.5rem;"><i class="fa-solid fa-file-pdf"></i> Exporter PDF</button>
                        <a href="addUtilisateur.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Ajouter Utilisateur</a>
                    </div>
                </div>

                <div class="card admin-card hover-zoom">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <input type="text" id="emailSearchInput" placeholder="Rechercher par email..." style="padding: 0.5rem; border: 1px solid var(--gray-light); border-radius: var(--radius); width: 250px;">
                        <div style="display: flex; gap: 0.5rem;">
                            <button id="sortNameBtn" class="btn btn-outline" style="border: 1px solid var(--gray-light); padding: 0.5rem 1rem; border-radius: var(--radius); cursor: pointer; color: var(--dark);"><i class="fa-solid fa-sort-alpha-down"></i> Trier par nom</button>
                            <select id="roleSelectInput" style="padding: 0.5rem; border: 1px solid var(--gray-light); border-radius: var(--radius);">
                                <option value="all">Tous les rôles</option>
                                <option value="entreprise">Entreprise</option>
                                <option value="expert">Expert</option>
                            </select>
                        </div>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom / Raison Sociale</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Domaine / Secteur</th>
                                <th>Adresse</th>
                                <th>Téléphone / Tarif</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody">
                            <?php foreach ($users as $u): ?>
                                <tr data-role="<?php echo htmlspecialchars(strtolower($u['role'] ?? '')); ?>">
                                    <td>#U<?php echo htmlspecialchars($u['id_user']); ?></td>
                                    <td><?php echo $u['role'] === 'expert' ? htmlspecialchars(trim(($u['nom'] ?? '') . ' ' . ($u['prenom'] ?? ''))) : htmlspecialchars($u['nom_entreprise'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td>
                                        <?php if (strtolower($u['role']) === 'expert'): ?>
                                            <span class="badge warning" style="background:rgba(14,165,233,0.1); color:var(--secondary);">Expert</span>
                                        <?php elseif (strtolower($u['role']) === 'entreprise'): ?>
                                            <span class="badge primary">Entreprise</span>
                                        <?php else: ?>
                                            <span class="badge success"><?php echo htmlspecialchars(ucfirst($u['role'])); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars(strtolower($u['role']) === 'expert' ? ($u['domaine'] ?? '') : ($u['secteur_activite'] ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars($u['adresse'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars(strtolower($u['role']) === 'expert' ? ($u['tarif_journalier'] ?? '') : ($u['telephone'] ?? '')); ?></td>
                                    <td>
                                        <?php if (strtolower($u['statut_compte']) === 'actif'): ?>
                                            <span class="badge success">Actif</span>
                                        <?php elseif (strtolower($u['statut_compte']) === 'en attente'): ?>
                                            <span class="badge warning">En attente</span>
                                        <?php else: ?>
                                            <span class="badge primary"><?php echo htmlspecialchars(ucfirst($u['statut_compte'])); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form action="../traitement/banUtilisateurTraitement.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="id_user" value="<?php echo htmlspecialchars($u['id_user']); ?>">
                                            <?php $isBanned = in_array(strtolower($u['statut_compte'] ?? ''), ['désactivé', 'banni', 'ban', 'banned', 'suspendu', '']); ?>
                                            <?php if ($isBanned): ?>
                                                <input type="hidden" name="new_status" value="actif">
                                                <button type="submit" class="btn btn-outline btn-sm" style="color:var(--success); border-color:var(--success);" title="Débannir" onclick="return confirm('Débannir cet utilisateur et restaurer son accès ?');"><i class="fa-solid fa-unlock"></i> Débannir</button>
                                            <?php else: ?>
                                                <input type="hidden" name="new_status" value="désactivé">
                                                <button type="submit" class="btn btn-outline btn-sm" style="color:var(--warning); border-color:var(--warning);" title="Bannir" onclick="return confirm('Bannir cet utilisateur ? Il ne pourra plus se connecter.');"><i class="fa-solid fa-ban"></i> Bannir</button>
                                            <?php endif; ?>
                                        </form>
                                        <form action="../traitement/deleteUtilisateurTraitement.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="id_user" value="<?php echo htmlspecialchars($u['id_user']); ?>">
                                            <button type="submit" class="btn btn-outline btn-sm" style="color:var(--danger); border-color:var(--danger);" title="Supprimer" onclick="return confirm('Supprimer cet utilisateur ?');"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr id="noResultsRow" style="display: none;">
                                <td colspan="9" style="text-align: center; color: var(--danger); font-weight: bold; padding: 2rem;">Aucun résultat</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <!-- Pagination container -->
                    <div id="paginationControls" style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 1.5rem; align-items: center;"></div>
                </div>
            </section>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('emailSearchInput');
            const roleSelectInput = document.getElementById('roleSelectInput');
            const tableBody = document.getElementById('userTableBody');
            const noResultsRow = document.getElementById('noResultsRow');
            const paginationControls = document.getElementById('paginationControls');

            const itemsPerPage = 10;
            let currentPage = 1;
            let filteredRows = [];

            function filterTable() {
                const searchTerm = searchInput.value.trim().toLowerCase();
                const roleFilter = roleSelectInput.value;
                const rows = Array.from(tableBody.querySelectorAll('tr:not(#noResultsRow)'));
                
                filteredRows = [];

                rows.forEach(row => {
                    const emailCell = row.cells[2];
                    const rowRole = row.getAttribute('data-role');
                    
                    if (emailCell) {
                        const email = emailCell.textContent.toLowerCase();
                        
                        const matchesSearch = email.includes(searchTerm);
                        const matchesRole = (roleFilter === 'all') || (rowRole === roleFilter);

                        if (matchesSearch && matchesRole) {
                            filteredRows.push(row);
                        } else {
                            row.style.display = 'none'; // Hide non-matching rows immediately
                        }
                    }
                });

                if (filteredRows.length === 0 && rows.length > 0) {
                    noResultsRow.style.display = '';
                    paginationControls.innerHTML = '';
                } else {
                    noResultsRow.style.display = 'none';
                    currentPage = 1; // Reset to first page on filter change
                    displayPage();
                    setupPagination();
                }
            }

            function displayPage() {
                const startIndex = (currentPage - 1) * itemsPerPage;
                const endIndex = startIndex + itemsPerPage;

                filteredRows.forEach((row, index) => {
                    if (index >= startIndex && index < endIndex) {
                        row.style.display = ''; // Show
                    } else {
                        row.style.display = 'none'; // Hide out-of-bounds rows
                    }
                });
            }

            function setupPagination() {
                paginationControls.innerHTML = '';
                const totalPages = Math.ceil(filteredRows.length / itemsPerPage);

                if (totalPages <= 1) return; // No pagination needed

                // Bouton Précédent
                const prevBtn = document.createElement('button');
                prevBtn.className = 'btn btn-outline btn-sm';
                prevBtn.innerHTML = '<i class="fa-solid fa-chevron-left"></i>';
                prevBtn.disabled = currentPage === 1;
                prevBtn.onclick = () => { if(currentPage > 1) { currentPage--; updatePagination(); } };
                paginationControls.appendChild(prevBtn);

                // Numéros de page
                for (let i = 1; i <= totalPages; i++) {
                    const pageBtn = document.createElement('button');
                    pageBtn.className = `btn btn-sm ${i === currentPage ? 'btn-primary' : 'btn-outline'}`;
                    pageBtn.textContent = i;
                    pageBtn.onclick = () => { currentPage = i; updatePagination(); };
                    paginationControls.appendChild(pageBtn);
                }

                // Bouton Suivant
                const nextBtn = document.createElement('button');
                nextBtn.className = 'btn btn-outline btn-sm';
                nextBtn.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';
                nextBtn.disabled = currentPage === totalPages;
                nextBtn.onclick = () => { if(currentPage < totalPages) { currentPage++; updatePagination(); } };
                paginationControls.appendChild(nextBtn);
            }

            function updatePagination() {
                displayPage();
                setupPagination();
            }

            // --- Sort by name ---
            let sortOrderAsc = true;
            const sortNameBtn = document.getElementById('sortNameBtn');
            if (sortNameBtn) {
                sortNameBtn.addEventListener('click', function() {
                    sortOrderAsc = !sortOrderAsc;
                    this.innerHTML = sortOrderAsc ? '<i class="fa-solid fa-sort-alpha-down"></i> Trier par nom' : '<i class="fa-solid fa-sort-alpha-up"></i> Trier par nom';
                    
                    filteredRows.sort((a, b) => {
                        const nameA = a.cells[1].textContent.trim().toLowerCase();
                        const nameB = b.cells[1].textContent.trim().toLowerCase();
                        if (nameA < nameB) return sortOrderAsc ? -1 : 1;
                        if (nameA > nameB) return sortOrderAsc ? 1 : -1;
                        return 0;
                    });
                    
                    // Re-append to DOM to visually sort the table rows
                    filteredRows.forEach(row => tableBody.appendChild(row));
                    
                    currentPage = 1;
                    displayPage();
                    setupPagination();
                });
            }

            // --- Export to PDF ---
            const exportPdfBtn = document.getElementById('exportPdfBtn');
            if (exportPdfBtn) {
                exportPdfBtn.addEventListener('click', function() {
                    const table = document.querySelector('.data-table');
                    const clone = table.cloneNode(true);
                    
                    const rowsToRemove = clone.querySelectorAll('tbody tr');
                    rowsToRemove.forEach(tr => {
                        if (tr.id === 'noResultsRow') return;
                        tr.remove();
                    });
                    
                    const tbody = clone.querySelector('tbody');
                    filteredRows.forEach(row => {
                        tbody.appendChild(row.cloneNode(true));
                    });

                    // Remove Actions column
                    clone.querySelectorAll('th:last-child, td:last-child').forEach(el => el.remove());

                    const wrapper = document.createElement('div');
                    wrapper.style.padding = '20px';
                    wrapper.style.background = 'white';
                    
                    const headerContainer = document.createElement('div');
                    headerContainer.style.display = 'flex';
                    headerContainer.style.alignItems = 'center';
                    headerContainer.style.justifyContent = 'center';
                    headerContainer.style.position = 'relative';
                    headerContainer.style.marginBottom = '30px';

                    const logoContainer = document.createElement('div');
                    logoContainer.style.position = 'absolute';
                    logoContainer.style.left = '0';
                    logoContainer.style.top = '50%';
                    logoContainer.style.transform = 'translateY(-50%)';
                    logoContainer.style.color = '#2563eb';
                    logoContainer.style.fontSize = '24px';
                    logoContainer.style.fontWeight = 'bold';
                    logoContainer.style.fontFamily = 'sans-serif';
                    logoContainer.innerHTML = '<i class="fa-solid fa-chart-pie"></i> Digit Advisory';

                    const title = document.createElement('h2');
                    title.textContent = 'Liste des Utilisateurs';
                    title.style.margin = '0';
                    title.style.fontFamily = 'sans-serif';
                    title.style.color = '#2563eb';
                    title.style.fontWeight = 'bold';

                    headerContainer.appendChild(logoContainer);
                    headerContainer.appendChild(title);
                    
                    wrapper.appendChild(headerContainer);
                    
                    wrapper.appendChild(clone);
                    
                    const clonedRows = clone.querySelectorAll('tr');
                    clonedRows.forEach(tr => tr.style.display = ''); // ensure they are visible

                    const opt = {
                        margin:       0.5,
                        filename:     'utilisateurs.pdf',
                        image:        { type: 'jpeg', quality: 0.98 },
                        html2canvas:  { scale: 2 },
                        jsPDF:        { unit: 'in', format: 'letter', orientation: 'landscape' }
                    };
                    
                    html2pdf().set(opt).from(wrapper).save();
                });
            }

            if (searchInput && roleSelectInput && tableBody) {
                searchInput.addEventListener('input', filterTable);
                roleSelectInput.addEventListener('change', filterTable);
                
                // Initialize the table filtering on load
                filterTable();
            }
        });
    </script>
</body>
</html>

