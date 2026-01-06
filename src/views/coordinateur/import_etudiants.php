<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinateur') {
    header("Location: ../auth/login.php"); exit();
}

$message = "";
$msg_type = "";

// Récupération des filières
$stmt = $pdo->query("SELECT * FROM filieres ORDER BY nom");
$filieres = $stmt->fetchAll();

if (isset($_POST['import_csv'])) {
    if (isset($_FILES['fichier_csv']) && $_FILES['fichier_csv']['error'] == 0) {
        $target_filiere_id = $_POST['filiere_id']; 
        
        // --- LOGIQUE DE NETTOYAGE ---
        if (isset($_POST['reinitialiser']) && $_POST['reinitialiser'] == '1') {
            try {
                // 1. Supprimer PROJETS liés
                $sqlDeleteProjets = "DELETE p FROM projets p 
                                     INNER JOIN users u ON p.etudiant_id = u.id 
                                     WHERE u.role = 'etudiant' AND u.filiere_id = ?";
                $stmtDel = $pdo->prepare($sqlDeleteProjets);
                $stmtDel->execute([$target_filiere_id]);

                // 2. Supprimer ÉTUDIANTS
                $sqlDeleteUsers = "DELETE FROM users WHERE role = 'etudiant' AND filiere_id = ?";
                $stmtDelUsers = $pdo->prepare($sqlDeleteUsers);
                $stmtDelUsers->execute([$target_filiere_id]);
                
            } catch (PDOException $e) {
                $message = "Erreur nettoyage : " . $e->getMessage();
                $msg_type = "danger";
            }
        }

        // --- LECTURE CSV ---
        $csvFile = fopen($_FILES['fichier_csv']['tmp_name'], 'r');
        $bom = fread($csvFile, 3);
        if ($bom != "\xEF\xBB\xBF") rewind($csvFile);

        $count = 0;
        while (($line = fgetcsv($csvFile, 1000, ";")) !== FALSE) {
            if (count($line) < 4) {
                rewind($csvFile);
                while (($line = fgetcsv($csvFile, 1000, ",")) !== FALSE) {
                   processLine($line, $pdo, $target_filiere_id, $count);
                }
                break;
            }
            processLine($line, $pdo, $target_filiere_id, $count);
        }
        fclose($csvFile);
        
        if(empty($message)) { 
            $message = "$count étudiants importés avec succès.";
            $msg_type = "success";
        }
    }
}

function processLine($line, $pdo, $filiere_id, &$count) {
    if(count($line) < 4) return;
    $nom = trim($line[0]);
    $email = trim($line[1]);
    $login = trim($line[2]);
    $mdp = trim($line[3]);
    if (empty($nom) || empty($email)) return;

    $sql = "INSERT INTO users (nom, email, login, password, role, filiere_id) 
            VALUES (?, ?, ?, ?, 'etudiant', ?)
            ON DUPLICATE KEY UPDATE nom=VALUES(nom), login=VALUES(login), password=VALUES(password)";
    $pdo->prepare($sql)->execute([$nom, $email, $login, password_hash($mdp, PASSWORD_DEFAULT), $filiere_id]);
    $count++;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Import Étudiants | UEMF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-2">
        <div class="container">
            <a class="navbar-brand text-uppercase fw-bold" href="index.php">UEMF Pilotage</a>
            <div class="d-flex align-items-center text-white-50">
                <a href="index.php" class="btn btn-outline-light btn-sm me-3"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-hero">
        <div class="container">
            <h2 class="mb-1"><i class="fas fa-user-graduate me-2"></i>Importation des Étudiants</h2>
            <p class="mb-0 opacity-75">Mise à jour de la base de données étudiante par fichier CSV.</p>
        </div>
    </div>

    <div class="container pb-5" style="margin-top: -3rem;">
        
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                
                <?php if($message): ?>
                    <div class="alert alert-<?= $msg_type ?> shadow-sm border-0 mb-4 d-flex align-items-center">
                        <i class="fas fa-<?= ($msg_type=='success')?'check-circle':'exclamation-triangle' ?> me-2 fa-lg"></i>
                        <div><?= $message ?></div>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        
                        <form method="POST" enctype="multipart/form-data">
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold text-secondary text-uppercase small">1. Filière cible</label>
                                <select name="filiere_id" class="form-select" required>
                                    <?php foreach($filieres as $f): ?>
                                        <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-secondary text-uppercase small">2. Fichier CSV</label>
                                <input type="file" name="fichier_csv" class="form-control" accept=".csv" required>
                                <div class="form-text mt-2"><i class="fas fa-info-circle me-1"></i> Format: Nom;Email;Login;MotDePasse</div>
                            </div>

                            <div class="alert alert-warning border-0 d-flex align-items-start mb-4">
                                <input class="form-check-input mt-1 me-3" type="checkbox" name="reinitialiser" value="1" id="resetCheck">
                                <div>
                                    <label class="form-check-label fw-bold text-dark" for="resetCheck">Mode Remplacement</label>
                                    <p class="small mb-0 text-muted">Cochez pour supprimer tous les étudiants actuels de cette filière avant l'import.</p>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" name="import_csv" class="btn btn-primary py-2 fw-bold shadow-sm">
                                    <i class="fas fa-file-import me-2"></i>LANCER L'IMPORTATION
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>
</html>