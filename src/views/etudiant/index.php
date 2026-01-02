<?php
session_start();
require_once '../../../config/database.php';

// SÉCURITÉ
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'etudiant') {
    header("Location: ../auth/login.php");
    exit();
}

$id_etudiant = $_SESSION['user_id'];
$projet = null;

// Chercher si l'étudiant a déjà un projet
try {
    $stmt = $pdo->prepare("SELECT * FROM projets WHERE etudiant_id = ?");
    $stmt->execute([$id_etudiant]);
    $projet = $stmt->fetch();
} catch (PDOException $e) {
    // Erreur silencieuse
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Espace Étudiant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-secondary px-4">
        <span class="navbar-brand"> Espace Étudiant</span>
        <div class="ms-auto">
            <span class="text-white me-3">Bonjour, <?php echo $_SESSION['user_nom']; ?></span>
            <a href="../../controllers/AuthController.php?logout=1" class="btn btn-sm btn-light">Déconnexion</a>
        </div>
    </nav>

    <div class="container mt-5">
        <?php if (!$projet): ?>
            <div class="text-center py-5">
                <h1>Vous n'avez pas encore soumis de sujet.</h1>
                <p class="lead">La date limite approche !</p>
                <a href="soumettre.php" class="btn btn-primary btn-lg"> Proposer un Sujet PFE</a>
            </div>
        <?php else: ?>
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Mon Projet : <?php echo htmlspecialchars($projet['titre']); ?></h4>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <p class="mb-0"><strong>Statut :</strong> 
                            <span class="badge bg-<?php echo ($projet['statut'] == 'inscrit') ? 'warning' : 'success'; ?>">
                                <?php echo strtoupper($projet['statut']); ?>
                            </span>
                        </p>
                        
                        <?php if($projet['statut'] == 'inscrit'): ?>
                            <a href="modifier.php" class="btn btn-outline-primary btn-sm"> Modifier le projet</a>
                        <?php endif; ?>
                    </div>
                    
                    <hr>
                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($projet['description'])); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>