# GESTION SOUTENANCES - ÉTAT D'AVANCEMENT

**STATUS :** Backend terminé. Données chargées. Phase de développement Frontend & Algo.

---

##  INFOS TECHNIQUES (À lire avant de coder)
- **Login :** Format `prenom.nom` (ex: `ihab.admin`, `amine.berrada`).
- **Admin :** Réinitialisable via `http://localhost/Gestion-Soutenances/scripts/reset_admin.php`.
- **Dossiers :** Code dans `src/`. Données brutes (CSV/Python) dans `_data_generators/`.

---

## 1. IHAB (Backend & Architecture) -  TERMINÉ
- [x] Structure MVC et Nettoyage du projet (`organize.py`).
- [x] Base de données peuplée (+200 étudiants, +25 profs, Clés étrangères OK).
- [x] Auth sécurisée via Login (Coordinateur, Prof, Étudiant).
- [x] Backend "Soumission Sujet" opérationnel.

## 2. ABDELMOUGHIT (Algo & Profs) -  À FAIRE
*Objectif : Matcher les sujets validés avec les disponibilités des profs.*
- [ ] **Vue Disponibilités :** Formulaire où le prof coche ses plages horaires libres.
- [ ] **Algorithme :** Script PHP qui assigne automatiquement 2 profs (Encadrant + Rapporteur) + 1 Salle + 1 Date à un projet.
- [ ] **Conflits :** Gérer les cas impossibles (pas de salle/prof dispo).

## 3. NIZAR (Frontend & Salles) -  À FAIRE
*Objectif : Rendre le site utilisable et gérer la logistique.*
- [ ] **Design :** Appliquer Bootstrap sur `views/etudiant/index.php` et `soumettre.php`.
- [ ] **CRUD Salles :** Page Coordinateur pour Ajouter/Modifier/Supprimer des salles (Numéro, Capacité).
- [ ] **Binômes :** Permettre à l'étudiant d'ajouter un camarade lors de la soumission.

## 4. NOURDDINE (Reporting) -  À FAIRE
*Objectif : Sortir les documents officiels.*
- [ ] **PDF :** Installer DomPDF/FPDF.
- [ ] **Exports :** Générer "Planning des soutenances.pdf" et "Fiches d'évaluation.pdf".

---

## DÉMARRAGE RAPIDE
1. **Nettoyage :** Supprimez votre ancien dossier local.
2. **Copie :** Récupérez la nouvelle version fournie par Ihab.
3. **Admin :** Lancez le script `scripts/reset_admin.php` pour créer votre accès coordinateur.
4. **Test :** Connectez-vous avec `ihab.admin` / `123456`.