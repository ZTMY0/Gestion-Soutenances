<?php
/**
 * =====================================================
 * SERVICE D'AFFECTATION AUTOMATIQUE DES ENCADRANTS
 * =====================================================
 * Auteur: Abdelmoughit
 * Date: Janvier 2026
 * 
 * CORRECTION: Gestion des projets sans préférences
 */

class AffectationService 
{
    private PDO $pdo;
    
    public function __construct(PDO $pdo) 
    {
        $this->pdo = $pdo;
    }
    
    /**
     * Récupère tous les projets sans encadrant (statut 'inscrit')
     */
    public function getProjetsNonAffectes(): array 
    {
        $sql = "SELECT p.*, u.nom as nom_etudiant, 
               COALESCE(f.code, 'NON_DEFINIE') as filiere_code
        FROM projets p
        JOIN users u ON p.etudiant_id = u.id
        LEFT JOIN filieres f ON p.filiere_id = f.id  -- LEFT JOIN au lieu de JOIN
        WHERE p.encadrant_id IS NULL
        ORDER BY p.created_at ASC";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère tous les professeurs avec leur charge actuelle
     */
    public function getProfesseursAvecCharge(): array 
    {
        $sql = "SELECT u.*, 
                       COALESCE(u.specialite, '') as specialite,
                       (SELECT COUNT(*) FROM projets p 
                        WHERE p.encadrant_id = u.id 
                        AND p.annee_universitaire = :annee) as nb_encadrements
                FROM users u
                WHERE u.role = 'prof'
                ORDER BY nb_encadrements ASC, u.nom ASC";
        
        $annee = $this->getAnneeUniversitaire();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['annee' => $annee]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Calcule le score de compatibilité entre un projet et un professeur
     * Score basé sur: mots-clés, spécialités, préférences étudiant, charge
     * 
     * @return float Score entre 0 et 100
     */
    public function calculerScoreCompatibilite(array $projet, array $professeur): float 
    {
        $score = 0;
        $maxScore = 100;
        
        // SCORE DE BASE : 20 points (NOUVEAU)
        // Tout professeur disponible a un score minimum
        $score += 20;
        
        // 1. MATCHING MOTS-CLÉS / SPÉCIALITÉS (30 points max, réduit de 40)
        $motsClesProjets = $this->parseMotsCles($projet['mots_cles'] ?? '');
        $motsClesTechnos = $this->parseMotsCles($projet['technologies'] ?? '');
        $specialitesProf = $this->parseMotsCles($professeur['specialite'] ?? '');
        
        $motsClesProjet = array_merge($motsClesProjets, $motsClesTechnos);
        
        if (count($motsClesProjet) > 0 && count($specialitesProf) > 0) {
            $motsCommuns = $this->compterMotsCommuns($motsClesProjet, $specialitesProf);
            $tauxMatching = $motsCommuns / max(count($motsClesProjet), 1);
            $score += min(30, $tauxMatching * 50); // Boost si matching
        }
        
        // 2. PRÉFÉRENCES DE L'ÉTUDIANT (30 points max)
        if (isset($projet['encadrant_pref1_id']) && $projet['encadrant_pref1_id'] == $professeur['id']) {
            $score += 30; // 1er choix
        } elseif (isset($projet['encadrant_pref2_id']) && $projet['encadrant_pref2_id'] == $professeur['id']) {
            $score += 20; // 2ème choix
        } elseif (isset($projet['encadrant_pref3_id']) && $projet['encadrant_pref3_id'] == $professeur['id']) {
            $score += 10; // 3ème choix
        }
        
        // 3. ÉQUILIBRAGE DE CHARGE (30 points max)
        // Moins le prof a d'encadrements, plus le score est élevé
        $chargeMax = 8; // Capacité max estimée
        $chargeActuelle = (int) $professeur['nb_encadrements'];
        
        if ($chargeActuelle < $chargeMax) {
            $ratioDisponibilite = 1 - ($chargeActuelle / $chargeMax);
            $score += $ratioDisponibilite * 30;
        }
        
        // 4. MÊME FILIÈRE (Bonus 10 points)
        if (isset($projet['filiere_id']) && isset($professeur['filiere_id'])) {
            if ($projet['filiere_id'] == $professeur['filiere_id']) {
                $score += 10;
            }
        }
        
        return min($maxScore, round($score, 2));
    }
    
    /**
     * Parse une chaîne de mots-clés (séparés par virgules ou JSON)
     */
    private function parseMotsCles(?string $input): array 
    {
        if (empty($input)) return [];
        
        // Tenter de décoder en JSON
        $decoded = json_decode($input, true);
        if (is_array($decoded)) {
            return array_map('strtolower', array_map('trim', $decoded));
        }
        
        // Sinon, séparer par virgules
        $mots = explode(',', $input);
        return array_map('strtolower', array_map('trim', $mots));
    }
    
    /**
     * Compte les mots en commun (matching flou)
     */
    private function compterMotsCommuns(array $mots1, array $mots2): int 
    {
        $count = 0;
        foreach ($mots1 as $mot1) {
            foreach ($mots2 as $mot2) {
                // Matching exact ou partiel (contient)
                if ($mot1 === $mot2 || 
                    strpos($mot1, $mot2) !== false || 
                    strpos($mot2, $mot1) !== false ||
                    similar_text($mot1, $mot2) / max(strlen($mot1), strlen($mot2), 1) > 0.7) {
                    $count++;
                    break;
                }
            }
        }
        return $count;
    }
    
    /**
     * Génère une proposition d'affectation automatique pour tous les projets
     * 
     * @return array ['affectations' => [...], 'non_affectes' => [...], 'stats' => [...]]
     */
    public function genererPropositionAffectation(): array 
    {
        $projets = $this->getProjetsNonAffectes();
        $professeurs = $this->getProfesseursAvecCharge();
        
        $affectations = [];
        $nonAffectes = [];
        $chargesTemporaires = [];
        
        // Initialiser les charges temporaires
        foreach ($professeurs as $prof) {
            $chargesTemporaires[$prof['id']] = (int) $prof['nb_encadrements'];
        }
        
        // Trier les projets par priorité (date d'inscription)
        usort($projets, function($a, $b) {
            return strtotime($a['created_at']) - strtotime($b['created_at']);
        });
        
        foreach ($projets as $projet) {
            $meilleurProf = null;
            $meilleurScore = -1;
            
            foreach ($professeurs as $prof) {
                // Vérifier la capacité (max 8 encadrements)
                if ($chargesTemporaires[$prof['id']] >= 8) {
                    continue;
                }
                
                // Mettre à jour temporairement la charge pour le calcul
                $profTemp = $prof;
                $profTemp['nb_encadrements'] = $chargesTemporaires[$prof['id']];
                
                $score = $this->calculerScoreCompatibilite($projet, $profTemp);
                
                if ($score > $meilleurScore) {
                    $meilleurScore = $score;
                    $meilleurProf = $prof;
                }
            }
            
            // SEUIL RÉDUIT : Accepter tout score > 0 (MODIFIÉ)
            // Avant c'était > 10, maintenant on accepte même les faibles scores
            if ($meilleurProf !== null && $meilleurScore > 0) {
                $affectations[] = [
                    'projet_id' => $projet['id'],
                    'projet_titre' => $projet['titre'],
                    'etudiant_nom' => $projet['nom_etudiant'],
                    'professeur_id' => $meilleurProf['id'],
                    'professeur_nom' => $meilleurProf['nom'],
                    'score' => $meilleurScore,
                    'raisons' => $this->getRaisonsAffectation($projet, $meilleurProf)
                ];
                
                // Incrémenter la charge temporaire
                $chargesTemporaires[$meilleurProf['id']]++;
            } else {
                $nonAffectes[] = [
                    'projet_id' => $projet['id'],
                    'projet_titre' => $projet['titre'],
                    'etudiant_nom' => $projet['nom_etudiant'],
                    'raison' => 'Aucun professeur disponible (tous à charge maximale)'
                ];
            }
        }
        
        // Statistiques
        $stats = [
            'total_projets' => count($projets),
            'affectes' => count($affectations),
            'non_affectes' => count($nonAffectes),
            'taux_affectation' => count($projets) > 0 
                ? round(count($affectations) / count($projets) * 100, 1) 
                : 0,
            'score_moyen' => count($affectations) > 0 
                ? round(array_sum(array_column($affectations, 'score')) / count($affectations), 1) 
                : 0
        ];
        
        return [
            'affectations' => $affectations,
            'non_affectes' => $nonAffectes,
            'stats' => $stats
        ];
    }
    
    /**
     * Explique les raisons de l'affectation
     */
    private function getRaisonsAffectation(array $projet, array $professeur): array 
    {
        $raisons = [];
        
        // Préférence étudiant
        if (isset($projet['encadrant_pref1_id']) && $projet['encadrant_pref1_id'] == $professeur['id']) {
            $raisons[] = "1er choix de l'étudiant";
        } elseif (isset($projet['encadrant_pref2_id']) && $projet['encadrant_pref2_id'] == $professeur['id']) {
            $raisons[] = "2ème choix de l'étudiant";
        } elseif (isset($projet['encadrant_pref3_id']) && $projet['encadrant_pref3_id'] == $professeur['id']) {
            $raisons[] = "3ème choix de l'étudiant";
        } else {
            // NOUVEAU : Message si pas de préférences
            $raisons[] = "Affectation automatique (pas de préférence exprimée)";
        }
        
        // Matching spécialités
        $motsCles = $this->parseMotsCles($projet['mots_cles'] ?? '');
        $technos = $this->parseMotsCles($projet['technologies'] ?? '');
        $specs = $this->parseMotsCles($professeur['specialite'] ?? '');
        
        $motsCommuns = [];
        foreach (array_merge($motsCles, $technos) as $mot) {
            foreach ($specs as $spec) {
                if (strpos($mot, $spec) !== false || strpos($spec, $mot) !== false) {
                    $motsCommuns[] = $mot;
                    break;
                }
            }
        }
        
        if (!empty($motsCommuns)) {
            $raisons[] = "Compétences: " . implode(', ', array_unique($motsCommuns));
        }
        
        // Charge faible
        if ((int)$professeur['nb_encadrements'] < 3) {
            $raisons[] = "Charge actuelle faible ({$professeur['nb_encadrements']} projets)";
        }
        
        return $raisons;
    }
    
    /**
     * Applique une affectation (met à jour la BDD)
     */
    public function appliquerAffectation(int $projetId, int $professeurId): bool 
    {
        try {
            $sql = "UPDATE projets 
                    SET encadrant_id = ?, 
                        statut = 'encadrant_affecte' 
                    WHERE id = ? AND encadrant_id IS NULL";
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([$professeurId, $projetId]);
            
            return $result && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erreur affectation: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Applique toutes les affectations proposées
     */
    public function appliquerToutesAffectations(array $affectations): array 
    {
        $resultats = ['succes' => 0, 'echecs' => 0, 'details' => []];
        
        $this->pdo->beginTransaction();
        
        try {
            foreach ($affectations as $aff) {
                if ($this->appliquerAffectation($aff['projet_id'], $aff['professeur_id'])) {
                    $resultats['succes']++;
                    $resultats['details'][] = [
                        'projet' => $aff['projet_titre'],
                        'professeur' => $aff['professeur_nom'],
                        'statut' => 'OK'
                    ];
                } else {
                    $resultats['echecs']++;
                    $resultats['details'][] = [
                        'projet' => $aff['projet_titre'],
                        'professeur' => $aff['professeur_nom'],
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
     * Retourne l'année universitaire courante
     */
    private function getAnneeUniversitaire(): string 
    {
        $mois = (int) date('m');
        $annee = (int) date('Y');
        
        if ($mois >= 9) {
            return $annee . '-' . ($annee + 1);
        } else {
            return ($annee - 1) . '-' . $annee;
        }
    }
    
    /**
     * Affectation manuelle d'un projet à un professeur
     */
    public function affecterManuellement(int $projetId, int $professeurId): array 
    {
        // Vérifier que le projet existe et n'a pas d'encadrant
        $stmt = $this->pdo->prepare("SELECT * FROM projets WHERE id = ?");
        $stmt->execute([$projetId]);
        $projet = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$projet) {
            return ['success' => false, 'message' => 'Projet introuvable'];
        }
        
        if ($projet['encadrant_id']) {
            return ['success' => false, 'message' => 'Ce projet a déjà un encadrant'];
        }
        
        // Vérifier que le professeur existe
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'prof'");
        $stmt->execute([$professeurId]);
        $prof = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$prof) {
            return ['success' => false, 'message' => 'Professeur introuvable'];
        }
        
        // Appliquer l'affectation
        if ($this->appliquerAffectation($projetId, $professeurId)) {
            return [
                'success' => true, 
                'message' => "Projet \"{$projet['titre']}\" affecté à {$prof['nom']}"
            ];
        }
        
        return ['success' => false, 'message' => 'Erreur lors de l\'affectation'];
    }
}