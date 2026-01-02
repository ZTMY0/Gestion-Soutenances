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
$stmt = $pdo->prepare("SELECT COUNT(*) FROM projets WHERE encadrant_id = ? AND statut = 'encadrant_affecte'");
$stmt->execute([$prof_id]);
$nbEnAttente = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Professeur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .stat-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 15px;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .menu-card {
            transition: all 0.3s ease;
            border: 2px solid transparent;
            cursor: pointer;
            text-decoration: none;
        }
        .menu-card:hover {
            border-color: #0d6efd;
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark px-4 shadow-sm">
        <div class="d-flex align-items-center">
            <span class="navbar-brand mb-0 h1">
                <i class="fas fa-chalkboard-teacher me-2"></i>Espace Professeur
            </span>
        </div>
        <div class="d-flex align-items-center">
            <span class="text-white me-3 d-none d-md-block">
                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['user_nom']); ?>
            </span>
            <a href="../auth/logout.php" class="btn btn-outline-light btn-sm">
                <i class="fas fa-sign-out-alt me-1"></i>Déconnexion
            </a>
        </div>
    </nav>

    <div class="container py-5">
        <!-- WELCOME -->
        <div class="text-center mb-5">
            <h1 class="fw-bold">Bienvenue, <?php echo htmlspecialchars($_SESSION['user_nom']); ?> 👋</h1>
            <p class="lead text-muted">Gérez vos encadrements et disponibilités pour les soutenances</p>
        </div>

        <!-- STATS -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold"><?php echo $nbEncadres; ?></h3>
                            <small class="text-muted">Étudiants encadrés</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold"><?php echo $nbDispos; ?></h3>
                            <small class="text-muted">Créneaux disponibles</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold"><?php echo $nbEnAttente; ?></h3>
                            <small class="text-muted">Rapports en attente</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MENU -->
        <h4 class="mb-4"><i class="fas fa-th-large text-primary me-2"></i>Accès rapide</h4>
        <div class="row g-4">
            <div class="col-md-6">
                <a href="disponibilites.php" class="card menu-card shadow-sm h-100 text-decoration-none">
                    <div class="card-body text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-calendar-alt fa-3x text-success"></i>
                        </div>
                        <h5 class="fw-bold text-dark">Mes Disponibilités</h5>
                        <p class="text-muted mb-0">Indiquer vos créneaux pour les jurys</p>
                        <?php if ($nbDispos == 0): ?>
                            <span class="badge bg-danger mt-2">À remplir</span>
                        <?php else: ?>
                            <span class="badge bg-success mt-2"><?php echo $nbDispos; ?> créneaux</span>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
            <div class="col-md-6">
                <a href="encadrement.php" class="card menu-card shadow-sm h-100 text-decoration-none">
                    <div class="card-body text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-user-graduate fa-3x text-primary"></i>
                        </div>
                        <h5 class="fw-bold text-dark">Mes Encadrements</h5>
                        <p class="text-muted mb-0">Suivre vos étudiants et valider les rapports</p>
                        <?php if ($nbEnAttente > 0): ?>
                            <span class="badge bg-warning mt-2"><?php echo $nbEnAttente; ?> en attente</span>
                        <?php else: ?>
                            <span class="badge bg-secondary mt-2"><?php echo $nbEncadres; ?> projets</span>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
        </div>

        <!-- ALERT SI PAS DE DISPO -->
        <?php if ($nbDispos == 0): ?>
            <div class="alert alert-warning mt-5 d-flex align-items-center" role="alert">
                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                <div>
                    <strong>Action requise !</strong><br>
                    Vous n'avez pas encore saisi vos disponibilités. Le coordinateur en a besoin pour planifier les soutenances.
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>