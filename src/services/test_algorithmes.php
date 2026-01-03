<?php
/**
 * TEST RÉEL DES ALGORITHMES
 * Exécuter via MAMP: http://localhost/Gestion-Soutenances/src/services/test_algorithmes.php
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/AffectationService.php';
require_once __DIR__ . '/PlanificationService.php';
require_once __DIR__ . '/JuryService.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Test Algorithmes - Abdelmoughit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-section { background: #f8f9fa; border-radius: 10px; padding: 20px; margin-bottom: 20px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        pre { background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body class="bg-light p-4">
    <div class="container">
        <h1 class="mb-4">🧪 Test Réel des Algorithmes</h1>
        <p class="text-muted">Auteur: Abdelmoughit | Base: <?php echo DB_NAME; ?></p>
        <hr>

        <?php
        // ============================================
        // TEST 1: AFFECTATION SERVICE
        // ============================================
        ?>
        <div class="test-section">
            <h3>📚 1. AffectationService</h3>
            <?php
            try {
                $affectationService = new AffectationService($pdo);
                echo '<p class="success">✅ Service instancié avec succès</p>';
                
                // Test: Récupérer projets sans encadrant
                $projets = $affectationService->getProjetsNonAffectes();
                echo "<p><strong>Projets sans encadrant:</strong> " . count($projets) . "</p>";
                
                if (count($projets) > 0) {
                    echo "<details><summary>Voir les projets</summary><ul>";
                    foreach (array_slice($projets, 0, 5) as $p) {
                        echo "<li>{$p['titre']} - {$p['nom_etudiant']}</li>";
                    }
                    if (count($projets) > 5) echo "<li>... et " . (count($projets) - 5) . " autres</li>";
                    echo "</ul></details>";
                }
                
                // Test: Récupérer professeurs avec charge
                $profs = $affectationService->getProfesseursAvecCharge();
                echo "<p><strong>Professeurs disponibles:</strong> " . count($profs) . "</p>";
                
                if (count($profs) > 0) {
                    echo "<details><summary>Voir les professeurs</summary><ul>";
                    foreach (array_slice($profs, 0, 5) as $p) {
                        $specs = $p['specialite'] ?: 'Non défini';
                        echo "<li>{$p['nom']} - Encadrements: {$p['nb_encadrements']} - Spécialités: {$specs}</li>";
                    }
                    echo "</ul></details>";
                }
                
                // Test: Générer proposition
                if (count($projets) > 0 && count($profs) > 0) {
                    $proposition = $affectationService->genererPropositionAffectation();
                    echo "<p><strong>Proposition générée:</strong></p>";
                    echo "<ul>";
                    echo "<li>Affectés: {$proposition['stats']['affectes']}</li>";
                    echo "<li>Non affectés: {$proposition['stats']['non_affectes']}</li>";
                    echo "<li>Taux: {$proposition['stats']['taux_affectation']}%</li>";
                    echo "<li>Score moyen: {$proposition['stats']['score_moyen']}/100</li>";
                    echo "</ul>";
                    
                    if (!empty($proposition['affectations'])) {
                        echo "<details><summary>Détail des affectations proposées</summary>";
                        echo "<table class='table table-sm'><tr><th>Projet</th><th>Prof</th><th>Score</th></tr>";
                        foreach (array_slice($proposition['affectations'], 0, 10) as $a) {
                            echo "<tr><td>{$a['projet_titre']}</td><td>{$a['professeur_nom']}</td><td>{$a['score']}</td></tr>";
                        }
                        echo "</table></details>";
                    }
                } else {
                    echo "<p class='text-warning'>⚠️ Pas assez de données pour tester la génération</p>";
                }
                
            } catch (Exception $e) {
                echo '<p class="error">❌ Erreur: ' . $e->getMessage() . '</p>';
            }
            ?>
        </div>

        <?php
        // ============================================
        // TEST 2: PLANIFICATION SERVICE
        // ============================================
        ?>
        <div class="test-section">
            <h3>📅 2. PlanificationService</h3>
            <?php
            try {
                $planificationService = new PlanificationService($pdo);
                echo '<p class="success">✅ Service instancié avec succès</p>';
                
                // Test: Projets à planifier
                $projetsAPlanifier = $planificationService->getProjetsAPlanifier();
                echo "<p><strong>Projets prêts à planifier (validés):</strong> " . count($projetsAPlanifier) . "</p>";
                
                // Test: Salles
                $salles = $planificationService->getSalles();
                echo "<p><strong>Salles disponibles:</strong> " . count($salles) . "</p>";
                if (!empty($salles)) {
                    echo "<ul>";
                    foreach ($salles as $s) {
                        echo "<li>{$s['nom']} (capacité: {$s['capacite']})</li>";
                    }
                    echo "</ul>";
                }
                
                // Test: Planning existant
                $planningExistant = $planificationService->getPlanningExistant();
                echo "<p><strong>Soutenances déjà planifiées:</strong> " . count($planningExistant) . "</p>";
                
                // Test: Génération planning
                if (count($projetsAPlanifier) > 0) {
                    $dateDebut = date('Y-m-d', strtotime('+1 week'));
                    $dateFin = date('Y-m-d', strtotime('+3 weeks'));
                    
                    echo "<p>Test génération planning du $dateDebut au $dateFin...</p>";
                    $resultat = $planificationService->genererPlanningAutomatique($dateDebut, $dateFin);
                    
                    echo "<ul>";
                    echo "<li>Planifiés: {$resultat['stats']['planifies']}</li>";
                    echo "<li>Échecs: {$resultat['stats']['echecs']}</li>";
                    echo "<li>Taux: {$resultat['stats']['taux']}%</li>";
                    echo "</ul>";
                    
                    if (!empty($resultat['planning'])) {
                        echo "<details><summary>Voir le planning proposé</summary>";
                        echo "<table class='table table-sm'><tr><th>Date</th><th>Heure</th><th>Salle</th><th>Projet</th></tr>";
                        foreach (array_slice($resultat['planning'], 0, 10) as $p) {
                            echo "<tr><td>{$p['date']}</td><td>{$p['heure_debut']}</td><td>{$p['salle_nom']}</td><td>{$p['projet_titre']}</td></tr>";
                        }
                        echo "</table></details>";
                    }
                    
                    if (!empty($resultat['echecs'])) {
                        echo "<details><summary class='text-danger'>Échecs</summary><ul>";
                        foreach ($resultat['echecs'] as $e) {
                            echo "<li>{$e['projet_titre']}: {$e['raison']}</li>";
                        }
                        echo "</ul></details>";
                    }
                } else {
                    echo "<p class='text-warning'>⚠️ Aucun projet avec statut 'valide_encadrant' à planifier</p>";
                }
                
                // Test: Détection conflits
                $conflits = $planificationService->detecterConflits();
                echo "<p><strong>Conflits détectés:</strong> " . count($conflits) . "</p>";
                
            } catch (Exception $e) {
                echo '<p class="error">❌ Erreur: ' . $e->getMessage() . '</p>';
            }
            ?>
        </div>

        <?php
        // ============================================
        // TEST 3: JURY SERVICE
        // ============================================
        ?>
        <div class="test-section">
            <h3>👥 3. JuryService</h3>
            <?php
            try {
                $juryService = new JuryService($pdo);
                echo '<p class="success">✅ Service instancié avec succès</p>';
                
                // Test: Soutenances sans jury
                $soutenancesSansJury = $juryService->getSoutenancesSansJury();
                echo "<p><strong>Soutenances sans jury complet:</strong> " . count($soutenancesSansJury) . "</p>";
                
                // Test: Charges jury
                $charges = $juryService->getChargesJury();
                echo "<p><strong>Professeurs avec stats jury:</strong> " . count($charges) . "</p>";
                
                if (!empty($charges)) {
                    echo "<details><summary>Top 5 participations</summary><ul>";
                    $sorted = $charges;
                    uasort($sorted, fn($a, $b) => $b['nb_jurys'] - $a['nb_jurys']);
                    foreach (array_slice($sorted, 0, 5, true) as $id => $c) {
                        echo "<li>{$c['nom']}: {$c['nb_jurys']} jurys</li>";
                    }
                    echo "</ul></details>";
                }
                
                // Test: Génération jurys
                if (count($soutenancesSansJury) > 0) {
                    $proposition = $juryService->genererPropositionJurys();
                    echo "<p><strong>Proposition jurys:</strong></p>";
                    echo "<ul>";
                    echo "<li>Succès: {$proposition['stats']['success']}</li>";
                    echo "<li>Échecs: {$proposition['stats']['echecs']}</li>";
                    echo "<li>Taux: {$proposition['stats']['taux']}%</li>";
                    echo "</ul>";
                } else {
                    echo "<p class='text-warning'>⚠️ Aucune soutenance planifiée sans jury</p>";
                }
                
                // Stats globales
                $stats = $juryService->getStatistiquesJurys();
                if (!empty($stats['totaux'])) {
                    echo "<p><strong>Stats globales:</strong> {$stats['totaux']['total_soutenances_avec_jury']} soutenances avec jury</p>";
                }
                
            } catch (Exception $e) {
                echo '<p class="error">❌ Erreur: ' . $e->getMessage() . '</p>';
            }
            ?>
        </div>

        <div class="alert alert-info">
            <strong>💡 Note:</strong> Ce test est en lecture seule. Aucune donnée n'est modifiée.
            <br>Pour appliquer les propositions, utiliser les méthodes <code>appliquer*()</code> des services.
        </div>

    </div>
</body>
</html>
