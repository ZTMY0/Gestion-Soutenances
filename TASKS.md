# PROJET SOUTENANCES - TASKS

**URGENCE:** 1 Semaine + Exams.
**MODE:** MVP. Si ca marche, c'est valide.

---

## ?? REGLE UNIQUE
**NE TOUCHEZ PAS A LA BDD (sql/schema.sql).**
C'est la fondation. Si vous changez une colonne sans prevenir, tout casse.

---

## 1. IHAB (Archi & Admin)
- [ ] **Setup:** Repo GitHub + Structure fichiers.
- [ ] **BDD:** Structure SQL (users, projets, soutenances).
- [ ] **Auth:** Login + Deconnexion + Securite par Role.
- [ ] **Coordinateur:** Vue liste projets + Validation planning.
- [ ] **Directeur:** Stats simples + Bouton Signature PV.

## 2. ABDELMOUGHIT (Algo & Profs)
- [ ] **Dispos Prof:** Interface pour cocher les creneaux.
- [ ] **Logique:** Fonction checkConflit(prof, date).
- [ ] **ALGO:** Script qui prend un projet et trouve le 1er creneau libre.
- [ ] **Controles:** Empecher Encadrant = President.

## 3. NIZAR (Front & Input)
- [ ] **Etudiant:** Formulaire Inscription + Upload PDF.
- [ ] **Salles:** Ajouter / Modifier / Supprimer (CRUD).
- [ ] **Integration:** Faire les vues HTML propres pour l'equipe.
- [ ] **Design:** Utiliser Bootstrap, ne pas perdre de temps sur le CSS.

## 4. NOURDDINE (PDF)
- [ ] **Script:** Installer lib PDF (DomPDF/FPDF).
- [ ] **Templates:** Convocation, PV Soutenance, Fiche Notation.
- [ ] **Export:** Bouton "Telecharger" connecte a la BDD.

---

## ?? PLANNING
-  Setup + Vues HTML (Nizar) + BDD (Ihab).
-  Connexion BDD + Inscriptions + Dispos.
- L'Algo (Abdel).
- PDFs (Nourddine) + Tests.
