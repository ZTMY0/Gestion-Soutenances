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
        $message = "Message envoyé avec succès !";
        $messageType = "success";
    }
}

// 2. Validation Rapport
if (isset($_POST['valider_rapport'])) {
    $projet_id = intval($_POST['projet_id']);
    $stmt = $pdo->prepare("UPDATE projets SET statut = 'valide_encadrant' WHERE id = ? AND encadrant_id = ?");
    if ($stmt->execute([$projet_id, $prof_id])) {
        $message = "Rapport validé avec succès !"; 
        $messageType = "success";
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
$nbEnAttente = 0; $nbValides = 0; $nbSoutenus = 0;
foreach($projets as $p) {
    if($p['statut'] == 'valide_encadrant') $nbValides++;
    elseif($p['statut'] == 'rapport_soumis') $nbEnAttente++;
    elseif($p['statut'] == 'soutenu') $nbSoutenus++;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Encadrements - UEMF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../public/assets/css/style.css">
</head>
<body>
    
    <!-- NAVBAR -->
    <nav class="navbar-modern">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center w-100">
                <a href="index.php" class="navbar-brand-modern text-white text-decoration-none">
                    <i class="fas fa-graduation-cap"></i>
                    <span>UEMF Professeur</span>
                </a>
                <div class="user-info">
                    <i class="fas fa-user-circle text-white-50"></i>
                    <span class="text-white d-none d-md-inline">Pr. <?= htmlspecialchars($_SESSION['user_nom']) ?></span>
                    <a href="../auth/logout.php" class="btn btn-sm btn-danger btn-modern">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="d-none d-md-inline">Déconnexion</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        
        <!-- HEADER -->
        <div class="row mb-4 align-items-center animate-fade-in">
            <div class="col-md-8">
                <h2 class="fw-bold text-dark mb-1">
                    <i class="fas fa-tasks text-primary me-2"></i>
                    Suivi des Projets
                </h2>
                <p class="text-muted mb-0">
                    Gérez vos encadrements, validez les rapports et communiquez avec vos étudiants
                </p>
            </div>
            <div class="col-md-4 text-end mt-3 mt-md-0">
                <a href="index.php" class="btn btn-outline-modern">
                    <i class="fas fa-arrow-left me-2"></i>
                    Retour
                </a>
            </div>
        </div>

        <!-- MESSAGES -->
        <?php if($message): ?>
            <div class="alert-modern <?= $messageType ?> animate-fade-in">
                <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'times-circle' ?>"></i>
                <div>
                    <strong><?= $messageType === 'success' ? 'Succès !' : 'Erreur !' ?></strong><br>
                    <span class="small"><?= $message ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- STATS -->
        <div class="row g-4 mb-5">
            <div class="col-md-3 col-sm-6 animate-fade-in" style="animation-delay: 0.1s">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <div class="stat-number"><?= $nbTotal ?></div>
                    <div class="stat-label">Total Projets</div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 animate-fade-in" style="animation-delay: 0.2s">
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-number"><?= $nbEnAttente ?></div>
                    <div class="stat-label">En attente validation</div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 animate-fade-in" style="animation-delay: 0.3s">
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-number"><?= $nbValides ?></div>
                    <div class="stat-label">Validés</div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 animate-fade-in" style="animation-delay: 0.4s">
                <div class="stat-card">
                    <div class="stat-icon danger">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="stat-number"><?= $nbSoutenus ?></div>
                    <div class="stat-label">Soutenus</div>
                </div>
            </div>
        </div>

        <!-- PROJETS -->
        <?php if(empty($projets)): ?>
            <div class="card animate-fade-in" style="border-radius: var(--radius-xl); border: 1px solid var(--gray-200);">
                <div class="card-body text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3 opacity-50"></i>
                    <h5 class="text-muted">Aucun projet assigné</h5>
                    <p class="text-muted small">Vous n'avez pas encore de projets à encadrer</p>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach($projets as $index => $p): 
                    // Chat Count
                    $stmtMsg = $pdo->prepare("SELECT m.*, u.nom FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.projet_id = ? ORDER BY m.created_at ASC");
                    $stmtMsg->execute([$p['id']]);
                    $msgs = $stmtMsg->fetchAll();
                    
                    // Définir le statut pour le style
                    $statusClass = '';
                    $statusBadge = '';
                    switch($p['statut']) {
                        case 'valide_encadrant':
                            $statusClass = 'valide';
                            $statusBadge = 'success';
                            break;
                        case 'rapport_soumis':
                            $statusClass = 'en_cours';
                            $statusBadge = 'warning';
                            break;
                        case 'soutenu':
                            $statusClass = 'valide';
                            $statusBadge = 'info';
                            break;
                        default:
                            $statusClass = 'en_cours';
                            $statusBadge = 'secondary';
                    }
                ?>
                <div class="col-lg-6 animate-fade-in" style="animation-delay: <?= $index * 0.1 ?>s">
                    <div class="project-card" data-status="<?= $statusClass ?>">
                        <div class="card-header bg-white py-3 border-0">
                            <div class="d-flex align-items-center">
                                <div class="student-avatar me-3">
                                    <?= strtoupper(substr($p['etudiant_nom'], 0, 2)) ?>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 fw-bold"><?= htmlspecialchars($p['etudiant_nom']) ?></h6>
                                    <small class="text-muted">
                                        <?= $p['binome_nom'] ? '<i class="fas fa-users me-1"></i>Binôme: '.$p['binome_nom'] : '<i class="fas fa-user me-1"></i>Monôme' ?>
                                    </small>
                                </div>
                                <span class="badge-modern <?= $statusBadge ?>">
                                    <?= ucfirst(str_replace('_', ' ', $p['statut'])) ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <h5 class="card-title text-primary mb-3">
                                <i class="fas fa-project-diagram me-2"></i>
                                <?= htmlspecialchars($p['titre']) ?>
                            </h5>
                            
                            <?php if($p['filiere_nom']): ?>
                                <p class="mb-2">
                                    <span class="badge-modern secondary">
                                        <i class="fas fa-graduation-cap me-1"></i>
                                        <?= htmlspecialchars($p['filiere_nom']) ?>
                                    </span>
                                </p>
                            <?php endif; ?>
                            
                            <?php if($p['description']): ?>
                                <p class="text-muted small mb-3">
                                    <?= htmlspecialchars(substr($p['description'], 0, 150)) ?>
                                    <?= strlen($p['description']) > 150 ? '...' : '' ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if($p['rapport_path']): ?>
                                <div class="alert alert-light border p-3 mb-3 d-flex align-items-center justify-content-between">
                                    <div>
                                        <i class="fas fa-file-pdf text-danger fa-2x me-3"></i>
                                        <strong>Rapport reçu</strong>
                                    </div>
                                    <a href="../../../public/<?= $p['rapport_path'] ?>" target="_blank" class="btn btn-sm btn-outline-modern">
                                        <i class="fas fa-download me-1"></i>
                                        Télécharger
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-footer bg-white border-0 pb-3">
                            <div class="d-flex gap-2 mb-3">
                                <button class="btn btn-primary-modern btn-sm flex-fill" data-bs-toggle="collapse" data-bs-target="#chat<?= $p['id'] ?>">
                                    <i class="fas fa-comments me-1"></i> 
                                    Chat 
                                    <?php if(count($msgs) > 0): ?>
                                        <span class="badge bg-white text-primary rounded-pill"><?= count($msgs) ?></span>
                                    <?php endif; ?>
                                </button>
                                
                                <a href="mailto:<?= htmlspecialchars($p['etudiant_email']) ?>" class="btn btn-outline-modern btn-sm flex-fill">
                                    <i class="fas fa-envelope me-1"></i> 
                                    Email
                                </a>

                                <?php if($p['statut'] == 'rapport_soumis'): ?>
                                    <form method="POST" class="flex-fill">
                                        <input type="hidden" name="projet_id" value="<?= $p['id'] ?>">
                                        <button type="submit" name="valider_rapport" class="btn btn-success-modern btn-sm w-100" onclick="return confirm('Valider ce rapport ?')">
                                            <i class="fas fa-check me-1"></i> 
                                            Valider
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>

                            <!-- CHAT ZONE -->
                            <div class="collapse" id="chat<?= $p['id'] ?>">
                                <div class="bg-light p-3 rounded" style="border-radius: var(--radius-lg);">
                                    <div class="chat-box mb-3" id="box<?= $p['id'] ?>">
                                        <?php if(empty($msgs)): ?>
                                            <div class="text-center text-muted py-3">
                                                <i class="fas fa-comments fa-2x mb-2 opacity-50"></i>
                                                <p class="mb-0 small">Aucun message pour le moment</p>
                                            </div>
                                        <?php else: ?>
                                            <?php foreach($msgs as $m): $isMe = ($m['sender_id'] == $prof_id); ?>
                                                <div class="msg <?= $isMe ? 'msg-me' : 'msg-other' ?>">
                                                    <strong class="d-block mb-1">
                                                        <i class="fas fa-<?= $isMe ? 'chalkboard-teacher' : 'user-graduate' ?> me-1"></i>
                                                        <?= $isMe ? 'Moi' : htmlspecialchars($m['nom']) ?>
                                                    </strong>
                                                    <?= nl2br(htmlspecialchars($m['message'])) ?>
                                                    <small class="d-block mt-1 opacity-75">
                                                        <?= date('d/m/Y H:i', strtotime($m['created_at'])) ?>
                                                    </small>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <form method="POST" class="input-group">
                                        <input type="hidden" name="projet_id" value="<?= $p['id'] ?>">
                                        <input type="text" name="msg_text" class="form-control" placeholder="Écrivez votre message..." required style="border-radius: var(--radius) 0 0 var(--radius);">
                                        <button type="submit" name="send_msg" class="btn btn-primary-modern" style="border-radius: 0 var(--radius) var(--radius) 0;">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
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
        // Auto-scroll chat au bas
        document.querySelectorAll('.collapse').forEach(el => {
            el.addEventListener('shown.bs.collapse', () => {
                const box = el.querySelector('.chat-box');
                if(box) {
                    box.scrollTop = box.scrollHeight;
                    // Animation smooth
                    box.style.animation = 'fadeInUp 0.3s ease-out';
                }
            });
        });

        // Animation des project cards au scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.project-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'all 0.6s ease-out';
            observer.observe(card);
        });
    </script>
</body>
</html>