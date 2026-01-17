<?php
// 1. SÉCURITÉ & CONFIGURATION
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Chemins absolus
require_once __DIR__ . '/../../../config/session_check.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../services/SecurityService.php';

// Initialisation Service Sécurité
$security = new SecurityService($pdo);

// 2. VÉRIFICATION DU RÔLE
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'etudiant') {
    header("Location: ../auth/login.php"); exit();
}

// 3. FONCTION RÉCUPÉRATION IP
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
    return $_SERVER['REMOTE_ADDR'];
}
$user_ip = getUserIP();

$uid = $_SESSION['user_id'];
$projet = null; 
$messages = []; 
$soutenance = null;

// Vérification BDD
if (!isset($pdo)) { die("Erreur critique : Connexion BDD échouée."); }

// 4. TRAITEMENT DU CHAT (Envoi message)
if (isset($_POST['send_msg']) && !empty($_POST['msg'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO messages (projet_id, expediteur_id, contenu, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$_POST['pid'], $uid, trim($_POST['msg'])]);
        header("Location: index.php"); exit(); 
    } catch (PDOException $e) {
        $error = "Erreur d'envoi : " . $e->getMessage();
    }
}

// 5. RÉCUPÉRATION DES DONNÉES
try {
    // Infos Projet + Encadrant
        $stmt = $pdo->prepare("SELECT p.*, u.nom as nom_enc, u.prenom as prenom_enc, u.email as email_enc, f.nom as filiere_nom
                               FROM projets p
                               LEFT JOIN users u ON p.encadrant_id = u.id
                               LEFT JOIN filieres f ON p.filiere_id = f.id
                               WHERE p.etudiant_id = ?");    $stmt->execute([$uid]);
    $projet = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($projet) {
        // Messages du chat
        $stmtMsg = $pdo->prepare("SELECT m.* FROM messages m WHERE m.projet_id = ? ORDER BY m.created_at ASC");
        $stmtMsg->execute([$projet['id']]);
        $messages = $stmtMsg->fetchAll();
        
        // Infos Soutenance (si existe)
        try {
            $stmtSout = $pdo->prepare("SELECT s.*, sal.nom as salle_nom FROM soutenances s JOIN salles sal ON s.salle_id = sal.id WHERE s.projet_id = ?");
            $stmtSout->execute([$projet['id']]);
            $soutenance = $stmtSout->fetch();
        } catch (Exception $e) { /* Table n'existe peut-être pas encore */ }
    }
} catch (PDOException $e) {
    die("Erreur SQL : " . $e->getMessage());
}

// 6. CALCUL DE LA PROGRESSION (1 à 5)
$progress_level = 1; // 1 = Inscrit
if ($projet) {
    if (!empty($projet['encadrant_id'])) { $progress_level = 2; } // 2 = Encadré
    
    $has_report = !empty($projet['rapport_chemin']);
    if ($has_report) { $progress_level = 3; } // 3 = Rapport déposé
    
    $statut_clean = trim(strtolower($projet['statut']));
    // Si validé ou soutenance programmée
    if ($statut_clean == 'valide' || $statut_clean == 'pret_soutenance' || ($statut_clean == 'valide_encadrant' && $has_report) || !empty($soutenance)) { 
        $progress_level = 4; // 4 = Soutenance
    }
    
    if (!empty($projet['note_finale'])) { $progress_level = 5; } // 5 = Terminé
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
        /* CSS Spécifique Timeline */
        .steps-container { display: flex; justify-content: space-between; align-items: center; position: relative; margin-bottom: 20px; }
        .steps-container::before { content: ''; position: absolute; top: 25px; left: 0; right: 0; height: 4px; background: #e9ecef; z-index: 0; border-radius: 2px; }
        .step-item { position: relative; z-index: 1; text-align: center; flex: 1; }
        .step-circle { width: 50px; height: 50px; border-radius: 50%; background: #fff; border: 4px solid #e9ecef; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px auto; font-weight: 700; color: #adb5bd; font-size: 1.2rem; transition: all 0.3s ease; }
        .step-label { font-size: 0.85rem; color: #6c757d; font-weight: 600; text-transform: uppercase; }
        
        /* État Actif */
        .step-item.active .step-circle { border-color: #004d99; background: #004d99; color: #fff; box-shadow: 0 4px 10px rgba(0, 77, 153, 0.2); }
        .step-item.active .step-label { color: #004d99; }
        
        /* Chat */
        .chat-window { background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; }
        .msg-bubble { padding: 8px 12px; border-radius: 15px; margin-bottom: 8px; display: inline-block; word-wrap: break-word; }
        .msg-me { background-color: #004d99; color: white; float: right; clear: both; }
        .msg-other { background-color: #e9ecef; color: #333; float: left; clear: both; }
        .chat-container { overflow-y: auto; height: 300px; padding: 15px; display: flex; flex-direction: column; }
    </style>
    
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-2 mb-4">
        <div class="container">
            <a class="navbar-brand text-uppercase fw-bold" href="#">UEMF Espace PFE</a>
            <div class="d-flex align-items-center text-white-50">
                <a href="profil.php" class="btn btn-outline-light btn-sm me-3 border-0">
                    <i class="fas fa-user-cog me-1"></i> Mon Profil
                </a>
                <span class="me-3 small text-uppercase"><i class="fas fa-user-graduate me-2"></i><?php echo $_SESSION['user_nom'] ?? 'Étudiant'; ?></span>
                <a href="../auth/logout.php" class="text-white"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>
    </nav>

    <?php if (!$projet): ?>
        
        <div class="container text-center mt-5">
            <div class="card shadow-lg border-0 mx-auto p-5" style="max-width: 600px;">
             
                <h3 class="fw-bold text-dark mb-3">Aucun projet enregistré</h3>
                <p class="text-muted mb-4">Démarrez votre année académique en soumettant votre sujet de PFE.</p>
                <div class="alert alert-info d-inline-block">
                    <i class="fas fa-network-wired me-1"></i>Votre IP : <?= $user_ip ?>
                </div>
                <br><br>
                <a href="soumettre.php" class="btn btn-primary btn-lg px-4 fw-bold shadow-sm">
                    <i class="fas fa-plus-circle me-2"></i>Déposer mon sujet
                </a>
            </div>
        </div>

    <?php else: ?>

        <div class="container pb-5">
            
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <div class="mb-2">
                        <span class="badge bg-warning text-dark me-1">
                            <i class="fas fa-code-branch me-1"></i><?= htmlspecialchars($projet['filiere_nom']) ?>
                        </span>
                        
                        <span class="badge bg-secondary me-1">
                            <i class="fas fa-hashtag me-1"></i>ID: <?= $projet['id'] ?>
                        </span>

                        <span class="badge bg-info text-dark" title="Votre adresse IP actuelle">
                            <i class="fas fa-network-wired me-1"></i>IP: <?= $user_ip ?>
                        </span>
                    </div>

                    <h2 class="mb-0 fw-bold"><?= htmlspecialchars($projet['titre']) ?></h2>
                </div>

                <?php if($soutenance && is_array($soutenance)): ?>
                    <div class="text-end">
                        <div class="h4 mb-0 fw-bold text-success"><?= date('d M Y', strtotime($soutenance['date_soutenance'])) ?></div>
                        <div class="badge bg-light text-primary border"><?= date('H:i', strtotime($soutenance['date_soutenance'])) ?> - <?= htmlspecialchars($soutenance['salle_nom']) ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body py-4 px-5">
                    <div class="steps-container">
                        <div class="step-item <?= ($progress_level >= 1) ? 'active' : '' ?>">
                            <div class="step-circle">1</div><div class="step-label">Inscription</div>
                        </div>
                        <div class="step-item <?= ($progress_level >= 2) ? 'active' : '' ?>">
                            <div class="step-circle">2</div><div class="step-label">Encadré</div>
                        </div>
                        <div class="step-item <?= ($progress_level >= 3) ? 'active' : '' ?>">
                            <div class="step-circle">3</div><div class="step-label">Rapport</div>
                        </div>
                        <div class="step-item <?= ($progress_level >= 4) ? 'active' : '' ?>">
                            <div class="step-circle">4</div><div class="step-label">Soutenance</div>
                        </div>
                        <div class="step-item <?= ($progress_level >= 5) ? 'active' : '' ?>">
                            <div class="step-circle"><i class="fas fa-check"></i></div><div class="step-label">Terminé</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3 fw-bold"><i class="fas fa-file-alt me-2 text-primary"></i>Détails du Projet</div>
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-2">Description</h5>
                            <p class="text-muted mb-4"><?= nl2br(htmlspecialchars($projet['description'])) ?></p>
                            
                            <h6 class="fw-bold mb-2">Technologies</h6>
                            <div class="mb-4">
                                <?php foreach(explode(',', $projet['mots_cles']) as $t): ?>
                                    <span class="badge bg-light text-dark border me-1"><?= trim($t) ?></span>
                                <?php endforeach; ?>
                            </div>

                            <hr class="my-4 opacity-25">

                            <div class="bg-light p-4 rounded border text-center">
                                <?php if($progress_level >= 4): ?>
                                    <?php if(!empty($projet['rapport_chemin'])): ?>
                                        <div class="text-success mb-3"><i class="fas fa-check-circle fa-2x"></i><br><strong>Dossier Validé</strong></div>
                                        <a href="../../../public/uploads/<?= htmlspecialchars($projet['rapport_chemin']) ?>" target="_blank" class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-download me-2"></i>Télécharger mon rapport
                                        </a>
                                    <?php else: ?>
                                        <div class="text-primary mb-3"><i class="fas fa-clock fa-2x"></i><br><strong>Dossier Validé</strong></div>
                                        <p class="small text-muted">Votre dossier est validé. Déposez votre rapport final.</p>
                                        <a href="depot.php" class="btn btn-primary fw-bold px-4 shadow-sm"><i class="fas fa-cloud-upload-alt me-2"></i>DÉPOSER</a>
                                    <?php endif; ?>
                                <?php elseif($progress_level == 3): ?>
                                    <div class="text-primary mb-3"><i class="fas fa-clock fa-2x"></i><br><strong>En attente de validation</strong></div>
                                    <p class="small text-muted">Votre rapport est chez l'encadrant.</p>
                                    <a href="../../../public/uploads/<?= htmlspecialchars($projet['rapport_chemin']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">Voir le fichier</a>
                                <?php elseif($progress_level == 2): ?>
                                    <h5>Dépôt du Rapport</h5>
                                    <p class="small text-muted">Déposez votre PDF final ici.</p>
                                    <a href="depot.php" class="btn btn-primary fw-bold px-4 shadow-sm"><i class="fas fa-cloud-upload-alt me-2"></i>DÉPOSER</a>
                                <?php else: ?>
                                    <div class="text-muted"><i class="fas fa-exclamation-triangle me-2"></i>Attendez l'affectation d'un encadrant.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3 fw-bold"><i class="fas fa-comments me-2 text-primary"></i>Encadrement</div>
                        <div class="card-body d-flex flex-column p-0">
                            
                            <?php if($projet['encadrant_id']): ?>
                                <div class="p-3 border-bottom bg-light d-flex align-items-center">
                                    <div class="bg-white text-primary border rounded-circle p-3 me-3"><i class="fas fa-user-tie fa-lg"></i></div>
                                    <div>
                                        <div class="small text-uppercase text-muted fw-bold">Encadrant</div>
                                        <div class="fw-bold text-dark">Pr. <?= htmlspecialchars($projet['nom_enc']) ?></div>
                                        <a href="mailto:<?= $projet['email_enc'] ?>" class="small text-decoration-none"><?= $projet['email_enc'] ?></a>
                                    </div>
                                </div>

                                <div class="chat-container flex-grow-1 bg-white" id="chat">
                                    <?php if(empty($messages)): ?>
                                        <div class="text-center text-muted mt-5"><i class="far fa-comment-dots fa-2x mb-2"></i><br>Aucun message</div>
                                    <?php else: ?>
                                        <?php foreach($messages as $m): ?>
                                            <div class="msg-bubble <?= ($m['expediteur_id'] == $uid) ? 'msg-me' : 'msg-other' ?>">
                                                <?= nl2br(htmlspecialchars($m['contenu'])) ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                                <div class="p-3 border-top bg-light">
                                    <form method="POST" class="d-flex">
                                        <input type="hidden" name="pid" value="<?= $projet['id'] ?>">
                                        <input type="text" name="msg" class="form-control me-2" placeholder="Message..." required autocomplete="off">
                                        <button name="send_msg" class="btn btn-primary"><i class="fas fa-paper-plane"></i></button>
                                    </form>
                                </div>

                            <?php else: ?>
                                <div class="p-5 text-center text-muted">
                                    <i class="fas fa-user-slash fa-3x mb-3 opacity-25"></i>
                                    <p>Chat indisponible.<br>En attente d'encadrant.</p>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>

            </div>
        </div>

    <?php endif; ?>

    <script>
        // Scroll automatique vers le bas du chat
        var chat = document.getElementById("chat");
        if(chat) chat.scrollTop = chat.scrollHeight;
    </script>
</body>
</html>