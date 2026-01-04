#  GESTION SOUTENANCES - ÉTAT D'AVANCEMENT (V4)

**PROJET :** Plateforme de Gestion des Soutenances de Fin d'Études (PFE)  
**STATUS :** Opérationnel / En phase de finalisation  
**URL RÉFÉRENCE :** [Cahier des charges EIDIA](https://cs-eidia-projects-2025.netlify.app/projet_3_projet3)

---

##  RÉPARTITION DES RÔLES ET MISSIONS

| Membre | Module Responsable | Tâches Principales |
| :--- | :--- | :--- |
| **IHAB** | **Étudiants & Coordinateur** | Inscription, Dépôts, Affectations encadrants, Supervision. |
| **ABDELMOUGHIT** | **Professeurs & Algorithmes** | Disponibilités, Validation rapports, Planning Auto, Constitution Jurys. |
| **NIZAR** | **Directeur** | Validation planning, KPIs, Signature électronique des PV. |
| **NOURDDINE** | **Secrétaire Générale** | Logistique salles, Génération PDF (Convocations/PV), Archivage. |
| **TOUTE L'ÉQUIPE** | **UI/UX & SÉCURITÉ** | Harmonisation design, RBAC, Audit de sécurité et Intégrité des notes. |

---

##  DÉTAIL DES RÉALISATIONS PAR MODULE

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

#  MODULE DIRECTEUR - NIZAR (TASKS)

**Objectif :** Supervision stratégique, validation du planning et signature officielle des PV.

---

## 1.  DASHBOARD EXÉCUTIF (STATISTIQUES)
*Fichier cible : `src/views/directeur/index.php`*
- [ ] **Visualisation de données :** Intégrer Chart.js pour afficher la répartition des PFE par filière.
- [ ] **KPIs de progression :** Compteur dynamique des rapports déposés vs rapports attendus.
- [ ] **Système d'Alertes :** - Liste des projets sans encadrant (Urgent).
    - Liste des étudiants n'ayant pas déposé leur rapport à J-7.
- [ ] **Comparaisons :** Graphique affichant les moyennes des notes des 3 dernières années.

## 2.  VALIDATION STRATÉGIQUE (PLANNING)
*Fichier cible : `src/views/directeur/validation.php`*
- [ ] **Vue globale :** Consulter le calendrier complet généré par l'algorithme d'Abdelmoughit.
- [ ] **Workflow d'approbation :**
    - Bouton "Approuver tout le planning" (Statut `planifié` -> `confirmé`).
    - Option "Demander correction" envoyant un message au Coordinateur (Ihab).
- [ ] **Revue des Jurys :** Vérifier visuellement qu'il n'y a pas de surcharge sur un professeur spécifique.

## 3.  SIGNATURE ÉLECTRONIQUE DES PV
*Fichier cible : `src/views/directeur/signatures.php`*
- [ ] **File d'attente :** Lister tous les PV générés par Nourddine après les soutenances.
- [ ] **Validation sécurisée :** - Bouton "Signer numériquement" (Simuler le hachage SHA-256 du document).
    - Passage du statut `pv_genere` à `pv_signe`.
- [ ] **Archivage :** Déclenchement du déplacement du PDF final vers le dossier d'archivage sécurisé.

## 4.  GESTION DES COMPTES & PARAMÈTRES
*Fichier cible : `src/views/directeur/parametres.php`*
- [ ] **Contrôle des accès :** Interface pour activer/désactiver les comptes des Coordinateurs de filières.
- [ ] **Règles métier :**
    - Configurer la durée standard d'une soutenance (ex: 60 min).
    - Définir les dates limites de soumission pour l'ensemble de l'université.

---

##  CONTRAINTES TECHNIQUES (UI/UX & SÉCURITÉ)
- [ ] **Héritage CSS :** Utiliser exclusivement `<link rel="stylesheet" href="../../../public/assets/css/style.css">`.
- [ ] **Sécurité (RBAC) :** Vérifier en haut de chaque fichier : 
  ```php
  if($_SESSION['user_role'] !== 'directeur') { header('Location: ../auth/login.php'); exit(); }

### 4. NOURDDINE (Responsable Secrétaire Générale & Reporting) - 🔄 EN COURS
* **Logistique :** Référentiel des salles (Capacité, équipements type vidéoprojecteur/visio).
* **Reporting PDF :** Automatisation des Convocations, Grilles d'évaluation et Attestations de réussite.
* **Archivage :** Numérisation et classement des PV signés par année universitaire.

---

##  AXES TRANSVERSAUX (TRAVAIL COLLECTIF)

### **Sécurité & Intégrité**
* **RBAC (Contrôle d'accès) :** Vérification stricte des permissions à chaque requête (un étudiant ne voit que son projet).
* **Protection des Documents :** Rapports stockés hors du répertoire web public avec noms aléatoires pour éviter les fuites.
* **Audit Trail :** Journalisation (Logs) de toutes les modifications de notes et d'affectations.

### **UI/UX & Design**
* **Harmonisation :** Utilisation de composants communs (Alertes Succès/Erreur, Boutons, Modals).
* **Workflow :** Validation du parcours utilisateur de l'étape 1 (Inscription) à l'étape 9 (Notes & PV).

---

##  RAPPEL DES ACCÈS
* **BDD :** Réimporter `soutenances_db.sql` (Tables `jurys`, `rapports`, `disponibilites` mises à jour).
* **Git :** `git pull origin main` avant toute modification sur l'UI ou la Sécurité.
