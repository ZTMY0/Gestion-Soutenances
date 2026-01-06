-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 05, 2026 at 12:50 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `soutenances_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `disponibilites`
--

CREATE TABLE `disponibilites` (
  `id` int(11) NOT NULL,
  `prof_id` int(11) NOT NULL,
  `periode_id` int(11) NOT NULL,
  `jour` date NOT NULL,
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `disponibilites_profs`
--

CREATE TABLE `disponibilites_profs` (
  `id` int(11) NOT NULL,
  `prof_id` int(11) NOT NULL,
  `jour_semaine` enum('Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi') NOT NULL,
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL,
  `est_disponible` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `disponibilites_profs`
--

INSERT INTO `disponibilites_profs` (`id`, `prof_id`, `jour_semaine`, `heure_debut`, `heure_fin`, `est_disponible`) VALUES
(1, 766, 'Mardi', '08:30:00', '09:00:00', 1),
(2, 766, 'Mercredi', '09:30:00', '10:00:00', 1),
(3, 766, 'Vendredi', '11:30:00', '12:00:00', 1),
(4, 783, 'Mardi', '08:00:00', '08:30:00', 1),
(5, 783, 'Mercredi', '09:30:00', '10:00:00', 1),
(6, 783, 'Vendredi', '09:30:00', '10:00:00', 1),
(7, 783, 'Lundi', '10:30:00', '11:00:00', 1),
(8, 783, 'Mercredi', '10:30:00', '11:00:00', 1),
(9, 783, 'Vendredi', '11:30:00', '12:00:00', 1),
(10, 783, 'Mardi', '13:00:00', '13:30:00', 1),
(11, 783, 'Vendredi', '14:30:00', '15:00:00', 1),
(12, 767, 'Lundi', '08:00:00', '08:30:00', 1),
(13, 767, 'Lundi', '12:00:00', '12:30:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `filieres`
--

CREATE TABLE `filieres` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `nom` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `coordinateur_id` int(11) DEFAULT NULL,
  `duree_soutenance` int(11) DEFAULT 60,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `filieres`
--

INSERT INTO `filieres` (`id`, `code`, `nom`, `description`, `coordinateur_id`, `duree_soutenance`, `created_at`) VALUES
(2, 'CYBER', 'Cybersécurité', NULL, NULL, 60, '2026-01-02 18:11:49'),
(3, 'BIGDATA', 'Big Data & Analyse', NULL, NULL, 60, '2026-01-02 18:11:49'),
(4, 'AI', 'Intelligence Artificielle', NULL, NULL, 60, '2026-01-02 18:11:49'),
(5, 'ROBO', 'Robotique & Cobotique', NULL, NULL, 60, '2026-01-02 18:11:49'),
(7, 'FULLSTACK', 'Développement FullStack', NULL, NULL, 60, '2026-01-04 22:54:52');

-- --------------------------------------------------------

--
-- Table structure for table `jurys`
--

CREATE TABLE `jurys` (
  `id` int(11) NOT NULL,
  `soutenance_id` int(11) NOT NULL,
  `prof_id` int(11) NOT NULL,
  `role_jury` enum('president','examinateur','rapporteur','encadrant','invite') NOT NULL,
  `present` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jurys`
--

INSERT INTO `jurys` (`id`, `soutenance_id`, `prof_id`, `role_jury`, `present`) VALUES
(1, 3, 777, 'president', 1),
(2, 3, 770, 'examinateur', 1),
(3, 4, 788, 'president', 1),
(4, 4, 767, 'examinateur', 1);

-- --------------------------------------------------------

--
-- Table structure for table `jury_soutenance`
--

CREATE TABLE `jury_soutenance` (
  `id` int(11) NOT NULL,
  `projet_id` int(11) NOT NULL,
  `prof1_id` int(11) NOT NULL,
  `prof2_id` int(11) NOT NULL,
  `prof3_id` int(11) DEFAULT NULL,
  `date_soutenance` datetime DEFAULT NULL,
  `salle` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `projet_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `projet_id`, `sender_id`, `message`, `created_at`) VALUES
(5, 8, 1249, 'kk', '2026-01-05 00:46:58'),
(6, 8, 767, 'ok', '2026-01-05 00:47:13');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(191) NOT NULL,
  `token` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `created_at`) VALUES
(8, 'abdelmoughit.mossaid@eidia.ueuromed.org', '611829', '2026-01-03 14:30:20', '2026-01-03 13:15:20');

-- --------------------------------------------------------

--
-- Table structure for table `periodes`
--

CREATE TABLE `periodes` (
  `id` int(11) NOT NULL,
  `filiere_id` int(11) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `titre` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projets`
--

CREATE TABLE `projets` (
  `id` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `domaine` varchar(100) DEFAULT NULL,
  `technologies` varchar(255) DEFAULT NULL,
  `binome_email` varchar(150) DEFAULT NULL,
  `etudiant_id` int(11) NOT NULL,
  `encadrant_id` int(11) DEFAULT NULL,
  `filiere_id` int(11) NOT NULL,
  `encadrant_pref1_id` int(11) DEFAULT NULL,
  `encadrant_pref2_id` int(11) DEFAULT NULL,
  `encadrant_pref3_id` int(11) DEFAULT NULL,
  `statut` varchar(50) DEFAULT 'inscrit',
  `annee_universitaire` varchar(20) DEFAULT '2025-2026',
  `created_at` datetime DEFAULT current_timestamp(),
  `rapport_chemin` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projets`
--

INSERT INTO `projets` (`id`, `titre`, `description`, `domaine`, `technologies`, `binome_email`, `etudiant_id`, `encadrant_id`, `filiere_id`, `encadrant_pref1_id`, `encadrant_pref2_id`, `encadrant_pref3_id`, `statut`, `annee_universitaire`, `created_at`, `rapport_chemin`) VALUES
(6, 'Système de recommandation de films', '......', 'IA', 'IA : Python (Scikit-learn)  Backend : FastAPI  Front : React', '', 855, 772, 3, 772, NULL, NULL, 'valide', '2025-2026', '2026-01-04 23:23:11', 'rapport_6_1767565475.pdf'),
(7, 'Système de détection d\'intrusions', 'test', 'Cyber', 'Docker,PHP', '', 1157, 780, 1, 780, NULL, NULL, 'valide', '2025-2026', '2026-01-04 23:29:50', 'rapport_7_1767565862.pdf'),
(8, 'RFID based Gate', 'test', 'Robotique', 'C++ Arduino', '', 1249, 767, 4, 767, 785, NULL, 'valide', '2025-2026', '2026-01-05 00:43:50', 'rapport_8_1767570411.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `rapports`
--

CREATE TABLE `rapports` (
  `id` int(11) NOT NULL,
  `projet_id` int(11) NOT NULL,
  `nom_fichier` varchar(255) NOT NULL,
  `chemin_fichier` varchar(255) NOT NULL,
  `taille_fichier` int(11) NOT NULL,
  `resume` text DEFAULT NULL,
  `mots_cles` varchar(255) DEFAULT NULL,
  `remerciements` text DEFAULT NULL,
  `est_original` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rapports`
--

INSERT INTO `rapports` (`id`, `projet_id`, `nom_fichier`, `chemin_fichier`, `taille_fichier`, `resume`, `mots_cles`, `remerciements`, `est_original`, `created_at`) VALUES
(5, 6, 'mini-projets_2025.pdf', 'uploads/rapport_6_1767565475.pdf', 169547, 'test', NULL, NULL, 1, '2026-01-04 23:24:35'),
(6, 7, 'mini-projets_2025.pdf', 'uploads/rapport_7_1767565862.pdf', 169547, 'test', NULL, NULL, 1, '2026-01-04 23:31:02'),
(7, 8, 'Revision_question - Exercises.pdf', 'uploads/rapport_8_1767570411.pdf', 182147, 'rapport', NULL, NULL, 1, '2026-01-05 00:46:51');

-- --------------------------------------------------------

--
-- Table structure for table `salles`
--

CREATE TABLE `salles` (
  `id` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `capacite` int(11) NOT NULL,
  `equipements` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `salles`
--

INSERT INTO `salles` (`id`, `nom`, `capacite`, `equipements`) VALUES
(1, 'Salle B12', 30, NULL),
(2, 'Amphi OCP', 200, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `soutenances`
--

CREATE TABLE `soutenances` (
  `id` int(11) NOT NULL,
  `projet_id` int(11) NOT NULL,
  `date_soutenance` datetime DEFAULT NULL,
  `salle` varchar(50) DEFAULT NULL,
  `jury_infos` text DEFAULT NULL,
  `note_finale` decimal(4,2) DEFAULT NULL,
  `mention` varchar(50) DEFAULT NULL,
  `commentaire_jury` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `soutenances`
--

INSERT INTO `soutenances` (`id`, `projet_id`, `date_soutenance`, `salle`, `jury_infos`, `note_finale`, `mention`, `commentaire_jury`, `created_at`) VALUES
(3, 7, '2026-04-13 13:00:00', 'B4 - 2.15', NULL, NULL, NULL, NULL, '2026-01-04 23:52:31'),
(4, 8, '2026-08-27 12:47:00', 'Amphi A', NULL, NULL, NULL, NULL, '2026-01-05 00:47:54');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `login` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('etudiant','prof','coordinateur','directeur','assistante') NOT NULL,
  `filiere_id` int(11) DEFAULT NULL,
  `specialite` varchar(255) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `must_change_password` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nom`, `prenom`, `email`, `username`, `login`, `password`, `role`, `filiere_id`, `specialite`, `telephone`, `created_at`, `must_change_password`) VALUES
(256, 'Mme Assistante', NULL, 'assistante@uemf.org', NULL, 'assistante.admin', '$2y$10$W83FhbK88Djqssz9LE9TBOEal8eHc0J7cGyxOLKpI1AInDkE1hhuG', 'assistante', NULL, NULL, NULL, '2026-01-02 20:16:10', 1),
(257, 'Monsieur le Directeur', NULL, 'directeur@uemf.org', NULL, 'directeur.general', '$2y$10$W83FhbK88Djqssz9LE9TBOEal8eHc0J7cGyxOLKpI1AInDkE1hhuG', 'directeur', NULL, NULL, NULL, '2026-01-02 20:16:10', 1),
(283, 'Ihab Admin', NULL, 'ihab@admin.com', NULL, 'ihab.admin', '$2y$10$yP33I5WWNhc5SnwP7WfSdOUB65mwgtjq06Koes0Si.wvkMdPC6SqK', 'coordinateur', NULL, NULL, NULL, '2026-01-03 12:14:12', 1),
(764, 'Mernissi', 'Oussama', 'oussama.mernissi@prof.ueuromed.org', NULL, 'oussama.mernissi', '9QYftMS1', 'prof', NULL, 'AI, CYBER', NULL, '2026-01-04 12:20:16', 1),
(765, 'Bennis', 'Mehdi', 'm.bennis@prof.ueuromed.org', NULL, 'm.bennis', 'xU8MDlmX', 'prof', NULL, 'AI, CYBER', NULL, '2026-01-04 12:20:16', 1),
(766, 'Kabbaj', 'Rania', 'rania.kabbaj@prof.ueuromed.org', NULL, 'rania.kabbaj', 'llJsd9eS', 'prof', NULL, 'ROBO, AI', NULL, '2026-01-04 12:20:16', 1),
(767, 'Berrada', 'Sarah', 'sarah.berrada@prof.ueuromed.org', NULL, 'sarah.berrada', '0RNvFHnU', 'prof', NULL, 'ROBO, AI', NULL, '2026-01-04 12:20:16', 1),
(768, 'Raiss', 'Brahim', 'brahim.raiss@prof.ueuromed.org', NULL, 'brahim.raiss', '6VjXZT93', 'prof', NULL, 'ROBO, CYBER', NULL, '2026-01-04 12:20:16', 1),
(769, 'Talbi', 'Noura', 'noura.talbi@prof.ueuromed.org', NULL, 'noura.talbi', 'GLEfElNN', 'prof', NULL, 'BIGDATA, ROBO', NULL, '2026-01-04 12:20:16', 1),
(770, 'Guedira', 'Anas', 'anas.guedira@prof.ueuromed.org', NULL, 'anas.guedira', 'oTIA4k5f', 'prof', NULL, 'BIGDATA, CYBER', NULL, '2026-01-04 12:20:16', 1),
(771, 'Alami', 'Reda', 'reda.alami@prof.ueuromed.org', NULL, 'reda.alami', 'okSUjwMI', 'prof', NULL, 'ROBO, BIGDATA', NULL, '2026-01-04 12:20:16', 1),
(772, 'Chraibi', 'Aya', 'aya.chraibi@prof.ueuromed.org', NULL, 'aya.chraibi', 'QMn5TiPX', 'prof', NULL, 'AI, CYBER', NULL, '2026-01-04 12:20:16', 1),
(773, 'Mansouri', 'Zineb', 'zineb.mansouri@prof.ueuromed.org', NULL, 'zineb.mansouri', 'lDsKbIqY', 'prof', NULL, 'CYBER, AI', NULL, '2026-01-04 12:20:16', 1),
(774, 'Chraibi', 'Mehdi', 'm.chraibi@prof.ueuromed.org', NULL, 'm.chraibi', 'AFBvuK8w', 'prof', NULL, 'FULL, BIGDATA', NULL, '2026-01-04 12:20:16', 1),
(775, 'Lahlou', 'Najat', 'n.lahlou@prof.ueuromed.org', NULL, 'n.lahlou', '0mPQPoOH', 'prof', NULL, 'FULL, ROBO', NULL, '2026-01-04 12:20:16', 1),
(776, 'Fassi', 'Ali', 'a.fassi@prof.ueuromed.org', NULL, 'a.fassi', 'pmzkQYS0', 'prof', NULL, 'AI, BIGDATA', NULL, '2026-01-04 12:20:16', 1),
(777, 'Jettou', 'Oussama', 'oussama.jettou@prof.ueuromed.org', NULL, 'oussama.jettou', 'vOBpmPcQ', 'prof', NULL, 'BIGDATA, ROBO', NULL, '2026-01-04 12:20:16', 1),
(778, 'El Amrani', 'Youssef', 'youssef.elamrani@prof.ueuromed.org', NULL, 'youssef.elamrani', 'Bl3syTNb', 'prof', NULL, 'FULL, AI', NULL, '2026-01-04 12:20:16', 1),
(779, 'Berrada', 'Omar', 'o.berrada@prof.ueuromed.org', NULL, 'o.berrada', 'B77siU6s', 'prof', NULL, 'ROBO, FULL', NULL, '2026-01-04 12:20:16', 1),
(780, 'Bennis', 'Salma', 's.bennis@prof.ueuromed.org', NULL, 's.bennis', '232RpRNy', 'prof', NULL, 'CYBER, ROBO', NULL, '2026-01-04 12:20:16', 1),
(781, 'Slaoui', 'Sarah', 'sarah.slaoui@prof.ueuromed.org', NULL, 'sarah.slaoui', 'Iywm0GxU', 'prof', NULL, 'BIGDATA, AI', NULL, '2026-01-04 12:20:16', 1),
(782, 'Chaoui', 'Brahim', 'brahim.chaoui@prof.ueuromed.org', NULL, 'brahim.chaoui', '3SLAhd4L', 'prof', NULL, 'FULL, CYBER', NULL, '2026-01-04 12:20:16', 1),
(783, 'Berrada', 'Asmaa', 'asmaa.berrada@prof.ueuromed.org', NULL, 'asmaa.berrada', 'RbVuRTIT', 'prof', NULL, 'CYBER, ROBO', NULL, '2026-01-04 12:20:16', 1),
(784, 'Daoudi', 'Rania', 'rania.daoudi@prof.ueuromed.org', NULL, 'rania.daoudi', '0zZmfgRj', 'prof', NULL, 'CYBER, FULL', NULL, '2026-01-04 12:20:16', 1),
(785, 'Lahlou', 'Ali', 'ali.lahlou@prof.ueuromed.org', NULL, 'ali.lahlou', 'kldI1VXZ', 'prof', NULL, 'ROBO, CYBER', NULL, '2026-01-04 12:20:16', 1),
(786, 'Raiss', 'Anas', 'a.raiss@prof.ueuromed.org', NULL, 'a.raiss', 'YWOpZeBz', 'prof', NULL, 'ROBO, AI', NULL, '2026-01-04 12:20:16', 1),
(787, 'Jettou', 'Ismail', 'ismail.jettou@prof.ueuromed.org', NULL, 'ismail.jettou', 'MLaSfhui', 'prof', NULL, 'CYBER, AI', NULL, '2026-01-04 12:20:16', 1),
(788, 'Chraibi', 'Manal', 'chraibi.manal@prof.ueuromed.org', NULL, 'chraibi.manal', 'PCib8fPB', 'prof', NULL, 'FULL, BIGDATA', NULL, '2026-01-04 12:20:16', 1),
(789, 'Tazi', 'Ahmed', 'ahmed.tazi@eidia.ueuromed.org', NULL, 'ahmed.tazi', 'tob2eXwQ', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(790, 'Tazi', 'Reda', 'reda.tazi@eidia.ueuromed.org', NULL, 'reda.tazi', 'iTBsMvmW', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(791, 'Kabbaj', 'Oussama', 'oussama.kabbaj@eidia.ueuromed.org', NULL, 'oussama.kabbaj', 'aLSANitE', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(792, 'Slaoui', 'Walid', 'walid.slaoui@eidia.ueuromed.org', NULL, 'walid.slaoui', 'lII2f7ER', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(793, 'Daoudi', 'Youssef', 'youssef.daoudi@eidia.ueuromed.org', NULL, 'youssef.daoudi', 'xz7yiO1N', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(794, 'Jettou', 'Meryem', 'meryem.jettou@eidia.ueuromed.org', NULL, 'meryem.jettou', 'WZP1X4rc', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(795, 'Benjelloun', 'Sofia', 'sofia.benjelloun@eidia.ueuromed.org', NULL, 'sofia.benjelloun', 'kp2WhKki', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(796, 'Daoudi', 'Sarah', 'sarah.daoudi@eidia.ueuromed.org', NULL, 'sarah.daoudi', 'tbXGWe2P', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(797, 'Chraibi', 'Reda', 'reda.chraibi@eidia.ueuromed.org', NULL, 'reda.chraibi', 'hkGHDkAR', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(798, 'Bennis', 'Rania', 'rania.bennis@eidia.ueuromed.org', NULL, 'rania.bennis', '9QWP0ec3', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(799, 'Zerrad', 'Noura', 'noura.zerrad@eidia.ueuromed.org', NULL, 'noura.zerrad', 'OpwjTB92', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(800, 'Bennani', 'Ali', 'ali.bennani@eidia.ueuromed.org', NULL, 'ali.bennani', 'yTpyXvEC', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(801, 'Raiss', 'Salma', 'salma.raiss@eidia.ueuromed.org', NULL, 'salma.raiss', 'Kcg43JiS', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(802, 'Jettou', 'Latifa', 'latifa.jettou@eidia.ueuromed.org', NULL, 'latifa.jettou', 'AmTUU9tK', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(803, 'Benjelloun', 'Rania', 'rania.benjelloun@eidia.ueuromed.org', NULL, 'rania.benjelloun', 'TtTZnqu0', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(804, 'Slaoui', 'Taha', 'taha.slaoui@eidia.ueuromed.org', NULL, 'taha.slaoui', 'D0TyofHm', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(805, 'Naciri', 'Rim', 'rim.naciri@eidia.ueuromed.org', NULL, 'rim.naciri', 'y585gC2a', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(806, 'Zouhair', 'Driss', 'driss.zouhair@eidia.ueuromed.org', NULL, 'driss.zouhair', 'vlNORdCF', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(807, 'Naciri', 'Amine', 'amine.naciri@eidia.ueuromed.org', NULL, 'amine.naciri', '0fVTq1OR', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(808, 'Benjelloun', 'Latifa', 'latifa.benjelloun@eidia.ueuromed.org', NULL, 'latifa.benjelloun', 'u2M0NWcA', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(809, 'Benali', 'Bilal', 'bilal.benali@eidia.ueuromed.org', NULL, 'bilal.benali', 'kvGZYn74', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(810, 'Benali', 'Ghita', 'ghita.benali@eidia.ueuromed.org', NULL, 'ghita.benali', 'ifscmRX2', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(811, 'Daoudi', 'Reda', 'reda.daoudi@eidia.ueuromed.org', NULL, 'reda.daoudi', '2TINBb4r', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(812, 'Daoudi', 'Omar', 'omar.daoudi@eidia.ueuromed.org', NULL, 'omar.daoudi', 'zyJ6843z', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(813, 'Lahlou', 'Hassan', 'hassan.lahlou@eidia.ueuromed.org', NULL, 'hassan.lahlou', 'VfMjkaYl', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(814, 'Talbi', 'Najat', 'najat.talbi@eidia.ueuromed.org', NULL, 'najat.talbi', 'NMVHNenW', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(815, 'Chaoui', 'Aya', 'aya.chaoui@eidia.ueuromed.org', NULL, 'aya.chaoui', 'NPLREqQB', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(816, 'Kabbaj', 'Kaoutar', 'kaoutar.kabbaj@eidia.ueuromed.org', NULL, 'kaoutar.kabbaj', 'UvP53PHN', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(817, 'Chraibi', 'Kenza', 'kenza.chraibi@eidia.ueuromed.org', NULL, 'kenza.chraibi', 'No6MyWID', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(818, 'Raiss', 'Rim', 'rim.raiss@eidia.ueuromed.org', NULL, 'rim.raiss', 'SvhNF2wb', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(819, 'Zerrad', 'Sofia', 'sofia.zerrad@eidia.ueuromed.org', NULL, 'sofia.zerrad', 'rWciGNkp', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(820, 'Ouazzani', 'Sanae', 'sanae.ouazzani@eidia.ueuromed.org', NULL, 'sanae.ouazzani', 'w4n8gsCW', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(821, 'Zouhair', 'Asmaa', 'asmaa.zouhair@eidia.ueuromed.org', NULL, 'asmaa.zouhair', '2vSAWpZE', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(822, 'Guessous', 'Youssef', 'youssef.guessous@eidia.ueuromed.org', NULL, 'youssef.guessous', '9iJeF6W1', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(823, 'Mernissi', 'Rim', 'rim.mernissi2@eidia.ueuromed.org', NULL, 'rim.mernissi2', 'xt6EAfJL', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(824, 'Guedira', 'Omar', 'omar.guedira@eidia.ueuromed.org', NULL, 'omar.guedira', 'QOybCWI7', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(825, 'Daoudi', 'Karim', 'karim.daoudi@eidia.ueuromed.org', NULL, 'karim.daoudi', '7FZNHse4', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(826, 'Guessous', 'Najat', 'najat.guessous@eidia.ueuromed.org', NULL, 'najat.guessous', 'xAryfXl8', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(827, 'Bennis', 'Ali', 'ali.bennis@eidia.ueuromed.org', NULL, 'ali.bennis', '7lLVPdee', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(828, 'Fassi', 'Taha', 'taha.fassi@eidia.ueuromed.org', NULL, 'taha.fassi', 'khlujwtx', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(829, 'Lahlou', 'Hajar', 'hajar.lahlou@eidia.ueuromed.org', NULL, 'hajar.lahlou', 'cBrzNZhT', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(830, 'Guedira', 'Mohamed', 'mohamed.guedira@eidia.ueuromed.org', NULL, 'mohamed.guedira', 'KqqaNDk1', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(831, 'Kadiri', 'Omar', 'omar.kadiri@eidia.ueuromed.org', NULL, 'omar.kadiri', '53qKbw7j', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(832, 'Naciri', 'Youssef', 'youssef.naciri@eidia.ueuromed.org', NULL, 'youssef.naciri', 'tZJx0mFZ', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(833, 'Berrada', 'Manal', 'manal.berrada@eidia.ueuromed.org', NULL, 'manal.berrada', 'FOb2toRp', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(834, 'Talbi', 'Sofia', 'sofia.talbi@eidia.ueuromed.org', NULL, 'sofia.talbi', '0SY794Yb', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(835, 'Sefrioui', 'Amine', 'amine.sefrioui@eidia.ueuromed.org', NULL, 'amine.sefrioui', 'pWyEEe5B', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(836, 'Ouazzani', 'Bilal', 'bilal.ouazzani@eidia.ueuromed.org', NULL, 'bilal.ouazzani', '9tIsPoek', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(837, 'Chaoui', 'Nizar', 'nizar.chaoui@eidia.ueuromed.org', NULL, 'nizar.chaoui', 'n1kCZcjA', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(838, 'Zerrad', 'Rim', 'rim.zerrad@eidia.ueuromed.org', NULL, 'rim.zerrad', 'CbLaRyUv', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(839, 'Benali', 'Brahim', 'brahim.benali@eidia.ueuromed.org', NULL, 'brahim.benali', 'wl5gGafj', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(840, 'Zouhair', 'Bilal', 'bilal.zouhair@eidia.ueuromed.org', NULL, 'bilal.zouhair', '1FLpUSNz', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(841, 'Fassi', 'Asmaa', 'asmaa.fassi@eidia.ueuromed.org', NULL, 'asmaa.fassi', 'XrDMi4vR', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(842, 'Slaoui', 'Anas', 'anas.slaoui@eidia.ueuromed.org', NULL, 'anas.slaoui', 'Fesq4cEw', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(843, 'Chaoui', 'Bilal', 'bilal.chaoui@eidia.ueuromed.org', NULL, 'bilal.chaoui', 'mOL9MAL2', 'etudiant', 2, NULL, NULL, '2026-01-04 12:20:46', 1),
(844, 'Lahlou', 'Driss', 'driss.lahlou@eidia.ueuromed.org', NULL, 'driss.lahlou', 'tSkjDRe5', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:58', 1),
(845, 'Sefrioui', 'Taha', 'taha.sefrioui@eidia.ueuromed.org', NULL, 'taha.sefrioui', 'iC4bKkVl', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:58', 1),
(846, 'Bennani', 'Rim', 'rim.bennani@eidia.ueuromed.org', NULL, 'rim.bennani', 'fqHRqX1K', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:58', 1),
(847, 'Zouhair', 'Karim', 'karim.zouhair@eidia.ueuromed.org', NULL, 'karim.zouhair', 'QpBwSS2q', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:58', 1),
(848, 'Naciri', 'Taha', 'taha.naciri@eidia.ueuromed.org', NULL, 'taha.naciri', '8Z2WuHiZ', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:58', 1),
(849, 'Naciri', 'Salma', 'salma.naciri@eidia.ueuromed.org', NULL, 'salma.naciri', 'JBbszcv7', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:58', 1),
(850, 'Bennani', 'Reda', 'reda.bennani@eidia.ueuromed.org', NULL, 'reda.bennani', 'rSjsPkNh', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:58', 1),
(851, 'Kabbaj', 'Brahim', 'brahim.kabbaj@eidia.ueuromed.org', NULL, 'brahim.kabbaj', 'uh6eZOGB', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:58', 1),
(852, 'Alami', 'Samir', 'samir.alami@eidia.ueuromed.org', NULL, 'samir.alami', '1CNuJbdq', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:58', 1),
(853, 'Bennis', 'Salma', 'salma.bennis@eidia.ueuromed.org', NULL, 'salma.bennis', 'SorgfaeX', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:58', 1),
(854, 'Bennani', 'Salma', 'salma.bennani@eidia.ueuromed.org', NULL, 'salma.bennani', 'pnS7Bqm9', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:58', 1),
(855, 'Zerrad', 'Aya', 'aya.zerrad@eidia.ueuromed.org', NULL, 'aya.zerrad', 'vXE9JmpR', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:58', 1),
(856, 'Bennis', 'Houda', 'houda.bennis@eidia.ueuromed.org', NULL, 'houda.bennis', 'KerrdQoS', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:58', 1),
(857, 'Benali', 'Karim', 'karim.benali@eidia.ueuromed.org', NULL, 'karim.benali', 'WoKgmGBg', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:59', 1),
(858, 'Slaoui', 'Oussama', 'oussama.slaoui@eidia.ueuromed.org', NULL, 'oussama.slaoui', 'iHQYLPYZ', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:59', 1),
(859, 'Alami', 'Salma', 'salma.alami@eidia.ueuromed.org', NULL, 'salma.alami', 'jfDOBhJU', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:59', 1),
(860, 'Jettou', 'Taha', 'taha.jettou@eidia.ueuromed.org', NULL, 'taha.jettou', 'yH0zK9XJ', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:59', 1),
(861, 'Berrada', 'Yassine', 'yassine.berrada@eidia.ueuromed.org', NULL, 'yassine.berrada', 'ezehXY1c', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:59', 1),
(862, 'Chraibi', 'Ahmed', 'ahmed.chraibi@eidia.ueuromed.org', NULL, 'ahmed.chraibi', 'JnHa79UZ', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:59', 1),
(863, 'Idrissi', 'Yassine', 'yassine.idrissi@eidia.ueuromed.org', NULL, 'yassine.idrissi', 'GFtEygvh', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:59', 1),
(864, 'El Amrani', 'Mehdi', 'mehdi.elamrani@eidia.ueuromed.org', NULL, 'mehdi.elamrani', 'StXpbFpU', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:59', 1),
(865, 'El Amrani', 'Sofia', 'sofia.elamrani@eidia.ueuromed.org', NULL, 'sofia.elamrani', 'n40HhhuD', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:59', 1),
(866, 'Chraibi', 'Ghita', 'ghita.chraibi@eidia.ueuromed.org', NULL, 'ghita.chraibi', '6nHScNKf', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:59', 1),
(867, 'Berrada', 'Kaoutar', 'kaoutar.berrada@eidia.ueuromed.org', NULL, 'kaoutar.berrada', 'DrmTkV0g', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:59', 1),
(868, 'Lahlou', 'Najat', 'najat.lahlou@eidia.ueuromed.org', NULL, 'najat.lahlou', 'WljYLAIu', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:59', 1),
(869, 'Jettou', 'Rania', 'rania.jettou@eidia.ueuromed.org', NULL, 'rania.jettou', 'AKuxFjZB', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:59', 1),
(870, 'Kadiri', 'Driss', 'driss.kadiri@eidia.ueuromed.org', NULL, 'driss.kadiri', 'cfLBJq8s', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:59', 1),
(871, 'Zerrad', 'Anas', 'anas.zerrad@eidia.ueuromed.org', NULL, 'anas.zerrad', 'HuwtmNwT', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:59', 1),
(872, 'Mansouri', 'Asmaa', 'asmaa.mansouri@eidia.ueuromed.org', NULL, 'asmaa.mansouri', 'E2csuyuh', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:59', 1),
(873, 'Ouazzani', 'Sofia', 'sofia.ouazzani@eidia.ueuromed.org', NULL, 'sofia.ouazzani', 'mNnSGnEH', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:59', 1),
(874, 'Alami', 'Rania', 'rania.alami@eidia.ueuromed.org', NULL, 'rania.alami', '0J4hUSIX', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:59', 1),
(875, 'Fassi', 'Zineb', 'zineb.fassi@eidia.ueuromed.org', NULL, 'zineb.fassi', 'RaHPabtK', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:59', 1),
(876, 'Mernissi', 'Reda', 'reda.mernissi@eidia.ueuromed.org', NULL, 'reda.mernissi', 'DPY6SWBq', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:59', 1),
(877, 'Benali', 'Lamia', 'lamia.benali@eidia.ueuromed.org', NULL, 'lamia.benali', '7fc7g0QB', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:59', 1),
(878, 'Chaoui', 'Taha', 'taha.chaoui@eidia.ueuromed.org', NULL, 'taha.chaoui', 'Sq1h6HcO', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:59', 1),
(879, 'Raiss', 'Noura', 'noura.raiss@eidia.ueuromed.org', NULL, 'noura.raiss', 'vJIBDyzU', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:59', 1),
(880, 'Zouhair', 'Noura', 'noura.zouhair@eidia.ueuromed.org', NULL, 'noura.zouhair', 'zqKsfqgz', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:59', 1),
(881, 'Guedira', 'Hajar', 'hajar.guedira@eidia.ueuromed.org', NULL, 'hajar.guedira', 'JJ48MunF', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:59', 1),
(882, 'Idrissi', 'Nizar', 'nizar.idrissi@eidia.ueuromed.org', NULL, 'nizar.idrissi', 'hFipW5va', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:59', 1),
(883, 'Sefrioui', 'Reda', 'reda.sefrioui@eidia.ueuromed.org', NULL, 'reda.sefrioui', 'U5vkcg39', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:59', 1),
(884, 'Alami', 'Najat', 'najat.alami@eidia.ueuromed.org', NULL, 'najat.alami', 'gJqyIilF', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:59', 1),
(885, 'Fassi', 'Driss', 'driss.fassi@eidia.ueuromed.org', NULL, 'driss.fassi', 'JOAk4YNw', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:59', 1),
(886, 'Benali', 'Noura', 'noura.benali@eidia.ueuromed.org', NULL, 'noura.benali', 'ieZbMJxJ', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:59', 1),
(887, 'Naciri', 'Bilal', 'bilal.naciri@eidia.ueuromed.org', NULL, 'bilal.naciri', 'zLO33l3r', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:59', 1),
(888, 'Kadiri', 'Ali', 'ali.kadiri@eidia.ueuromed.org', NULL, 'ali.kadiri', 'cGOOkMJP', 'etudiant', 3, NULL, NULL, '2026-01-04 12:20:59', 1),
(1156, 'Zaghdane', 'Ihab', 'ihab.zaghdane@eidia.ueuromed.org', NULL, 'ihab.zaghdane', 'T6YaPbNd', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:39', 1),
(1157, 'Mossaid', 'Abdelmoughit', 'abdelmoughit.mossaid@eidia.ueuromed.org', NULL, 'abdelmoughit.mossaid', 'dXSOEYrn', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:39', 1),
(1158, 'Zouizra', 'Nizar', 'nizar.zouizra@eidia.ueuromed.org', NULL, 'nizar.zouizra', 'YSdJa65u', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:39', 1),
(1159, 'Kissiri', 'Nourddine', 'nourddine.kissiri@eidia.ueuromed.org', NULL, 'nourddine.kissiri', 'y9TxRSOr', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:39', 1),
(1160, 'Filali', 'Omar', 'omar.filali@eidia.ueuromed.org', NULL, 'omar.filali', 'APBYiVUH', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:39', 1),
(1161, 'Kabbaj', 'Zineb', 'zineb.kabbaj@eidia.ueuromed.org', NULL, 'zineb.kabbaj', 'QNijw3x3', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:39', 1),
(1162, 'Bennani', 'Yassine', 'yassine.bennani@eidia.ueuromed.org', NULL, 'yassine.bennani', 'ZxWpGU1d', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:39', 1),
(1163, 'Kabbaj', 'Houda', 'houda.kabbaj@eidia.ueuromed.org', NULL, 'houda.kabbaj', 'rnrC70sJ', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:39', 1),
(1164, 'Berrada', 'Oussama', 'oussama.berrada@eidia.ueuromed.org', NULL, 'oussama.berrada', 'ruc93z2T', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:39', 1),
(1165, 'Tahiri', 'Walid', 'walid.tahiri@eidia.ueuromed.org', NULL, 'walid.tahiri', '8fxsbpXZ', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:39', 1),
(1166, 'Zouhair', 'Ghita', 'ghita.zouhair@eidia.ueuromed.org', NULL, 'ghita.zouhair', 'C058GFxK', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:39', 1),
(1167, 'Chraibi', 'Rania', 'rania.chraibi@eidia.ueuromed.org', NULL, 'rania.chraibi', 'QtE0es3D', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:39', 1),
(1168, 'Idrissi', 'Hassan', 'hassan.idrissi@eidia.ueuromed.org', NULL, 'hassan.idrissi', 'iOVE2NFX', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:39', 1),
(1169, 'Tahiri', 'Ghita', 'ghita.tahiri@eidia.ueuromed.org', NULL, 'ghita.tahiri', '4DXR2K35', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:39', 1),
(1170, 'Chaoui', 'Asmaa', 'asmaa.chaoui@eidia.ueuromed.org', NULL, 'asmaa.chaoui', 'zSqsDepl', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:39', 1),
(1171, 'Kadiri', 'Manal', 'manal.kadiri@eidia.ueuromed.org', NULL, 'manal.kadiri', 'lwTLrdXd', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:39', 1),
(1172, 'Zouhair', 'Brahim', 'brahim.zouhair@eidia.ueuromed.org', NULL, 'brahim.zouhair', 'q55H9dxV', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:39', 1),
(1173, 'Kadiri', 'Zineb', 'zineb.kadiri@eidia.ueuromed.org', NULL, 'zineb.kadiri', 'pFJwUOYO', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:39', 1),
(1174, 'Tahiri', 'Noura', 'noura.tahiri@eidia.ueuromed.org', NULL, 'noura.tahiri', '9UI6NQta', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:39', 1),
(1175, 'Benjelloun', 'Salma', 'salma.benjelloun@eidia.ueuromed.org', NULL, 'salma.benjelloun', 'oEGA3R6V', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:39', 1),
(1176, 'Idrissi', 'Aya', 'aya.idrissi@eidia.ueuromed.org', NULL, 'aya.idrissi', 'KZPCtXU9', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:39', 1),
(1177, 'Sefrioui', 'Zineb', 'zineb.sefrioui@eidia.ueuromed.org', NULL, 'zineb.sefrioui', 'pacb1Crn', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:39', 1),
(1178, 'Alami', 'Manal', 'manal.alami@eidia.ueuromed.org', NULL, 'manal.alami', 'iWkLH3Cs', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:39', 1),
(1179, 'Mernissi', 'Khalid', 'khalid.mernissi@eidia.ueuromed.org', NULL, 'khalid.mernissi', 'xckAB3G5', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:39', 1),
(1180, 'Talbi', 'Rania', 'rania.talbi@eidia.ueuromed.org', NULL, 'rania.talbi', 'UioVxAuh', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:39', 1),
(1181, 'Chraibi', 'Manal', 'manal.chraibi@eidia.ueuromed.org', NULL, 'manal.chraibi', 'RdbdZ5sL', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:39', 1),
(1182, 'Raiss', 'Manal', 'manal.raiss@eidia.ueuromed.org', NULL, 'manal.raiss', 'cqkwquXq', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:39', 1),
(1183, 'Kadiri', 'Lamia', 'lamia.kadiri@eidia.ueuromed.org', NULL, 'lamia.kadiri', 'ITwOUCJp', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:39', 1),
(1184, 'Fassi', 'Mehdi', 'mehdi.fassi@eidia.ueuromed.org', NULL, 'mehdi.fassi', 'qE3EhYVh', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:39', 1),
(1185, 'Raiss', 'Youssef', 'youssef.raiss@eidia.ueuromed.org', NULL, 'youssef.raiss', 'NmNPSQXi', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:39', 1),
(1186, 'Zouhair', 'Khalid', 'khalid.zouhair@eidia.ueuromed.org', NULL, 'khalid.zouhair', '9YAPEJF7', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:39', 1),
(1187, 'Mernissi', 'Rim', 'rim.mernissi@eidia.ueuromed.org', NULL, 'rim.mernissi', 'rbidsmFG', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1188, 'Daoudi', 'Walid', 'walid.daoudi@eidia.ueuromed.org', NULL, 'walid.daoudi', 'MnKOd8fN', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1189, 'Lahlou', 'Salma', 'salma.lahlou@eidia.ueuromed.org', NULL, 'salma.lahlou', 'p0tEWqwf', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1190, 'Tazi', 'Najat', 'najat.tazi@eidia.ueuromed.org', NULL, 'najat.tazi', 'bsd1sfwa', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1191, 'Slaoui', 'Amine', 'amine.slaoui@eidia.ueuromed.org', NULL, 'amine.slaoui', '8I8YQP1h', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1192, 'Naciri', 'Ahmed', 'ahmed.naciri@eidia.ueuromed.org', NULL, 'ahmed.naciri', 'YzsblEl1', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1193, 'Zouhair', 'Yassine', 'yassine.zouhair@eidia.ueuromed.org', NULL, 'yassine.zouhair', 'zTBKBmbn', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1194, 'Guessous', 'Sofia', 'sofia.guessous@eidia.ueuromed.org', NULL, 'sofia.guessous', '9JSRzSY2', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1195, 'Bennani', 'Taha', 'taha.bennani@eidia.ueuromed.org', NULL, 'taha.bennani', '76tqmOtA', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1196, 'Filali', 'Fatima', 'fatima.filali@eidia.ueuromed.org', NULL, 'fatima.filali', 'D2XOQ6FX', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1197, 'Tazi', 'Rania', 'rania.tazi@eidia.ueuromed.org', NULL, 'rania.tazi', '0hfookKy', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1198, 'Raiss', 'Sanae', 'sanae.raiss@eidia.ueuromed.org', NULL, 'sanae.raiss', 'pdVzMSwD', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1199, 'Mernissi', 'Saad', 'saad.mernissi@eidia.ueuromed.org', NULL, 'saad.mernissi', 'Yn3FEi65', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1200, 'Filali', 'Omar', 'omar.filali2@eidia.ueuromed.org', NULL, 'omar.filali2', 'lxKsU5MF', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1201, 'Chraibi', 'Mehdi', 'mehdi.chraibi@eidia.ueuromed.org', NULL, 'mehdi.chraibi', 'fW1xKO9J', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1202, 'Zouhair', 'Ahmed', 'ahmed.zouhair@eidia.ueuromed.org', NULL, 'ahmed.zouhair', 'owv5XBqc', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1203, 'Tazi', 'Kaoutar', 'kaoutar.tazi@eidia.ueuromed.org', NULL, 'kaoutar.tazi', 'qkytbvH0', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1204, 'Slaoui', 'Latifa', 'latifa.slaoui@eidia.ueuromed.org', NULL, 'latifa.slaoui', 'DxwpWyVc', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1205, 'Idrissi', 'Latifa', 'latifa.idrissi@eidia.ueuromed.org', NULL, 'latifa.idrissi', 'oDC9cy3I', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1206, 'Benjelloun', 'Anas', 'anas.benjelloun@eidia.ueuromed.org', NULL, 'anas.benjelloun', 'iKVgazjs', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1207, 'Tahiri', 'Mohamed', 'mohamed.tahiri@eidia.ueuromed.org', NULL, 'mohamed.tahiri', 'F5kO7Mlv', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1208, 'Chraibi', 'Meryem', 'meryem.chraibi@eidia.ueuromed.org', NULL, 'meryem.chraibi', 'ImUtFr4h', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1209, 'Idrissi', 'Imane', 'imane.idrissi@eidia.ueuromed.org', NULL, 'imane.idrissi', '2TxfhdDf', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1210, 'Berrada', 'Omar', 'omar.berrada@eidia.ueuromed.org', NULL, 'omar.berrada', '1kRqTxK3', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1211, 'Talbi', 'Rim', 'rim.talbi@eidia.ueuromed.org', NULL, 'rim.talbi', 'Y1lc1ULU', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1212, 'Alami', 'Youssef', 'youssef.alami@eidia.ueuromed.org', NULL, 'youssef.alami', 'pyFoSekQ', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1213, 'Slaoui', 'Najat', 'najat.slaoui@eidia.ueuromed.org', NULL, 'najat.slaoui', '5PVvHwCa', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1214, 'Berrada', 'Ghita', 'ghita.berrada@eidia.ueuromed.org', NULL, 'ghita.berrada', 'ZKkzRixT', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1215, 'Chaoui', 'Sanae', 'sanae.chaoui@eidia.ueuromed.org', NULL, 'sanae.chaoui', 'lqi5nXnd', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1216, 'Benali', 'Amine', 'amine.benali@eidia.ueuromed.org', NULL, 'amine.benali', 'SZq6OBca', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1217, 'Raiss', 'Youssef', 'youssef.raiss2@eidia.ueuromed.org', NULL, 'youssef.raiss2', 'JLCrdQyu', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1218, 'Lahlou', 'Meryem', 'meryem.lahlou@eidia.ueuromed.org', NULL, 'meryem.lahlou', 'GV85eOH0', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1219, 'Chaoui', 'Amine', 'amine.chaoui@eidia.ueuromed.org', NULL, 'amine.chaoui', '1JhQU32e', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1220, 'Benjelloun', 'Anas', 'anas.benjelloun2@eidia.ueuromed.org', NULL, 'anas.benjelloun2', 'ZDR1jCa4', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1221, 'Sefrioui', 'Rim', 'rim.sefrioui@eidia.ueuromed.org', NULL, 'rim.sefrioui', 'ttjiY60T', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1222, 'Bennis', 'Driss', 'driss.bennis@eidia.ueuromed.org', NULL, 'driss.bennis', '8GFoZE6V', 'etudiant', 1, NULL, NULL, '2026-01-04 12:25:40', 1),
(1223, 'Bennis', 'Taha', 'taha.bennis2@eidia.ueuromed.org', NULL, 'taha.bennis2', 'NY6bshtp', 'etudiant', 5, NULL, NULL, '2026-01-04 12:25:51', 1),
(1224, 'Benali', 'Sarah', 'sarah.benali@eidia.ueuromed.org', NULL, 'sarah.benali', 'Mpcx5J86', 'etudiant', 5, NULL, NULL, '2026-01-04 12:25:51', 1),
(1225, 'Bennani', 'Brahim', 'brahim.bennani@eidia.ueuromed.org', NULL, 'brahim.bennani', '3KTePTkx', 'etudiant', 5, NULL, NULL, '2026-01-04 12:25:51', 1),
(1226, 'Bennani', 'Hassan', 'hassan.bennani@eidia.ueuromed.org', NULL, 'hassan.bennani', 'UyPdJPBL', 'etudiant', 5, NULL, NULL, '2026-01-04 12:25:51', 1),
(1227, 'Bennani', 'Ahmed', 'ahmed.bennani@eidia.ueuromed.org', NULL, 'ahmed.bennani', 'Yoeh3Rg3', 'etudiant', 5, NULL, NULL, '2026-01-04 12:25:51', 1),
(1228, 'Kadiri', 'Meryem', 'meryem.kadiri@eidia.ueuromed.org', NULL, 'meryem.kadiri', '71p3vLeB', 'etudiant', 5, NULL, NULL, '2026-01-04 12:25:51', 1),
(1229, 'Tazi', 'Ahmed', 'ahmed.tazi2@eidia.ueuromed.org', NULL, 'ahmed.tazi2', 'TrUndjuH', 'etudiant', 5, NULL, NULL, '2026-01-04 12:25:51', 1),
(1230, 'Jettou', 'Manal', 'manal.jettou@eidia.ueuromed.org', NULL, 'manal.jettou', 'zRp7nCPk', 'etudiant', 5, NULL, NULL, '2026-01-04 12:25:51', 1),
(1231, 'Guedira', 'Sanae', 'sanae.guedira@eidia.ueuromed.org', NULL, 'sanae.guedira', 'KHoNxrDO', 'etudiant', 5, NULL, NULL, '2026-01-04 12:25:51', 1),
(1232, 'Mernissi', 'Mohamed', 'mohamed.mernissi@eidia.ueuromed.org', NULL, 'mohamed.mernissi', 'biSsbKtb', 'etudiant', 5, NULL, NULL, '2026-01-04 12:25:51', 1),
(1233, 'Talbi', 'Amine', 'amine.talbi@eidia.ueuromed.org', NULL, 'amine.talbi', 'zUFUu7HF', 'etudiant', 5, NULL, NULL, '2026-01-04 12:25:51', 1),
(1234, 'Filali', 'Sarah', 'sarah.filali@eidia.ueuromed.org', NULL, 'sarah.filali', 'QHGYHbMw', 'etudiant', 5, NULL, NULL, '2026-01-04 12:25:51', 1),
(1235, 'Idrissi', 'Mohamed', 'mohamed.idrissi@eidia.ueuromed.org', NULL, 'mohamed.idrissi', 'gV4HBLBV', 'etudiant', 5, NULL, NULL, '2026-01-04 12:25:51', 1),
(1236, 'Zouhair', 'Hassan', 'hassan.zouhair@eidia.ueuromed.org', NULL, 'hassan.zouhair', 'aYWjJiS0', 'etudiant', 5, NULL, NULL, '2026-01-04 12:25:51', 1),
(1237, 'Idrissi', 'Asmaa', 'asmaa.idrissi@eidia.ueuromed.org', NULL, 'asmaa.idrissi', 'VcWbeuSo', 'etudiant', 5, NULL, NULL, '2026-01-04 12:25:51', 1),
(1238, 'Ouazzani', 'Lamia', 'lamia.ouazzani@eidia.ueuromed.org', NULL, 'lamia.ouazzani', 'qOoRl8aX', 'etudiant', 5, NULL, NULL, '2026-01-04 12:25:51', 1),
(1239, 'Bennis', 'Brahim', 'brahim.bennis@eidia.ueuromed.org', NULL, 'brahim.bennis', '6qXuC4jS', 'etudiant', 5, NULL, NULL, '2026-01-04 12:25:51', 1),
(1240, 'Tahiri', 'Salma', 'salma.tahiri@eidia.ueuromed.org', NULL, 'salma.tahiri', 'qyeOn2ex', 'etudiant', 5, NULL, NULL, '2026-01-04 12:25:51', 1),
(1241, 'Lahlou', 'Ghita', 'ghita.lahlou@eidia.ueuromed.org', NULL, 'ghita.lahlou', 'Jx8VeqTJ', 'etudiant', 5, NULL, NULL, '2026-01-04 12:25:51', 1),
(1242, 'Ouazzani', 'Najat', 'najat.ouazzani@eidia.ueuromed.org', NULL, 'najat.ouazzani', 'noCNGrUz', 'etudiant', 5, NULL, NULL, '2026-01-04 12:25:52', 1),
(1243, 'Naciri', 'Sofia', 'sofia.naciri@eidia.ueuromed.org', NULL, 'sofia.naciri', 'xfn0XaTq', 'etudiant', 5, NULL, NULL, '2026-01-04 12:25:52', 1),
(1244, 'Tazi', 'Youssef', 'youssef.tazi@eidia.ueuromed.org', NULL, 'youssef.tazi', 'EBIIh1TO', 'etudiant', 5, NULL, NULL, '2026-01-04 12:25:52', 1),
(1245, 'Bennis', 'Mehdi', 'mehdi.bennis@eidia.ueuromed.org', NULL, 'mehdi.bennis', '5QfVbkNg', 'etudiant', 5, NULL, NULL, '2026-01-04 12:25:52', 1),
(1246, 'Idrissi', 'Hamza', 'hamza.idrissi@eidia.ueuromed.org', NULL, 'hamza.idrissi', '7i59xhxB', 'etudiant', 5, NULL, NULL, '2026-01-04 12:25:52', 1),
(1247, 'Zouhair', 'Reda', 'reda.zouhair@eidia.ueuromed.org', NULL, 'reda.zouhair', 'nz6l1JrM', 'etudiant', 5, NULL, NULL, '2026-01-04 12:25:52', 1),
(1248, 'Kadiri', 'Zineb', 'zineb.kadiri2@eidia.ueuromed.org', NULL, 'zineb.kadiri2', 'wwxZGUG1', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:02', 1),
(1249, 'Filali', 'Kenza', 'kenza.filali@eidia.ueuromed.org', NULL, 'kenza.filali', '7vMYw2vC', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:02', 1),
(1250, 'Chraibi', 'Hamza', 'hamza.chraibi@eidia.ueuromed.org', NULL, 'hamza.chraibi', 'ANWwcHKr', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:02', 1),
(1251, 'Alami', 'Asmaa', 'asmaa.alami@eidia.ueuromed.org', NULL, 'asmaa.alami', 'OUw90VFq', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:02', 1),
(1252, 'Fassi', 'Ali', 'ali.fassi@eidia.ueuromed.org', NULL, 'ali.fassi', 'SAjUmHiI', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:02', 1),
(1253, 'Benjelloun', 'Mohamed', 'mohamed.benjelloun@eidia.ueuromed.org', NULL, 'mohamed.benjelloun', '4F8MJqlK', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:02', 1),
(1254, 'Daoudi', 'Mohamed', 'mohamed.daoudi@eidia.ueuromed.org', NULL, 'mohamed.daoudi', '3HDjy66G', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:02', 1),
(1255, 'Berrada', 'Ali', 'ali.berrada@eidia.ueuromed.org', NULL, 'ali.berrada', 'A39bBsTn', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:02', 1),
(1256, 'Slaoui', 'Najat', 'najat.slaoui2@eidia.ueuromed.org', NULL, 'najat.slaoui2', 'xvm9bJVd', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:02', 1),
(1257, 'Bennis', 'Asmaa', 'asmaa.bennis@eidia.ueuromed.org', NULL, 'asmaa.bennis', 'EujIzNPt', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:02', 1),
(1258, 'Bennis', 'Taha', 'taha.bennis@eidia.ueuromed.org', NULL, 'taha.bennis', 'dr8BPZLw', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:02', 1),
(1259, 'Ouazzani', 'Sanae', 'sanae.ouazzani2@eidia.ueuromed.org', NULL, 'sanae.ouazzani2', 'BXsN1Hgu', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:02', 1),
(1260, 'Mernissi', 'Rania', 'rania.mernissi@eidia.ueuromed.org', NULL, 'rania.mernissi', 'paZ5KVRe', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:02', 1),
(1261, 'El Amrani', 'Ismail', 'ismail.elamrani@eidia.ueuromed.org', NULL, 'ismail.elamrani', 'qUP6Tjdf', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:02', 1),
(1262, 'Benali', 'Amine', 'amine.benali2@eidia.ueuromed.org', NULL, 'amine.benali2', '0EeWb4mY', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:02', 1),
(1263, 'Tazi', 'Aya', 'aya.tazi@eidia.ueuromed.org', NULL, 'aya.tazi', 'MTtcY1RG', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:02', 1),
(1264, 'Talbi', 'Manal', 'manal.talbi@eidia.ueuromed.org', NULL, 'manal.talbi', '9teSBbQY', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:02', 1),
(1265, 'Bennani', 'Nizar', 'nizar.bennani@eidia.ueuromed.org', NULL, 'nizar.bennani', 'u4A2DnVc', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:02', 1),
(1266, 'Berrada', 'Kenza', 'kenza.berrada@eidia.ueuromed.org', NULL, 'kenza.berrada', 'ylnRaHRb', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:02', 1),
(1267, 'Alami', 'Manal', 'manal.alami2@eidia.ueuromed.org', NULL, 'manal.alami2', 'BIPOUVUq', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:02', 1),
(1268, 'Idrissi', 'Saad', 'saad.idrissi@eidia.ueuromed.org', NULL, 'saad.idrissi', 'qJZNkdUf', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:03', 1),
(1269, 'El Amrani', 'Mohamed', 'mohamed.elamrani@eidia.ueuromed.org', NULL, 'mohamed.elamrani', 'MzQGeAil', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:03', 1),
(1270, 'Sefrioui', 'Latifa', 'latifa.sefrioui@eidia.ueuromed.org', NULL, 'latifa.sefrioui', 'QPwsfMoi', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:03', 1),
(1271, 'Benali', 'Meryem', 'meryem.benali@eidia.ueuromed.org', NULL, 'meryem.benali', 'Zq6wqRKr', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:03', 1),
(1272, 'Berrada', 'Bilal', 'bilal.berrada@eidia.ueuromed.org', NULL, 'bilal.berrada', 'WqX2z0ad', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:03', 1),
(1273, 'Zouhair', 'Sofia', 'sofia.zouhair@eidia.ueuromed.org', NULL, 'sofia.zouhair', 'fVczA5Vv', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:03', 1),
(1274, 'Naciri', 'Youssef', 'youssef.naciri2@eidia.ueuromed.org', NULL, 'youssef.naciri2', 'VpEBJP80', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:03', 1),
(1275, 'Bennani', 'Walid', 'walid.bennani@eidia.ueuromed.org', NULL, 'walid.bennani', 'KflNr5Oh', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:03', 1),
(1276, 'Zerrad', 'Hajar', 'hajar.zerrad@eidia.ueuromed.org', NULL, 'hajar.zerrad', 'Xgl7yFKz', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:03', 1),
(1277, 'Guedira', 'Samir', 'samir.guedira@eidia.ueuromed.org', NULL, 'samir.guedira', '5tcxDW82', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:03', 1),
(1278, 'Lahlou', 'Rachid', 'rachid.lahlou@eidia.ueuromed.org', NULL, 'rachid.lahlou', 'JjD6qqO7', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:03', 1),
(1279, 'Chaoui', 'Yassine', 'yassine.chaoui@eidia.ueuromed.org', NULL, 'yassine.chaoui', 'AJiXFYTM', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:03', 1),
(1280, 'Talbi', 'Yassine', 'yassine.talbi@eidia.ueuromed.org', NULL, 'yassine.talbi', 'QdYc8hHr', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:03', 1),
(1281, 'Raiss', 'Anas', 'anas.raiss@eidia.ueuromed.org', NULL, 'anas.raiss', '8nMzVM1a', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:03', 1),
(1282, 'Bennis', 'Najat', 'najat.bennis@eidia.ueuromed.org', NULL, 'najat.bennis', 'uS4Yq9yG', 'etudiant', 4, NULL, NULL, '2026-01-04 12:26:03', 1),
(1416, 'Bennis', NULL, 'Taha', NULL, 'taha.bennis2@eidia.ueuromed.org', '$2y$10$oOgIJVCKGtrYMqpHUhwFXuFJ32xp6t1SuBkfp/kO9hRarDAFU0ibi', 'etudiant', 3, NULL, NULL, '2026-01-04 12:40:57', 1),
(1417, 'Filali', NULL, 'Sarah', NULL, 'sarah.filali@eidia.ueuromed.org', '$2y$10$5cqwQkty8t8U7qNXuyc2uenLB0ZVJgHIJi16ZnVVVizYkUU7zLln.', 'etudiant', 3, NULL, NULL, '2026-01-04 12:40:57', 1),
(1418, 'Bennis', NULL, 'Brahim', NULL, 'brahim.bennis@eidia.ueuromed.org', '$2y$10$isBJSY36EfIr0MGd2NEO2.7HRmWq1gdb1JtIH.cWTdjp6DXsRRMlS', 'etudiant', 3, NULL, NULL, '2026-01-04 12:40:58', 1),
(1419, 'Zouhair', NULL, 'Hassan', NULL, 'hassan.zouhair@eidia.ueuromed.org', '$2y$10$9fe4x3Y0ZqmH1Pwiu.zyjeh4m5JVS2imQovCW1WOrfxXBCDqsn4SO', 'etudiant', 3, NULL, NULL, '2026-01-04 12:40:58', 1),
(1420, 'Tazi', NULL, 'Ahmed', NULL, 'ahmed.tazi2@eidia.ueuromed.org', '$2y$10$46S.p/y7N5oyxz5YVAKFo.b531ktuVGmmIzzGsKSAkOUa5yrqb6V6', 'etudiant', 3, NULL, NULL, '2026-01-04 12:40:58', 1),
(1421, 'Kadiri', NULL, 'Meryem', NULL, 'meryem.kadiri@eidia.ueuromed.org', '$2y$10$eOS.6Q/Kcf9tIQk.vK2TpuVSjx2spvkimID95UDeL7oqodvE5DmB2', 'etudiant', 3, NULL, NULL, '2026-01-04 12:40:58', 1),
(1423, 'Jettou', NULL, 'Manal', NULL, 'manal.jettou@eidia.ueuromed.org', '$2y$10$6pPnmxFvasCPFkLX8nOPgeOw6h93pjtqy8t9F8Ep4r6onC7x/HUye', 'etudiant', 3, NULL, NULL, '2026-01-04 12:40:58', 1),
(1424, 'Guedira', NULL, 'Sanae', NULL, 'sanae.guedira@eidia.ueuromed.org', '$2y$10$2dsiv2qb0gBd2/etfALgN.KjleSkNbOlOVcFe90t0BPlHbY1PDjgK', 'etudiant', 3, NULL, NULL, '2026-01-04 12:40:58', 1),
(1425, 'Idrissi', NULL, 'Mohamed', NULL, 'mohamed.idrissi@eidia.ueuromed.org', '$2y$10$wc4Qzs1UwtdQjVy6HXjx9un7drj/asLpL0rCWxSYHEaw5t9KudR/W', 'etudiant', 3, NULL, NULL, '2026-01-04 12:40:58', 1),
(1426, 'Talbi', NULL, 'Amine', NULL, 'amine.talbi@eidia.ueuromed.org', '$2y$10$E6VSTavC0qUhYPgPIOluw.5g/jqYfou1WtGr9Jj3Gouimq1a9Vwy2', 'etudiant', 3, NULL, NULL, '2026-01-04 12:40:58', 1),
(1430, 'Idrissi', NULL, 'Asmaa', NULL, 'asmaa.idrissi@eidia.ueuromed.org', '$2y$10$RNZg7EbRfCRt63D9ZM074OmfoLdzFqGPbuehKJ18DU4gDP3qsOpdO', 'etudiant', 3, NULL, NULL, '2026-01-04 12:40:59', 1),
(1431, 'Ouazzani', NULL, 'Lamia', NULL, 'lamia.ouazzani@eidia.ueuromed.org', '$2y$10$AdD79/T42G/lrKX/ajgMKus7fW8IODBv0Y6OKQ5GPjaJwcMQa3IGK', 'etudiant', 3, NULL, NULL, '2026-01-04 12:40:59', 1),
(1433, 'Tahiri', NULL, 'Salma', NULL, 'salma.tahiri@eidia.ueuromed.org', '$2y$10$WK1UMTWQJ1S4OaBB0gcxFe97zWukhCuuu4AEROLc8smIUHJQTMG52', 'etudiant', 3, NULL, NULL, '2026-01-04 12:40:59', 1),
(1434, 'Lahlou', NULL, 'Ghita', NULL, 'ghita.lahlou@eidia.ueuromed.org', '$2y$10$IApEBe3oP1JprgcHmcFEk.N.ZsQMmTIwAXa8u34pi5C0ERZf6GJTS', 'etudiant', 3, NULL, NULL, '2026-01-04 12:40:59', 1),
(1435, 'Ouazzani', NULL, 'Najat', NULL, 'najat.ouazzani@eidia.ueuromed.org', '$2y$10$vzz5kwpZBlVXcYVkJqWmaecHDNhhHFZTK3O5BWgl/x5FXqRNQF.dC', 'etudiant', 3, NULL, NULL, '2026-01-04 12:40:59', 1),
(1436, 'Naciri', NULL, 'Sofia', NULL, 'sofia.naciri@eidia.ueuromed.org', '$2y$10$cA4rmaH18VzTe73jV11iqe/WyyMvmYnb4VR0KiuiGhmOB9zFYhUq6', 'etudiant', 3, NULL, NULL, '2026-01-04 12:40:59', 1),
(1437, 'Tazi', NULL, 'Youssef', NULL, 'youssef.tazi@eidia.ueuromed.org', '$2y$10$E0FPFzwE7fbC4BFhk9ZWFeyhrbbWHGW.BW2ksZqOswp9e75Y3k3cK', 'etudiant', 3, NULL, NULL, '2026-01-04 12:40:59', 1),
(1438, 'Bennis', NULL, 'Mehdi', NULL, 'mehdi.bennis@eidia.ueuromed.org', '$2y$10$861S7FYhinKAc5lmts0JxOM7Zg7Gk9.yvWiTLErDQLizohS9Gixw2', 'etudiant', 3, NULL, NULL, '2026-01-04 12:40:59', 1),
(1439, 'Idrissi', NULL, 'Hamza', NULL, 'hamza.idrissi@eidia.ueuromed.org', '$2y$10$SV5Zkv2a85..iDend6TRUOCbKHVR1BLi0VG3fNszK.GuEXzQWJUse', 'etudiant', 3, NULL, NULL, '2026-01-04 12:40:59', 1),
(1440, 'Zouhair', NULL, 'Reda', NULL, 'reda.zouhair@eidia.ueuromed.org', '$2y$10$rxXIfYyJtccFQkg2exnalebG0RdZrfGtkKLjdG1yCvBPkRWx7YFpi', 'etudiant', 3, NULL, NULL, '2026-01-04 12:40:59', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `disponibilites`
--
ALTER TABLE `disponibilites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prof_id` (`prof_id`),
  ADD KEY `periode_id` (`periode_id`);

--
-- Indexes for table `disponibilites_profs`
--
ALTER TABLE `disponibilites_profs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prof_id` (`prof_id`);

--
-- Indexes for table `filieres`
--
ALTER TABLE `filieres`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `fk_filieres_coord` (`coordinateur_id`);

--
-- Indexes for table `jurys`
--
ALTER TABLE `jurys`
  ADD PRIMARY KEY (`id`),
  ADD KEY `soutenance_id` (`soutenance_id`),
  ADD KEY `prof_id` (`prof_id`);

--
-- Indexes for table `jury_soutenance`
--
ALTER TABLE `jury_soutenance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `projet_id` (`projet_id`),
  ADD KEY `prof1_id` (`prof1_id`),
  ADD KEY `prof2_id` (`prof2_id`),
  ADD KEY `prof3_id` (`prof3_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `projet_id` (`projet_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `periodes`
--
ALTER TABLE `periodes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `filiere_id` (`filiere_id`);

--
-- Indexes for table `projets`
--
ALTER TABLE `projets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `etudiant_id` (`etudiant_id`);

--
-- Indexes for table `rapports`
--
ALTER TABLE `rapports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `projet_id` (`projet_id`);

--
-- Indexes for table `salles`
--
ALTER TABLE `salles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `soutenances`
--
ALTER TABLE `soutenances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `projet_id` (`projet_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `login` (`login`),
  ADD KEY `filiere_id` (`filiere_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `disponibilites`
--
ALTER TABLE `disponibilites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `disponibilites_profs`
--
ALTER TABLE `disponibilites_profs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `filieres`
--
ALTER TABLE `filieres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `jurys`
--
ALTER TABLE `jurys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `jury_soutenance`
--
ALTER TABLE `jury_soutenance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `periodes`
--
ALTER TABLE `periodes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projets`
--
ALTER TABLE `projets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `rapports`
--
ALTER TABLE `rapports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `salles`
--
ALTER TABLE `salles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `soutenances`
--
ALTER TABLE `soutenances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1441;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `disponibilites`
--
ALTER TABLE `disponibilites`
  ADD CONSTRAINT `disponibilites_ibfk_1` FOREIGN KEY (`prof_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `disponibilites_ibfk_2` FOREIGN KEY (`periode_id`) REFERENCES `periodes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `disponibilites_profs`
--
ALTER TABLE `disponibilites_profs`
  ADD CONSTRAINT `disponibilites_profs_ibfk_1` FOREIGN KEY (`prof_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `filieres`
--
ALTER TABLE `filieres`
  ADD CONSTRAINT `fk_filieres_coord` FOREIGN KEY (`coordinateur_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `jurys`
--
ALTER TABLE `jurys`
  ADD CONSTRAINT `jurys_ibfk_1` FOREIGN KEY (`soutenance_id`) REFERENCES `soutenances` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `jurys_ibfk_2` FOREIGN KEY (`prof_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `jury_soutenance`
--
ALTER TABLE `jury_soutenance`
  ADD CONSTRAINT `jury_soutenance_ibfk_1` FOREIGN KEY (`projet_id`) REFERENCES `projets` (`id`),
  ADD CONSTRAINT `jury_soutenance_ibfk_2` FOREIGN KEY (`prof1_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `jury_soutenance_ibfk_3` FOREIGN KEY (`prof2_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `jury_soutenance_ibfk_4` FOREIGN KEY (`prof3_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`projet_id`) REFERENCES `projets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `periodes`
--
ALTER TABLE `periodes`
  ADD CONSTRAINT `periodes_ibfk_1` FOREIGN KEY (`filiere_id`) REFERENCES `filieres` (`id`);

--
-- Constraints for table `projets`
--
ALTER TABLE `projets`
  ADD CONSTRAINT `projets_ibfk_1` FOREIGN KEY (`etudiant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rapports`
--
ALTER TABLE `rapports`
  ADD CONSTRAINT `rapports_ibfk_1` FOREIGN KEY (`projet_id`) REFERENCES `projets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `soutenances`
--
ALTER TABLE `soutenances`
  ADD CONSTRAINT `soutenances_ibfk_1` FOREIGN KEY (`projet_id`) REFERENCES `projets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`filiere_id`) REFERENCES `filieres` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
