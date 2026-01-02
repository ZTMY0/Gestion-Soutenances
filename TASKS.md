# ⚡ PROJET GESTION SOUTENANCES - TASKS

**STATUS:** BDD "Ultimate" active (Respect strict du cahier des charges).

---

##  NOTE A LIRE
**NE TOUCHEZ PAS A LA BDD (`sql/schema.sql`).**
Le schéma est maintenant complexe (Relations clés étrangères partout).
Si vous modifiez une table, vous cassez le travail des autres.
**Consultez Ihab avant toute modif SQL.**

---

## 1. IHAB (Chef de Projet & Backend Core)
- [x] **Setup:** Repo GitHub + Structure fichiers MVC.
- [x] **BDD:** Schéma complet validé (10 tables).
- [ ] **Auth:** Login sécurisé + Gestion des Sessions (Prof vs Etudiant vs Admin).
- [ ] **Dashboard Coordinateur:** Vue globale + Gestion des "Périodes de saisie".
- [ ] **Messagerie:** Système simple d'envoi de notifs (Table `messages`).

## 2. ABDELMOUGHIT (Algo & Logique Métier)
- [ ] **Dispos Profs:** Interface pour qu'un prof saisisse ses créneaux (Table `disponibilites`).
- [ ] **ALGO DE LA MORT:** Script de planification automatique.
    - *Input:* 1 Projet.
    - *Logic:* Trouver 1 Salle libre + 1 Président libre + 1 Examinateur libre.
    - *Output:* Remplir tables `soutenances` ET `jurys`.
- [ ] **Contraintes:** Vérifier que l'Encadrant n'est PAS Président.

## 3. NIZAR (Front Étudiant & Logistique)
- [ ] **Inscription:** Formulaire complet (Titre, Description, Mots-clés).
- [ ] **Binômes:** Ajouter un champ pour sélectionner son binôme (Liste déroulante des étudiants sans projet).
- [ ] **Salles:** CRUD simple (Ajout/Modif de salles avec capacité).
- [ ] **Design:** Intégration Bootstrap propre (Barre de nav, Tableaux responsives).

## 4. NOURDDINE (Reporting & PDF)
- [ ] **Engine:** Installer DomPDF ou FPDF.
- [ ] **Convocations:** PDF dynamique qui liste TOUT le jury (Président, Exam, Encadrant).
- [ ] **PV de Soutenance:** Le document officiel avec champ "Signature Numérique".
- [ ] **Export:** Bouton "Générer Planning" pour le Coordinateur.

---

##  PLANNING FLASH
- **Phase 1:** Tout le monde clone + Importe le nouveau SQL.
- **Phase 2:** Nizar finit les formulaires, Ihab finit l'Auth.
- **Phase 3:** Abdel connecte l'Algo sur les données de Nizar.
- **Phase 4:** Nourddine branche les PDF sur le résultat.