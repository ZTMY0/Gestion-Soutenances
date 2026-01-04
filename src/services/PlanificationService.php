<?php
/**
 * =====================================================
 * SERVICE DE PLANIFICATION AUTOMATIQUE DES SOUTENANCES
 * =====================================================
 * Auteur: Abdelmoughit
 * Date: Janvier 2026
 * 
 * Algorithme de planification des soutenances respectant:
 *   - Disponibilités des professeurs (encadrant obligatoire)
 *   - Disponibilité des salles
 *   - Pas de chevauchement (1 prof = 1 soutenance à la fois)
 *   - Pause entre les soutenances (15 min minimum)
 *   - Équilibrage des jurys entre professeurs
 */

class PlanificationService 
{
    private PDO $pdo;
    
    // Configuration
    private int $dureeSoutenance = 60;      // minutes
    private int $pauseEntreSoutenances = 15; // minutes
    private string $heureDebutJournee = '08:00';
    private string $heureFinJournee = '18:00';
    
    public function __construct(PDO $pdo) 
    {
        $this->pdo = $pdo;
    }
    
    /**
     * Configure la durée d'une soutenance
     */
    public function setDureeSoutenance(int $minutes): void 
    {
        $this->dureeSoutenance = $minutes;
    }
    
    /**
     * Récupère les projets prêts à être planifiés (validés par encadrant)
     */
    public function getProjetsAPlanifier(): array 
    {
        $sql = "SELECT p.*, 
                       u_etu.nom as etudiant_nom,
                       u_enc.nom as encadrant_nom,
                       u_enc.id as encadrant_id,
                       f.code as filiere_code,
                       f.duree_soutenance
                FROM projets p
                JOIN users u_etu ON p.etudiant_id = u_etu.id
                LEFT JOIN users u_enc ON p.encadrant_id = u_enc.id
                JOIN filieres f ON p.filiere_id = f.id
                WHERE p.statut = 'valide_encadrant'
                AND p.id NOT IN (SELECT projet_id FROM soutenances)
                ORDER BY p.created_at ASC";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère toutes les salles disponibles
     */
    public function getSalles(): array 
    {
        $sql = "SELECT * FROM salles WHERE 1 ORDER BY nom";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère les disponibilités d'un professeur
     */
    public function getDisponibilitesProf(int $profId): array 
    {
        $sql = "SELECT * FROM disponibilites_profs 
                WHERE prof_id = ? AND est_disponible = 1
                ORDER BY FIELD(jour_semaine, 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'), heure_debut";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$profId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Vérifie si une salle est libre sur un créneau
     */
    public function salleLibre(int $salleId, string $date, string $heureDebut, string $heureFin): bool 
    {
        $sql = "SELECT COUNT(*) FROM soutenances 
                WHERE salle_id = ? 
                AND DATE(date_soutenance) = ?
                AND (
                    (TIME(date_soutenance) < ? AND ADDTIME(TIME(date_soutenance), SEC_TO_TIME(? * 60)) > ?)
                    OR
                    (TIME(date_soutenance) >= ? AND TIME(date_soutenance) < ?)
                )";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $salleId, 
            $date, 
            $heureFin, 
            $this->dureeSoutenance, 
            $heureDebut,
            $heureDebut,
            $heureFin
        ]);
        
        return $stmt->fetchColumn() == 0;
    }
    
    /**
     * Vérifie si un professeur est libre sur un créneau
     */
    public function profLibre(int $profId, string $date, string $heureDebut, string $heureFin): bool 
    {
        // Vérifier qu'il n'a pas déjà une soutenance
        $sql = "SELECT COUNT(*) FROM soutenances s
                JOIN jurys j ON s.id = j.soutenance_id
                WHERE j.prof_id = ?
                AND DATE(s.date_soutenance) = ?
                AND (
                    (TIME(s.date_soutenance) < ? AND ADDTIME(TIME(s.date_soutenance), SEC_TO_TIME(? * 60)) > ?)
                    OR
                    (TIME(s.date_soutenance) >= ? AND TIME(s.date_soutenance) < ?)
                )";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $profId, 
            $date, 
            $heureFin, 
            $this->dureeSoutenance + $this->pauseEntreSoutenances, 
            $heureDebut,
            $heureDebut,
            $heureFin
        ]);
        
        return $stmt->fetchColumn() == 0;
    }
    
    /**
     * Vérifie si le prof a déclaré être disponible sur ce créneau
     */
    public function profDisponible(int $profId, string $date, string $heureDebut, string $heureFin): bool 
    {
        $jourSemaine = $this->getJourSemaine($date);
        
        $sql = "SELECT COUNT(*) FROM disponibilites_profs
                WHERE prof_id = ?
                AND jour_semaine = ?
                AND heure_debut <= ?
                AND heure_fin >= ?
                AND est_disponible = 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$profId, $jourSemaine, $heureDebut, $heureFin]);
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Génère les créneaux possibles sur une période
     */
    public function genererCreneaux(string $dateDebut, string $dateFin): array 
    {
        $creneaux = [];
        $current = new DateTime($dateDebut);
        $fin = new DateTime($dateFin);
        
        while ($current <= $fin) {
            $jour = $current->format('N'); // 1=Lundi, 7=Dimanche
            
            // Exclure le dimanche
            if ($jour < 7) {
                $date = $current->format('Y-m-d');
                $jourSemaine = $this->getJourSemaine($date);
                
                // Générer les créneaux de la journée
                $heureActuelle = new DateTime($date . ' ' . $this->heureDebutJournee);
                $heureLimite = new DateTime($date . ' ' . $this->heureFinJournee);
                
                while ($heureActuelle < $heureLimite) {
                    $heureDebut = $heureActuelle->format('H:i:s');
                    $heureActuelle->modify('+' . $this->dureeSoutenance . ' minutes');
                    $heureFin = $heureActuelle->format('H:i:s');
                    
                    if ($heureActuelle <= $heureLimite) {
                        $creneaux[] = [
                            'date' => $date,
                            'jour_semaine' => $jourSemaine,
                            'heure_debut' => $heureDebut,
                            'heure_fin' => $heureFin
                        ];
                    }
                    
                    // Ajouter la pause
                    $heureActuelle->modify('+' . $this->pauseEntreSoutenances . ' minutes');
                }
            }
            
            $current->modify('+1 day');
        }
        
        return $creneaux;
    }
    
    /**
     * Trouve les créneaux possibles pour un projet donné
     */
    public function trouverCreneauxPossibles(array $projet, string $dateDebut, string $dateFin): array 
    {
        $creneauxPossibles = [];
        $salles = $this->getSalles();
        $creneaux = $this->genererCreneaux($dateDebut, $dateFin);
        
        $encadrantId = $projet['encadrant_id'];
        
        if (!$encadrantId) {
            return []; // Pas d'encadrant = pas de planification possible
        }
        
        foreach ($creneaux as $creneau) {
            // Vérifier que l'encadrant est disponible
            if (!$this->profDisponible($encadrantId, $creneau['date'], $creneau['heure_debut'], $creneau['heure_fin'])) {
                continue;
            }
            
            // Vérifier que l'encadrant est libre
            if (!$this->profLibre($encadrantId, $creneau['date'], $creneau['heure_debut'], $creneau['heure_fin'])) {
                continue;
            }
            
            // Trouver une salle libre
            foreach ($salles as $salle) {
                if ($this->salleLibre($salle['id'], $creneau['date'], $creneau['heure_debut'], $creneau['heure_fin'])) {
                    $creneauxPossibles[] = [
                        'date' => $creneau['date'],
                        'jour_semaine' => $creneau['jour_semaine'],
                        'heure_debut' => $creneau['heure_debut'],
                        'heure_fin' => $creneau['heure_fin'],
                        'salle_id' => $salle['id'],
                        'salle_nom' => $salle['nom']
                    ];
                    break; // Une salle suffit
                }
            }
        }
        
        return $creneauxPossibles;
    }
    
    /**
     * Génère un planning automatique pour tous les projets en attente
     */
    public function genererPlanningAutomatique(string $dateDebut, string $dateFin): array 
    {
        $projets = $this->getProjetsAPlanifier();
        $planning = [];
        $echecs = [];
        
        // Créer une copie du planning pour éviter les conflits
        $creneauxUtilises = [];
        
        foreach ($projets as $projet) {
            $creneauxPossibles = $this->trouverCreneauxPossibles($projet, $dateDebut, $dateFin);
            
            // Filtrer les créneaux déjà utilisés dans cette génération
            $creneauxPossibles = array_filter($creneauxPossibles, function($c) use ($creneauxUtilises, $projet) {
                $key = $c['date'] . '_' . $c['heure_debut'] . '_' . $c['salle_id'];
                $keyProf = $c['date'] . '_' . $c['heure_debut'] . '_prof_' . $projet['encadrant_id'];
                return !isset($creneauxUtilises[$key]) && !isset($creneauxUtilises[$keyProf]);
            });
            
            if (!empty($creneauxPossibles)) {
                // Prendre le premier créneau disponible
                $creneau = array_values($creneauxPossibles)[0];
                
                $planning[] = [
                    'projet_id' => $projet['id'],
                    'projet_titre' => $projet['titre'],
                    'etudiant_nom' => $projet['etudiant_nom'],
                    'encadrant_nom' => $projet['encadrant_nom'],
                    'encadrant_id' => $projet['encadrant_id'],
                    'filiere' => $projet['filiere_code'],
                    'date' => $creneau['date'],
                    'jour_semaine' => $creneau['jour_semaine'],
                    'heure_debut' => $creneau['heure_debut'],
                    'heure_fin' => $creneau['heure_fin'],
                    'salle_id' => $creneau['salle_id'],
                    'salle_nom' => $creneau['salle_nom']
                ];
                
                // Marquer le créneau comme utilisé
                $keySalle = $creneau['date'] . '_' . $creneau['heure_debut'] . '_' . $creneau['salle_id'];
                $keyProf = $creneau['date'] . '_' . $creneau['heure_debut'] . '_prof_' . $projet['encadrant_id'];
                $creneauxUtilises[$keySalle] = true;
                $creneauxUtilises[$keyProf] = true;
                
            } else {
                $echecs[] = [
                    'projet_id' => $projet['id'],
                    'projet_titre' => $projet['titre'],
                    'etudiant_nom' => $projet['etudiant_nom'],
                    'encadrant_nom' => $projet['encadrant_nom'] ?? 'Non assigné',
                    'raison' => !$projet['encadrant_id'] 
                        ? 'Pas d\'encadrant assigné' 
                        : 'Aucun créneau disponible (encadrant ou salle)'
                ];
            }
        }
        
        // Trier le planning par date et heure
        usort($planning, function($a, $b) {
            $cmp = strcmp($a['date'], $b['date']);
            if ($cmp === 0) {
                return strcmp($a['heure_debut'], $b['heure_debut']);
            }
            return $cmp;
        });
        
        return [
            'planning' => $planning,
            'echecs' => $echecs,
            'stats' => [
                'total_projets' => count($projets),
                'planifies' => count($planning),
                'echecs' => count($echecs),
                'taux' => count($projets) > 0 
                    ? round(count($planning) / count($projets) * 100, 1) 
                    : 0,
                'periode' => [
                    'debut' => $dateDebut,
                    'fin' => $dateFin
                ]
            ]
        ];
    }
    
    /**
     * Enregistre une soutenance dans la base de données
     */
    public function enregistrerSoutenance(array $planification): ?int 
    {
        try {
            $datetime = $planification['date'] . ' ' . $planification['heure_debut'];
            
            $sql = "INSERT INTO soutenances (projet_id, salle_id, date_soutenance) 
                    VALUES (?, ?, ?)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $planification['projet_id'],
                $planification['salle_id'],
                $datetime
            ]);
            
            $soutenanceId = $this->pdo->lastInsertId();
            
            // Mettre à jour le statut du projet
            $stmt = $this->pdo->prepare("UPDATE projets SET statut = 'planifie' WHERE id = ?");
            $stmt->execute([$planification['projet_id']]);
            
            // Ajouter l'encadrant au jury automatiquement
            if (isset($planification['encadrant_id']) && $planification['encadrant_id']) {
                $stmt = $this->pdo->prepare(
                    "INSERT INTO jurys (soutenance_id, prof_id, role_jury, present) VALUES (?, ?, 'encadrant', 1)"
                );
                $stmt->execute([$soutenanceId, $planification['encadrant_id']]);
            }
            
            return $soutenanceId;
            
        } catch (PDOException $e) {
            error_log("Erreur enregistrement soutenance: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Applique tout le planning proposé
     */
    public function appliquerPlanning(array $planning): array 
    {
        $resultats = ['succes' => 0, 'echecs' => 0, 'details' => []];
        
        $this->pdo->beginTransaction();
        
        try {
            foreach ($planning as $plan) {
                $soutenanceId = $this->enregistrerSoutenance($plan);
                
                if ($soutenanceId) {
                    $resultats['succes']++;
                    $resultats['details'][] = [
                        'projet' => $plan['projet_titre'],
                        'date' => $plan['date'],
                        'heure' => $plan['heure_debut'],
                        'salle' => $plan['salle_nom'],
                        'statut' => 'OK',
                        'soutenance_id' => $soutenanceId
                    ];
                } else {
                    $resultats['echecs']++;
                    $resultats['details'][] = [
                        'projet' => $plan['projet_titre'],
                        'statut' => 'ECHEC'
                    ];
                }
            }
            
            $this->pdo->commit();
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
        
        return $resultats;
    }
    
    /**
     * Récupère le planning existant
     */
    public function getPlanningExistant(string $dateDebut = null, string $dateFin = null): array 
    {
        $sql = "SELECT s.*, 
                       p.titre as projet_titre,
                       u_etu.nom as etudiant_nom,
                       u_enc.nom as encadrant_nom,
                       sal.nom as salle_nom,
                       f.code as filiere_code
                FROM soutenances s
                JOIN projets p ON s.projet_id = p.id
                JOIN users u_etu ON p.etudiant_id = u_etu.id
                LEFT JOIN users u_enc ON p.encadrant_id = u_enc.id
                JOIN salles sal ON s.salle_id = sal.id
                JOIN filieres f ON p.filiere_id = f.id
                WHERE 1=1";
        
        $params = [];
        
        if ($dateDebut) {
            $sql .= " AND DATE(s.date_soutenance) >= ?";
            $params[] = $dateDebut;
        }
        
        if ($dateFin) {
            $sql .= " AND DATE(s.date_soutenance) <= ?";
            $params[] = $dateFin;
        }
        
        $sql .= " ORDER BY s.date_soutenance ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Modifie la date/heure/salle d'une soutenance
     */
    public function modifierSoutenance(int $soutenanceId, string $nouvelleDate, string $nouvelleHeure, int $nouvelleSalleId): array 
    {
        try {
            // Récupérer la soutenance actuelle
            $stmt = $this->pdo->prepare("SELECT s.*, p.encadrant_id FROM soutenances s JOIN projets p ON s.projet_id = p.id WHERE s.id = ?");
            $stmt->execute([$soutenanceId]);
            $soutenance = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$soutenance) {
                return ['success' => false, 'message' => 'Soutenance introuvable'];
            }
            
            $heureFin = date('H:i:s', strtotime($nouvelleHeure) + $this->dureeSoutenance * 60);
            
            // Vérifier la disponibilité de la salle
            // (exclure la soutenance actuelle de la vérification)
            $sqlSalle = "SELECT COUNT(*) FROM soutenances 
                         WHERE salle_id = ? 
                         AND DATE(date_soutenance) = ?
                         AND id != ?
                         AND (
                             (TIME(date_soutenance) < ? AND ADDTIME(TIME(date_soutenance), SEC_TO_TIME(? * 60)) > ?)
                             OR
                             (TIME(date_soutenance) >= ? AND TIME(date_soutenance) < ?)
                         )";
            
            $stmt = $this->pdo->prepare($sqlSalle);
            $stmt->execute([
                $nouvelleSalleId, $nouvelleDate, $soutenanceId,
                $heureFin, $this->dureeSoutenance, $nouvelleHeure,
                $nouvelleHeure, $heureFin
            ]);
            
            if ($stmt->fetchColumn() > 0) {
                return ['success' => false, 'message' => 'La salle n\'est pas disponible sur ce créneau'];
            }
            
            // Vérifier les disponibilités des membres du jury
            $sqlJury = "SELECT j.prof_id, u.nom FROM jurys j JOIN users u ON j.prof_id = u.id WHERE j.soutenance_id = ?";
            $stmt = $this->pdo->prepare($sqlJury);
            $stmt->execute([$soutenanceId]);
            $membres = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $profsIndisponibles = [];
            foreach ($membres as $membre) {
                // Vérifier si le prof est libre (en excluant cette soutenance)
                $sqlProf = "SELECT COUNT(*) FROM soutenances s
                            JOIN jurys j ON s.id = j.soutenance_id
                            WHERE j.prof_id = ?
                            AND s.id != ?
                            AND DATE(s.date_soutenance) = ?
                            AND (
                                (TIME(s.date_soutenance) < ? AND ADDTIME(TIME(s.date_soutenance), SEC_TO_TIME(? * 60)) > ?)
                                OR
                                (TIME(s.date_soutenance) >= ? AND TIME(s.date_soutenance) < ?)
                            )";
                
                $stmt = $this->pdo->prepare($sqlProf);
                $stmt->execute([
                    $membre['prof_id'], $soutenanceId, $nouvelleDate,
                    $heureFin, $this->dureeSoutenance + $this->pauseEntreSoutenances, $nouvelleHeure,
                    $nouvelleHeure, $heureFin
                ]);
                
                if ($stmt->fetchColumn() > 0) {
                    $profsIndisponibles[] = $membre['nom'];
                }
            }
            
            if (!empty($profsIndisponibles)) {
                return [
                    'success' => false, 
                    'message' => 'Membres du jury indisponibles: ' . implode(', ', $profsIndisponibles)
                ];
            }
            
            // Appliquer la modification
            $datetime = $nouvelleDate . ' ' . $nouvelleHeure;
            $stmt = $this->pdo->prepare(
                "UPDATE soutenances SET date_soutenance = ?, salle_id = ? WHERE id = ?"
            );
            $stmt->execute([$datetime, $nouvelleSalleId, $soutenanceId]);
            
            return ['success' => true, 'message' => 'Soutenance modifiée avec succès'];
            
        } catch (PDOException $e) {
            error_log("Erreur modification soutenance: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur technique'];
        }
    }
    
    /**
     * Annule une soutenance
     */
    public function annulerSoutenance(int $soutenanceId): bool 
    {
        try {
            // Récupérer le projet lié
            $stmt = $this->pdo->prepare("SELECT projet_id FROM soutenances WHERE id = ?");
            $stmt->execute([$soutenanceId]);
            $projetId = $stmt->fetchColumn();
            
            // Supprimer la soutenance (pas de colonne statut)
            $stmt = $this->pdo->prepare("DELETE FROM soutenances WHERE id = ?");
            $stmt->execute([$soutenanceId]);
            
            // Remettre le projet en statut "valide_encadrant" pour replanification
            if ($projetId) {
                $stmt = $this->pdo->prepare("UPDATE projets SET statut = 'valide_encadrant' WHERE id = ?");
                $stmt->execute([$projetId]);
            }
            
            return true;
            
        } catch (PDOException $e) {
            error_log("Erreur annulation soutenance: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Détecte les conflits dans le planning
     */
    public function detecterConflits(): array 
    {
        $conflits = [];
        
        // Conflits de salles
        $sqlSalle = "SELECT s1.id as id1, s2.id as id2, 
                            p1.titre as titre1, p2.titre as titre2,
                            s1.date_soutenance, sal.nom as salle
                     FROM soutenances s1
                     JOIN soutenances s2 ON s1.salle_id = s2.salle_id 
                          AND s1.id < s2.id
                          AND DATE(s1.date_soutenance) = DATE(s2.date_soutenance)
                          AND ABS(TIMESTAMPDIFF(MINUTE, s1.date_soutenance, s2.date_soutenance)) < ?
                     JOIN projets p1 ON s1.projet_id = p1.id
                     JOIN projets p2 ON s2.projet_id = p2.id
                     JOIN salles sal ON s1.salle_id = sal.id";
        
        $stmt = $this->pdo->prepare($sqlSalle);
        $stmt->execute([$this->dureeSoutenance]);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $conflits[] = [
                'type' => 'SALLE',
                'message' => "Conflit de salle ({$row['salle']}): \"{$row['titre1']}\" et \"{$row['titre2']}\"",
                'date' => $row['date_soutenance'],
                'soutenances' => [$row['id1'], $row['id2']]
            ];
        }
        
        // Conflits de professeurs
        $sqlProf = "SELECT j1.prof_id, u.nom as prof_nom,
                           s1.id as id1, s2.id as id2,
                           p1.titre as titre1, p2.titre as titre2,
                           s1.date_soutenance
                    FROM jurys j1
                    JOIN jurys j2 ON j1.prof_id = j2.prof_id AND j1.soutenance_id < j2.soutenance_id
                    JOIN soutenances s1 ON j1.soutenance_id = s1.id
                    JOIN soutenances s2 ON j2.soutenance_id = s2.id
                    JOIN projets p1 ON s1.projet_id = p1.id
                    JOIN projets p2 ON s2.projet_id = p2.id
                    JOIN users u ON j1.prof_id = u.id
                    WHERE DATE(s1.date_soutenance) = DATE(s2.date_soutenance)
                    AND ABS(TIMESTAMPDIFF(MINUTE, s1.date_soutenance, s2.date_soutenance)) < ?";
        
        $stmt = $this->pdo->prepare($sqlProf);
        $stmt->execute([$this->dureeSoutenance + $this->pauseEntreSoutenances]);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $conflits[] = [
                'type' => 'PROFESSEUR',
                'message' => "Conflit pour {$row['prof_nom']}: \"{$row['titre1']}\" et \"{$row['titre2']}\"",
                'date' => $row['date_soutenance'],
                'prof_id' => $row['prof_id'],
                'soutenances' => [$row['id1'], $row['id2']]
            ];
        }
        
        return $conflits;
    }
    
    /**
     * Statistiques du planning
     */
    public function getStatistiquesPlanning(): array 
    {
        // Par jour
        $sqlParJour = "SELECT DATE(date_soutenance) as jour, COUNT(*) as total
                       FROM soutenances 
                       GROUP BY DATE(date_soutenance)
                       ORDER BY jour";
        
        $stmt = $this->pdo->query($sqlParJour);
        $parJour = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Par salle
        $sqlParSalle = "SELECT sal.nom, COUNT(*) as total
                        FROM soutenances s
                        JOIN salles sal ON s.salle_id = sal.id
                        GROUP BY sal.id, sal.nom";
        
        $stmt = $this->pdo->query($sqlParSalle);
        $parSalle = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Par filière
        $sqlParFiliere = "SELECT f.code, COUNT(*) as total
                          FROM soutenances s
                          JOIN projets p ON s.projet_id = p.id
                          JOIN filieres f ON p.filiere_id = f.id
                          GROUP BY f.id, f.code";
        
        $stmt = $this->pdo->query($sqlParFiliere);
        $parFiliere = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Totaux
        $sqlTotaux = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN note_finale IS NULL THEN 1 ELSE 0 END) as planifiees,
                        SUM(CASE WHEN note_finale IS NOT NULL THEN 1 ELSE 0 END) as terminees,
                        0 as annulees
                      FROM soutenances";
        
        $stmt = $this->pdo->query($sqlTotaux);
        $totaux = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'par_jour' => $parJour,
            'par_salle' => $parSalle,
            'par_filiere' => $parFiliere,
            'totaux' => $totaux,
            'conflits' => $this->detecterConflits()
        ];
    }
    
    /**
     * Convertit une date en jour de la semaine (français)
     */
    private function getJourSemaine(string $date): string 
    {
        $jours = [
            1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi',
            4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche'
        ];
        
        $timestamp = strtotime($date);
        $numJour = (int) date('N', $timestamp);
        
        return $jours[$numJour] ?? 'Lundi';
    }
}
