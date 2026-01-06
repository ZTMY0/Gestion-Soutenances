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

if (isset($_POST['import_profs'])) {
    if (isset($_FILES['fichier_csv']) && $_FILES['fichier_csv']['error'] == 0) {
        
        // --- NETTOYAGE ---
        if (isset($_POST['reinitialiser']) && $_POST['reinitialiser'] == '1') {
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            $pdo->query("DELETE FROM users WHERE role = 'prof'");
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        }

        $file = fopen($_FILES['fichier_csv']['tmp_name'], 'r');
        $count = 0;
        
        while (($line = fgetcsv($file, 1000, ";")) !== FALSE) {
            if (count($line) < 6) { $line = str_getcsv($line[0], ","); }
            if (count($line) < 6) continue;

            $nom = trim($line[0]);
            $email = trim($line[1]);
            $login = trim($line[2]);
            $f_code = trim($line[3]);
            $specs = trim($line[4]);
            $mdp = trim($line[5]);

            $stmtF = $pdo->prepare("SELECT id FROM filieres WHERE code = ?");
            $stmtF->execute([$f_code]);
            $f_id = ($f = $stmtF->fetch()) ? $f['id'] : NULL;

            $sql = "INSERT INTO users (nom, email, login, password, role, filiere_id, specialite) 
                    VALUES (?, ?, ?, ?, 'prof', ?, ?)
                    ON DUPLICATE KEY UPDATE nom=VALUES(nom), login=VALUES(login), specialite=VALUES(specialite)";
            $pdo->prepare($sql)->execute([$nom, $email, $login, password_hash($mdp, PASSWORD_DEFAULT), $f_id, $specs]);
            $count++;
        }
        fclose($file);
        
        $message = "$count professeurs importés avec succès.";
        $msg_type = "success";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Import Professeurs | UEMF</title>
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
            <h2 class="mb-1"><i class="fas fa-chalkboard-teacher me-2"></i>Importation des Professeurs</h2>
            <p class="mb-0 opacity-75">Gestion du corps professoral et des spécialités.</p>
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
                                <label class="form-label fw-bold text-secondary text-uppercase small">Fichier CSV (Profs)</label>
                                <input type="file" name="fichier_csv" class="form-control" accept=".csv" required>
                                <div class="form-text mt-2"><i class="fas fa-info-circle me-1"></i> Format: Nom;Email;Login;CodeFiliere;Specialite;Mdp</div>
                            </div>

                            <div class="alert alert-danger bg-danger bg-opacity-10 border-0 d-flex align-items-start mb-4">
                                <input class="form-check-input mt-1 me-3" type="checkbox" name="reinitialiser" value="1" id="resetProf">
                                <div>
                                    <label class="form-check-label fw-bold text-danger" for="resetProf">Suppression Totale</label>
                                    <p class="small mb-0 text-danger opacity-75">Attention : Cela supprimera TOUS les professeurs existants avant l'import.</p>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" name="import_profs" class="btn btn-warning py-2 fw-bold shadow-sm">
                                    <i class="fas fa-file-import me-2"></i>IMPORTER MAINTENANT
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