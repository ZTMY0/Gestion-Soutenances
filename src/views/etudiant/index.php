<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'etudiant') {
    header("Location: ../auth/login.php"); exit();
}

$uid = $_SESSION['user_id'];
$projet = null; $messages = []; $soutenance = null;

// TRAITEMENT MESSAGE
if (isset($_POST['send_msg']) && !empty($_POST['msg'])) {
    $pdo->prepare("INSERT INTO messages (projet_id, sender_id, message) VALUES (?, ?, ?)")->execute([$_POST['pid'], $uid, trim($_POST['msg'])]);
    header("Location: index.php"); exit();
}

// RECUPERATION DONNEES
$stmt = $pdo->prepare("SELECT p.*, u.nom as nom_enc, u.prenom as prenom_enc, u.email as email_enc FROM projets p LEFT JOIN users u ON p.encadrant_id = u.id WHERE p.etudiant_id = ?");
$stmt->execute([$uid]);
$projet = $stmt->fetch(PDO::FETCH_ASSOC);

if ($projet) {
    $stmtMsg = $pdo->prepare("SELECT m.* FROM messages m WHERE m.projet_id = ? ORDER BY m.created_at ASC");
    $stmtMsg->execute([$projet['id']]);
    $messages = $stmtMsg->fetchAll();
    
    $stmtSout = $pdo->prepare("SELECT * FROM soutenances WHERE projet_id = ?");
    $stmtSout->execute([$projet['id']]);
    $soutenance = $stmtSout->fetch();
}

// CALCUL PROGRESSION (1 à 5)
$progress_level = 1; 
if ($projet) {
    if (!empty($projet['encadrant_id'])) { $progress_level = 2; }
    $has_report = !empty($projet['rapport_chemin']);
    if ($has_report) { $progress_level = 3; } 
    $statut_clean = trim(strtolower($projet['statut']));
    if ($statut_clean == 'valide' || $statut_clean == 'pret_soutenance' || ($statut_clean == 'valide_encadrant' && $has_report) || !empty($soutenance)) { $progress_level = 4; }
    if (!empty($projet['note_finale'])) { $progress_level = 5; }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Espace Étudiant | UEMF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .steps-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }
        .steps-container::before {
            content: '';
            position: absolute;
            top: 25px;
            left: 0;
            right: 0;
            height: 4px;
            background: #e9ecef;
            z-index: 0;
            border-radius: 2px;
        }
        .step-item {
            position: relative;
            z-index: 1;
            text-align: center;
            flex: 1;
        }
        .step-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #fff;
            border: 4px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px auto;
            font-weight: 700;
            color: #adb5bd;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }
        .step-label {
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
        }
        /* État Actif */
        .step-item.active .step-circle {
            border-color: #004d99; /* Bleu UEMF */
            background: #004d99;
            color: #fff;
            box-shadow: 0 4px 10px rgba(0, 77, 153, 0.2);
        }
        .step-item.active .step-label {
            color: #004d99;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-2">
        <div class="container">
            <a class="navbar-brand text-uppercase fw-bold" href="#">UEMF Espace PFE</a>
            <div class="d-flex align-items-center text-white-50">
                <span class="me-3 small text-uppercase"><i class="fas fa-user-graduate me-2"></i><?php echo $_SESSION['user_nom']; ?></span>
                <a href="../auth/logout.php" class="text-white"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>
    </nav>

    <?php if (!$projet): ?>
        
        <div class="dashboard-hero" style="padding-bottom: 8rem;">
            <div class="container text-center">
                <h1 class="mb-2">Bienvenue sur votre Espace PFE</h1>
                <p class="opacity-75 lead">Démarrez votre année académique en soumettant votre sujet.</p>
            </div>
        </div>

        <div class="container" style="margin-top: -5rem;">
            <div class="card shadow-lg border-0 mx-auto text-center p-5" style="max-width: 600px;">
                <div class="mb-4 text-primary opacity-25">
                    <i class="fas fa-rocket fa-5x"></i>
                </div>
                <h3 class="fw-bold text-dark mb-3">Aucun projet enregistré</h3>
                <p class="text-muted mb-4">Vous devez proposer un sujet de Projet de Fin d'Études pour validation par le coordinateur.</p>
                <a href="soumettre.php" class="btn btn-primary btn-lg px-5 fw-bold shadow-sm">
                    <i class="fas fa-plus-circle me-2"></i>Déposer mon sujet
                </a>
            </div>
        </div>

    <?php else: ?>

        <div class="dashboard-hero">
            <div class="container">
                <div class="d-flex justify-content-between align-items-end">
                    <div style="max-width: 70%;">
                        <span class="badge bg-warning text-dark mb-2"><i class="fas fa-code-branch me-1"></i><?= htmlspecialchars($projet['domaine']) ?></span>
                        <h2 class="mb-1 text-truncate"><?= htmlspecialchars($projet['titre']) ?></h2>
                        <p class="mb-0 opacity-75 small">Soumis le <?= date('d/m/Y', strtotime($projet['created_at'])) ?></p>
                    </div>
                    <?php if($soutenance): ?>
                        <div class="text-end d-none d-md-block">
                            <div class="h2 mb-0 fw-bold"><?= date('d M', strtotime($soutenance['date_soutenance'])) ?></div>
                            <div class="badge bg-light text-primary"><?= date('H:i', strtotime($soutenance['date_soutenance'])) ?> - <?= $soutenance['salle'] ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="container pb-5" style="margin-top: -3rem;">
            
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body py-4 px-5">
                    <div class="steps-container">
                        <div class="step-item <?= ($progress_level >= 1) ? 'active' : '' ?>">
                            <div class="step-circle">1</div>
                            <div class="step-label">Inscription</div>
                        </div>
                        <div class="step-item <?= ($progress_level >= 2) ? 'active' : '' ?>">
                            <div class="step-circle">2</div>
                            <div class="step-label">Encadré</div>
                        </div>
                        <div class="step-item <?= ($progress_level >= 3) ? 'active' : '' ?>">
                            <div class="step-circle">3</div>
                            <div class="step-label">Rapport</div>
                        </div>
                        <div class="step-item <?= ($progress_level >= 4) ? 'active' : '' ?>">
                            <div class="step-circle">4</div>
                            <div class="step-label">Soutenance</div>
                        </div>
                        <div class="step-item <?= ($progress_level >= 5) ? 'active' : '' ?>">
                            <div class="step-circle"><i class="fas fa-check"></i></div>
                            <div class="step-label">Terminé</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3 fw-bold text-dark border-bottom">
                            <i class="fas fa-file-alt me-2 text-primary"></i>Fiche Projet
                        </div>
                        <div class="card-body p-4">
                            
                            <h5 class="fw-bold text-dark mb-3">Description</h5>
                            <p class="text-secondary lh-lg mb-4"><?= nl2br(htmlspecialchars($projet['description'])) ?></p>
                            
                            <h6 class="fw-bold text-dark mb-2">Technologies</h6>
                            <div class="mb-4">
                                <?php foreach(explode(',', $projet['technologies']) as $t): ?>
                                    <span class="badge bg-light text-dark border me-1 px-2 py-2"><?= trim($t) ?></span>
                                <?php endforeach; ?>
                            </div>

                            <hr class="my-4 opacity-25">

                            <div class="bg-light p-4 rounded border">
                                <?php if($progress_level >= 4): ?>
                                    <div class="text-center text-success">
                                        <i class="fas fa-check-circle fa-3x mb-3"></i>
                                        <h5 class="fw-bold">Dossier Validé</h5>
                                        <p class="small mb-3">Votre rapport est validé. Préparez votre soutenance.</p>
                                        
                                        <?php if($soutenance): ?>
                                            <div class="alert alert-success d-inline-block px-4 py-2 mb-0 shadow-sm border-0">
                                                <strong><i class="fas fa-calendar-day me-2"></i>Convocation :</strong> 
                                                <?= date('d/m/Y à H:i', strtotime($soutenance['date_soutenance'])) ?> en <?= $soutenance['salle'] ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Planning en cours...</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mt-3 text-center">
                                        <a href="../../../public/uploads/<?= htmlspecialchars($projet['rapport_chemin']) ?>" target="_blank" class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-download me-2"></i>Télécharger mon rapport
                                        </a>
                                    </div>

                                <?php elseif($progress_level == 3): ?>
                                    <div class="text-center text-primary">
                                        <i class="fas fa-hourglass-half fa-3x mb-3"></i>
                                        <h5 class="fw-bold">En attente de validation</h5>
                                        <p class="small mb-3">Votre rapport a été déposé. L'encadrant doit le valider.</p>
                                        <a href="../../../public/uploads/<?= htmlspecialchars($projet['rapport_chemin']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye me-2"></i>Voir le fichier déposé
                                        </a>
                                    </div>

                                <?php elseif($progress_level == 2): ?>
                                    <div class="text-center">
                                        <h5 class="fw-bold">Dépôt du Rapport</h5>
                                        <p class="small text-muted mb-3">Veuillez déposer votre rapport final en PDF une fois terminé.</p>
                                        <a href="depot.php" class="btn btn-primary w-100 fw-bold shadow-sm">
                                            <i class="fas fa-cloud-upload-alt me-2"></i>DÉPOSER MON RAPPORT
                                        </a>
                                    </div>

                                <?php else: ?>
                                    <div class="alert alert-warning border-0 mb-0 text-center">
                                        <i class="fas fa-exclamation-triangle me-2"></i>En attente d'affectation d'un encadrant.
                                    </div>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3 fw-bold text-dark border-bottom">
                            <i class="fas fa-comments me-2 text-primary"></i>Encadrement
                        </div>
                        <div class="card-body d-flex flex-column p-0">
                            
                            <?php if($projet['encadrant_id']): ?>
                                <div class="p-3 border-bottom bg-light">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-white text-primary border rounded-circle p-3 me-3">
                                            <i class="fas fa-user-tie fa-lg"></i>
                                        </div>
                                        <div>
                                            <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Encadrant</small>
                                            <div class="fw-bold text-dark">Pr. <?= htmlspecialchars($projet['nom_enc']) ?></div>
                                            <a href="mailto:<?= $projet['email_enc'] ?>" class="small text-decoration-none"><i class="far fa-envelope me-1"></i>Email</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="chat-window flex-grow-1 bg-white" id="chat" style="height: 350px; overflow-y: auto; padding: 15px;">
                                    <?php if(empty($messages)): ?>
                                        <div class="h-100 d-flex align-items-center justify-content-center text-muted small">
                                            <div><i class="far fa-comment-dots fa-2x mb-2 d-block"></i>Aucun message</div>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach($messages as $m): ?>
                                            <div class="msg-bubble <?= ($m['sender_id'] == $uid) ? 'msg-me' : 'msg-other' ?>" style="margin-bottom: 10px; padding: 10px; border-radius: 10px; max-width: 80%; <?= ($m['sender_id'] == $uid) ? 'background-color: #004d99; color: white; margin-left: auto;' : 'background-color: #f1f3f5; margin-right: auto;' ?>">
                                                <?= nl2br(htmlspecialchars($m['message'])) ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                                <div class="p-3 border-top bg-light">
                                    <form method="POST" class="d-flex">
                                        <input type="hidden" name="pid" value="<?= $projet['id'] ?>">
                                        <input type="text" name="msg" class="form-control me-2" placeholder="Votre message..." required>
                                        <button name="send_msg" class="btn btn-primary"><i class="fas fa-paper-plane"></i></button>
                                    </form>
                                </div>

                            <?php else: ?>
                                <div class="p-5 text-center text-muted">
                                    <i class="fas fa-user-slash fa-3x mb-3 opacity-25"></i>
                                    <p>L'espace de discussion s'ouvrira dès qu'un encadrant vous sera assigné.</p>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>

            </div>
        </div>

    <?php endif; ?>

    <script>
        var chat = document.getElementById("chat");
        if(chat) chat.scrollTop = chat.scrollHeight;
    </script>
</body>
</html>