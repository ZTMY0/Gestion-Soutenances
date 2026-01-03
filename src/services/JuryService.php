<?php
/**
 * =====================================================
 * SERVICE DE CONSTITUTION AUTOMATIQUE DES JURYS
 * =====================================================
 * Auteur: Abdelmoughit
 * Date: Janvier 2026
 * 
 * Algorithme de constitution des jurys de soutenance
 * Règles: 
 *   - L'encadrant fait partie du jury mais ne peut pas être président
 *   - Minimum 2 membres (président + encadrant) ou 3 avec examinateur
 *   - Équilibrage des charges entre professeurs
 *   - Respect des disponibilités
 */

class JuryService 
{
    private PDO $pdo;
    
    // Configuration par défaut
    private int $tailleMinJury = 2;
    private int $tailleMaxJury = 4;
    
    public function __construct(PDO $pdo) 
    {
        $this->pdo = $pdo;
    }
    
    /**
     * Récupère les soutenances planifiées sans jury complet
     */
    public function getSoutenancesSansJury(): array 
    {
        $sql = "SELECT s.*, p.titre as projet_titre, p.encadrant_id,
                       u_enc.nom as encadrant_nom,
                       u_etu.nom as etudiant_nom,
                       sal.nom as salle_nom,
                       (SELECT COUNT(*) FROM jurys j WHERE j.soutenance_id = s.id) as nb_membres_jury
                FROM soutenances s
                JOIN projets p ON s.projet_id = p.id
                LEFT JOIN users u_enc ON p.encadrant_id = u_enc.id
                JOIN users u_etu ON p.etudiant_id = u_etu.id
                JOIN salles sal ON s.salle_id = sal.id
                WHERE s.note_finale IS NULL
                HAVING nb_membres_jury < :taille_min
                ORDER BY s.date_soutenance ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['taille_min' => $this->tailleMinJury]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère les professeurs disponibles pour un créneau donné
     */
    public function getProfesseursDisponibles(string $dateSoutenance, string $heureDebut, string $heureFin): array 
    {
        $jourSemaine = $this->getJourSemaine($dateSoutenance);
        
        // Professeurs qui ont déclaré être disponibles sur ce créneau
        $sql = "SELECT DISTINCT u.*, 
                       dp.heure_debut as dispo_debut, 
                       dp.heure_fin as dispo_fin,
                       (SELECT COUNT(*) FROM jurys j 
                        JOIN soutenances s ON j.soutenance_id = s.id 
                        WHERE j.prof_id = u.id 
                        AND DATE(s.date_soutenance) >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)) as nb_jurys_annee
                FROM users u
                JOIN disponibilites_profs dp ON u.id = dp.prof_id
                WHERE u.role = 'prof'
                AND dp.jour_semaine = :jour
                AND dp.heure_debut <= :heure_debut
                AND dp.heure_fin >= :heure_fin
                AND dp.est_disponible = 1
                AND u.id NOT IN (
                    -- Exclure les profs déjà pris sur ce créneau exact
                    SELECT j2.prof_id FROM jurys j2
                    JOIN soutenances s2 ON j2.soutenance_id = s2.id
                    WHERE DATE(s2.date_soutenance) = :date_sout
                    AND (
                        (s2.date_soutenance <= :datetime_debut AND DATE_ADD(s2.date_soutenance, INTERVAL 60 MINUTE) > :datetime_debut)
                        OR
                        (s2.date_soutenance < :datetime_fin AND DATE_ADD(s2.date_soutenance, INTERVAL 60 MINUTE) >= :datetime_fin)
                    )
                )
                ORDER BY nb_jurys_annee ASC, u.nom ASC";
        
        $datetimeDebut = $dateSoutenance . ' ' . $heureDebut;
        $datetimeFin = $dateSoutenance . ' ' . $heureFin;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'jour' => $jourSemaine,
            'heure_debut' => $heureDebut,
            'heure_fin' => $heureFin,
            'date_sout' => $dateSoutenance,
            'datetime_debut' => $datetimeDebut,
            'datetime_fin' => $datetimeFin
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère le nombre de participations aux jurys par professeur
     */
    public function getChargesJury(): array 
    {
        $sql = "SELECT u.id, u.nom, 
                       COUNT(j.id) as nb_jurys
                FROM users u
                LEFT JOIN jurys j ON u.id = j.prof_id
                LEFT JOIN soutenances s ON j.soutenance_id = s.id
                WHERE u.role = 'prof'
                AND (s.date_soutenance >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR) OR s.id IS NULL)
                GROUP BY u.id, u.nom
                ORDER BY nb_jurys ASC";
        
        $stmt = $this->pdo->query($sql);
        $result = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['id']] = [
                'nom' => $row['nom'],
                'nb_jurys' => (int) $row['nb_jurys']
            ];
        }
        
        return $result;
    }
    
    /**
     * Constitue un jury pour une soutenance donnée
     * 
     * @param array $soutenance Données de la soutenance
     * @return array|null Composition du jury ou null si impossible
     */
    public function constituerJury(array $soutenance): ?array 
    {
        $dateSout = date('Y-m-d', strtotime($soutenance['date_soutenance']));
        $heureDebut = date('H:i:s', strtotime($soutenance['date_soutenance']));
        $heureFin = date('H:i:s', strtotime($soutenance['date_soutenance']) + 3600); // +1h
        
        $encadrantId = $soutenance['encadrant_id'];
        
        // Récupérer les profs disponibles
        $profsDisponibles = $this->getProfesseursDisponibles($dateSout, $heureDebut, $heureFin);
        $chargesJury = $this->getChargesJury();
        
        // Vérifier que l'encadrant existe
        if (!$encadrantId) {
            return null; // Pas d'encadrant = pas de jury possible
        }
        
        $jury = [];
        $profsUtilises = [$encadrantId]; // L'encadrant sera ajouté
        
        // 1. TROUVER UN PRÉSIDENT (ne peut pas être l'encadrant, charge la plus faible)
        $president = $this->trouverMembreJury(
            $profsDisponibles, 
            $chargesJury, 
            $profsUtilises, 
            'president'
        );
        
        if (!$president) {
            return null; // Impossible de trouver un président
        }
        
        $jury[] = [
            'prof_id' => $president['id'],
            'prof_nom' => $president['nom'],
            'role_jury' => 'president'
        ];
        $profsUtilises[] = $president['id'];
        
        // 2. AJOUTER L'ENCADRANT
        $jury[] = [
            'prof_id' => $encadrantId,
            'prof_nom' => $soutenance['encadrant_nom'],
            'role_jury' => 'encadrant'
        ];
        
        // 3. TROUVER UN EXAMINATEUR (optionnel mais recommandé)
        $examinateur = $this->trouverMembreJury(
            $profsDisponibles, 
            $chargesJury, 
            $profsUtilises, 
            'examinateur'
        );
        
        if ($examinateur) {
            $jury[] = [
                'prof_id' => $examinateur['id'],
                'prof_nom' => $examinateur['nom'],
                'role_jury' => 'examinateur'
            ];
            $profsUtilises[] = $examinateur['id'];
        }
        
        // 4. OPTIONNEL: RAPPORTEUR (si on veut 4 membres)
        if (count($jury) < $this->tailleMaxJury) {
            $rapporteur = $this->trouverMembreJury(
                $profsDisponibles, 
                $chargesJury, 
                $profsUtilises, 
                'rapporteur'
            );
            
            if ($rapporteur) {
                $jury[] = [
                    'prof_id' => $rapporteur['id'],
                    'prof_nom' => $rapporteur['nom'],
                    'role_jury' => 'rapporteur'
                ];
            }
        }
        
        return [
            'soutenance_id' => $soutenance['id'],
            'projet_titre' => $soutenance['projet_titre'],
            'etudiant_nom' => $soutenance['etudiant_nom'],
            'date_soutenance' => $soutenance['date_soutenance'],
            'salle' => $soutenance['salle_nom'],
            'membres' => $jury,
            'complet' => count($jury) >= $this->tailleMinJury
        ];
    }
    
    /**
     * Trouve le meilleur membre disponible pour un rôle
     */
    private function trouverMembreJury(array $profsDisponibles, array $chargesJury, array $exclus, string $role): ?array 
    {
        $candidats = [];
        
        foreach ($profsDisponibles as $prof) {
            if (in_array($prof['id'], $exclus)) {
                continue;
            }
            
            $charge = $chargesJury[$prof['id']]['nb_jurys'] ?? 0;
            
            $candidats[] = [
                'id' => $prof['id'],
                'nom' => $prof['nom'],
                'charge' => $charge,
                'specialite' => $prof['specialite'] ?? ''
            ];
        }
        
        if (empty($candidats)) {
            return null;
        }
        
        // Trier par charge croissante (équilibrage)
        usort($candidats, function($a, $b) {
            return $a['charge'] - $b['charge'];
        });
        
        return $candidats[0];
    }
    
    /**
     * Génère une proposition de jurys pour toutes les soutenances sans jury
     */
    public function genererPropositionJurys(): array 
    {
        $soutenances = $this->getSoutenancesSansJury();
        
        $propositions = [];
        $echecs = [];
        
        foreach ($soutenances as $soutenance) {
            $jury = $this->constituerJury($soutenance);
            
            if ($jury && $jury['complet']) {
                $propositions[] = $jury;
            } else {
                $echecs[] = [
                    'soutenance_id' => $soutenance['id'],
                    'projet_titre' => $soutenance['projet_titre'],
                    'etudiant_nom' => $soutenance['etudiant_nom'],
                    'date_soutenance' => $soutenance['date_soutenance'],
                    'raison' => $jury ? 'Jury incomplet' : 'Aucun professeur disponible'
                ];
            }
        }
        
        return [
            'propositions' => $propositions,
            'echecs' => $echecs,
            'stats' => [
                'total' => count($soutenances),
                'success' => count($propositions),
                'echecs' => count($echecs),
                'taux' => count($soutenances) > 0 
                    ? round(count($propositions) / count($soutenances) * 100, 1) 
                    : 0
            ]
        ];
    }
    
    /**
     * Enregistre un jury dans la base de données
     */
    public function enregistrerJury(int $soutenanceId, array $membres): bool 
    {
        try {
            $this->pdo->beginTransaction();
            
            // Supprimer l'ancien jury s'il existe
            $stmt = $this->pdo->prepare("DELETE FROM jurys WHERE soutenance_id = ?");
            $stmt->execute([$soutenanceId]);
            
            // Insérer les nouveaux membres
            $sql = "INSERT INTO jurys (soutenance_id, prof_id, role_jury, present) VALUES (?, ?, ?, 1)";
            $stmt = $this->pdo->prepare($sql);
            
            foreach ($membres as $membre) {
                $stmt->execute([
                    $soutenanceId,
                    $membre['prof_id'],
                    $membre['role_jury']
                ]);
            }
            
            $this->pdo->commit();
            return true;
            
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Erreur enregistrement jury: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Applique toutes les propositions de jurys
     */
    public function appliquerTousLesJurys(array $propositions): array 
    {
        $resultats = ['succes' => 0, 'echecs' => 0, 'details' => []];
        
        foreach ($propositions as $proposition) {
            $success = $this->enregistrerJury(
                $proposition['soutenance_id'], 
                $proposition['membres']
            );
            
            if ($success) {
                $resultats['succes']++;
                $resultats['details'][] = [
                    'projet' => $proposition['projet_titre'],
                    'statut' => 'OK',
                    'nb_membres' => count($proposition['membres'])
                ];
            } else {
                $resultats['echecs']++;
                $resultats['details'][] = [
                    'projet' => $proposition['projet_titre'],
                    'statut' => 'ECHEC'
                ];
            }
        }
        
        return $resultats;
    }
    
    /**
     * Récupère le jury d'une soutenance
     */
    public function getJuryBySoutenance(int $soutenanceId): array 
    {
        $sql = "SELECT j.*, u.nom as prof_nom, u.email as prof_email
                FROM jurys j
                JOIN users u ON j.prof_id = u.id
                WHERE j.soutenance_id = ?
                ORDER BY FIELD(j.role_jury, 'president', 'encadrant', 'examinateur', 'rapporteur', 'invite')";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$soutenanceId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Modifie un membre du jury
     */
    public function modifierMembreJury(int $soutenanceId, int $ancienProfId, int $nouveauProfId, string $role): bool 
    {
        try {
            // Vérifier que le nouveau prof n'est pas déjà dans le jury
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM jurys WHERE soutenance_id = ? AND prof_id = ?"
            );
            $stmt->execute([$soutenanceId, $nouveauProfId]);
            
            if ($stmt->fetchColumn() > 0) {
                return false; // Déjà membre
            }
            
            // Modifier le membre
            $sql = "UPDATE jurys SET prof_id = ? WHERE soutenance_id = ? AND prof_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$nouveauProfId, $soutenanceId, $ancienProfId]);
            
            return $stmt->rowCount() > 0;
            
        } catch (PDOException $e) {
            error_log("Erreur modification jury: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Statistiques des participations aux jurys
     */
    public function getStatistiquesJurys(): array 
    {
        // Participation par professeur
        $sqlParProf = "SELECT u.nom, 
                              COUNT(j.id) as total_jurys,
                              SUM(CASE WHEN j.role_jury = 'president' THEN 1 ELSE 0 END) as president,
                              SUM(CASE WHEN j.role_jury = 'examinateur' THEN 1 ELSE 0 END) as examinateur,
                              SUM(CASE WHEN j.role_jury = 'rapporteur' THEN 1 ELSE 0 END) as rapporteur,
                              SUM(CASE WHEN j.role_jury = 'encadrant' THEN 1 ELSE 0 END) as encadrant
                       FROM users u
                       LEFT JOIN jurys j ON u.id = j.prof_id
                       WHERE u.role = 'prof'
                       GROUP BY u.id, u.nom
                       ORDER BY total_jurys DESC";
        
        $stmt = $this->pdo->query($sqlParProf);
        $parProf = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Totaux
        $sqlTotaux = "SELECT 
                        COUNT(DISTINCT j.soutenance_id) as total_soutenances_avec_jury,
                        COUNT(j.id) as total_participations,
                        AVG(membres_par_sout.nb) as moyenne_membres
                      FROM jurys j
                      JOIN (
                          SELECT soutenance_id, COUNT(*) as nb 
                          FROM jurys 
                          GROUP BY soutenance_id
                      ) membres_par_sout ON j.soutenance_id = membres_par_sout.soutenance_id";
        
        $stmt = $this->pdo->query($sqlTotaux);
        $totaux = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'par_professeur' => $parProf,
            'totaux' => $totaux
        ];
    }
    
    /**
     * Convertit une date en jour de la semaine
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
