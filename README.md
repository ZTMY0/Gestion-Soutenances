# GESTION DES SOUTENANCES - RAPPORT D'ÉTAT D'AVANCEMENT Final

**PROJET :** Plateforme de Gestion des Soutenances de Fin d'Études (PFE)  
**URL OFFICIELLE :** [https://web-dev.live/Gestion-Soutenances/](https://web-dev.live/Gestion-Soutenances/)  
**HÉBERGEMENT :** Serveur distant avec nom de domaine personnalisé et certification SSL active.  
**STATUT :** **CLÔTURÉ (LOCK)** - Aucune modification de l'arborescence ou ajout de fichier n'est désormais autorisé pour garantir la stabilité avant livraison.

---

## RÉPARTITION DES RÔLES ET MISSIONS

| Membre | Module Responsable | Tâches Principales | État |
| :--- | :--- | :--- | :--- |
| **IHAB** | **Coord. & Étudiant** | Espace Coordinateur, Espace Étudiant, Architecture Globale. | TERMINÉ |
| **ABDELMOUGHIT** | **Prof & Algo** | Espace Professeur, Algorithmes (Affectation, Planning, Jury). | TERMINÉ |
| **NIZAR** | **Directeur** | Validation planning, KPIs, Signature électronique. | TERMINÉ |
| ~~**NOURDDINE**~~ | **Secrétaire GÉNÉRALE** | Logistique, PDF, Archivage. | **AUCUNE RÉALISATION** |

---

## MISES À JOUR SÉCURITÉ ET INFRASTRUCTURE (09 JANVIER 2026) - RESPONSABLE : IHAB

### **A. Déploiement et Certification**
* **Hébergement Web :** Migration réussie vers l'environnement de production sous le domaine `web-dev.live`.
* **Certification SSL :** Implémentation d'un certificat SSL/TLS garantissant le chiffrement des données (HTTPS) et la protection contre les interceptions de flux.
* **Compatibilité Multi-Environnement :** Refactorisation du code source pour assurer un fonctionnement transparent entre le serveur de production (SSL) et les environnements locaux (XAMPP/MAMPP).

### **B. Hardening**
* **Contrôle d'Accès Strict (RBAC) :** Vérification systématique des permissions par rôle lors de chaque accès aux fichiers pour prévenir les escalades de privilèges.
* **Gestion des Sessions :** Sécurisation des cookies de session (HttpOnly/Secure) et timeout d'inactivité de 30 minutes.
* **Sécurité Documentaire :** Isolation des rapports et application d'un renommage aléatoire des fichiers pour prévenir l'exposition via l'URL.
* **Intégrité des Données :** Implémentation du hashage SHA-256 pour garantir l'intégrité des notes et préparer la signature électronique.
* **Audit Trail :** Mise en place d'une traçabilité complète des actions critiques sur la plateforme.

---

## DÉTAIL DES RÉALISATIONS PAR MODULE

### 1. IHAB - MODULES COORDINATEUR ET ÉTUDIANT
* **Interface Utilisateur :** Design institutionnel basé sur la charte graphique de l'UEMF.
* **Espace Coordinateur :** Dashboard stratégique, pilotage de l'affectation IA et gestion des imports de données.
* **Espace Étudiant :** Timeline interactive de suivi et messagerie instantanée avec l'encadrant.

### 2. ABDELMOUGHIT - MODULE PROFESSEUR ET ALGORITHMES
* **Espace Professeur :** Gestion des disponibilités et validation des rapports académiques.
* **Algorithmes :** Services automatisés d'affectation des sujets et détection de conflits de planning.

### 3. NIZAR - MODULE DIRECTEUR
* **Espace Direction :** Module de validation globale des plannings et interface de contrôle des résultats.

### 4. ~~NOURDDINE~~ - MODULE SECRÉTAIRE GÉNÉRALE
* **État :** Non réalisé.
* **Note :** Les objectifs de gestion logistique, génération de PDF et archivage n'ont pas été soumis. Ce module est exclu de la version finale suite à l'absence de contributions techniques.

---

## ÉLÉMENTS EN COURS DE FINALISATION (OPTI D'INFRASTRUCTURE)
* **Obfuscation d'URL (Alias) :** Configuration des redirections serveurs pour masquer l'arborescence physique.
* **Masquage des Métadonnées :** Suppression de l'affichage des extensions de fichiers (.php) dans l'URL.

---

## NOTE DE CLÔTURE DU PROJET
> **Dépôt Figé :** Pour assurer l'intégrité du système, le repository est officiellement clos. Le travail résiduel porte exclusivement sur la stabilisation réseau et l'optimisation de la sécurité.
