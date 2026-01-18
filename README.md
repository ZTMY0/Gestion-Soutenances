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
| **IHAB** | **Architecture Globale, Sécurité, Infra, Modules Étudiant & Coordinateur.** |
| **ABDELMOUGHIT** | Espace Professeur & Algorithmes de notation. |
| **NIZAR** | Espace Directeur & Tableaux de bord (KPI). |
| **NOURDDINE** | Espace Logistique & Génération de documents. |

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

> **[PLACEHOLDER : Gestion des Profs & Algorithmes]**
> 
> 

---
>  **PROJET Reseau**
>
>  **[Accéder au Repo GitHub Réseau](INSÉRER_LE_LIEN_DU_REPO_RESEAU_ICI)**
---

### 3.3. MODULE PILOTAGE (NIZAR)
*[Intégration du module de directeur]*

> **[PLACEHOLDER : Dashboard & KPI]**
> 
>

---
>  **PROJET Reseau**
>
>  **[Accéder au Repo GitHub Réseau](INSÉRER_LE_LIEN_DU_REPO_RESEAU_ICI)**
---

### 3.4. MODULE LOGISTIQUE (NOURDDINE)
*[Intégration du module de secrétariat]*

> **[PLACEHOLDER : Gestion Salles & PDF]**
> 
>

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
