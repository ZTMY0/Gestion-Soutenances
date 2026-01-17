-- GESTION SOUTENANCES - SCHEMA COMPLET (ULTIMATE VERSION)
-- Basé sur le Cahier des Charges Détaillé
SET FOREIGN_KEY_CHECKS = 0; -- Désactiver vérif pour pouvoir tout recréer

-- 1. TABLE FILIERES (Le point de départ)
DROP TABLE IF EXISTS filieres;
CREATE TABLE filieres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL, -- Ex: "G-INFO", "G-INDUS"
    nom VARCHAR(150) NOT NULL,
    description TEXT,
    coordinateur_id INT, -- Sera lié plus tard
    duree_soutenance INT DEFAULT 60, -- En minutes
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. TABLE UTILISATEURS (Tous les acteurs : Directeur, Assistante, etc.)
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prenom VARCHAR(100) NULL,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    login VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('etudiant', 'prof', 'coordinateur', 'directeur', 'assistante') NOT NULL,
    filiere_id INT NULL, -- L'étudiant appartient à une filière
    specialite VARCHAR(255) NULL, -- Pour les profs (JSON possible ou texte)
    telephone VARCHAR(20) NULL,
    otp_secret VARCHAR(255) NULL, -- Pour le 2FA (OTP Secret)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (filiere_id) REFERENCES filieres(id) ON DELETE SET NULL
);

-- (Mise à jour de la clé étrangère coordinateur dans filieres)
ALTER TABLE filieres ADD CONSTRAINT fk_filieres_coord FOREIGN KEY (coordinateur_id) REFERENCES users(id) ON DELETE SET NULL;

-- 3. TABLE PROJETS (Avec Binôme et Mots-clés)
DROP TABLE IF EXISTS projets;
CREATE TABLE projets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    description TEXT,
    mots_cles TEXT, -- Stocké en JSON ex: ["IA", "Web"]
    etudiant_id INT NOT NULL,
    binome_id INT NULL, -- Gestion du binôme incluse
    encadrant_id INT NULL,
    encadrant_pref1_id INT NULL, -- New column for first encadrant preference
    encadrant_pref2_id INT NULL, -- New column for second encadrant preference
    encadrant_pref3_id INT NULL, -- New column for third encadrant preference
    filiere_id INT NOT NULL,
    annee_universitaire VARCHAR(9) NOT NULL, -- Ex: "2025-2026"
    statut ENUM('inscrit', 'encadrant_affecte', 'valide_encadrant', 'rapport_soumis', 'planifie', 'soutenu') DEFAULT 'inscrit',
    rapport_chemin VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (etudiant_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (binome_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (encadrant_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (filiere_id) REFERENCES filieres(id)
);

-- 4. TABLE RAPPORTS (Versioning des fichiers)
DROP TABLE IF EXISTS rapports;
CREATE TABLE rapports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projet_id INT NOT NULL,
    version INT DEFAULT 1,
    chemin_fichier VARCHAR(255) NOT NULL,
    commentaire TEXT,
    date_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (projet_id) REFERENCES projets(id) ON DELETE CASCADE
);

-- 5. TABLE SALLES
DROP TABLE IF EXISTS salles;
CREATE TABLE salles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    capacite INT NOT NULL,
    equipements TEXT -- Ex: "Projecteur, Tableau"
);

-- 6. TABLE PERIODES DISPONIBILITE (Gestion des campagnes de saisie)
DROP TABLE IF EXISTS periodes;
CREATE TABLE periodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filiere_id INT NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    titre VARCHAR(100), -- Ex: "Soutenance Session Hiver"
    FOREIGN KEY (filiere_id) REFERENCES filieres(id)
);

-- 7. TABLE DISPONIBILITES (Liées aux périodes)
DROP TABLE IF EXISTS disponibilites;
CREATE TABLE disponibilites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prof_id INT NOT NULL,
    periode_id INT NOT NULL,
    jour DATE NOT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    FOREIGN KEY (prof_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (periode_id) REFERENCES periodes(id) ON DELETE CASCADE
);

-- 8. TABLE SOUTENANCES (Planification)
DROP TABLE IF EXISTS soutenances;
CREATE TABLE soutenances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projet_id INT UNIQUE NOT NULL,
    salle_id INT NOT NULL,
    date_soutenance DATETIME NOT NULL,
    note_finale DECIMAL(4,2) NULL,
    mention VARCHAR(50) NULL,
    pv_signe BOOLEAN DEFAULT FALSE, -- Signature Directeur
    statut ENUM('planifie', 'publie', 'annule') DEFAULT 'planifie', -- Nouveau statut pour la soutenance
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (projet_id) REFERENCES projets(id),
    FOREIGN KEY (salle_id) REFERENCES salles(id)
);

-- 8.5. TABLE PV (Procès-Verbaux des Soutenances)
DROP TABLE IF EXISTS pv;
CREATE TABLE pv (
    id INT AUTO_INCREMENT PRIMARY KEY,
    soutenance_id INT UNIQUE NOT NULL, -- UNIQUE car un seul PV par soutenance
    statut VARCHAR(20) DEFAULT 'attente_signature', -- Ex: 'attente_signature', 'signe', 'rejete'
    signature_hash VARCHAR(64) DEFAULT NULL, -- Hash de la signature numérique
    signed_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (soutenance_id) REFERENCES soutenances(id) ON DELETE CASCADE
);

-- 9. TABLE JURYS (Composition complexe : Président, Examinateur...)
DROP TABLE IF EXISTS jurys;
CREATE TABLE jurys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    soutenance_id INT NOT NULL,
    prof_id INT NOT NULL,
    role_jury ENUM('president', 'examinateur', 'rapporteur', 'encadrant', 'invite') NOT NULL,
    present BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (soutenance_id) REFERENCES soutenances(id) ON DELETE CASCADE,
    FOREIGN KEY (prof_id) REFERENCES users(id)
);

-- 10. TABLE MESSAGES (Communication interne)
DROP TABLE IF EXISTS messages;
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projet_id INT NOT NULL,
    expediteur_id INT NOT NULL,
    contenu TEXT NOT NULL,
    lu BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (projet_id) REFERENCES projets(id),
    FOREIGN KEY (expediteur_id) REFERENCES users(id)
);

-- 11. TABLE PARAMETRES (Configuration système)
DROP TABLE IF EXISTS parametres;
CREATE TABLE parametres (
    cle VARCHAR(100) PRIMARY KEY,
    valeur TEXT NOT NULL,
    description VARCHAR(255) NULL
);

-- =============================================
-- JEU DE DONNÉES COMPLET (SEEDER)
-- =============================================

-- 1. Filière
INSERT INTO filieres (code, nom, duree_soutenance) VALUES ('GINF', 'Génie Informatique', 60);

-- 2. Utilisateurs (Tous les rôles)

INSERT INTO users (prenom, nom, email, login, password, role, filiere_id, specialite) VALUES
('Rachid', 'Directeur', 'directeur@ecole.com', 'directeur.rachid', '123456', 'directeur', 1, NULL),
('Sarah', 'Assistante', 'assistante@ecole.com', 'assistante.sarah', '123456', 'assistante', 1, NULL),
('Ihab', 'Coordinateur', 'ihab@admin.com', 'ihab.admin', '123456', 'coordinateur', 1, NULL),
('Abdel', 'Prof', 'abdel@prof.com', 'abdel.prof', '123456', 'prof', 1, 'Algorithmique'),
('Bennani', 'Prof', 'bennani@prof.com', 'bennani.prof', '123456', 'prof', 1, 'Reseaux'),
('Chami', 'Prof', 'chami.prof', 'chami.prof', '123456', 'prof', 1, 'Gestion'),
('Nizar', 'Etudiant', 'nizar@etud.com', 'nizar.etudiant', '123456', 'etudiant', 1, NULL),
('Amine', 'Binome', 'amine@etud.com', 'amine.etudiant', '123456', 'etudiant', 1, NULL);




-- Lier le coordinateur à la filière
UPDATE filieres SET coordinateur_id = 3 WHERE code = 'GINF';

-- 3. Salle
INSERT INTO salles (nom, capacite) VALUES ('Salle B12', 30), ('Amphi OCP', 200);

-- 4. Projet
INSERT INTO projets (titre, description, etudiant_id, binome_id, encadrant_id, filiere_id, annee_universitaire, statut) VALUES 
('Gestion Soutenances', 'Application Web complète', 7, 8, 4, 1, '2025-2026', 'valide_encadrant');

-- 5. Message
INSERT INTO messages (projet_id, expediteur_id, contenu) VALUES 
(1, 4, 'Bonjour, n oubliez pas de déposer le rapport V1 avant lundi.');

-- 6. Paramètres
INSERT INTO parametres (cle, valeur, description) VALUES
('delai_saisie_notes', '15', 'Délai en jours pour la saisie des notes après soutenance'),
('annee_universitaire_courante', '2025-2026', 'Année universitaire actuellement configurée'),
('chemin_archives_pv', '/var/www/html/archives/pv_signes', 'Chemin de stockage des PV signés'),
('min_jury_prof', '3', 'Nombre minimum de professeurs dans un jury'),
('validation_auto_projet', 'false', 'Validation automatique des projets à l\'inscription');

-- 12. TABLE SUPPORT_TICKETS (Gestion des tickets de support)
DROP TABLE IF EXISTS support_tickets;
CREATE TABLE support_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    sujet VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    statut ENUM('ouvert', 'en_cours', 'ferme') DEFAULT 'ouvert',
    priorite ENUM('basse', 'moyenne', 'haute') DEFAULT 'moyenne',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


-- 13. TABLE AUDIT_LOGS (Historique des actions)
DROP TABLE IF EXISTS audit_logs;
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL, -- NULL if action is not linked to a specific user (e.g., system action)
    action VARCHAR(255) NOT NULL,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);


-- 14. TABLE DISPONIBILITES_PROFS (Disponibilités spécifiques des professeurs pour les jurys)
DROP TABLE IF EXISTS disponibilites_profs;
CREATE TABLE disponibilites_profs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prof_id INT NOT NULL,
    jour_semaine VARCHAR(20) NOT NULL, -- Ex: 'Lundi', 'Mardi'
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    est_disponible BOOLEAN DEFAULT TRUE, -- Not strictly necessary if a record implies availability
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (prof_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE(prof_id, jour_semaine, heure_debut) -- A professor can't have overlapping slots for the same day
);

SET FOREIGN_KEY_CHECKS = 1;