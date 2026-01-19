 # TP — HTML simple et CSS (sans solutions)

Durée estimée : 3 à 5 heures  
Niveau : débutant / débutant avancé  
Prérequis : notions de base en HTML (balises, attributs) et en CSS (sélecteurs, propriétés simples)

Objectifs pédagogiques
- Savoir structurer une page HTML de façon sémantique (header, nav, main, footer, sections).
- Construire un formulaire accessible et fonctionnel (labels, fieldset, attributs required, types d'input).
- Créer et styliser un tableau d'information.
- Maîtriser lier un fichier CSS externe et organiser le style (variables CSS, règles, organisation).
- Mettre en place une mise en page responsive simple (Flexbox ou Grid), gérer breakpoints.
- Appliquer des bonnes pratiques d'accessibilité de base et du design visuel (contraste, focus, espaces).

Consignes générales (strictes)
- Travail sans framework CSS (pas de Bootstrap, Bulma, etc.).
- Fichiers obligatoires : index.html, styles.css, un dossier assets/ (images, icônes éventuelles) et README.md.
- Le HTML et le CSS doivent être séparés (pas de styles inline).
- Utiliser des balises sémantiques et associer systématiquement labels et champs de formulaire.
- Pas de solution fournie : les étudiants doivent implémenter eux-mêmes.

Contexte du TP (scénario)
Vous créez la page d'accueil d'un petit site d'inscription à des ateliers informatiques. La page doit présenter la liste des ateliers (tableau), un formulaire d'inscription, et une barre de navigation. L'apparence doit être propre, lisible et responsive.

Tâches demandées

Partie A — Structure HTML (20 points)
1. Créer une page HTML sémantique contenant :
   - un header contenant le nom du site et une navigation (3 liens fictifs),
   - une section principale (main) qui contient :
     - une section "Ateliers" avec un tableau présentant au moins 5 ateliers,
     - une section "Inscription" qui contient le formulaire,
   - un footer avec des informations de contact.
2. Le tableau doit comporter un en-tête (thead) et un corps (tbody). Colonnes suggérées : Titre, Durée, Niveau, Prix, Date.
3. Faire apparaître dans le code des classes/ids pertinents pour le ciblage CSS (par ex. .hero, .workshops, .signup-form).

Contraintes HTML
- Tous les champs du formulaire doivent avoir un attribut name.
- Utiliser fieldset et legend pour grouper des champs logiquement (ex : informations personnelles vs choix d'atelier).
- Utiliser des placeholders et l'attribut required sur les champs essentiels.
- Prévoir un input type="file" pour un justificatif optionnel (non envoyé réellement).
- Fournir des attributs aria-* ou role lorsque nécessaire (ex : role="navigation" si vous ajoutez).

Partie B — Formulaire (30 points)
1. Le formulaire d'inscription doit proposer au minimum les champs suivants :
   - Prénom (texte)
   - Nom (texte)
   - Email (type email)
   - Date de naissance (type date)
   - Genre (radio)
   - Ateliers souhaités (cases à cocher) — au moins 3 options
   - Niveau d'expérience (select) — au moins 3 options
   - Commentaire (textarea)
   - Boutons Envoyer (submit) et Réinitialiser (reset)
2. Implémenter la validation HTML5 (required, pattern ou type).
3. Prévoir des messages d'aide (attribut title ou small sous les champs) pour expliquer les formats admis.

Partie C — Mise en page & CSS (30 points)
1. Lier un fichier styles.css externe.
2. Créer une identité visuelle simple en utilisant :
   - une palette de couleurs (utiliser des variables CSS : --primary, --accent, --bg, --text),
   - une famille de polices (web-safe ou Google Fonts si Internet autorisé).
3. Layout :
   - Sur écran large : disposition à deux colonnes dans main, colonne gauche pour le tableau, colonne droite pour le formulaire (ratio environ 60/40).
   - Sur écran petit (< 700px) : disposition en une colonne (tableau au-dessus, formulaire en dessous).
   - Utiliser Flexbox ou CSS Grid pour la mise en page (un seul choix est suffisant).
4. Styliser le tableau :
   - en-têtes visibles, bordures ou séparateurs, lignes alternées (zebra striping),
   - effet hover sur les lignes,
   - gestion claire des cellules.
5. Styliser le formulaire :
   - espacement cohérent (marges/paddings), tailles des inputs homogènes,
   - états focus visibles pour les inputs,
   - boutons stylisés avec état hover et actif,
   - gérer l'affichage des erreurs HTML5 par le style (mettre en évidence required manquants).
6. Ajouter au moins une petite transition (ex : transition sur hover des boutons ou hover de lignes de tableau).

Partie D — Accessibilité et bonnes pratiques (10 points)
1. S'assurer que :
   - Tous les inputs ont des labels visibles ou aria-label s'il est caché visuellement,
   - Les contrastes de couleurs respectent une lisibilité suffisante (texte sur fond),
   - Les éléments importants sont navigables au clavier (tabindex naturel),
   - Les images (si utilisées) ont des alt text significatifs.
2. Ajouter des états visibles de focus (outline ou box-shadow) afin de faciliter la navigation au clavier.

Partie E — Bonus (facultatif, jusqu'à +10 points)
- Transformer le formulaire en une carte avec ombre portée et design soigné.
- Créer des radio-buttons / checkboxes customisées uniquement en CSS (sans JS).
- Ajouter une modal CSS-only (input checkbox hack) qui affiche les conditions d'inscription.
- Utiliser Grid pour la grille des ateliers au lieu d'un tableau (preuve d'alternative).
- Ajouter une petite animation CSS lors du survol d'un atelier.

Livrables
- Un dossier compressé (.zip) contenant :
  - index.html
  - styles.css
  - assets/ (images, icônes) — facultatif
  - README.md (1 page) : expliquer les choix de design, la palette de couleurs, les difficultés rencontrées.
- Optionnel : lien vers une démonstration en ligne (GitHub Pages) si possible.

Structure de fichiers recommandée (exemple)
- index.html
- styles.css
- assets/
  - logo.png
  - hero.jpg
- README.md

Barème de notation (exemple)
- Respect du cahier des charges et complétude : 40%
  - Toutes les parties (tableau, formulaire, nav, footer) présentes et fonctionnelles.
- Qualité du HTML (sémantique & accessibilité) : 20%
- Qualité du CSS (organisation, variables, responsive) : 20%
- Responsive & adaptation mobile : 10%
- Présentation générale et soin visuel : 10%
Bonus : jusqu'à +10 points ajoutés si les fonctionnalités bonus sont implémentées proprement.

Critères d'évaluation spécifiques (checklist pour le correcteur)
- [ ] Page sémantique (header, nav, main, footer)
- [ ] Tableau avec thead/tbody et 5 lignes au minimum
- [ ] Formulaire complet avec fieldset/legend
- [ ] Labels associés à chaque input
- [ ] Validation HTML5 utilisée (required, type=email, etc.)
- [ ] CSS externe bien lié et sans styles inline
- [ ] Variables CSS utilisées pour les couleurs
- [ ] Responsive : disposition 2 colonnes → 1 colonne sur mobile
- [ ] États : hover, focus, transitions présents
- [ ] README présent et explicatif
- [ ] Accessibilité basique respectée (alt, focus visible, contraste)

Consignes pédagogiques pour l'enseignant
- Demander une remise via zip ou dépôt GitHub privé avec branche principale.
- Autoriser les échanges entre étudiants mais exiger un travail individuel.
- Lors de la correction, vérifier l'HTML au validator.w3.org et vérifier l'absence de code inline.
- Donner un feedback écrit en se basant sur le barème et la checklist.

Suggestions de déroulé en classe (3 heures)
- 15 min : Explication du TP et consignes.
- 30–45 min : Structure HTML + création de la table.
- 45–60 min : Création du formulaire et validation HTML5.
- 45–60 min : Application du CSS, mise en page responsive.
- 15–30 min : Tests d'accessibilité et rendu final / dépôt.

Indices utiles (ne pas fournir de code)
- Pensez à grouper les éléments similaires avec des classes plutôt que d'imbriquer des styles sur des balises globales.
- Utilisez des variables CSS pour changer rapidement la palette de couleur.
- Pour le responsive, initiez-vous aux media queries et définissez un breakpoint unique pour mobile.
- Flexbox est souvent plus simple pour aligner 2 colonnes ; Grid permet plus de contrôle pour des mises en page complexes.
- Tester le formulaire dans le navigateur pour voir les messages d'erreur HTML5 natifs avant d'ajouter du style.

