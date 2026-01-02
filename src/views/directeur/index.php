<?php
session_start();
// Sécurité stricte
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'directeur') {
    header("Location: ../auth/login.php");
    exit();
}
require_once __DIR__ . '/../../../config/database.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Direction - UEMF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/assets/css/style.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">UEMF | DIRECTION</a>
            <div class="d-flex text-white">
                <span class="me-3">Bienvenue, M. le Directeur</span>
                <a href="../auth/logout.php" class="btn btn-sm btn-outline-light">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2 class="mb-4">Supervision des Soutenances</h2>
        
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Étudiants</h5>
                        <p class="card-text display-4">215</p> </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Taux de Validation</h5>
                        <p class="card-text display-4">98%</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Alertes / Retards</h5>
                        <p class="card-text display-4">3</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">Actions Requises</div>
            <div class="card-body">
                <div class="d-grid gap-2 d-md-block">
                    <button class="btn btn-outline-primary btn-lg me-2">
                        <i class="fas fa-calendar-check me-2"></i>Valider le Planning Final
                    </button>
                    <button class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-file-signature me-2"></i>Signer les PV (0 en attente)
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>