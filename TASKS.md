# 🎓 GESTION SOUTENANCES - ÉTAT D'AVANCEMENT (V4)

**PROJET :** Plateforme de Gestion des Soutenances de Fin d'Études (PFE)  
**STATUS :** Opérationnel / En phase de finalisation  
**URL RÉFÉRENCE :** [Cahier des charges EIDIA](https://cs-eidia-projects-2025.netlify.app/projet_3_projet3)

---

## 👥 RÉPARTITION DES RÔLES ET MISSIONS

| Membre | Module Responsable | Tâches Principales |
| :--- | :--- | :--- |
| **IHAB** | **Étudiants & Coordinateur** | Inscription, Dépôts, Affectations encadrants, Supervision. |
| **ABDELMOUGHIT** | **Professeurs & Algorithmes** | Disponibilités, Validation rapports, Planning Auto, Constitution Jurys. |
| **NIZAR** | **Directeur** | Validation planning, KPIs, Signature électronique des PV. |
| **NOURDDINE** | **Secrétaire Générale** | Logistique salles, Génération PDF (Convocations/PV), Archivage. |
| **TOUTE L'ÉQUIPE** | **UI/UX & SÉCURITÉ** | Harmonisation design, RBAC, Audit de sécurité et Intégrité des notes. |

---

## 🛠️ DÉTAIL DES RÉALISATIONS PAR MODULE

### 1. IHAB (Responsable Étudiant & Coordinateur) - ✅ FAIT
* **Module Étudiant :** Formulaire d'inscription (Titre, Mots-clés), gestion des binômes et upload du rapport PDF (< 50 Mo).
* **Module Coordinateur :** Interface de matching intelligent entre projets (mots-clés) et spécialités des professeurs.
* **Architecture BDD :** Implémentation des tables `filières`, `utilisateurs` et `projets` avec gestion des statuts (Inscrit → Soutenu).

### 2. ABDELMOUGHIT (Responsable Professeurs & Algo) - ✅ FAIT
* **Module Professeur :** Saisie des disponibilités sur calendrier et interface de validation finale des rapports.
* **Algorithmes (Services PHP) :**
    * `AffectationService.php` : Répartition équitable des charges d'encadrement.
    * `PlanificationService.php` : Génération de planning sans conflits (salle/prof/étudiant).
    * `JuryService.php` : Constitution automatique respectant la règle (Président ≠ Encadrant).

### 3. NIZAR (Responsable Directeur) - 🔄 EN COURS
* **Validation Stratégique :** Interface de revue globale du planning avant publication officielle.
* **Signature Électronique :** Système de validation des Procès-verbaux (PV) avec horodatage certifié.
* **Dashboard :** Vue d'ensemble des statistiques de réussite par filière et alertes anomalies.

### 4. NOURDDINE (Responsable Secrétaire Générale & Reporting) - 🔄 EN COURS
* **Logistique :** Référentiel des salles (Capacité, équipements type vidéoprojecteur/visio).
* **Reporting PDF :** Automatisation des Convocations, Grilles d'évaluation et Attestations de réussite.
* **Archivage :** Numérisation et classement des PV signés par année universitaire.

---

## 🔐 AXES TRANSVERSAUX (TRAVAIL COLLECTIF)

### **Sécurité & Intégrité**
* **RBAC (Contrôle d'accès) :** Vérification stricte des permissions à chaque requête (un étudiant ne voit que son projet).
* **Protection des Documents :** Rapports stockés hors du répertoire web public avec noms aléatoires pour éviter les fuites.
* **Audit Trail :** Journalisation (Logs) de toutes les modifications de notes et d'affectations.

### **UI/UX & Design**
* **Harmonisation :** Utilisation de composants communs (Alertes Succès/Erreur, Boutons, Modals).
* **Workflow :** Validation du parcours utilisateur de l'étape 1 (Inscription) à l'étape 9 (Notes & PV).

---

## 🚀 RAPPEL DES ACCÈS
* **BDD :** Réimporter `soutenances_db.sql` (Tables `jurys`, `rapports`, `disponibilites` mises à jour).
* **Git :** `git pull origin main` avant toute modification sur l'UI ou la Sécurité.