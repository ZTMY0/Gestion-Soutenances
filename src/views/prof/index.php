<?php
// 1. SÉCURITÉ & CONFIGURATION
ini_set('display_errors', 0); // En production, on masque les erreurs brutes
ini_set('log_errors', 1);     // On les log dans les fichiers serveur
error_reporting(E_ALL);

// Chemins absolus
require_once __DIR__ . '/../../../config/session_check.php';
require_once __DIR__ . '/../../../config/database.php';

// 2. VÉRIFICATION DU RÔLE
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'prof') {
    header("Location: ../auth/login.php"); exit();
}

// 3. FONCTION RÉCUPÉRATION IP & INFOS SESSION
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
    return $_SERVER['REMOTE_ADDR'];
}
$user_ip = getUserIP();
$prof_id = $_SESSION['user_id'];
$nom_prof = $_SESSION['user_nom'] ?? 'Professeur';

// Vérification BDD
if (!isset($pdo)) { die("Erreur critique : La connexion base de données a échoué."); }

// 4. RÉCUPÉRATION DES DONNÉES
try {
    // A. Compter les disponibilités
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM disponibilites WHERE prof_id = ?");
    $stmt->execute([$prof_id]);
    $nbDispos = $stmt->fetchColumn();

    // B. Compter les étudiants encadrés
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM projets WHERE encadrant_id = ?");
    $stmt->execute([$prof_id]);
    $nbEncadres = $stmt->fetchColumn();

    // C. Compter les rapports en attente
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM projets WHERE encadrant_id = ? AND statut = 'rapport_soumis'");
    $stmt->execute([$prof_id]);
    $nbEnAttente = $stmt->fetchColumn();

    // D. Compter les jurys (Gestion d'erreur si la table n'existe pas encore)
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
        // Table inexistante ou vide, on ignore pour l'instant
    }

    // E. Récupérer les projets récents (5 derniers)
    $stmt = $pdo->prepare("SELECT p.titre, u.nom AS etudiant_nom, u.prenom AS etudiant_prenom, p.statut, p.created_at 
                           FROM projets p 
                           JOIN users u ON p.etudiant_id = u.id 
                           WHERE p.encadrant_id = ? 
                           ORDER BY p.created_at DESC 
                           LIMIT 5");
    $stmt->execute([$prof_id]);
    $projetsRecents = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Erreur de chargement des données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Professeur | UEMF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../public/assets/css/style.css">
</head>
<body class="bg-light">
    
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-2">
        <div class="container">
            <a class="navbar-brand text-uppercase fw-bold" href="index.php">
                <i class="fas fa-graduation-cap me-2"></i>UEMF Professeur
            </a>
            
            <div class="d-flex align-items-center text-white-50">
                <a href="profil.php" class="btn btn-outline-light btn-sm me-3 border-0">
                    <i class="fas fa-user-circle me-1"></i> Mon Profil
                </a>
                <span class="me-3 small text-uppercase">Pr. <?= htmlspecialchars($nom_prof) ?></span>
                <a href="../auth/logout.php" class="text-white hover-white">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="dashboard-hero bg-primary text-white pb-5 pt-4 mb-5" style="background: linear-gradient(135deg, #004d99 0%, #00264d 100%);">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end">
                <div>
                    <h2 class="mb-2 fw-bold">Espace Enseignant</h2>
                    <p class="mb-0 opacity-75">Bienvenue, Pr. <?= htmlspecialchars($nom_prof) ?>. Gérez vos encadrements.</p>
                    
                    <div class="mt-3">
                        <span class="badge bg-white text-primary me-2">
                            <i class="fas fa-chalkboard-teacher me-1"></i>Professeur
                        </span>
                        <span class="badge bg-info text-dark opacity-75">
                            <i class="fas fa-network-wired me-1"></i>IP: <?= $user_ip ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container pb-5" style="margin-top: -3rem;">
        
        <div class="row g-4 mb-5">
            <div class="col-md-3 col-sm-6 animate-fade-in" style="animation-delay: 0.1s">
                <div class="stat-card bg-white shadow-sm h-100 p-4 rounded border-0">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="stat-number h2 fw-bold text-primary mb-0"><?= $nbEncadres ?></div>
                        <div class="text-primary bg-primary bg-opacity-10 p-2 rounded-circle"><i class="fas fa-users"></i></div>
                    </div>
                    <div class="text-muted small text-uppercase fw-bold">Étudiants encadrés</div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 animate-fade-in" style="animation-delay: 0.2s">
                <div class="stat-card bg-white shadow-sm h-100 p-4 rounded border-0">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="stat-number h2 fw-bold text-success mb-0"><?= $nbDispos ?></div>
                        <div class="text-success bg-success bg-opacity-10 p-2 rounded-circle"><i class="fas fa-calendar-check"></i></div>
                    </div>
                    <div class="text-muted small text-uppercase fw-bold">Créneaux dispo</div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 animate-fade-in" style="animation-delay: 0.3s">
                <div class="stat-card bg-white shadow-sm h-100 p-4 rounded border-0">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="stat-number h2 fw-bold text-warning mb-0"><?= $nbEnAttente ?></div>
                        <div class="text-warning bg-warning bg-opacity-10 p-2 rounded-circle"><i class="fas fa-hourglass-half"></i></div>
                    </div>
                    <div class="text-muted small text-uppercase fw-bold">Rapports en attente</div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 animate-fade-in" style="animation-delay: 0.4s">
                <div class="stat-card bg-white shadow-sm h-100 p-4 rounded border-0">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="stat-number h2 fw-bold text-danger mb-0"><?= $nbJurys ?></div>
                        <div class="text-danger bg-danger bg-opacity-10 p-2 rounded-circle"><i class="fas fa-gavel"></i></div>
                    </div>
                    <div class="text-muted small text-uppercase fw-bold">Participations jury</div>
                </div>
            </div>
        </div>

        <?php if ($nbDispos == 0): ?>
            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-5 animate-fade-in" role="alert">
                <i class="fas fa-exclamation-triangle fa-2x me-3 text-warning"></i>
                <div>
                    <h5 class="alert-heading fw-bold mb-1">Action requise : Disponibilités</h5>
                    <p class="mb-0 small">Vous n'avez pas encore saisi vos créneaux. Le coordinateur en a besoin pour planifier les soutenances.</p>
                </div>
                <a href="disponibilites.php" class="btn btn-warning btn-sm ms-auto fw-bold text-dark">Remplir maintenant</a>
            </div>
        <?php endif; ?>

        <h5 class="mb-4 fw-bold text-dark border-bottom pb-2">
            <i class="fas fa-th-large text-primary me-2"></i>Accès rapide
        </h5>
        
        <div class="row g-4 mb-5">
            <div class="col-md-4 animate-fade-in" style="animation-delay: 0.1s">
                <a href="disponibilites.php" class="text-decoration-none">
                    <div class="card shadow-sm border-0 h-100 hover-shadow transition-all">
                        <div class="card-body text-center p-4">
                            <div class="text-success mb-3"><i class="fas fa-calendar-alt fa-3x"></i></div>
                            <h5 class="fw-bold text-dark">Mes Disponibilités</h5>
                            <p class="text-muted small">Indiquer vos créneaux pour les jurys</p>
                            <?php if ($nbDispos == 0): ?>
                                <span class="badge bg-danger rounded-pill">À remplir</span>
                            <?php else: ?>
                                <span class="badge bg-success rounded-pill"><i class="fas fa-check me-1"></i>OK</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="col-md-4 animate-fade-in" style="animation-delay: 0.2s">
                <a href="encadrement.php" class="text-decoration-none">
                    <div class="card shadow-sm border-0 h-100 hover-shadow transition-all">
                        <div class="card-body text-center p-4">
                            <div class="text-primary mb-3"><i class="fas fa-user-graduate fa-3x"></i></div>
                            <h5 class="fw-bold text-dark">Mes Encadrements</h5>
                            <p class="text-muted small">Suivre vos étudiants et valider les rapports</p>
                            <span class="badge bg-secondary rounded-pill"><?= $nbEncadres ?> dossiers</span>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="col-md-4 animate-fade-in" style="animation-delay: 0.3s">
                <a href="jurys.php" class="text-decoration-none">
                    <div class="card shadow-sm border-0 h-100 hover-shadow transition-all">
                        <div class="card-body text-center p-4">
                            <div class="text-danger mb-3"><i class="fas fa-gavel fa-3x"></i></div>
                            <h5 class="fw-bold text-dark">Mes Jurys</h5>
                            <p class="text-muted small">Convocations et saisie des notes</p>
                            <span class="badge bg-secondary rounded-pill"><?= $nbJurysAVenir ?> à venir</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-6 animate-fade-in" style="animation-delay: 0.4s">
                <a href="statistiques.php" class="text-decoration-none">
                    <div class="card shadow-sm border-0 h-100 hover-shadow transition-all bg-light">
                        <div class="card-body text-center p-4 d-flex flex-column justify-content-center">
                            <div class="text-info mb-3"><i class="fas fa-chart-bar fa-3x"></i></div>
                            <h5 class="fw-bold text-dark">Mes Statistiques</h5>
                            <p class="text-muted small mb-0">Historique et graphiques d'activité</p>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="col-md-6 animate-fade-in" style="animation-delay: 0.5s">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 fw-bold">
                        <i class="fas fa-clock text-primary me-2"></i>Activité récente
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($projetsRecents)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($projetsRecents as $projet): ?>
                                    <div class="list-group-item px-4 py-3 border-0 border-bottom">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($projet['titre']) ?></div>
                                                <small class="text-muted">
                                                    <i class="fas fa-user me-1"></i>
                                                    <?= htmlspecialchars($projet['etudiant_nom'] . ' ' . $projet['etudiant_prenom']) ?>
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-light text-dark border mb-1"><?= htmlspecialchars($projet['statut']) ?></span>
                                                <br>
                                                <small class="text-muted" style="font-size: 0.75rem;"><?= date('d/m/Y', strtotime($projet['created_at'])) ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="text-center p-3">
                                <a href="encadrement.php" class="text-decoration-none fw-bold small">
                                    Voir tous les projets <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
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