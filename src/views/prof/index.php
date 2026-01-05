<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'prof') {
    header("Location: ../auth/login.php"); exit();
}

$prof_id = $_SESSION['user_id'];

// Compter les disponibilités
$stmt = $pdo->prepare("SELECT COUNT(*) FROM disponibilites_profs WHERE prof_id = ?");
$stmt->execute([$prof_id]);
$nbDispos = $stmt->fetchColumn();

// Compter les étudiants encadrés
$stmt = $pdo->prepare("SELECT COUNT(*) FROM projets WHERE encadrant_id = ?");
$stmt->execute([$prof_id]);
$nbEncadres = $stmt->fetchColumn();

// Compter les rapports en attente de validation
$stmt = $pdo->prepare("SELECT COUNT(*) FROM projets WHERE encadrant_id = ? AND statut = 'rapport_soumis'");
$stmt->execute([$prof_id]);
$nbEnAttente = $stmt->fetchColumn();

// Compter les jurys
$nbJurys = 0;
$nbJurysAVenir = 0;
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM jurys WHERE prof_id = ?");
    $stmt->execute([$prof_id]);
    $nbJurys = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM jurys j 
                           JOIN soutenances s ON j.soutenance_id = s.id 
                           WHERE j.prof_id = ? AND s.date_soutenance >= NOW()");
    $stmt->execute([$prof_id]);
    $nbJurysAVenir = $stmt->fetchColumn();
} catch (PDOException $e) {
    // Tables n'existent peut-être pas encore
}

// Récupérer les projets récents
$stmt = $pdo->prepare("SELECT p.titre, u.nom AS etudiant_nom, p.statut, p.created_at 
                       FROM projets p 
                       JOIN users u ON p.etudiant_id = u.id 
                       WHERE p.encadrant_id = ? 
                       ORDER BY p.created_at DESC 
                       LIMIT 5");
$stmt->execute([$prof_id]);
$projetsRecents = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Professeur - UEMF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../public/assets/css/style.css">
</head>
<body>
    
    <!-- NAVBAR STYLE COORDINATEUR -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-2">
        <div class="container">
            <a class="navbar-brand text-uppercase fw-bold" href="index.php">
                <i class="fas fa-graduation-cap me-2"></i>UEMF Professeur
            </a>
            <div class="d-flex align-items-center text-white-50">
                <span class="me-3 small">Pr. <?= htmlspecialchars($_SESSION['user_nom']) ?></span>
                <a href="../auth/logout.php" class="btn btn-sm btn-logout">
                    <i class="fas fa-sign-out-alt me-1"></i>
                    <span class="d-none d-md-inline">Déconnexion</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION STYLE COORDINATEUR -->
    <div class="dashboard-hero">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end">
                <div>
                    <h2 class="mb-1">Bonjour, Pr. <?= htmlspecialchars($_SESSION['user_nom']) ?></h2>
                    <p class="mb-0 opacity-75">Gérez vos encadrements et disponibilités en toute simplicité</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container pb-5">
        
        <!-- STATISTIQUES -->
        <div class="row g-4 mb-5">
            <div class="col-md-3 col-sm-6 animate-fade-in" style="animation-delay: 0.1s">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number"><?= $nbEncadres ?></div>
                    <div class="stat-label">Étudiants encadrés</div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 animate-fade-in" style="animation-delay: 0.2s">
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-number"><?= $nbDispos ?></div>
                    <div class="stat-label">Créneaux disponibles</div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 animate-fade-in" style="animation-delay: 0.3s">
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="stat-number"><?= $nbEnAttente ?></div>
                    <div class="stat-label">Rapports en attente</div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 animate-fade-in" style="animation-delay: 0.4s">
                <div class="stat-card">
                    <div class="stat-icon danger">
                        <i class="fas fa-gavel"></i>
                    </div>
                    <div class="stat-number"><?= $nbJurys ?></div>
                    <div class="stat-label">Participations jury</div>
                </div>
            </div>
        </div>

        <!-- ALERT SI PAS DE DISPO -->
        <?php if ($nbDispos == 0): ?>
            <div class="alert-modern warning mb-4 animate-fade-in">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Action requise !</strong><br>
                    <span class="small">Vous n'avez pas encore saisi vos disponibilités. Le coordinateur en a besoin pour planifier les soutenances.</span>
                </div>
            </div>
        <?php endif; ?>

        <!-- MENU PRINCIPAL -->
        <h5 class="mb-3 fw-bold text-dark">
            <i class="fas fa-th-large text-primary me-2"></i>
            Accès rapide
        </h5>
        
        <div class="row g-4 mb-5">
            <div class="col-md-4 animate-fade-in" style="animation-delay: 0.1s">
                <a href="disponibilites.php" class="menu-card">
                    <div class="text-center">
                        <div class="menu-icon text-success">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <h5 class="menu-title">Mes Disponibilités</h5>
                        <p class="menu-description">Indiquer vos créneaux pour les jurys</p>
                        <?php if ($nbDispos == 0): ?>
                            <span class="badge-modern danger">
                                <i class="fas fa-exclamation-circle"></i>
                                À remplir
                            </span>
                        <?php else: ?>
                            <span class="badge-modern success">
                                <i class="fas fa-check-circle"></i>
                                <?= $nbDispos ?> créneaux
                            </span>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
            
            <div class="col-md-4 animate-fade-in" style="animation-delay: 0.2s">
                <a href="encadrement.php" class="menu-card">
                    <div class="text-center">
                        <div class="menu-icon text-primary">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h5 class="menu-title">Mes Encadrements</h5>
                        <p class="menu-description">Suivre vos étudiants et valider les rapports</p>
                        <?php if ($nbEnAttente > 0): ?>
                            <span class="badge-modern warning">
                                <i class="fas fa-clock"></i>
                                <?= $nbEnAttente ?> en attente
                            </span>
                        <?php else: ?>
                            <span class="badge-modern secondary">
                                <i class="fas fa-users"></i>
                                <?= $nbEncadres ?> projets
                            </span>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
            
            <div class="col-md-4 animate-fade-in" style="animation-delay: 0.3s">
                <a href="jurys.php" class="menu-card">
                    <div class="text-center">
                        <div class="menu-icon text-danger">
                            <i class="fas fa-gavel"></i>
                        </div>
                        <h5 class="menu-title">Mes Jurys</h5>
                        <p class="menu-description">Convocations et saisie des notes</p>
                        <?php if ($nbJurysAVenir > 0): ?>
                            <span class="badge-modern warning">
                                <i class="fas fa-calendar-day"></i>
                                <?= $nbJurysAVenir ?> à venir
                            </span>
                        <?php else: ?>
                            <span class="badge-modern secondary">
                                <i class="fas fa-history"></i>
                                <?= $nbJurys ?> total
                            </span>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
        </div>

        <!-- DEUXIÈME LIGNE -->
        <div class="row g-4 mb-5">
            <div class="col-md-6 animate-fade-in" style="animation-delay: 0.4s">
                <a href="statistiques.php" class="menu-card">
                    <div class="text-center">
                        <div class="menu-icon text-info">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h5 class="menu-title">Mes Statistiques</h5>
                        <p class="menu-description">Historique et graphiques d'activité</p>
                        <span class="badge-modern primary">
                            <i class="fas fa-chart-line"></i>
                            Voir les stats
                        </span>
                    </div>
                </a>
            </div>
            
            <div class="col-md-6 animate-fade-in" style="animation-delay: 0.5s">
                <div class="card">
                    <div class="card-header bg-white border-0 pt-3">
                        <h6 class="fw-bold mb-0">
                            <i class="fas fa-clock text-primary me-2"></i>
                            Activité récente
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($projetsRecents)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($projetsRecents as $projet): ?>
                                    <div class="list-group-item px-0 py-2 border-0 border-bottom">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 fw-semibold small"><?= htmlspecialchars($projet['titre']) ?></h6>
                                                <small class="text-muted">
                                                    <i class="fas fa-user me-1"></i>
                                                    <?= htmlspecialchars($projet['etudiant_nom']) ?>
                                                </small>
                                            </div>
                                            <small class="text-muted">
                                                <?= date('d/m/Y', strtotime($projet['created_at'])) ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="text-center mt-3">
                                <a href="encadrement.php" class="btn btn-sm btn-outline-modern">
                                    Voir tous les projets
                                    <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 opacity-50"></i>
                                <p class="mb-0">Aucune activité récente</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>