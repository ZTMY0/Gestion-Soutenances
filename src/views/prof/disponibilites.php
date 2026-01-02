<?php
session_start();
require_once '../../../config/database.php';

// SÉCURITÉ : Vérifier que c'est bien un prof connecté
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'prof') {
    header("Location: ../auth/login.php");
    exit();
}

$prof_id = $_SESSION['user_id'];
$message = '';
$messageType = '';

// TRAITEMENT DU FORMULAIRE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Supprimer les anciennes disponibilités du prof
    $stmt = $pdo->prepare("DELETE FROM disponibilites_profs WHERE prof_id = ?");
    $stmt->execute([$prof_id]);
    
    // Insérer les nouvelles disponibilités
    if (isset($_POST['disponibilites']) && is_array($_POST['disponibilites'])) {
        $stmt = $pdo->prepare("INSERT INTO disponibilites_profs (prof_id, jour_semaine, heure_debut, heure_fin, est_disponible) VALUES (?, ?, ?, ?, 1)");
        
        foreach ($_POST['disponibilites'] as $dispo) {
            if (!empty($dispo['jour']) && !empty($dispo['heure_debut']) && !empty($dispo['heure_fin'])) {
                $stmt->execute([
                    $prof_id,
                    $dispo['jour'],
                    $dispo['heure_debut'],
                    $dispo['heure_fin']
                ]);
            }
        }
        
        $message = "Vos disponibilités ont été enregistrées avec succès !";
        $messageType = "success";
    } else {
        $message = "Aucune disponibilité sélectionnée.";
        $messageType = "warning";
    }
}

// RÉCUPÉRER LES DISPONIBILITÉS EXISTANTES
$stmt = $pdo->prepare("SELECT * FROM disponibilites_profs WHERE prof_id = ? ORDER BY FIELD(jour_semaine, 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'), heure_debut");
$stmt->execute([$prof_id]);
$disponibilites = $stmt->fetchAll();

// Jours de la semaine
$jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];

// Créneaux horaires possibles
$creneaux = [
    '08:00' => '08:00', '08:30' => '08:30', '09:00' => '09:00', '09:30' => '09:30',
    '10:00' => '10:00', '10:30' => '10:30', '11:00' => '11:00', '11:30' => '11:30',
    '12:00' => '12:00', '12:30' => '12:30', '13:00' => '13:00', '13:30' => '13:30',
    '14:00' => '14:00', '14:30' => '14:30', '15:00' => '15:00', '15:30' => '15:30',
    '16:00' => '16:00', '16:30' => '16:30', '17:00' => '17:00', '17:30' => '17:30',
    '18:00' => '18:00'
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Disponibilités - Espace Professeur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dispo-card {
            transition: all 0.3s ease;
            border-left: 4px solid #0d6efd;
        }
        .dispo-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .btn-add-slot {
            border: 2px dashed #dee2e6;
            color: #6c757d;
            transition: all 0.3s ease;
        }
        .btn-add-slot:hover {
            border-color: #0d6efd;
            color: #0d6efd;
            background-color: #f8f9fa;
        }
        .slot-row {
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 10px;
        }
        .day-column {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 10px;
            min-height: 200px;
        }
        .day-header {
            font-weight: bold;
            text-align: center;
            padding: 10px;
            background: #0d6efd;
            color: white;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .time-slot {
            background: white;
            border-radius: 5px;
            padding: 8px;
            margin-bottom: 5px;
            font-size: 0.85rem;
            border-left: 3px solid #28a745;
        }
    </style>
</head>
<body class="bg-light">
    <!-- NAVBAR -->
    <nav class="navbar navbar-dark bg-dark px-4 shadow-sm">
        <div class="d-flex align-items-center">
            <a href="index.php" class="navbar-brand mb-0 h1">
                <i class="fas fa-chalkboard-teacher me-2"></i>Espace Professeur
            </a>
        </div>
        <div class="d-flex align-items-center">
            <span class="text-white me-3 d-none d-md-block">
                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['user_nom']); ?>
            </span>
            <a href="../auth/logout.php" class="btn btn-outline-light btn-sm">
                <i class="fas fa-sign-out-alt me-1"></i>Déconnexion
            </a>
        </div>
    </nav>

    <div class="container py-4">
        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="fas fa-calendar-alt text-primary me-2"></i>Mes Disponibilités</h2>
                <p class="text-muted mb-0">Indiquez vos créneaux disponibles pour les jurys de soutenance</p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Retour
            </a>
        </div>

        <!-- MESSAGE -->
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- FORMULAIRE -->
            <div class="col-lg-7">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-plus-circle text-success me-2"></i>Ajouter des créneaux</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="dispoForm">
                            <div id="slotsContainer">
                                <?php if (count($disponibilites) > 0): ?>
                                    <?php foreach ($disponibilites as $index => $dispo): ?>
                                        <div class="slot-row row g-2 mb-3 align-items-end">
                                            <div class="col-md-4">
                                                <label class="form-label small text-muted">Jour</label>
                                                <select name="disponibilites[<?php echo $index; ?>][jour]" class="form-select" required>
                                                    <?php foreach ($jours as $jour): ?>
                                                        <option value="<?php echo $jour; ?>" <?php echo ($dispo['jour_semaine'] === $jour) ? 'selected' : ''; ?>>
                                                            <?php echo $jour; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small text-muted">De</label>
                                                <select name="disponibilites[<?php echo $index; ?>][heure_debut]" class="form-select" required>
                                                    <?php foreach ($creneaux as $h): ?>
                                                        <option value="<?php echo $h; ?>" <?php echo ($dispo['heure_debut'] === $h.':00') ? 'selected' : ''; ?>>
                                                            <?php echo $h; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small text-muted">À</label>
                                                <select name="disponibilites[<?php echo $index; ?>][heure_fin]" class="form-select" required>
                                                    <?php foreach ($creneaux as $h): ?>
                                                        <option value="<?php echo $h; ?>" <?php echo ($dispo['heure_fin'] === $h.':00') ? 'selected' : ''; ?>>
                                                            <?php echo $h; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-outline-danger w-100" onclick="removeSlot(this)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <!-- Créneau par défaut si aucune dispo -->
                                    <div class="slot-row row g-2 mb-3 align-items-end">
                                        <div class="col-md-4">
                                            <label class="form-label small text-muted">Jour</label>
                                            <select name="disponibilites[0][jour]" class="form-select" required>
                                                <?php foreach ($jours as $jour): ?>
                                                    <option value="<?php echo $jour; ?>"><?php echo $jour; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small text-muted">De</label>
                                            <select name="disponibilites[0][heure_debut]" class="form-select" required>
                                                <?php foreach ($creneaux as $h): ?>
                                                    <option value="<?php echo $h; ?>"><?php echo $h; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small text-muted">À</label>
                                            <select name="disponibilites[0][heure_fin]" class="form-select" required>
                                                <?php foreach ($creneaux as $h): ?>
                                                    <option value="<?php echo $h; ?>" <?php echo ($h === '10:00') ? 'selected' : ''; ?>><?php echo $h; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-outline-danger w-100" onclick="removeSlot(this)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <button type="button" class="btn btn-add-slot w-100 py-3 mb-4" onclick="addSlot()">
                                <i class="fas fa-plus me-2"></i>Ajouter un autre créneau
                            </button>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>Enregistrer mes disponibilités
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- APERÇU CALENDRIER -->
            <div class="col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-eye text-info me-2"></i>Aperçu de vos disponibilités</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($disponibilites) > 0): ?>
                            <?php 
                            // Grouper par jour
                            $dispoParJour = [];
                            foreach ($disponibilites as $d) {
                                $dispoParJour[$d['jour_semaine']][] = $d;
                            }
                            ?>
                            <?php foreach ($jours as $jour): ?>
                                <?php if (isset($dispoParJour[$jour])): ?>
                                    <div class="mb-3">
                                        <h6 class="fw-bold text-primary">
                                            <i class="fas fa-calendar-day me-1"></i><?php echo $jour; ?>
                                        </h6>
                                        <?php foreach ($dispoParJour[$jour] as $slot): ?>
                                            <div class="time-slot">
                                                <i class="fas fa-clock me-1 text-success"></i>
                                                <?php echo substr($slot['heure_debut'], 0, 5); ?> - <?php echo substr($slot['heure_fin'], 0, 5); ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-calendar-times fa-3x mb-3 opacity-50"></i>
                                <p>Aucune disponibilité enregistrée</p>
                                <small>Utilisez le formulaire pour ajouter vos créneaux</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- INFO BOX -->
                <div class="card border-info mt-3">
                    <div class="card-body">
                        <h6 class="text-info"><i class="fas fa-info-circle me-1"></i>Information</h6>
                        <small class="text-muted">
                            Ces créneaux seront utilisés par le coordinateur pour planifier les soutenances. 
                            Assurez-vous d'indiquer tous vos créneaux de libre pour maximiser les possibilités de planification.
                            <br><br>
                            <strong>Durée d'une soutenance :</strong> environ 60 minutes.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let slotIndex = <?php echo max(count($disponibilites), 1); ?>;
        
        const jours = <?php echo json_encode($jours); ?>;
        const creneaux = <?php echo json_encode(array_keys($creneaux)); ?>;
        
        function addSlot() {
            const container = document.getElementById('slotsContainer');
            
            let joursOptions = jours.map(j => `<option value="${j}">${j}</option>`).join('');
            let heuresOptions = creneaux.map(h => `<option value="${h}">${h}</option>`).join('');
            
            const html = `
                <div class="slot-row row g-2 mb-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Jour</label>
                        <select name="disponibilites[${slotIndex}][jour]" class="form-select" required>
                            ${joursOptions}
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">De</label>
                        <select name="disponibilites[${slotIndex}][heure_debut]" class="form-select" required>
                            ${heuresOptions}
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">À</label>
                        <select name="disponibilites[${slotIndex}][heure_fin]" class="form-select" required>
                            ${heuresOptions}
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-danger w-100" onclick="removeSlot(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', html);
            slotIndex++;
        }
        
        function removeSlot(btn) {
            const row = btn.closest('.slot-row');
            const container = document.getElementById('slotsContainer');
            
            // Garder au moins un créneau
            if (container.querySelectorAll('.slot-row').length > 1) {
                row.remove();
            } else {
                alert('Vous devez garder au moins un créneau.');
            }
        }
    </script>
</body>
</html>
