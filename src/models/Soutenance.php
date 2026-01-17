<?php

namespace App\Models;

use PDO;
use PDOException;

class Soutenance
{
    public static function getDetails(PDO $pdo, int $soutenanceId): ?array
    {
        $sql = "SELECT
                    s.id AS soutenance_id,
                    s.date_soutenance,
                    s.note_finale,
                    s.mention,
                    s.pv_signe,
                    s.statut AS soutenance_statut,
                    p.titre AS projet_titre,
                    p.description AS projet_description,
                    p.mots_cles AS projet_mots_cles,
                    etud.id AS etudiant_id,
                    etud.nom AS etudiant_nom,
                    etud.prenom AS etudiant_prenom,
                    etud.email AS etudiant_email,
                    bin.id AS binome_id,
                    bin.nom AS binome_nom,
                    bin.prenom AS binome_prenom,
                    bin.email AS binome_email,
                    enc.id AS encadrant_id,
                    enc.nom AS encadrant_nom,
                    enc.prenom AS encadrant_prenom,
                    enc.email AS encadrant_email,
                    sal.id AS salle_id,
                    sal.nom AS salle_nom,
                    sal.capacite AS salle_capacite,
                    GROUP_CONCAT(
                        CASE
                            WHEN j.role_jury = 'president' THEN CONCAT(jprof.prenom, ' ', jprof.nom, ' (Président)')
                            WHEN j.role_jury = 'examinateur' THEN CONCAT(jprof.prenom, ' ', jprof.nom, ' (Examinateur)')
                            WHEN j.role_jury = 'rapporteur' THEN CONCAT(jprof.prenom, ' ', jprof.nom, ' (Rapporteur)')
                            WHEN j.role_jury = 'encadrant' THEN CONCAT(jprof.prenom, ' ', jprof.nom, ' (Encadrant)')
                            ELSE CONCAT(jprof.prenom, ' ', jprof.nom, ' (', j.role_jury, ')')
                        END
                        ORDER BY j.role_jury ASC
                        SEPARATOR '|||' -- Using a distinct separator to split later
                    ) AS jury_members
                FROM soutenances s
                JOIN projets p ON s.projet_id = p.id
                JOIN users etud ON p.etudiant_id = etud.id
                LEFT JOIN users bin ON p.binome_id = bin.id
                LEFT JOIN users enc ON p.encadrant_id = enc.id
                LEFT JOIN salles sal ON s.salle_id = sal.id
                LEFT JOIN jurys j ON j.soutenance_id = s.id
                LEFT JOIN users jprof ON j.prof_id = jprof.id
                WHERE s.id = ?
                GROUP BY s.id";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$soutenanceId]);
            $soutenance = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($soutenance && !empty($soutenance['jury_members'])) {
                $soutenance['jury_members'] = explode('|||', $soutenance['jury_members']);
            } elseif ($soutenance) {
                $soutenance['jury_members'] = [];
            }
            
            return $soutenance;

        } catch (PDOException $e) {
            // Log the error or handle it as appropriate
            error_log("Error fetching soutenance details: " . $e->getMessage());
            return null;
        }
    }
}