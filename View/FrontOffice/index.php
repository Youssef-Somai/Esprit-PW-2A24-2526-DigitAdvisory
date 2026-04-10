<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digit Advisory | Plateforme d'Audit & Expertise</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>

    <!-- Header Navigation -->
    <header id="header">
        <div class="container nav-container">
            <a href="index.php" class="logo">
                <i class="fa-solid fa-chart-pie text-primary"></i>
                Digit Advisory
            </a>
            <ul class="nav-links">
                <li><a href="#services">Nos Services</a></li>
                <li><a href="#demarche">Démarche</a></li>
                <li><a href="#avis">Avis Clients</a></li>
            </ul>
            <div class="nav-actions">
                <a href="login.php" class="btn btn-outline">Se connecter</a>
                <a href="login.php#register" class="btn btn-primary" style="margin-left: 10px;">S'inscrire</a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-bg"></div>
        <div class="hero-bg-2"></div>
        <div class="container hero-content">
            <div class="hero-text fade-in-up">
                <h1>L'expertise de haut niveau à portée de main</h1>
                <p>Connectez-vous avec les meilleurs consultants experts pour vos besoins en audit, recommandations et certifications ISO. Menez vos projets vers l'excellence.</p>
                <div style="display: flex; gap: 1rem;">
                    <a href="login.php#register" class="btn btn-primary">Commencer maintenant <i class="fa-solid fa-arrow-right"></i></a>
                    <a href="#services" class="btn btn-outline">Découvrir</a>
                </div>
            </div>
            <div class="hero-image fade-in-up delay-2">
                <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&q=80&w=800" alt="Consulting Meeting">
                <div class="floating-badge badge-1">
                    <i class="fa-solid fa-certificate text-accent" style="font-size: 1.5rem;"></i>
                    <span>Certification ISO</span>
                </div>
                <div class="floating-badge badge-2">
                    <div style="display: flex; flex-direction: column;">
                        <span style="font-size: 1.5rem; color: var(--primary);">+500</span>
                        <span style="font-size: 0.8rem; color: var(--gray);">Experts Qualifiés</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="section glass" style="margin: 2rem; border-radius: 20px;">
        <div class="container">
            <div class="section-header fade-in-up">
                <h2>Nos Services</h2>
                <p>Une suite complète d'outils et de services pour accompagner la croissance de votre entreprise avec l'aide d'experts qualifiés.</p>
            </div>
            <div class="services-grid">
                <div class="service-card fade-in-up delay-1">
                    <div class="service-icon"><i class="fa-solid fa-magnifying-glass-chart"></i></div>
                    <h3>Audit & Expertise</h3>
                    <p>Accédez à un vaste réseau de consultants experts pour évaluer vos processus et obtenir des recommandations stratégiques.</p>
                </div>
                <div class="service-card fade-in-up delay-2">
                    <div class="service-icon"><i class="fa-solid fa-list-check"></i></div>
                    <h3>Gestion de Missions</h3>
                    <p>Gérez vos offres, suivez les candidatures, et communiquez avec vos experts. Suivi complet des livrables.</p>
                </div>
                <div class="service-card fade-in-up delay-3">
                    <div class="service-icon"><i class="fa-solid fa-award"></i></div>
                    <h3>Certifications ISO</h3>
                    <p>Obtenez des recommandations de certifications ISO adaptées à votre profil, avec l'explication des critères et avantages.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Démarche / Process Section -->
    <section id="demarche" class="section">
        <div class="container">
            <div class="section-header fade-in-up">
                <h2>Notre Démarche</h2>
                <p>Comment fonctionne Digit Advisory ? Un processus simple, transparent et redoutablement efficace.</p>
            </div>
            <div class="process-timeline">
                <div class="process-step fade-in-up delay-1">
                    <div class="process-icon">1</div>
                    <div class="process-content">
                        <h3>Création du Profil & Quiz</h3>
                        <p>Les entreprises et les experts créent leur compte. Les entreprises passent un quiz personnalisé pour nous aider à cibler leurs besoins (portfolio, recommandations).</p>
                    </div>
                </div>
                <div class="process-step fade-in-up delay-2">
                    <div class="process-icon">2</div>
                    <div class="process-content">
                        <h3>Match et Offres</h3>
                        <p>Les entreprises publient des offres de mission. Les experts consultent les offres en fonction de leurs domaines d'expertise et soumettent leur candidature.</p>
                    </div>
                </div>
                <div class="process-step fade-in-up delay-3">
                    <div class="process-icon">3</div>
                    <div class="process-content">
                        <h3>Collaboration & Messagerie</h3>
                        <p>Sélectionnez le meilleur candidat, et lancez la mission. Utilisez notre système de messagerie interne pour échanger et suivre la progression des livrables.</p>
                    </div>
                </div>
                <div class="process-step fade-in-up delay-3">
                    <div class="process-icon">4</div>
                    <div class="process-content">
                        <h3>Certification & Croissance</h3>
                        <p>Atteignez vos objectifs et explorez les recommandations de certifications ISO (9001, 27001, etc.) pour valoriser l'excellence de votre entreprise.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials / Avis Clients -->
    <section id="avis" class="testimonials section">
        <div class="container">
            <div class="section-header fade-in-up">
                <h2 style="color: white;">Ce que disent nos clients</h2>
                <p style="color: var(--gray-light);">Rejoignez des centaines d'entreprises et d'experts satisfaits par notre plateforme.</p>
            </div>
            <div class="testimonial-grid">
                <div class="testimonial-card fade-in-up delay-1">
                    <div class="text-accent mb-1">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                    </div>
                    <p>"Grâce à Digit Advisory, nous avons pu trouver un expert en sécurité de l'information en un temps record pour préparer notre certification ISO 27001."</p>
                    <div class="client-info">
                        <img src="https://ui-avatars.com/api/?name=Sophie+Martin&background=random" alt="Sophie Martin" class="client-img">
                        <div>
                            <h4 style="font-size: 1rem;">Sophie Martin</h4>
                            <span style="font-size: 0.8rem; color: var(--gray);">Directrice Générale, TechSolutions</span>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card fade-in-up delay-2">
                    <div class="text-accent mb-1">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                    </div>
                    <p>"Une plateforme incroyablement intuitive. En tant qu'expert métier, je trouve facilement des missions qui correspondent parfaitement à mon portfolio et à mes compétences métier."</p>
                    <div class="client-info">
                        <img src="https://ui-avatars.com/api/?name=Thomas+Dubois&background=random" alt="Thomas Dubois" class="client-img">
                        <div>
                            <h4 style="font-size: 1rem;">Thomas Dubois</h4>
                            <span style="font-size: 0.8rem; color: var(--gray);">Consultant Senior</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-col">
                    <a href="#" class="logo" style="color: white; margin-bottom: 1.5rem;">
                        <i class="fa-solid fa-chart-pie text-primary"></i> Digit Advisory
                    </a>
                    <p style="color: var(--gray);">Connecter les talents experts aux entreprises ambitieuses pour un accompagnement vers l'excellence avec suivi de livrables et gestion des certifications.</p>
                </div>
                <div class="footer-col">
                    <h4>Liens Rapides</h4>
                    <ul class="footer-links">
                        <li><a href="#services">Nos Services</a></li>
                        <li><a href="#demarche">Notre Démarche</a></li>
                        <li><a href="#avis">Avis Clients</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Modules Plateforme</h4>
                    <ul class="footer-links">
                        <li><a href="#">Gestion des Offres & Candidatures</a></li>
                        <li><a href="#">Gestion de Portfolio</a></li>
                        <li><a href="#">Quiz & Recommandations</a></li>
                        <li><a href="#">Certifications ISO</a></li>
                        <li><a href="#">Messagerie Interne</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 Digit Advisory. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script>
        // Header scroll effect
        window.addEventListener('scroll', () => {
            const header = document.getElementById('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
        
        // Simple Intersection Observer for Animations
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animationPlayState = 'running';
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in-up').forEach(el => {
            el.style.animationPlayState = 'paused';
            observer.observe(el);
        });
    </script>
</body>
</html>
