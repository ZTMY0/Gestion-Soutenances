<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'prof') {
    header("Location: ../auth/login.php"); exit();
}

$prof_id = $_SESSION['user_id'];
$message = ''; $messageType = '';

// 1. Envoi Message (Chat interne)
if (isset($_POST['send_msg']) && !empty($_POST['msg_text'])) {
    $pid = intval($_POST['projet_id']);
    $msg = trim($_POST['msg_text']);
    $check = $pdo->prepare("SELECT id FROM projets WHERE id = ? AND encadrant_id = ?");
    $check->execute([$pid, $prof_id]);
    if($check->fetch()) {
        $pdo->prepare("INSERT INTO messages (projet_id, sender_id, message) VALUES (?, ?, ?)")->execute([$pid, $prof_id, $msg]);
    }
}

// 2. Validation Rapport
if (isset($_POST['valider_rapport'])) {
    $projet_id = intval($_POST['projet_id']);
    $stmt = $pdo->prepare("UPDATE projets SET statut = 'valide_encadrant' WHERE id = ? AND encadrant_id = ?");
    if ($stmt->execute([$projet_id, $prof_id])) {
        $message = "Rapport validé !"; $messageType = "success";
    }
}

// 3. Récupération Projets
$sql = "SELECT p.*, u.nom AS etudiant_nom, u.email AS etudiant_email, 
               b.nom AS binome_nom, f.nom AS filiere_nom, 
               r.chemin_fichier AS rapport_path 
        FROM projets p
        JOIN users u ON p.etudiant_id = u.id
        LEFT JOIN users b ON p.binome_email = b.email
        LEFT JOIN filieres f ON p.filiere_id = f.id
        LEFT JOIN rapports r ON r.projet_id = p.id
        WHERE p.encadrant_id = ? ORDER BY p.created_at DESC";
$projets = $pdo->prepare($sql);
$projets->execute([$prof_id]);
$projets = $projets->fetchAll();

// Stats
$nbTotal = count($projets);
$nbEnAttente = 0; $nbValides = 0;
foreach($projets as $p) {
    if($p['statut'] == 'valide_encadrant') $nbValides++;
    elseif($p['rapport_path']) $nbEnAttente++;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Encadrement Professeur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .project-card { border:none; border-radius:15px; transition:0.3s; }
        .project-card:hover { transform:translateY(-3px); box-shadow:0 10px 30px rgba(0,0,0,0.1); }
        .student-avatar { width:50px; height:50px; border-radius:50%; background:#6c5ce7; color:white; display:flex; align-items:center; justify-content:center; font-weight:bold; }
        .chat-box { height:250px; overflow-y:auto; background:white; border:1px solid #dee2e6; border-radius:10px; padding:15px; }
        .msg { padding:8px 12px; border-radius:15px; margin-bottom:8px; max-width:85%; font-size:0.9rem; }
        .msg-me { background:#d1e7dd; margin-left:auto; text-align:right; }
        .msg-other { background:#f1f2f6; margin-right:auto; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark px-4 shadow-sm">
        <span class="navbar-brand"><i class="fas fa-chalkboard-teacher me-2"></i>Espace Professeur</span>
        <div class="text-white"><?= htmlspecialchars($_SESSION['user_nom']) ?> <a href="../auth/logout.php" class="btn btn-sm btn-outline-light ms-3">Déconnexion</a></div>
    </nav>

    <div class="container py-4">
        <div class="d-flex justify-content-between mb-4">
            <h3><i class="fas fa-tasks text-primary me-2"></i>Suivi des Projets</h3>
            <a href="index.php" class="btn btn-outline-secondary">Retour</a>
        </div>

        <?php if($message): ?><div class="alert alert-<?= $messageType ?>"><?= $message ?></div><?php endif; ?>

        <div class="row g-3 mb-4">
            <div class="col-md-4"><div class="card p-3 border-0 shadow-sm"><h3 class="fw-bold text-primary"><?= $nbTotal ?></h3><small>Total Projets</small></div></div>
            <div class="col-md-4"><div class="card p-3 border-0 shadow-sm"><h3 class="fw-bold text-warning"><?= $nbEnAttente ?></h3><small>En attente validation</small></div></div>
            <div class="col-md-4"><div class="card p-3 border-0 shadow-sm"><h3 class="fw-bold text-success"><?= $nbValides ?></h3><small>Validés</small></div></div>
        </div>

        <?php if(empty($projets)): ?>
            <div class="text-center py-5 text-muted">Aucun projet assigné.</div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach($projets as $p): 
                    // Chat Count
                    $stmtMsg = $pdo->prepare("SELECT m.*, u.nom FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.projet_id = ? ORDER BY m.created_at ASC");
                    $stmtMsg->execute([$p['id']]);
                    $msgs = $stmtMsg->fetchAll();
                ?>
                <div class="col-lg-6">
                    <div class="card project-card shadow-sm h-100">
                        <div class="card-header bg-white py-3 d-flex align-items-center">
                            <div class="student-avatar me-3"><?= strtoupper(substr($p['etudiant_nom'],0,2)) ?></div>
                            <div>
                                <h6 class="mb-0 fw-bold"><?= htmlspecialchars($p['etudiant_nom']) ?></h6>
                                <small class="text-muted"><?= $p['binome_nom'] ? 'Binôme: '.$p['binome_nom'] : 'Monôme' ?></small>
                            </div>
                            <span class="badge bg-secondary ms-auto"><?= $p['statut'] ?></span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title text-primary"><?= htmlspecialchars($p['titre']) ?></h5>
                            <?php if($p['rapport_path']): ?>
                                <div class="alert alert-light border p-2 mt-3"><i class="fas fa-file-pdf text-danger me-2"></i>Rapport reçu <a href="../../../public/<?= $p['rapport_path'] ?>" target="_blank" class="float-end"><i class="fas fa-download"></i></a></div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-white border-0 pb-3">
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-primary btn-sm flex-fill" data-bs-toggle="collapse" data-bs-target="#chat<?= $p['id'] ?>">
                                    <i class="fas fa-comments"></i> Chat <span class="badge bg-primary rounded-pill"><?= count($msgs) ?></span>
                                </button>
                                
                                <a href="mailto:<?= htmlspecialchars($p['etudiant_email']) ?>" class="btn btn-outline-dark btn-sm flex-fill">
                                    <i class="fas fa-envelope"></i> Email
                                </a>

                                <?php if($p['statut'] == 'rapport_soumis'): ?>
                                    <form method="POST" class="flex-fill">
                                        <input type="hidden" name="projet_id" value="<?= $p['id'] ?>">
                                        <button type="submit" name="valider_rapport" class="btn btn-success btn-sm w-100" onclick="return confirm('Valider ?')"><i class="fas fa-check"></i> Valider</button>
                                    </form>
                                <?php endif; ?>
                            </div>

                            <div class="collapse mt-3" id="chat<?= $p['id'] ?>">
                                <div class="bg-light p-3 rounded">
                                    <div class="chat-box" id="box<?= $p['id'] ?>">
                                        <?php foreach($msgs as $m): $isMe = ($m['sender_id'] == $prof_id); ?>
                                            <div class="msg <?= $isMe ? 'msg-me' : 'msg-other' ?>">
                                                <strong><?= $isMe ? 'Moi' : htmlspecialchars($m['nom']) ?></strong><br>
                                                <?= nl2br(htmlspecialchars($m['message'])) ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <form method="POST" class="mt-2 input-group input-group-sm">
                                        <input type="hidden" name="projet_id" value="<?= $p['id'] ?>">
                                        <input type="text" name="msg_text" class="form-control" placeholder="Répondre..." required>
                                        <button type="submit" name="send_msg" class="btn btn-primary"><i class="fas fa-paper-plane"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.collapse').forEach(el => {
            el.addEventListener('shown.bs.collapse', () => {
                const box = el.querySelector('.chat-box');
                if(box) box.scrollTop = box.scrollHeight;
            })
        });
    </script>
</body>
</html>