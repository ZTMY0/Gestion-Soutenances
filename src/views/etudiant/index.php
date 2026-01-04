<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'etudiant') {
    header("Location: ../auth/login.php"); exit();
}

$uid = $_SESSION['user_id'];
$projet = null; $messages = []; $soutenance = null;

// Envoi Message
if (isset($_POST['send_msg']) && !empty($_POST['msg'])) {
    $pdo->prepare("INSERT INTO messages (projet_id, sender_id, message) VALUES (?, ?, ?)")
        ->execute([$_POST['pid'], $uid, trim($_POST['msg'])]);
    header("Location: index.php"); exit();
}

// Récupération Projet + Email Encadrant
$sql = "SELECT p.*, u.nom as nom_enc, u.prenom as prenom_enc, u.email as email_enc
        FROM projets p LEFT JOIN users u ON p.encadrant_id = u.id 
        WHERE p.etudiant_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$uid]);
$projet = $stmt->fetch();

if ($projet) {
    // Messages
    $stmtMsg = $pdo->prepare("SELECT m.*, u.nom FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.projet_id = ? ORDER BY m.created_at ASC");
    $stmtMsg->execute([$projet['id']]);
    $messages = $stmtMsg->fetchAll();
    // Soutenance
    $stmtSout = $pdo->prepare("SELECT * FROM soutenances WHERE projet_id = ?");
    $stmtSout->execute([$projet['id']]);
    $soutenance = $stmtSout->fetch();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Espace PFE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .timeline { display: flex; justify-content: space-between; position: relative; margin-bottom: 30px; }
        .step { text-align: center; width: 20%; opacity: 0.5; }
        .step.active { opacity: 1; font-weight: bold; color: #0d6efd; }
        .chat-box { height: 300px; overflow-y: auto; background: #f8f9fa; padding: 15px; border-radius: 10px; border: 1px solid #dee2e6; }
        .msg { padding: 8px 12px; border-radius: 15px; margin-bottom: 8px; max-width: 80%; }
        .msg-me { background: #d1e7dd; margin-left: auto; }
        .msg-other { background: #fff; border: 1px solid #dee2e6; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary px-4 shadow-sm mb-4">
        <span class="navbar-brand"><i class="fas fa-graduation-cap me-2"></i>Espace PFE</span>
        <div class="text-white"><?= $_SESSION['user_nom'] ?> <a href="../auth/logout.php" class="btn btn-sm btn-light ms-2">Déconnexion</a></div>
    </nav>

    <div class="container pb-5">
        <?php if (!$projet): ?>
            <div class="text-center py-5 bg-white rounded shadow-sm">
                <h2>Bienvenue</h2>
                <p class="text-muted">Commencez par proposer un sujet.</p>
                <a href="soumettre.php" class="btn btn-primary btn-lg rounded-pill">Nouvelle Inscription</a>
            </div>
        <?php else: ?>
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="timeline">
                        <?php 
                        $etapes = ['inscrit'=>'Inscription', 'valide_encadrant'=>'Encadré', 'rapport_soumis'=>'Rapport', 'soutenance_prog'=>'Soutenance', 'soutenu'=>'Terminé'];
                        foreach($etapes as $key => $label) {
                            $active = ($projet['statut'] == $key) ? 'active' : '';
                            echo "<div class='step $active'><i class='fas fa-circle mb-2'></i><br>$label</div>";
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card shadow-sm border-0 mb-4 h-100">
                        <div class="card-header bg-white py-3"><h5 class="mb-0 text-primary fw-bold"><?= htmlspecialchars($projet['titre']) ?></h5></div>
                        <div class="card-body">
                            <p class="text-muted"><?= nl2br(htmlspecialchars($projet['description'])) ?></p>
                            <div class="d-flex gap-2 mb-3">
                                <span class="badge bg-info text-dark">Domaine: <?= htmlspecialchars($projet['domaine']) ?></span>
                                <span class="badge bg-light text-dark border">Tech: <?= htmlspecialchars($projet['technologies']) ?></span>
                            </div>
                            <div class="d-grid mt-4">
                                <?php if(!$projet['encadrant_id']): ?>
                                    <button class="btn btn-secondary" disabled>En attente d'affectation...</button>
                                <?php elseif($projet['statut'] == 'valide_encadrant'): ?>
                                    <a href="depot.php" class="btn btn-success btn-lg shadow-sm"><i class="fas fa-cloud-upload-alt me-2"></i>Déposer Rapport Final</a>
                                <?php elseif($projet['statut'] == 'rapport_soumis'): ?>
                                    <button class="btn btn-primary" disabled>Rapport Déposé (Attente Validation)</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php if($soutenance): ?>
                        <div class="card shadow-sm border-warning border-start border-4 mt-3">
                            <div class="card-body">
                                <h5><i class="fas fa-calendar-alt text-warning me-2"></i>Convocation</h5>
                                Date: <strong><?= date('d/m/Y H:i', strtotime($soutenance['date_soutenance'])) ?></strong><br>
                                Salle: <strong><?= htmlspecialchars($soutenance['salle']) ?></strong>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-lg-5">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-comments me-2"></i>Messagerie</span>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <?php if($projet['encadrant_id']): ?>
                                <div class="alert alert-primary py-2 small mb-3 d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-user-tie me-2"></i>Encadrant : <strong>Pr. <?= htmlspecialchars($projet['nom_enc']) ?></strong></span>
                                    
                                    <a href="mailto:<?= htmlspecialchars($projet['email_enc']) ?>" class="btn btn-sm btn-light border text-primary" title="Envoyer un email Outlook">
                                        <i class="fas fa-envelope"></i> Email
                                    </a>
                                </div>
                                
                                <div class="chat-box mb-3" id="chat">
                                    <?php if(empty($messages)) echo "<p class='text-center text-muted small mt-5'>Aucun message.</p>"; ?>
                                    <?php foreach($messages as $m): ?>
                                        <div class="msg <?= ($m['sender_id'] == $uid) ? 'msg-me' : 'msg-other' ?>">
                                            <?= nl2br(htmlspecialchars($m['message'])) ?>
                                            <div class="text-end opacity-50" style="font-size:0.7em"><?= date('H:i', strtotime($m['created_at'])) ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <form method="POST" class="mt-auto">
                                    <input type="hidden" name="pid" value="<?= $projet['id'] ?>">
                                    <div class="input-group">
                                        <input type="text" name="msg" class="form-control" placeholder="Message..." required>
                                        <button name="send_msg" class="btn btn-primary"><i class="fas fa-paper-plane"></i></button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="text-center text-muted my-auto"><i class="fas fa-clock fa-3x mb-3 opacity-25"></i><br>Attente encadrant...</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <script>
        var chat = document.getElementById("chat");
        if(chat) chat.scrollTop = chat.scrollHeight;
    </script>
</body>
</html>