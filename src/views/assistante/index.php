<?php
session_start();
// Sécurité Assistante
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'assistante') {
    header("Location: ../auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Logistique & Salles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4 border-bottom">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold text-primary" href="#">LOGISTIQUE PFE</a>
            <div class="d-flex align-items-center">
                <span class="me-3 text-muted">Assistante Administrative</span>
                <a href="../auth/logout.php" class="btn btn-sm btn-danger">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center p-5">
                        <i class="fas fa-door-open fa-3x text-primary mb-3"></i>
                        <h3>Gestion des Salles</h3>
                        <p class="text-muted">Ajouter, modifier ou vérifier la disponibilité des salles.</p>
                        <a href="salles.php" class="btn btn-primary w-100">Gérer les Salles</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center p-5">
                        <i class="fas fa-print fa-3x text-secondary mb-3"></i>
                        <h3>Impressions & Documents</h3>
                        <p class="text-muted">Imprimer les convocations et feuilles d'émargement.</p>
                        <button class="btn btn-outline-secondary w-100">Voir les documents</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card shadow-sm border-warning">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div class="d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 p-3 rounded-circle me-3">
                        <i class="fas fa-box-archive fa-2x text-warning"></i>
                    </div>
                    <div>
                        <h4 class="mb-1">Archivage & Clôture</h4>
                        <p class="text-muted mb-0">Scanner les PV signés et générer les attestations de réussite.</p>
                    </div>
                </div>
                <button class="btn btn-outline-warning btn-lg">
                    <i class="fas fa-upload me-2"></i>Archiver une soutenance
                </button>
            </div>
        </div>
    </div>
</div>