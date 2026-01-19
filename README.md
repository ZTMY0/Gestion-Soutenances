# PLATEFORME DE GESTION DES SOUTENANCES (PFE)

![Statut](https://img.shields.io/badge/STATUT-FINALIS%C3%89-success)
![Sécurité](https://img.shields.io/badge/S%C3%89CURIT%C3%89-SSL%20%2F%20RBAC-blue)
![Version](https://img.shields.io/badge/VERSION-1.0.0-orange)

> **Lien de production :** [https://web-dev.live/Gestion-Soutenances/](https://web-dev.live/Gestion-Soutenances/)

---

## 1. PRÉSENTATION DU PROJET

Ce projet consiste en la conception et le développement d'une solution web centralisée pour la digitalisation du processus de gestion des Projets de Fin d'Études (PFE) au sein de l'UEMF. La plateforme gère l'intégralité du cycle de vie académique, depuis la soumission des sujets par les étudiants jusqu'à la délibération finale des jurys.

### Stack Technique
* **Langage :** PHP 7.4+
* **Base de données :** MySQL
* **Frontend :** HTML5, CSS3, Bootstrap 5, JavaScript
* **Hébergement :** Serveur Apache (InfinityFree) avec **Nom de Domaine Personnalisé** et **Certificat SSL**.

---

## 2. ÉQUIPE DE DÉVELOPPEMENT & RÔLES

| Membre | Module Responsable |
| :--- | :--- |
| **IHAB ZAGHDANE** | **Architecture Globale, Sécurité, Infra, Modules Étudiant & Coordinateur.** |
| **ABDELMOUGHIT MOSSAID** | Espace Professeur & Algorithmes de notation. |
| **MOHAMED NIZAR ZOUIZRA** | Espace Directeur & Tableaux de bord (KPI). |
| **NOURDDINE EL KISSIRI** | Espace Logistique & Génération de documents. |

---

## 3. DÉTAIL DES CONTRIBUTIONS

### 3.1. ESPACES ÉTUDIANT, COORDINATEUR & INFRASTRUCTURE (IHAB)

Cette section détaille l'architecture socle, la sécurité et les fonctionnalités administratives implémentées dans ce dépôt.

#### A. Architecture Applicative & Modules
* **Espace Étudiant :** Développement du workflow complet (Soumission du sujet -> Validation -> Dépôt du rapport). Intégration d'une **Timeline interactive** et d'un système d'upload sécurisé (renommage aléatoire, vérification MIME).
* **Espace Coordinateur :** Création du Dashboard de pilotage et de l'interface de gestion de l'affectation (Validation des propositions). Gestion CRUD centralisée des utilisateurs.

#### B. Hardening
Conformité avec les bonnes pratiques de sécurité Web :
* **RBAC (Role-Based Access Control) :** Implémentation d'un mécanisme de contrôle strict strict vérifiant les droits d'accès avant chaque chargement de vue.
* **Sécurité des Données :** Utilisation exclusive de `PDO Prepared Statements` (Anti-Injection SQL) et hashage des mots de passe (`Bcrypt`).
* **Protection de Session :** Lutte contre le Session Hijacking et failles CSRF.

#### C. Infrastructure Web & Déploiement
* **Production :** Mise en ligne sur serveur Apache (InfinityFree) avec sécurisation `.htaccess`.
* **Chiffrement :** Configuration d'un certificat **SSL/TLS** pour une connexion HTTPS sécurisée sur le domaine `web-dev.live`.

#### D. Modélisation des Données (MLD)
>  **[Voir le Diagramme MLD complet (PDF)](UML/diagramme_mld.pdf)**

---
>  **PROJET Reseau**
>
>  **[Accéder au Repo GitHub Réseau](https://github.com/ZTMY0/Projet-Reseau-GNS3)**
---

### 3.2. ESPACE PROFESSEUR (ABDELMOUGHIT)
*[Intégration du module Prof]*

> *A. ESPACE PROFESSEUR *
* [x] *Dashboard :*
    - Affichage personnalisé des étudiants encadrés avec notifications en temps réel (nouveaux rapports, messages, convocations).
    - Intégration d'une messagerie interne pour faciliter la communication entre professeurs et étudiants.
    - Accès direct aux convocations de jurys et aux prochaines soutenances.
* [x] *Gestion des disponibilités :*
    - Interface interactive basée sur le Drag & Drop pour sélectionner et modifier les créneaux horaires disponibles.
    - Synchronisation automatique avec le module de planification pour éviter les conflits de planning.
    - Visualisation des disponibilités sous forme de grille hebdomadaire, avec alertes en cas de chevauchement.
* [x] *Validation des rapports :*
    - Module dédié permettant de consulter, annoter et valider les rapports académiques soumis par les étudiants.
    - Historique des validations et possibilité de demander des corrections via la messagerie intégrée.
    - Génération automatique de notifications pour les étudiants après validation ou demande de correction.
* [x] *Suivi des projets :*
    - Visualisation détaillée de l’avancement des projets encadrés (étapes franchies, documents déposés, soutenance planifiée).
    - Système d’alertes pour les dossiers incomplets ou en retard.
    - Accès à l’historique des interactions et des modifications apportées au projet.
* [x] *Consultation des jurys et plannings :*
    - Liste dynamique des jurys auxquels le professeur participe (président, examinateur, encadrant).
    - Planning des soutenances avec filtres par date, filière et rôle.
    - Historique des participations et accès aux procès-verbaux signés.
* [x] *Statistiques :*
    - Tableaux de bord interactifs affichant le nombre de soutenances, le taux de validation, la répartition des rôles dans les jurys.
    - Graphiques sur la charge d’encadrement, la performance des étudiants encadrés et les tendances par filière.
    - Export des statistiques au format PDF ou CSV pour analyse externe.


*B. SERVICES CORE (ALGORITHMES)*
* [x] *Affectation intelligente :*
    - Développement d’un algorithme prenant en compte les préférences des professeurs, leur charge d’encadrement, et les contraintes institutionnelles pour proposer une affectation optimale.
    - Utilisation de scores de compatibilité basés sur les mots-clés des sujets, la disponibilité et l’historique d’encadrement.
    - Interface de validation permettant au coordinateur de visualiser et ajuster les propositions générées.
* [x] *Planification des soutenances :*
    - Algorithme de détection automatique des conflits de planning (chevauchement de créneaux, indisponibilité des membres du jury).
    - Suggestion de créneaux optimisés en fonction des disponibilités collectées et des contraintes de salle.
    - Simulation du planning pour anticiper les problèmes et ajuster avant validation finale.
* [x] *Constitution des jurys :*
    - Génération automatisée de jurys respectant les règles (président ≠ encadrant, diversité des membres, équilibre des charges).
    - Prise en compte des préférences et des exclusions pour garantir l’impartialité.
    - Export des propositions de jurys et planning au format PDF pour diffusion officielle.
> 
> 

---
>  **PROJET Reseau**
>
>  **[Accéder au Repo GitHub Réseau](https://github.com/AbdelmoughitMossaid/topologie_Projet_final_euromed)**
---

### 3.3. MODULE PILOTAGE (NIZAR)
*[Intégration du module de directeur]*

### 1. Gestion des paramètres académiques
Un module de paramétrage a été développé afin de permettre au directeur de définir dynamiquement les règles académiques, notamment :
- la date limite de dépôt des rapports ;
- la durée officielle des soutenances.

Ces paramètres sont stockés dans une table dédiée (`parametres`) et peuvent être modifiés sans intervention sur le code source, garantissant ainsi flexibilité et évolutivité du système.

### 2. Validation et suivi des projets
L’espace directeur permet de gérer le cycle de validation des projets PFE à travers les actions suivantes :
- approbation des projets ;
- demande de correction avec message explicatif ;
- publication officielle des soutenances.

Chaque action entraîne une mise à jour du statut du projet ou de la soutenance, assurant une traçabilité complète des décisions académiques.

### 3. Signature électronique des procès-verbaux (PV)
Un mécanisme de signature électronique des procès-verbaux a été implémenté :
- affichage des PV liés aux soutenances ;
- signature par le directeur à l’aide d’un hash cryptographique (SHA-256) ;
- enregistrement de la signature et de la date de validation en base de données.

Ce mécanisme garantit l’intégrité, l’authenticité et la traçabilité des documents officiels.

## 4. Tableaux de Bord et Indicateurs (KPI)
Un tableau de bord décisionnel a été mis en place afin de fournir au directeur une vue synthétique de l’état global du système à travers des indicateurs clés :
- nombre total de projets ;
- nombre de projets validés ;
- taux global de validation ;
- nombre de soutenances planifiées ;
- alertes sur les projets encadrés sans rapport déposé.

Ces indicateurs facilitent la prise de décision et le suivi de l’avancement du processus de soutenance
> 
>
>  **PROJET Reseau**
>
>  **[Accéder au Repo GitHub Réseau](INSÉRER_LE_LIEN_DU_REPO_RESEAU_ICI)**
---

### 3.4. MODULE LOGISTIQUE (NOURDDINE)
*[Intégration du module de secrétariat]*

# Rapport Technique : Implémentation du Module Secrétaire Générale

## I. Gestion des Salles (CRUD)

* **Fichier cible :** `src/views/assistante/gestion_salles.php`
* **Persistance des données :** Remplacement des tableaux statiques par l'utilisation de **`$_SESSION`** pour garantir la conservation des données entre les sessions.
* **Fonctionnalités :** Implémentation complète du CRUD (Créer, Lire, Mettre à jour, Supprimer) avec une option de réinitialisation pour les tests.


## II. Génération de PDF (FPDF)

L'intégration du moteur de génération PDF a nécessité la résolution de plusieurs défis techniques liés à l'environnement :

### 1. Environnement PHP & Composer

* **Problème :** Le CLI de MAMP ne supportait pas le HTTPS, bloquant l'installation de Composer.
* **Solution :** Configuration d'un fichier `php.ini` personnalisé pour activer l'extension **OpenSSL**.

### 2. Gestion des Dépendances

* **Autoloading :** Résolution de l'erreur "Class FPDF not found". FPDF étant une bibliothèque ancienne, elle a été mappée manuellement dans le fichier `composer.json` via la section `files` :
`vendor/fpdf/fpdf/src/Fpdf/Fpdf.php`
* **Installation propre :** Suppression des répertoires `vendor` et du fichier `composer.lock` pour une réinstallation complète afin d'éliminer tout conflit de cache.

### 3. Correction des Images

* **Bug :** Erreur "Not a JPEG file" lors de l'insertion du logo.
* **Diagnostic :** L'analyse de l'en-tête binaire a révélé que le fichier était un **PNG** avec une extension `.jpeg`. Le fichier a été renommé et le code mis à jour.


## III. Archivage des Documents

* **Infrastructure :** Création du répertoire `public/archives/`.
* **Automatisation :** Modification de `generer_pdf.php` pour sauvegarder automatiquement les fichiers avec un horodatage (ex: `convocation_1705623681.pdf`).
* **Accessibilité :** Affichage d'un lien direct vers le fichier archivé immédiatement après sa génération.


---
> **PROJET Reseau**
>
>  **[Accéder au Repo GitHub Réseau](INSÉRER_LE_LIEN_DU_REPO_RESEAU_ICI)**
---

## 4. INSTALLATION LOCALE

1.  **Cloner le dépôt :**
    ```bash
    git clone https://github.com/ZTMY0/Gestion-Soutenances.git
    ```
2.  **Base de Données :**
    Importer le fichier `soutenances_db.sql` dans votre SGBD (phpMyAdmin).
3.  **Configuration :**
    Modifier `config/database.php` avec vos paramètres locaux.
4.  **Lancement :**
    Démarrer via un serveur local (XAMPP / Laragon).

---
