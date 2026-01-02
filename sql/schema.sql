-- STRUCTURE DE LA BASE DE DONNEES (NE PAS MODIFIER)

-- 1. Table Utilisateurs (Tous les rôles)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('etudiant', 'prof', 'coordinateur', 'admin') NOT NULL,
    specialite VARCHAR(100) NULL, -- Pour les profs
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Salles
CREATE TABLE salles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    capacite INT NOT NULL
);

-- 3. Projets
CREATE TABLE projets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(200) NOT NULL,
    description TEXT,
    etudiant_id INT NOT NULL,
    encadrant_id INT NULL,
    rapport_path VARCHAR(255) NULL,
    statut ENUM('inscrit', 'valide', 'planifie', 'soutenu') DEFAULT 'inscrit',
    FOREIGN KEY (etudiant_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (encadrant_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 4. Disponibilités Profs
CREATE TABLE disponibilites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prof_id INT NOT NULL,
    jour DATE NOT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    FOREIGN KEY (prof_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 5. Soutenances
CREATE TABLE soutenances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projet_id INT NOT NULL,
    salle_id INT NOT NULL,
    date_soutenance DATETIME NOT NULL,
    jury_president_id INT,
    jury_examinateur_id INT,
    note_finale DECIMAL(4,2) NULL,
    FOREIGN KEY (projet_id) REFERENCES projets(id),
    FOREIGN KEY (salle_id) REFERENCES salles(id)
);

-- DONNEES DE TEST (SEEDER)
INSERT INTO users (nom, email, password, role) VALUES 
('Admin Ihab', 'ihab@admin.com', '123456', 'coordinateur'),
('Prof Abdel', 'abdel@prof.com', '123456', 'prof'),
('Etudiant Nizar', 'nizar@etud.com', '123456', 'etudiant');

INSERT INTO salles (nom, capacite) VALUES ('Salle A', 20), ('Amphi 1', 100);