#  GESTION SOUTENANCES - ÉTAT D'AVANCEMENT (V2)

**STATUS ACTUEL :**
 **Module Étudiant :** 100% Terminé.
 **Base de Données :** 100% Conforme (Tables officielles).
 **Module Professeur :** 0% (Démarrage immédiat).

---

##  ACTION REQUISE POUR TOUS
1. Faites un **`git pull`**.
2. **IMPÉRATIF :** Importez le fichier `soutenances_db.sql` (à la racine) dans votre phpMyAdmin.
 La structure de la BDD a changé (ajout tables `jurys`, `rapports`, `disponibilites`).
---

## 1. IHAB (Tech Lead & Admin/Support) -  FAIT
- [x] **Architecture BDD :** Mise à niveau complète (gestion des versions rapports, rôles jurys, périodes).
- [x] **Module Étudiant :**
    - Inscription complète (Titre + Mots-clés + Binôme + 3 Vœux).
    - Dashboard (Affichage dynamique de l'encadrant).
    - Dépôt Rapport (PDF <50Mo + Déclaration d'originalité).
- [x] **Rôles Support :** Création des vues pour **Directeur** (KPIs) et **Assistante** (Gestion Salles).

## 2. ABDELMOUGHIT (Professeurs & Algo) -  À FAIRE (Prioritaire)
*Tes vues sont prêtes mais vides dans `src/views/prof/`.*
- [ ] **Disponibilités (`disponibilites.php`) :** Créer le formulaire pour remplir la table `disponibilites_profs`. (Input indispensable pour ton algo).
- [ ] **Encadrement (`encadrement.php`) :** Liste des étudiants affectés + Bouton pour télécharger le PDF et **Valider le rapport** (débloque la soutenance).
- [ ] **Dashboard Prof :** Remplacer la page d'accueil temporaire par de vrais widgets.

## 3. NIZAR (Frontend & Tests) -  EN COURS
*Le module étudiant étant codé, focus sur l'UX et la communication.*
- [ ] **Tests Utilisateur :** Vérifier que le parcours étudiant (Inscription -> Dépôt) fonctionne sans bug.
- [ ] **Messagerie Interne :** La table `messages` est créée. Créer une interface simple pour que l'étudiant puisse écrire à son encadrant.
- [ ] **Design Global :** Harmoniser les boutons et les alertes (Succès/Erreur) sur toutes les pages.

## 4. NOURDDINE (Reporting & Archivage) -  EN ATTENTE
*Les données commencent à arriver, tu vas bientôt pouvoir générer les docs.*
- [ ] **PDF Convocations :** Maquetter la convocation (Logo UEMF, Date, Salle, Jury).
- [ ] **PV de Soutenance :** Préparer le modèle PDF qui sera rempli après la saisie des notes.

---

##  RAPPEL DES ACCÈS (Locaux)
* **Étudiant (Test) :** Créez un compte via `register.php`.
* **Prof (Test) :** `prof.test` / `123456` (à créer si besoin).
* **Coordinateur :** `ihab.admin` / `123456`.
* **Directeur :** `directeur.general` / `123456`.
* **Assistante :** `assistante.admin` / `123456`.