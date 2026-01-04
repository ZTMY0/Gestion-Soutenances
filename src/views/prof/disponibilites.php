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
    <title>Planning Interactif</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #334155ff; /* Indigo */
            --primary-light: #e0e7ff;
            --hover-color: #f3f4f6;
            --border-color: #f0f0f0;
        }

        body {
            background-color: #f8fafc;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        /* Navbar custom styles */
        .navbar-brand { font-weight: 600; letter-spacing: 0.5px; }
        
        /* Card Modernization */
        .card-custom {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            background: white;
            overflow: hidden;
        }

        /* Table Styling */
        .schedule-container {
            max-height: 70vh; /* Ajusté pour laisser place à la navbar */
            overflow-y: auto;
            position: relative;
            scrollbar-width: thin;
        }
        
        .schedule-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            user-select: none;
        }

        /* Sticky Headers */
        .schedule-table thead th {
            position: sticky;
            top: 0;
            background: white;
            z-index: 10;
            padding: 15px;
            font-weight: 600;
            color: #334155;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            border-bottom: 2px solid var(--border-color);
            text-align: center;
        }
        
        /* Time Column */
        .time-col {
            position: sticky;
            left: 0;
            background: white;
            z-index: 5;
            width: 70px;
            color: #94a3b8;
            font-size: 0.75rem;
            font-weight: 500;
            text-align: right;
            padding-right: 15px;
            border-right: 1px solid var(--border-color);
            vertical-align: middle;
        }

        /* Cells */
        .slot-cell {
            height: 32px;
            border-bottom: 1px solid var(--border-color);
            border-right: 1px solid var(--border-color);
            cursor: pointer;
            transition: background-color 0.1s ease;
            position: relative;
        }
        
        .slot-cell:hover {
            background-color: var(--hover-color);
        }

        .slot-cell.selected {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .slot-cell.selected::after {
            content: '';
            position: absolute;
            top: 50%; left: 50%;
            width: 4px; height: 4px;
            background: rgba(255,255,255,0.4);
            border-radius: 50%;
            transform: translate(-50%, -50%);
        }

        .stats-bar {
            background: #1e293b;
            color: white;
            border-radius: 0 0 16px 16px;
        }
        
        /* Scrollbar */
        .schedule-container::-webkit-scrollbar { width: 8px; }
        .schedule-container::-webkit-scrollbar-track { background: #f1f1f1; }
        .schedule-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    </style>
</head>
<body>

    <nav class="navbar navbar-dark bg-dark px-4 shadow-sm mb-4">
        <div class="d-flex align-items-center">
            <span class="navbar-brand mb-0 h1">
                <i class="fas fa-chalkboard-teacher me-2"></i>Espace Professeur
            </span>
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
    
    <div class="container pb-5">
        
        <div class="row mb-4 align-items-center">
            <div class="col-md-8">
                <h2 class="fw-bold text-dark mb-1">
                    <i class="fas fa-calendar-check text-primary me-2"></i>Mes Disponibilités
                </h2>
                <p class="text-muted mb-0">
                    Définissez vos créneaux pour les jurys.
                    <span class="badge bg-white text-dark border ms-2 shadow-sm">
                        <i class="fas fa-mouse-pointer me-1 text-primary"></i>Cliquez et glissez pour sélectionner
                    </span>
                </p>
            </div>
            <div class="col-md-4 text-end">
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Retour
                </a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> shadow-sm border-0 rounded-3 mb-4">
                <i class="fas fa-info-circle me-2"></i><?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="card card-custom">
            <form method="POST" id="scheduleForm">
                
                <div class="schedule-container">
                    <table class="schedule-table">
                        <thead>
                            <tr>
                                <th style="z-index: 20;"></th> <?php foreach ($jours as $jour): ?>
                                    <th><?php echo $jour; ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($heures as $heure): ?>
                                <tr>
                                    <td class="time-col"><?php echo $heure; ?></td>
                                    <?php foreach ($jours as $jour): ?>
                                        <?php 
                                            $key = $jour . '_' . $heure;
                                            $isSelected = in_array($key, $slots_actifs) ? 'selected' : '';
                                        ?>
                                        <td class="slot-cell <?php echo $isSelected; ?>" 
                                            data-value="<?php echo $key; ?>"
                                            onmousedown="startDrag(this)"
                                            onmouseover="dragOver(this)"
                                            onmouseup="endDrag()">
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="stats-bar p-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <div>
                            <span class="text-white-50 small d-block">Sélection</span>
                            <span class="fw-bold" id="counter">0 créneaux</span>
                        </div>
                        <div class="vr bg-secondary mx-2"></div>
                        <div>
                            <span class="text-white-50 small d-block">Durée estimée</span>
                            <span class="fw-bold text-info" id="timeCounter">0h 00</span>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-light btn-sm" onclick="resetGrid()">
                            <i class="fas fa-undo me-1"></i>Reset
                        </button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow">
                            <i class="fas fa-save me-2"></i>Enregistrer
                        </button>
                    </div>
                </div>

                <div id="hiddenInputsContainer"></div>
            </form>
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
            
            document.getElementById('counter').innerText = count + " créneaux";
            document.getElementById('timeCounter').innerText = `${hours}h ${minutes > 0 ? minutes : '00'}`;
        }

        function resetGrid() {
            if(confirm("Tout effacer ?")) {
                document.querySelectorAll('.slot-cell.selected').forEach(c => c.classList.remove('selected'));
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
    </script>
</body>
</html>