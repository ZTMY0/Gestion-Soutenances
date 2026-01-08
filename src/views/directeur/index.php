<?php
session_start();
require_once '../../../config/database.php';

// Sécurité
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'directeur') {
    header("Location: ../auth/login.php"); exit();
}

// 1. STATISTIQUES RÉELLES
// Nombre total d'étudiants
$nbEtudiants = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'etudiant'")->fetchColumn();

// Taux de validation (Projets validés / Total projets)
$nbProjets = $pdo->query("SELECT COUNT(*) FROM projets")->fetchColumn();
$nbValides = $pdo->query("SELECT COUNT(*) FROM projets WHERE statut = 'valide'")->fetchColumn();
$taux = ($nbProjets > 0) ? round(($nbValides / $nbProjets) * 100) : 0;

// Alertes (Projets sans rapport déposé alors qu'ils sont encadrés)
$nbAlertes = $pdo->query("
    SELECT COUNT(*)
    FROM projets p
    LEFT JOIN rapports r ON r.projet_id = p.id
    WHERE p.statut = 'encadre'
      AND r.id IS NULL
")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Direction - UEMF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .card-hover:hover { transform: translateY(-5px); transition: 0.3s; cursor: pointer; }
        .icon-box { font-size: 2rem; opacity: 0.8; }
    </style>
</head>
<body class="bg-light">
    
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-5 shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="fas fa-university me-2"></i>UEMF | DIRECTION</a>
            <div class="d-flex text-white align-items-center">
                <span class="me-3"><i class="fas fa-user-shield me-2"></i>M. le Directeur</span>
                <a href="../auth/logout.php" class="btn btn-sm btn-outline-danger">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2 class="mb-4 text-dark fw-bold border-bottom pb-2">Vue d'ensemble</h2>
        
        <div class="row mb-5">
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3 shadow border-0 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Total Étudiants</h5>
                            <p class="card-text display-4 fw-bold mb-0"><?= $nbEtudiants ?></p> 
                        </div>
                        <div class="icon-box"><i class="fas fa-users"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success mb-3 shadow border-0 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Taux de Validation</h5>
                            <p class="card-text display-4 fw-bold mb-0"><?= $taux ?>%</p>
                        </div>
                        <div class="icon-box"><i class="fas fa-chart-line"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-warning mb-3 shadow border-0 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Dossiers en attente</h5>
                            <p class="card-text display-4 fw-bold mb-0"><?= $nbAlertes ?></p>
                        </div>
                        <div class="icon-box"><i class="fas fa-exclamation-triangle"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <h4 class="mb-3 text-secondary"><i class="fas fa-cogs me-2"></i>Gestion Stratégique</h4>
        <div class="row g-4">
            
            <div class="col-md-4">
                <a href="validation.php" class="text-decoration-none">
                    <div class="card card-hover shadow-sm border-0 h-100">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-calendar-check fa-3x text-primary mb-3"></i>
                            <h5 class="text-dark">Validation Planning</h5>
                            <p class="text-muted small">Approuver les dates de soutenances proposées par les coordinateurs.</p>
                            <span class="btn btn-outline-primary btn-sm">Accéder</span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="signatures.php" class="text-decoration-none">
                    <div class="card card-hover shadow-sm border-0 h-100">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-file-signature fa-3x text-success mb-3"></i>
                            <h5 class="text-dark">Signatures PV</h5>
                            <p class="text-muted small">Signer électroniquement les procès-verbaux de délibération.</p>
                            <span class="btn btn-outline-success btn-sm">Accéder</span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="parametres.php" class="text-decoration-none">
                    <div class="card card-hover shadow-sm border-0 h-100">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-sliders-h fa-3x text-secondary mb-3"></i>
                            <h5 class="text-dark">Paramètres Système</h5>
                            <p class="text-muted small">Configurer les dates limites et la durée des soutenances.</p>
                            <span class="btn btn-outline-secondary btn-sm">Configurer</span>
                        </div>
                    </div>
                </a>
            </div>

        </div>
    </div>
</body>
</html>