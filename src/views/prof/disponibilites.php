<?php
session_start();
require_once '../../../config/database.php';

// SÉCURITÉ : Vérifier auth
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'prof') {
    header("Location: ../auth/login.php");
    exit();
}

$prof_id = $_SESSION['user_id'];
$message = '';
$messageType = '';

// CONFIGURATION
$jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
$heures = [];
$start = strtotime('08:00');
$end = strtotime('18:00');
while ($start < $end) {
    $heures[] = date('H:i', $start);
    $start = strtotime('+30 minutes', $start);
}

// TRAITEMENT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("DELETE FROM disponibilites_profs WHERE prof_id = ?");
        $stmt->execute([$prof_id]);
        
        if (isset($_POST['slots']) && is_array($_POST['slots'])) {
            $stmt = $pdo->prepare("INSERT INTO disponibilites_profs (prof_id, jour_semaine, heure_debut, heure_fin, est_disponible) VALUES (?, ?, ?, ?, 1)");
            foreach ($_POST['slots'] as $slot) {
                list($jour, $heure_debut) = explode('_', $slot);
                $fin_time = strtotime($heure_debut) + (30 * 60);
                $stmt->execute([$prof_id, $jour, $heure_debut, date('H:i', $fin_time)]);
            }
            $message = "Disponibilités mises à jour avec succès !";
            $messageType = "success";
        } else {
            $message = "Disponibilités effacées.";
            $messageType = "warning";
        }
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Erreur : " . $e->getMessage();
        $messageType = "danger";
    }
}

// RÉCUPÉRATION
$stmt = $pdo->prepare("SELECT jour_semaine, heure_debut FROM disponibilites_profs WHERE prof_id = ?");
$stmt->execute([$prof_id]);
$resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);
$slots_actifs = [];
foreach ($resultats as $res) {
    $slots_actifs[] = $res['jour_semaine'] . '_' . date('H:i', strtotime($res['heure_debut'])); 
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Disponibilités - UEMF</title>
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
                    <i class="fas fa-calendar-check text-primary me-2"></i>
                    Mes Disponibilités
                </h2>
                <p class="text-muted mb-0">
                    Définissez vos créneaux horaires pour les jurys de soutenance
                </p>
                <div class="d-flex gap-2 mt-3 flex-wrap">
                    <span class="badge-modern primary">
                        <i class="fas fa-mouse-pointer me-1"></i>
                        Cliquez et glissez pour sélectionner
                    </span>
                    <span class="badge-modern secondary">
                        <i class="fas fa-info-circle me-1"></i>
                        Créneaux de 30 minutes
                    </span>
                </div>
            </div>
            <div class="col-md-4 text-end mt-3 mt-md-0">
                <a href="index.php" class="btn btn-outline-modern">
                    <i class="fas fa-arrow-left me-2"></i>
                    Retour au dashboard
                </a>
            </div>
        </div>

        <!-- MESSAGES -->
        <?php if ($message): ?>
            <div class="alert-modern <?= $messageType ?> animate-fade-in">
                <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : ($messageType === 'warning' ? 'exclamation-triangle' : 'times-circle') ?>"></i>
                <div>
                    <strong><?= $messageType === 'success' ? 'Succès !' : ($messageType === 'warning' ? 'Attention !' : 'Erreur !') ?></strong><br>
                    <span class="small"><?= $message ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- PLANNING CARD -->
        <div class="card" style="border-radius: var(--radius-xl); border: 1px solid var(--gray-200); overflow: hidden; box-shadow: var(--shadow-lg);">
            <form method="POST" id="scheduleForm">
                
                <!-- TABLE PLANNING -->
                <div class="schedule-container">
                    <table class="schedule-table">
                        <thead>
                            <tr>
                                <th style="z-index: 20; width: 70px;"></th>
                                <?php foreach ($jours as $jour): ?>
                                    <th>
                                        <i class="fas fa-calendar-day me-1 d-none d-md-inline"></i>
                                        <?= $jour ?>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($heures as $heure): ?>
                                <tr>
                                    <td class="time-col">
                                        <i class="far fa-clock me-1 d-none d-md-inline"></i>
                                        <?= $heure ?>
                                    </td>
                                    <?php foreach ($jours as $jour): ?>
                                        <?php 
                                            $key = $jour . '_' . $heure;
                                            $isSelected = in_array($key, $slots_actifs) ? 'selected' : '';
                                        ?>
                                        <td class="slot-cell <?= $isSelected ?>" 
                                            data-value="<?= $key ?>"
                                            onmousedown="startDrag(this)"
                                            onmouseover="dragOver(this)"
                                            onmouseup="endDrag()"
                                            title="<?= $jour ?> à <?= $heure ?>">
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- STATS BAR -->
                <div class="stats-bar">
                    <div class="d-flex align-items-center gap-4 flex-wrap">
                        <div class="stat-indicator">
                            <span class="text-white-50 small d-block mb-1">Sélection actuelle</span>
                            <span class="fw-bold fs-5" id="counter">0 créneaux</span>
                        </div>
                        
                        <div class="vr bg-white opacity-25 d-none d-md-block" style="height: 50px;"></div>
                        
                        <div>
                            <span class="text-white-50 small d-block mb-1">
                                <i class="fas fa-clock me-1"></i>
                                Durée totale
                            </span>
                            <span class="fw-bold text-info fs-5" id="timeCounter">0h 00</span>
                        </div>
                        
                        <div class="vr bg-white opacity-25 d-none d-md-block" style="height: 50px;"></div>
                        
                        <div>
                            <span class="text-white-50 small d-block mb-1">
                                <i class="fas fa-info-circle me-1"></i>
                                Astuce
                            </span>
                            <span class="small">Maintenez le clic et glissez pour sélectionner rapidement</span>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3 mt-md-0">
                        <button type="button" class="btn btn-outline-light btn-modern" onclick="resetGrid()">
                            <i class="fas fa-undo me-2"></i>
                            Réinitialiser
                        </button>
                        <button type="button" class="btn btn-outline-light btn-modern" onclick="selectAll()">
                            <i class="fas fa-check-double me-2"></i>
                            Tout sélectionner
                        </button>
                        <button type="submit" class="btn btn-success-modern px-4">
                            <i class="fas fa-save me-2"></i>
                            Enregistrer
                        </button>
                    </div>
                </div>

                <div id="hiddenInputsContainer"></div>
            </form>
        </div>

        <!-- AIDE -->
        <div class="row mt-4">
            <div class="col-md-6 animate-fade-in" style="animation-delay: 0.1s">
                <div class="card" style="border-radius: var(--radius-xl); border: 1px solid var(--gray-200);">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">
                            <i class="fas fa-question-circle text-info me-2"></i>
                            Comment ça marche ?
                        </h5>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Cliquez sur une cellule pour la sélectionner
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Maintenez et glissez pour sélectionner plusieurs créneaux
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Cliquez sur un créneau sélectionné pour le désélectionner
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                N'oubliez pas d'enregistrer vos modifications
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 animate-fade-in" style="animation-delay: 0.2s">
                <div class="card" style="border-radius: var(--radius-xl); border: 1px solid var(--gray-200);">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">
                            <i class="fas fa-lightbulb text-warning me-2"></i>
                            Conseils
                        </h5>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fas fa-star text-warning me-2"></i>
                                Plus vous avez de disponibilités, plus il sera facile de planifier les jurys
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-star text-warning me-2"></i>
                                Privilégiez des plages continues pour une meilleure organisation
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-star text-warning me-2"></i>
                                Pensez à mettre à jour vos disponibilités régulièrement
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-star text-warning me-2"></i>
                                Les jurys durent généralement 1 à 2 heures
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let isMouseDown = false;
        let isSelecting = true; 

        // Initialiser le compteur au chargement
        document.addEventListener('DOMContentLoaded', updateCounter);

        document.addEventListener('dragstart', function(e) { e.preventDefault(); });
        document.addEventListener('mouseup', function() { isMouseDown = false; });

        function startDrag(cell) {
            isMouseDown = true;
            isSelecting = !cell.classList.contains('selected');
            toggleCell(cell);
        }

        function dragOver(cell) {
            if (isMouseDown) {
                toggleCell(cell, true); 
            }
        }

        function endDrag() {
            isMouseDown = false;
        }

        function toggleCell(cell, forceState = false) {
            if (forceState) {
                if (isSelecting) cell.classList.add('selected');
                else cell.classList.remove('selected');
            } else {
                cell.classList.toggle('selected');
            }
            updateCounter();
        }

        function updateCounter() {
            const count = document.querySelectorAll('.slot-cell.selected').length;
            const hours = Math.floor(count / 2);
            const minutes = (count % 2) * 30;
            
            document.getElementById('counter').innerHTML = `<i class="fas fa-calendar-check me-2"></i>${count} créneaux`;
            document.getElementById('timeCounter').innerHTML = `<i class="fas fa-clock me-2"></i>${hours}h ${minutes > 0 ? minutes : '00'}`;
        }

        function resetGrid() {
            if(confirm("Voulez-vous vraiment tout effacer ?")) {
                document.querySelectorAll('.slot-cell.selected').forEach(c => c.classList.remove('selected'));
                updateCounter();
            }
        }

        function selectAll() {
            if(confirm("Sélectionner tous les créneaux ?")) {
                document.querySelectorAll('.slot-cell').forEach(c => c.classList.add('selected'));
                updateCounter();
            }
        }

        document.getElementById('scheduleForm').addEventListener('submit', function(e) {
            const container = document.getElementById('hiddenInputsContainer');
            container.innerHTML = '';
            
            const selected = document.querySelectorAll('.slot-cell.selected');
            if (selected.length === 0 && !confirm("Aucune disponibilité sélectionnée. Continuer et effacer votre planning ?")) {
                e.preventDefault();
                return;
            }

            selected.forEach(cell => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'slots[]';
                input.value = cell.getAttribute('data-value');
                container.appendChild(input);
            });
        });

        // Animation de succès sur les cellules
        document.querySelectorAll('.slot-cell').forEach(cell => {
            cell.addEventListener('click', function() {
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 100);
            });
        });
    </script>
</body>
</html>