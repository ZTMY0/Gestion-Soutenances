<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinateur') {
    header("Location: ../auth/login.php"); exit();
}

// TRAITEMENT : MARQUER COMME TRAITÉ
if (isset($_POST['close_ticket'])) {
    $pdo->prepare("UPDATE support_tickets SET statut = 'traite' WHERE id = ?")->execute([$_POST['ticket_id']]);
    $msg = "Ticket archivé.";
}

// RÉCUPÉRATION DES TICKETS EN ATTENTE
$tickets = $pdo->query("SELECT * FROM support_tickets WHERE statut = 'en_attente' ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Support | UEMF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../../public/assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-2 mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">UEMF Pilotage</a>
            <a href="index.php" class="btn btn-outline-light btn-sm">Retour Dashboard</a>
        </div>
    </nav>

    <div class="container">
        <h3 class="mb-4"><i class="fas fa-headset me-2 text-primary"></i>Demandes de Support Étudiant</h3>

        <?php if(empty($tickets)): ?>
            <div class="alert alert-success text-center py-5">
                <i class="fas fa-check-circle fa-3x mb-3"></i>
                <h5>Tout est calme !</h5>
                <p>Aucune demande de support en attente.</p>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach($tickets as $t): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card shadow-sm border-start border-4 border-warning h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="badge bg-warning text-dark"><?= htmlspecialchars($t['motif']) ?></span>
                                    <small class="text-muted"><?= date('d/m/Y H:i', strtotime($t['created_at'])) ?></small>
                                </div>
                                <h6 class="fw-bold"><?= htmlspecialchars($t['email_contact']) ?></h6>
                                <p class="card-text bg-light p-2 rounded small text-secondary">
                                    "<?= nl2br(htmlspecialchars($t['message'])) ?>"
                                </p>
                                <div class="d-flex justify-content-between mt-3">
                                    <a href="mailto:<?= $t['email_contact'] ?>?subject=Réponse Support UEMF&body=Bonjour, concernant votre demande..." class="btn btn-sm btn-primary">
                                        <i class="fas fa-envelope me-1"></i> Répondre
                                    </a>
                                    
                                    <form method="POST">
                                        <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>">
                                        <button type="submit" name="close_ticket" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-check me-1"></i> Marquer Traité
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>