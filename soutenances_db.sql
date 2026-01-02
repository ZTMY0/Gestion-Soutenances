-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 02, 2026 at 09:39 PM
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
(6, 'FULL', 'Fullstack Web & Mobile', NULL, NULL, 60, '2026-01-02 18:11:49');

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
  `expediteur_id` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `lu` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `description` text DEFAULT NULL,
  `mots_cles` text DEFAULT NULL,
  `etudiant_id` int(11) NOT NULL,
  `binome_id` int(11) DEFAULT NULL,
  `encadrant_id` int(11) DEFAULT NULL,
  `filiere_id` int(11) NOT NULL,
  `annee_universitaire` varchar(9) NOT NULL,
  `statut` enum('inscrit','encadrant_affecte','valide_encadrant','planifie','soutenu') DEFAULT 'inscrit',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `binome_email` varchar(100) DEFAULT NULL,
  `rapport_path` varchar(255) DEFAULT NULL,
  `est_original` tinyint(1) DEFAULT 0,
  `technologies` varchar(255) DEFAULT NULL,
  `encadrant_pref1_id` int(11) DEFAULT NULL,
  `encadrant_pref2_id` int(11) DEFAULT NULL,
  `encadrant_pref3_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rapports`
--

CREATE TABLE `rapports` (
  `id` int(11) NOT NULL,
  `projet_id` int(11) NOT NULL,
  `version` int(11) DEFAULT 1,
  `chemin_fichier` varchar(255) NOT NULL,
  `commentaire` text DEFAULT NULL,
  `date_upload` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `salle_id` int(11) NOT NULL,
  `date_soutenance` datetime NOT NULL,
  `note_finale` decimal(4,2) DEFAULT NULL,
  `mention` varchar(50) DEFAULT NULL,
  `pv_signe` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `login` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('etudiant','prof','coordinateur','directeur','assistante') NOT NULL,
  `filiere_id` int(11) DEFAULT NULL,
  `specialite` varchar(255) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nom`, `email`, `login`, `password`, `role`, `filiere_id`, `specialite`, `telephone`, `created_at`) VALUES
(1, 'Oussama Berrada', 'oussama.berrada@eidia.ueuromed.org', 'oussama.berrada', '$2y$10$wN1cnP4RlsWjmTKtlQAGSOXcO8ycuQXFLr6P1ueNlKHJRzT7OeBN2', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:38'),
(2, 'Fatima Ouazzani', 'fatima.ouazzani@eidia.ueuromed.org', 'fatima.ouazzani', '$2y$10$SsAsqzn2M8g7Z8gNkQCgCeOlfNe8v3YDxyziH/VqKOWtLMQKr2bIS', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:38'),
(3, 'Imane Chaoui', 'imane.chaoui@eidia.ueuromed.org', 'imane.chaoui', '$2y$10$pfvTT1mVMcBW5eblywjmh.N3sNKgxFjsLyX.DNUuEcmhO6IXBWrca', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:38'),
(4, 'Sanae Kadiri', 'sanae.kadiri@eidia.ueuromed.org', 'sanae.kadiri', '$2y$10$x8uGJKhFUK/bufCqi87w2eqxxI6t1vNlvfnOXl8IrAWuVSWQP8lxO', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:38'),
(5, 'Reda Filali', 'reda.filali@eidia.ueuromed.org', 'reda.filali', '$2y$10$wi2LMhuSI2i2uPR21NiBTexzm3Krj.g2HvmKn4zQvtZRoOtVTc2.2', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:39'),
(6, 'Houda Benjelloun', 'houda.benjelloun2@eidia.ueuromed.org', 'houda.benjelloun2', '$2y$10$fOt6EXN2GsXdHy8i8kq0a.9VacQb4ou0QS.YtFpiOI6nGuf5O2Kom', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:39'),
(7, 'Fatima Benjelloun', 'fatima.benjelloun@eidia.ueuromed.org', 'fatima.benjelloun', '$2y$10$wMgCnsGMWawLBv1/5IOIbe7J6dxyW9Wne8.1qG.4QnYidTrZiR8VW', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:39'),
(8, 'Salma Tazi', 'salma.tazi@eidia.ueuromed.org', 'salma.tazi', '$2y$10$lgMrChyhByL9.XuFPVPFC..hTJzHNAAuAjbulm0fj0ixhMsmNVxGy', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:39'),
(9, 'Asmaa Mansouri', 'asmaa.mansouri2@eidia.ueuromed.org', 'asmaa.mansouri2', '$2y$10$4ftb4m8xnzzIx4lLuZTxB.C35BbhbJhF/cCu.vmNerRv/Xo1XwrEC', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:39'),
(10, 'Kenza Bennani', 'kenza.bennani@eidia.ueuromed.org', 'kenza.bennani', '$2y$10$7kBh6fsiiiVUUF/IpLxojuM54vlFyTBzAmGxY./e.0L2vjuQTi9ze', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:39'),
(11, 'Ismail Benali', 'ismail.benali@eidia.ueuromed.org', 'ismail.benali', '$2y$10$xFGQs128QX9EQezEH40ZduPB6BmsAwGG6ldxR0GXkvywEBuncHQa.', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:39'),
(12, 'Meryem Idrissi', 'meryem.idrissi2@eidia.ueuromed.org', 'meryem.idrissi2', '$2y$10$VWDkwx5O6cs3V0UnHXEO6e6dEhfMK9EikXFzHbFZDjV/7GOgTmyuS', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:39'),
(13, 'Manal Talbi', 'manal.talbi@eidia.ueuromed.org', 'manal.talbi', '$2y$10$8rSVegdoKflaOrNnSY4mWuxNkVLojZmIEEahEI33CJpjxMvTP7FG2', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:39'),
(14, 'Ismail Alami', 'ismail.alami@eidia.ueuromed.org', 'ismail.alami', '$2y$10$nlB5bxPbM9bqXnHWr5plS.1lP6BPNjXz34HhfUIlnfm3c4xxne3Ra', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:40'),
(15, 'Rim Zerrad', 'rim.zerrad@eidia.ueuromed.org', 'rim.zerrad', '$2y$10$OGnFnzsIWBqa6JbeaFw1wOjXEOUvKAdj8YdTk653c55ONPOmn2xj2', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:40'),
(16, 'Manal Raiss', 'manal.raiss@eidia.ueuromed.org', 'manal.raiss', '$2y$10$k9NE2BcIewXPIlpZ/hQDsuoUz6ZKbi7lJ/Z.hDnBrXE96TBQWq9Yu', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:40'),
(17, 'Mehdi Fassi', 'mehdi.fassi2@eidia.ueuromed.org', 'mehdi.fassi2', '$2y$10$eKM9z5gFIV.LqFEqDMdonuQ5XIqcgdWDOmZQnAPHK7j7jX2MA77Yi', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:40'),
(18, 'Youssef Mansouri', 'youssef.mansouri@eidia.ueuromed.org', 'youssef.mansouri', '$2y$10$dj5vnTLd.wd9EXT/HQV4pevRupvFlFWn69uQMpvQ.GADy/3v9ToUe', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:40'),
(19, 'Lamia Mansouri', 'lamia.mansouri@eidia.ueuromed.org', 'lamia.mansouri', '$2y$10$LZbTpIcXe1mmNd0AO/uOFecfkn34Z9Ld1ohYDf9oV87mYPUsyAi7e', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:40'),
(20, 'Ali Filali', 'ali.filali@eidia.ueuromed.org', 'ali.filali', '$2y$10$xZRfmEagZdpolyt.umI6AedIBweBlUSfN57OVRDCnjwOCthHaoRhK', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:40'),
(21, 'Zineb Mernissi', 'zineb.mernissi@eidia.ueuromed.org', 'zineb.mernissi', '$2y$10$lo7BDyF0CiVl/dL4thy02.X5/pwDJtwK0Xexyc2CEQ2ns05gw3FWm', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:40'),
(22, 'Ghita Chaoui', 'ghita.chaoui@eidia.ueuromed.org', 'ghita.chaoui', '$2y$10$c/zJLOOjs32Fv0OJHbNi1eqqZ1qd00ksIdKN5n9abgSQcZbNkwtA6', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:40'),
(23, 'Walid Idrissi', 'walid.idrissi@eidia.ueuromed.org', 'walid.idrissi', '$2y$10$rYBt4qrqDNMOkYRMGlOQd.wEnBfG6PdH5WyQhcNLDGmlIgQkqx6zO', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:41'),
(24, 'Sarah Mansouri', 'sarah.mansouri@eidia.ueuromed.org', 'sarah.mansouri', '$2y$10$SJmtOEcw8co5SCaqB8OUh.dW/McnbupIY/hdV9Y3Bf5ZQeV/eY1Dm', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:41'),
(25, 'Mehdi Alami', 'mehdi.alami@eidia.ueuromed.org', 'mehdi.alami', '$2y$10$vRiaVbNk88iJv8yynF7WX.M7j0ihRjUP.kiwDInCQV0Ampsnmapky', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:41'),
(26, 'Karim Benjelloun', 'karim.benjelloun@eidia.ueuromed.org', 'karim.benjelloun', '$2y$10$JJ.MtAbRvUxqIWeBivKmt.CbOVl76ZysnvTo.OXM2IP4l7AwI3Gha', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:41'),
(27, 'Nizar Tahiri', 'nizar.tahiri@eidia.ueuromed.org', 'nizar.tahiri', '$2y$10$pLRTpiHP.9Nf6/eTwP5mpOTnrzRYDtz81HaGqIQiZiFDzBJyj7FTC', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:41'),
(28, 'Saad Slaoui', 'saad.slaoui@eidia.ueuromed.org', 'saad.slaoui', '$2y$10$gkTXY8hTkTSFVZdTP6EOsuOWUGGayr7zU80gX.B3QlIGVphL9IKkm', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:41'),
(29, 'Anas Bennani', 'anas.bennani@eidia.ueuromed.org', 'anas.bennani', '$2y$10$PZhFV0HtaWNZVFF0xuW.J.fLS7f9j6OieWtD9Rbzw33fkTTibY4TC', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:41'),
(30, 'Kaoutar Chaoui', 'kaoutar.chaoui@eidia.ueuromed.org', 'kaoutar.chaoui', '$2y$10$noU0JW/59cdyhqvCMnMIGuMQpsZNRzi3vmeAM.xu6z/H2bJiVD2pm', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:41'),
(31, 'Houda Ouazzani', 'houda.ouazzani@eidia.ueuromed.org', 'houda.ouazzani', '$2y$10$fzH4bOGMhOYALYAXv672EeNPPYU65mKShHXRfKcisOVN1A3Coyvi2', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:41'),
(32, 'Sanae Kabbaj', 'sanae.kabbaj@eidia.ueuromed.org', 'sanae.kabbaj', '$2y$10$isyeqasr4FrXywtcWB9xzeNvv4l/kSxTX1dKf9WHO0szF.FTbyT62', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:42'),
(33, 'Kenza Raiss', 'kenza.raiss@eidia.ueuromed.org', 'kenza.raiss', '$2y$10$cXKtJdNVAHPxuYPZKedjPubAFejYF/mW/qN0bvZwcamesQtqHsmEy', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:42'),
(34, 'Rim Idrissi', 'rim.idrissi@eidia.ueuromed.org', 'rim.idrissi', '$2y$10$dlJLAWr5TzUxGkjQgvctzu4q6M7o2V5sutDA4WoNOiAjEgN2jE4xe', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:42'),
(35, 'Oussama Kabbaj', 'oussama.kabbaj@eidia.ueuromed.org', 'oussama.kabbaj', '$2y$10$MztUdUDlDZUHO2dEWugYjOZLtyXTXGstxdY58E25W8HsjL79WiVKu', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:42'),
(36, 'Bilal Mansouri', 'bilal.mansouri@eidia.ueuromed.org', 'bilal.mansouri', '$2y$10$nHKFTZ0oth5Mjz/DlYCOF.jiXQ8zuHH35yZeoKcbDJ1SREFkFdkJW', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:42'),
(37, 'Omar Daoudi', 'omar.daoudi@eidia.ueuromed.org', 'omar.daoudi', '$2y$10$W5dnq37VM6MfVdoTuptIZukOexADZJs121f/HI/OjphtEL6dQXbQW', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:42'),
(38, 'Salma Tazi', 'salma.tazi2@eidia.ueuromed.org', 'salma.tazi2', '$2y$10$rNSug0R9KUxcAEGjBAHdh.E2ldf2Mvws8Aiek74n1fMpgjuxf4BQu', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:42'),
(39, 'Bilal Alami', 'bilal.alami@eidia.ueuromed.org', 'bilal.alami', '$2y$10$Qlky87aN5vqfGdxfCOwwbeScJLcNy5Gcr4VDaKw8cKrbWgj67g9xm', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:42'),
(40, 'Asmaa Fassi', 'asmaa.fassi@eidia.ueuromed.org', 'asmaa.fassi', '$2y$10$B3jWUmcKxsGPBhxFWrDW8uCht/36L/sP91nqiF8ZAotlSWv23y8Je', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:42'),
(41, 'Manal Mansouri', 'manal.mansouri@eidia.ueuromed.org', 'manal.mansouri', '$2y$10$StHMKJ0KDFNYeFaV5Q7unuqP6oMYpFR7JGYC1prB9IKQYoti5kKfG', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:43'),
(42, 'Anas Naciri', 'anas.naciri@eidia.ueuromed.org', 'anas.naciri', '$2y$10$sHrB4sEAzFlGaHADq9ypwebhhgNdP1wRcEtrgp9js8Zh76X/26TJO', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:43'),
(43, 'Nizar Slaoui', 'nizar.slaoui@eidia.ueuromed.org', 'nizar.slaoui', '$2y$10$WvSuOxezagUTuPoNfFTGKOe.pokfcPNdjHJCSD4.K5ZlTCNahEmuG', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:43'),
(44, 'Reda Ouazzani', 'reda.ouazzani@eidia.ueuromed.org', 'reda.ouazzani', '$2y$10$csIsdwEhszpuv37jErHZGuvQWohros6t1A3G8jTelF3kPadiqrY/i', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:43'),
(45, 'Walid Guedira', 'walid.guedira@eidia.ueuromed.org', 'walid.guedira', '$2y$10$GZlTALNAo3IE3Qnbi.B/UufVw.XuIWFupoYXq7LkuXpeCkM6kpi.q', 'etudiant', 3, NULL, NULL, '2026-01-02 19:30:43'),
(46, 'Ihab Zaghdane', 'ihab.zaghdane@eidia.ueuromed.org', 'ihab.zaghdane', '$2y$10$flfyHJ..boa/aP/kD8Fk6eED2eJrfN68OFJROTIRJTH1xIGJa9U8m', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:48'),
(47, 'Abdelmoughit Mossaid', 'abdelmoughit.mossaid@eidia.ueuromed.org', 'abdelmoughit.mossaid', '$2y$10$GI0/hblc3JZEFG48kDYfkeRVzTRAiLut2q5w7gxtnlZSLeY.plB3u', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:48'),
(48, 'Nizar Zouizra', 'nizar.zouizra@eidia.ueuromed.org', 'nizar.zouizra', '$2y$10$U82PLmY99E3Xa0nmngwJEebtrSuU9tcqkQQYcDt1JWLkSlcmhL.v.', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:48'),
(49, 'Nourddine Kissiri', 'nourddine.kissiri@eidia.ueuromed.org', 'nourddine.kissiri', '$2y$10$c3DvUm2geSt.0kwo1M1Fau399ItVKKGMJhoeeAy6Xnoqm/jd3UhUC', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:49'),
(50, 'Hamza Raiss', 'hamza.raiss@eidia.ueuromed.org', 'hamza.raiss', '$2y$10$QW7m/RKJF8LjUC.3C4T4auSxNSnv8Bz3tG0FQkt52rwLgfn59/QG6', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:49'),
(51, 'Mehdi Kabbaj', 'mehdi.kabbaj@eidia.ueuromed.org', 'mehdi.kabbaj', '$2y$10$bFhdCNcc2SZ.SzQMLlerjugJMtALq2Qzbiq63QRLCaAcH91IWLFfK', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:49'),
(52, 'Salma Mernissi', 'salma.mernissi@eidia.ueuromed.org', 'salma.mernissi', '$2y$10$JkJNZrmrtZbPqz6dqLQb5u.XRNkdqrW5SPGzZldkx6xDSUhB5B.bK', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:49'),
(53, 'Lamia Tahiri', 'lamia.tahiri@eidia.ueuromed.org', 'lamia.tahiri', '$2y$10$hHNr6tbJrRaYdjsMYwmIkuwhugQ.kfRJjj7eJUKFd3bMscjydhI8y', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:49'),
(54, 'Sarah Fassi', 'sarah.fassi@eidia.ueuromed.org', 'sarah.fassi', '$2y$10$CG52mYS5v1VB2QghWPMCCeFXgvP/XTo9Cnz8YUVLHkiNyJWh.B1iG', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:49'),
(55, 'Salma Chraibi', 'salma.chraibi@eidia.ueuromed.org', 'salma.chraibi', '$2y$10$DM.1xgQtoWPmGCOdpQUeW.sH06TRivHCT2J8fghh6OKvSFuY56xj6', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:49'),
(56, 'Meryem Raiss', 'meryem.raiss@eidia.ueuromed.org', 'meryem.raiss', '$2y$10$/KGgdDc.j7re0z6Fx1Wy.OP8BqEN23WibVZm0AbHPsN0i6nLCk.lS', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:49'),
(57, 'Reda Kabbaj', 'reda.kabbaj@eidia.ueuromed.org', 'reda.kabbaj', '$2y$10$.s71mYJn40TEJywB0mdH7u7iSV/g0a/JWnZrW9Wd.p0zs/XJ8VPya', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:49'),
(58, 'Sarah El Amrani', 'sarah.el amrani@eidia.ueuromed.org', 'sarah.el amrani', '$2y$10$Nod65vHqGg.xcJFUNavYpOcxSHqbNRZcetSA2JyehRMhtgebXGeNq', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:50'),
(59, 'Imane Benjelloun', 'imane.benjelloun@eidia.ueuromed.org', 'imane.benjelloun', '$2y$10$Hn9W/HYWwIUr6rgCzu6CL.cuiUlN3/UePKeJLHoWPMlhesuW7ktey', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:50'),
(60, 'Taha El Amrani', 'taha.el amrani@eidia.ueuromed.org', 'taha.el amrani', '$2y$10$9Sbpf79lAxWf51Ed8TQhP.2cyVf.2N2w3jTcBw4a0yF1lXFJxhhue', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:50'),
(61, 'Kenza Mernissi', 'kenza.mernissi@eidia.ueuromed.org', 'kenza.mernissi', '$2y$10$qmOM47cGHyLd5Z3r.TONA.v3OSNfESH3BpZhACTsd0fd2jI03Tx4e', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:50'),
(62, 'Youssef Tazi', 'youssef.tazi@eidia.ueuromed.org', 'youssef.tazi', '$2y$10$dSy0x0mLiEMK/PYAcrSpHeqyRkUxTPTDqRqMn4BT/W9aj6J0IcadG', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:50'),
(63, 'Yassine Kadiri', 'yassine.kadiri@eidia.ueuromed.org', 'yassine.kadiri', '$2y$10$ivYnXAapEl.5WcazmofgieePMQxVEGpCRbEAEJUu.BWcPj0JwuzNy', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:50'),
(64, 'Sanae Naciri', 'sanae.naciri@eidia.ueuromed.org', 'sanae.naciri', '$2y$10$BOS6VkRv0CElb9JYiGM7ROQN3fRaZ.Dnh.GsuWJGjVwRHX8iLuiFC', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:50'),
(65, 'Mohamed Ouazzani', 'mohamed.ouazzani@eidia.ueuromed.org', 'mohamed.ouazzani', '$2y$10$2xBPQqqGewRCsIIt5d2aaO6E.pUFleibhUR8ya778abk15klW2W9q', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:50'),
(66, 'Omar Raiss', 'omar.raiss@eidia.ueuromed.org', 'omar.raiss', '$2y$10$1a.vRlNtKed2cikq1UZeE.2kAlE3xFgr7lzMzkzfzWtJH86qK0NqC', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:50'),
(67, 'Noura Benali', 'noura.benali@eidia.ueuromed.org', 'noura.benali', '$2y$10$/1HnHaqFG.TBJXynOisl5uKRwcLvseHJ6.HYbM5gGodZr1lbAAQA.', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:51'),
(68, 'Rania Daoudi', 'rania.daoudi@eidia.ueuromed.org', 'rania.daoudi', '$2y$10$FLDWgx2P/cjFCa.qgO8sMObnw1Mf0eDKcJmsuKBhmku8XdVYQPAlS', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:51'),
(69, 'Saad El Amrani', 'saad.el amrani@eidia.ueuromed.org', 'saad.el amrani', '$2y$10$XRbI8HBbeZjpxAsx3e5YPeMuLn/gdEl449kzqJFzNw8aZXWKsl4KK', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:51'),
(70, 'Youssef Berrada', 'youssef.berrada@eidia.ueuromed.org', 'youssef.berrada', '$2y$10$v/ZIbFfI3mQ/rrFRWTVgcuawIJRuI0qOQL1tzhARvDhYq32BxwBHC', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:51'),
(71, 'Noura Berrada', 'noura.berrada@eidia.ueuromed.org', 'noura.berrada', '$2y$10$8HKpH7qtQ0CNl64j3YfrQ.DGXb6fDRnax7feakHyMvxBJ68nxcM9.', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:51'),
(72, 'Kaoutar Berrada', 'kaoutar.berrada@eidia.ueuromed.org', 'kaoutar.berrada', '$2y$10$y6PSFWrDZNyUp4wjw6YcH.GaJQd5H9H4LoRlrhM11WAtHVZJLKuzi', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:51'),
(73, 'Driss Kabbaj', 'driss.kabbaj@eidia.ueuromed.org', 'driss.kabbaj', '$2y$10$U3x3Tgd4F6BvmARiVuYQDemNseC7pMZEzEigk3x9RtN22Xtob7ukK', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:51'),
(74, 'Taha El Amrani', 'taha.el amrani2@eidia.ueuromed.org', 'taha.el amrani2', '$2y$10$FKf0oFWMpzGWCzI6YkxS9enQ9bPTmGiy.trXiBYtd9x1RYYaLcyQi', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:51'),
(75, 'Rim Tazi', 'rim.tazi@eidia.ueuromed.org', 'rim.tazi', '$2y$10$Cb84AlxVo1LpYPS5pnCr7u.OPHzNdvPpyQ.zYwxNbgxZkb5H3tmRS', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:51'),
(76, 'Imane Mansouri', 'imane.mansouri@eidia.ueuromed.org', 'imane.mansouri', '$2y$10$.CWOTjYJ4caXoSOsxfnL6ONMjg9C4PmIuK7/fMgUoz1CcaDqLXQMK', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:52'),
(77, 'Hamza Tahiri', 'hamza.tahiri@eidia.ueuromed.org', 'hamza.tahiri', '$2y$10$wPyAG/f50qDLAnLLDE.o5eCJv7PxWbwLbfH7FiVeYGQJwcbmc2iai', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:52'),
(78, 'Noura Slaoui', 'noura.slaoui@eidia.ueuromed.org', 'noura.slaoui', '$2y$10$oRFpVTMal67lsoML.5qHdOy8QlIL6UNOFu.YlVfrE.UHl4e2GxiWi', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:52'),
(79, 'Asmaa Ouazzani', 'asmaa.ouazzani@eidia.ueuromed.org', 'asmaa.ouazzani', '$2y$10$akx9G2chqEiZYW62Xa.z1.rUu4jS.VyXyIm/QaLL2chciDR/YKIRC', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:52'),
(80, 'Saad Benali', 'saad.benali@eidia.ueuromed.org', 'saad.benali', '$2y$10$fvtu9a315MxXvMASec1jBuJXEA13Jvn43UOPuNqFoV0yQm0vgrSyO', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:52'),
(81, 'Sanae Filali', 'sanae.filali@eidia.ueuromed.org', 'sanae.filali', '$2y$10$myaOgdiFtuChyyU0DyVXa.RO/lJ4L.SFoekXDAGNDJl0CtEaZV006', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:52'),
(82, 'Taha Zerrad', 'taha.zerrad@eidia.ueuromed.org', 'taha.zerrad', '$2y$10$J0sqGAaa0afDoP1ebpvBdOBsuAovaMs4e9s5xYKWYnCgePOeK3wBe', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:52'),
(83, 'Anas Alami', 'anas.alami@eidia.ueuromed.org', 'anas.alami', '$2y$10$BwERPwBLjmurvN2c3V7Hy.sTwf0SMZlF26gQ5Y0TJ92oWE48CRsE.', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:52'),
(84, 'Meryem Kabbaj', 'meryem.kabbaj@eidia.ueuromed.org', 'meryem.kabbaj', '$2y$10$W.lwphmv.lKf02sXrceO5eHyIg1J/WtVixNpSlLSvOs1Bn/JKQ5Qq', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:52'),
(85, 'Meryem Fassi', 'meryem.fassi@eidia.ueuromed.org', 'meryem.fassi', '$2y$10$npIxqPsKvZtcPDDUbZE7o.Vy6ERTiwwmsWjidmrP2csJ1/94hSIY2', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:53'),
(86, 'Rim Mansouri', 'rim.mansouri@eidia.ueuromed.org', 'rim.mansouri', '$2y$10$VIMa1/wlWj6UUW..uCiyfeYV682e.XUxAXT0jB40u4HPYo2QSKqhK', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:53'),
(87, 'Hassan Talbi', 'hassan.talbi@eidia.ueuromed.org', 'hassan.talbi', '$2y$10$T30IcwKHmzy4ySwZ92B4bO.WA2odOJvoahHWyWL/4BJkbYZy2RKtu', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:53'),
(88, 'Ali Mernissi', 'ali.mernissi@eidia.ueuromed.org', 'ali.mernissi', '$2y$10$1mT/t4wtIviqLqAJCL7GdOcpMGN/u3G4rRn3naY9PIyGSR3owW9JK', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:53'),
(89, 'Youssef Slaoui', 'youssef.slaoui@eidia.ueuromed.org', 'youssef.slaoui', '$2y$10$c8VlSmvhaZotpUskqPvd8u0Or8ep4SPldHTshmlSglKklEpisDge2', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:53'),
(90, 'Houda Benjelloun', 'houda.benjelloun@eidia.ueuromed.org', 'houda.benjelloun', '$2y$10$bqO9rBJZNxhQQB4u.PTkV.59/C5xbhpmES0cQy0rrQROZOAfZ61FC', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:53'),
(91, 'Fatima Idrissi', 'fatima.idrissi@eidia.ueuromed.org', 'fatima.idrissi', '$2y$10$nH8FEO5twI9kn4dlIvFgJuCIYZ6UyKrhnIRMStQUpLfqDlk86jRo2', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:53'),
(92, 'Driss El Amrani', 'driss.el amrani@eidia.ueuromed.org', 'driss.el amrani', '$2y$10$hcONq4kUMyL7DhMID0rlOuuo4d7pynq0aomZM9Kh7TDFEeBx3vye6', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:53'),
(93, 'Meryem Kadiri', 'meryem.kadiri@eidia.ueuromed.org', 'meryem.kadiri', '$2y$10$bJuqeVISX6T9gEtzM1lwfuIH6XEe.S4on8HskZtjsuiHV8yYchzEq', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:53'),
(94, 'Karim Guedira', 'karim.guedira@eidia.ueuromed.org', 'karim.guedira', '$2y$10$WN88nO3ehLSBVDlIvcgR.ONHP7cFSopcqK1Pl/17q/j3EhtV/x.eO', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:54'),
(95, 'Yassine Chraibi', 'yassine.chraibi@eidia.ueuromed.org', 'yassine.chraibi', '$2y$10$oApvLohXN6B3rTT4DMHWvO7LBmzdAeOKxDslOebXMYUGDZLZCPz9y', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:54'),
(96, 'Yassine Bennani', 'yassine.bennani@eidia.ueuromed.org', 'yassine.bennani', '$2y$10$yJGyUfLwEod/NnqFK5gd7uUjUZ90upIsJ.Qp0Ulpjh.n4mvTAJXFm', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:54'),
(97, 'Omar Talbi', 'omar.talbi@eidia.ueuromed.org', 'omar.talbi', '$2y$10$y0L2jjaA9Ofp.5NQQiYll.TDvAqmC4fDzc0kOua48/fNw8dWyYqbG', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:54'),
(98, 'Aya Kadiri', 'aya.kadiri@eidia.ueuromed.org', 'aya.kadiri', '$2y$10$QCBcvJ0sB8.mn/6U2tILUOKtVS.rxz2eoUuNcZ2xGyxrSctW95CKi', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:54'),
(99, 'Ali Fassi', 'ali.fassi@eidia.ueuromed.org', 'ali.fassi', '$2y$10$q65oCEzlYKrND5MsRs5i8eYwrnsR4969SimluOM3sc9jBD0Ai1Gk6', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:54'),
(100, 'Aya Fassi', 'aya.fassi@eidia.ueuromed.org', 'aya.fassi', '$2y$10$9qFwAqti3XpjeLBDxZSJyO5GcRRucdsXNOt6XyQxGdb068OAU1qgm', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:54'),
(101, 'Fatima Mansouri', 'fatima.mansouri@eidia.ueuromed.org', 'fatima.mansouri', '$2y$10$QpQAZ9zKCWZQPtfjCllP.OzqUVpmmZai1ZKKXSdCyzfOv5RGRcHAG', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:54'),
(102, 'Mehdi Fassi', 'mehdi.fassi@eidia.ueuromed.org', 'mehdi.fassi', '$2y$10$ln9ojFoRxejHb3dvRVP3.OGFoUvttfgi3vcEDDD4rxZOmFKd/.dg2', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:54'),
(103, 'Noura Fassi', 'noura.fassi@eidia.ueuromed.org', 'noura.fassi', '$2y$10$SFKe57dcfVvpMr.oJMEOSOqc2bYJ0gwCsffO7E.GeVH7jCg9eWIFu', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:55'),
(104, 'Sarah Benjelloun', 'sarah.benjelloun@eidia.ueuromed.org', 'sarah.benjelloun', '$2y$10$w5jtufQyHnCtHuXTb2L5KuARWAyGf/59N5oFuAVIvRggsBP6uEnX.', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:55'),
(105, 'Rania Talbi', 'rania.talbi@eidia.ueuromed.org', 'rania.talbi', '$2y$10$gN6qUQ5nmBjYjVACO5HqOu9bZBm9ZvPL/Rk7OJugR02ZVWvE6EiVq', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:55'),
(106, 'Hajar Alami', 'hajar.alami@eidia.ueuromed.org', 'hajar.alami', '$2y$10$65PjI.n2drcOaQVdYsAuWuwSPBGA8nNPLZR5XrsOsCb32HFo6MuTC', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:55'),
(107, 'Youssef Berrada', 'youssef.berrada2@eidia.ueuromed.org', 'youssef.berrada2', '$2y$10$bXK42vAkjY8S/WucHfH2e.1UzVH4TbfG5Mn/Bq.qNKjLl8oKuSxza', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:55'),
(108, 'Yassine Daoudi', 'yassine.daoudi@eidia.ueuromed.org', 'yassine.daoudi', '$2y$10$huAHNnmxXekS4hV0JrgYl.fEOOtcflhivr2scmmlpiU7m.5MBiXla', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:55'),
(109, 'Yassine Slaoui', 'yassine.slaoui@eidia.ueuromed.org', 'yassine.slaoui', '$2y$10$9vs.84gQvv0AcEu8JzEMhuIf34JP2E7kuWXEKtEDXd.3xMdnaulZi', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:55'),
(110, 'Hajar Slaoui', 'hajar.slaoui@eidia.ueuromed.org', 'hajar.slaoui', '$2y$10$9RFaBuEWMRx44UifMzU.jOZAI2bSm9Y0lwT9f93M3qvB4g2AMNvmi', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:55'),
(111, 'Bilal Tahiri', 'bilal.tahiri@eidia.ueuromed.org', 'bilal.tahiri', '$2y$10$ekKOSSDT/P8uQPewB4v4IeLWCls7m2qHQaXKpRpJ87I9dZ3vjU/OC', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:56'),
(112, 'Imane Mansouri', 'imane.mansouri2@eidia.ueuromed.org', 'imane.mansouri2', '$2y$10$zLC6nHqU8CrNlHQQyjfuPOnZ0VVGs0Y6YXgo7T9lMZxzPhGA2Lmcy', 'etudiant', 2, NULL, NULL, '2026-01-02 19:30:56'),
(113, 'Mohamed Idrissi', 'mohamed.idrissi@eidia.ueuromed.org', 'mohamed.idrissi', '$2y$10$j2F2.ic3Klwlt3l/FlxadOiEQruUStWrzO8TUHc0wZ3VHo3KVDvju', 'etudiant', 6, NULL, NULL, '2026-01-02 19:31:03'),
(114, 'Fatima Filali', 'fatima.filali@eidia.ueuromed.org', 'fatima.filali', '$2y$10$jiXZU40OrcDEttxA82a1mei0Wi25gLPtixzufhY4pSOqjEc09f4NS', 'etudiant', 6, NULL, NULL, '2026-01-02 19:31:03'),
(115, 'Amine Benjelloun', 'amine.benjelloun@eidia.ueuromed.org', 'amine.benjelloun', '$2y$10$3xlHiuHfA.lbHSytjaCG7e4nTP6iLQsHwyIqb82sYWlSh6ADaTpeW', 'etudiant', 6, NULL, NULL, '2026-01-02 19:31:03'),
(116, 'Nizar Chaoui', 'nizar.chaoui@eidia.ueuromed.org', 'nizar.chaoui', '$2y$10$UGLxi8whgTPocxprddadeeV8i3Imgdiht8hLAF8vv3oVTNGaegvW2', 'etudiant', 6, NULL, NULL, '2026-01-02 19:31:03'),
(117, 'Karim Benjelloun', 'karim.benjelloun2@eidia.ueuromed.org', 'karim.benjelloun2', '$2y$10$X.9fwTNWDv9CYnWRyQo6P.CJrQz4JNC4fny33pkvOsvQv6wzO18Ou', 'etudiant', 6, NULL, NULL, '2026-01-02 19:31:03'),
(118, 'Houda Filali', 'houda.filali@eidia.ueuromed.org', 'houda.filali', '$2y$10$KX0qHs6XBifHpLoY2KFZWe2Ldw9.rkD4PRwJG.9fMG1U6LtSyNKTS', 'etudiant', 6, NULL, NULL, '2026-01-02 19:31:03'),
(119, 'Mehdi Kabbaj', 'mehdi.kabbaj2@eidia.ueuromed.org', 'mehdi.kabbaj2', '$2y$10$Dhx7w0qSAWUmGnokaJTda.oi1yGUr1MJuVIOgrmZHfVPtNjzZNOBa', 'etudiant', 6, NULL, NULL, '2026-01-02 19:31:04'),
(120, 'Nizar Raiss', 'nizar.raiss@eidia.ueuromed.org', 'nizar.raiss', '$2y$10$IieiyTK8j6WKQs9XOJBzi.6EDOXf6knbUYDPhGPuOqDAHwdAdJCaq', 'etudiant', 6, NULL, NULL, '2026-01-02 19:31:04'),
(121, 'Ismail Naciri', 'ismail.naciri@eidia.ueuromed.org', 'ismail.naciri', '$2y$10$YdOgYjOrbt4Jfn2hgK992OkQ5NPxZVDmvwbVJtJt.nIJRw4HZ6F4e', 'etudiant', 6, NULL, NULL, '2026-01-02 19:31:04'),
(122, 'Kaoutar Kabbaj', 'kaoutar.kabbaj@eidia.ueuromed.org', 'kaoutar.kabbaj', '$2y$10$lEZENcjwRfZPATxzqhquIeFaWnrrfEEAySRHPadizYoig76bTCG5S', 'etudiant', 6, NULL, NULL, '2026-01-02 19:31:04'),
(123, 'Asmaa El Amrani', 'asmaa.el amrani@eidia.ueuromed.org', 'asmaa.el amrani', '$2y$10$6/MFkzKvX928i.MGdkN7S.n524LweI1elhzOv599.7vgEx7HGMAV6', 'etudiant', 6, NULL, NULL, '2026-01-02 19:31:04'),
(124, 'Sarah Filali', 'sarah.filali@eidia.ueuromed.org', 'sarah.filali', '$2y$10$5DrgHsrpzUNxH0/e2ArfDusOQfy7.RE2iQ1HiHlLNAvgLX2sgOacK', 'etudiant', 6, NULL, NULL, '2026-01-02 19:31:04'),
(125, 'Mohamed Kabbaj', 'mohamed.kabbaj@eidia.ueuromed.org', 'mohamed.kabbaj', '$2y$10$7fvnGtVvKdOxLYMFoJOYLuvsWaseMsaeNpcGhpsTU4R/vWNFZ2etO', 'etudiant', 6, NULL, NULL, '2026-01-02 19:31:04'),
(126, 'Imane Guedira', 'imane.guedira@eidia.ueuromed.org', 'imane.guedira', '$2y$10$fvjbZ9FtaVQD5zLZVCS6Z.rIU7wuj7jTVkTjDzDS5Qv0tj50A9kra', 'etudiant', 6, NULL, NULL, '2026-01-02 19:31:04'),
(127, 'Taha Guedira', 'taha.guedira@eidia.ueuromed.org', 'taha.guedira', '$2y$10$foc/Psajxxtn5zMRmrwMgezR8BekFh0Vys2/snRG/iz0fGIUvipVe', 'etudiant', 6, NULL, NULL, '2026-01-02 19:31:04'),
(128, 'Nizar Guedira', 'nizar.guedira@eidia.ueuromed.org', 'nizar.guedira', '$2y$10$AvYDvOE3qhf9XNwsedGEluRusMsdtEn0qCyQz7cErIY98U17gg7PC', 'etudiant', 6, NULL, NULL, '2026-01-02 19:31:05'),
(129, 'Sofia Filali', 'sofia.filali2@eidia.ueuromed.org', 'sofia.filali2', '$2y$10$kHxMKKP7uvV86aVYehjrQOpE0iUu/5UVRxbbd4MtYFwhadawPcVPG', 'etudiant', 6, NULL, NULL, '2026-01-02 19:31:05'),
(130, 'Mehdi Filali', 'mehdi.filali@eidia.ueuromed.org', 'mehdi.filali', '$2y$10$uXlR11o84wIxe0lvsLKvVeyqvin.P0.wd1lm2RjwQBqAGLnrSWCYC', 'etudiant', 6, NULL, NULL, '2026-01-02 19:31:05'),
(131, 'Ghita Bennani', 'ghita.bennani@eidia.ueuromed.org', 'ghita.bennani', '$2y$10$.FGNBqkE/SAkkZn3tThBUeniL3V4FXqpxVmC4xqCm/0172aZNsxxG', 'etudiant', 6, NULL, NULL, '2026-01-02 19:31:05'),
(132, 'Anas Idrissi', 'anas.idrissi@eidia.ueuromed.org', 'anas.idrissi', '$2y$10$Dve4v2BBrhmWySZywNHkeOUZ95UqGdwxWU8OhL4JGk7cTcc8MDQ4m', 'etudiant', 6, NULL, NULL, '2026-01-02 19:31:05'),
(133, 'Asmaa Tazi', 'asmaa.tazi@eidia.ueuromed.org', 'asmaa.tazi', '$2y$10$3q/YVtsEc7pH0kTMoTvy.OmHVsskcMLy8EOh8sMA/h7k4LdgO4JG6', 'etudiant', 6, NULL, NULL, '2026-01-02 19:31:05'),
(134, 'Omar Benjelloun', 'omar.benjelloun@eidia.ueuromed.org', 'omar.benjelloun', '$2y$10$qMmMb36YsvgXjEt8tmREFuQX4b.LhJw3yz2Y0RI8QwLf30/EylKI2', 'etudiant', 6, NULL, NULL, '2026-01-02 19:31:05'),
(135, 'Walid Daoudi', 'walid.daoudi@eidia.ueuromed.org', 'walid.daoudi', '$2y$10$IQzvy8ab48hz9R5SBJLDOeCJFiKGF0gUDfrjcOEfu9fO1Rur7KQcS', 'etudiant', 6, NULL, NULL, '2026-01-02 19:31:05'),
(136, 'Ismail Tahiri', 'ismail.tahiri@eidia.ueuromed.org', 'ismail.tahiri', '$2y$10$yNfBIiIjh.Zel4XYGw7UuOuaDMe6JITN5ZxWW/2eEliPMCRUE9zjK', 'etudiant', 6, NULL, NULL, '2026-01-02 19:31:05'),
(137, 'Sarah Raiss', 'sarah.raiss@eidia.ueuromed.org', 'sarah.raiss', '$2y$10$enrivy/lUE4tSJ7NluIUA.esYMIyc3/Lvowqjnb7yPrcMhFhKcYr.', 'etudiant', 6, NULL, NULL, '2026-01-02 19:31:06'),
(138, 'Salma Raiss', 'salma.raiss@eidia.ueuromed.org', 'salma.raiss', '$2y$10$DmK..PtnobIkVU5N3OGAUeB2QfSkGOieLdbTtb2B9CQZaPtuqd7ca', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:11'),
(139, 'Kenza Kabbaj', 'kenza.kabbaj@eidia.ueuromed.org', 'kenza.kabbaj', '$2y$10$gigDjMvC3gjuWmYg33y8oepjBI6J7pv2bbM6irjzRLzitF3hEBJra', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:11'),
(140, 'Sofia Kadiri', 'sofia.kadiri@eidia.ueuromed.org', 'sofia.kadiri', '$2y$10$rfhH0Wbg1Djr8tqOZDXjCu/F1GWbJbbulZuoXMrsuSDbHh6JCBice', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:12'),
(141, 'Anas Chaoui', 'anas.chaoui@eidia.ueuromed.org', 'anas.chaoui', '$2y$10$vjp/TK6mXxInUuURh2VSXegUNn8jWyUUcxNuaxGLGPTUQrrTeXVsS', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:12'),
(142, 'Salma Mansouri', 'salma.mansouri@eidia.ueuromed.org', 'salma.mansouri', '$2y$10$uyJYbnK0P7WjmYQAT9b7fuD8qrDXotBxdDM.BTEndJHGASaW2ll8u', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:12'),
(143, 'Fatima Berrada', 'fatima.berrada@eidia.ueuromed.org', 'fatima.berrada', '$2y$10$PTNY/k.Q196E8ZxUnqZkNuEfSDKkB9pM.JKvv9rb/OR25ncyy3Hj.', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:12'),
(144, 'Salma Daoudi', 'salma.daoudi@eidia.ueuromed.org', 'salma.daoudi', '$2y$10$SjAy4S8kfRbNZG4P3ezoWefRbMM/LzFdj4BgKBS6UgwlsXjXGGB9e', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:12'),
(145, 'Taha Naciri', 'taha.naciri@eidia.ueuromed.org', 'taha.naciri', '$2y$10$xQucFMTZXo.dmbVVYQSNhuYyyONesoAeWT5JhL6ERLeACHaHKwQTa', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:12'),
(146, 'Hassan Berrada', 'hassan.berrada@eidia.ueuromed.org', 'hassan.berrada', '$2y$10$Y.y5IIw5BNlJYFahptTmleCcpbSPmXE.iwbSaLD7oVb3mJjjMJDJK', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:12'),
(147, 'Hassan Mansouri', 'hassan.mansouri@eidia.ueuromed.org', 'hassan.mansouri', '$2y$10$3/bhccoxnJ39N4H5vsvO9eST.kzuitvLTryK.nK9Z3Pa3Etu/bKiO', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:12'),
(148, 'Fatima Raiss', 'fatima.raiss@eidia.ueuromed.org', 'fatima.raiss', '$2y$10$sx.koTuTk/1b0JSSlgdUv.UDCgwgcIt06LrZt9U6P.O31ylTRcTFe', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:12'),
(149, 'Rim Mernissi', 'rim.mernissi@eidia.ueuromed.org', 'rim.mernissi', '$2y$10$w.xDuGCh5E8LIkUldIO46.OpaoQei7n1ij26.Ri7fxnChcQbKa5XO', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:13'),
(150, 'Aya Tazi', 'aya.tazi@eidia.ueuromed.org', 'aya.tazi', '$2y$10$OYpvf2XncfpRnBP3zcvzCeO2lEJHGs2SpFiqXI77YNNCCBn1aqnJK', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:13'),
(151, 'Driss Talbi', 'driss.talbi@eidia.ueuromed.org', 'driss.talbi', '$2y$10$6QWGTqZFDLSHZZhKZEiY5OKhpnczFCqyBZajzTXcM7JuR30knjklS', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:13'),
(152, 'Taha Naciri', 'taha.naciri2@eidia.ueuromed.org', 'taha.naciri2', '$2y$10$RIQUhcpSrmy58mz9p27a4uWEVZPBtRYqWFPQHAnGqjzN.pLx0DFN6', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:13'),
(153, 'Fatima Slaoui', 'fatima.slaoui@eidia.ueuromed.org', 'fatima.slaoui', '$2y$10$AWZPf0XWmUQkNwmgbOxIMOtVcWzw96Z8QglxITx84Zvv1bfTkmvYm', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:13'),
(154, 'Mohamed Mansouri', 'mohamed.mansouri@eidia.ueuromed.org', 'mohamed.mansouri', '$2y$10$gSr7FchvUlxavadvwGflqObdT7qQp1jF1QJFPF6NwPUwU.REaWOGe', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:13'),
(155, 'Imane Kadiri', 'imane.kadiri@eidia.ueuromed.org', 'imane.kadiri', '$2y$10$sBIii/p7uvj0vO7T420V9etuSo0RBXbt.wtO1sGJkp8RL/bIr2gpe', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:13'),
(156, 'Lamia Talbi', 'lamia.talbi@eidia.ueuromed.org', 'lamia.talbi', '$2y$10$SwchqcHmcA3NMIfG1ZWJHuXRMr3n1ucv66PA2xN0S2tZaJY08Y/Na', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:13'),
(157, 'Meryem Chaoui', 'meryem.chaoui@eidia.ueuromed.org', 'meryem.chaoui', '$2y$10$QXvh2cawIoX0oWW1thTJFOTuvpwSq6piO24mA8xrTt1P1k/Xw4tw6', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:13'),
(158, 'Noura Chaoui', 'noura.chaoui@eidia.ueuromed.org', 'noura.chaoui', '$2y$10$EqQe4sK7HgU.nCPFHwWOiu8cH5RsfukOjdcePkq0k5dgRts7MVoyS', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:14'),
(159, 'Taha Raiss', 'taha.raiss@eidia.ueuromed.org', 'taha.raiss', '$2y$10$djCUoqWKNjmcuEPQ7S5id.qPlr5ohzVtLE1quy9jZILhda2.xBJB6', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:14'),
(160, 'Ghita Mansouri', 'ghita.mansouri@eidia.ueuromed.org', 'ghita.mansouri', '$2y$10$b3wt/erPb./H0cjE6XzcsuzhBEytjqmBS0h46KGjii16mSrga1vgi', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:14'),
(161, 'Reda Benjelloun', 'reda.benjelloun@eidia.ueuromed.org', 'reda.benjelloun', '$2y$10$A58CpGhg5PQSUmGXpCzvSuLuRnrXSCsdulrLe3.Fvgu6NFWPANhNu', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:14'),
(162, 'Asmaa Berrada', 'asmaa.berrada@eidia.ueuromed.org', 'asmaa.berrada', '$2y$10$5OM9BR2HCO8N3CF5v1uuA.Uv93nOsYwvnK44DY4mz8OgTH/MbUi/y', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:14'),
(163, 'Kaoutar Tahiri', 'kaoutar.tahiri@eidia.ueuromed.org', 'kaoutar.tahiri', '$2y$10$.2TLUbQ99iIUo6PWR9Uc4u0Wco.vUBG8VFt/VtwcXeCzVsvjGYnWO', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:14'),
(164, 'Oussama Tazi', 'oussama.tazi@eidia.ueuromed.org', 'oussama.tazi', '$2y$10$DnaDcwGUMtdYzk/Bpphk9utWppMRfYy52Er.oE5FktcEcb0ETiGoa', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:14'),
(165, 'Anas Daoudi', 'anas.daoudi@eidia.ueuromed.org', 'anas.daoudi', '$2y$10$KRWMP3BNnSx8L6iUJ4ks9evLjmwp8bwqmcFzIUiw1ySFLWXHFbzRm', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:14'),
(166, 'Walid El Amrani', 'walid.el amrani@eidia.ueuromed.org', 'walid.el amrani', '$2y$10$fQgCtFlBhllW84pcVvmCQecwYc5ekoGF88SdH7P3N8CQln2itzzDK', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:14'),
(167, 'Meryem Idrissi', 'meryem.idrissi@eidia.ueuromed.org', 'meryem.idrissi', '$2y$10$XONKJtyI0gAHqJynHZcDCe39qVA80YEm0uT/tTfnaSXZZ3VInu3Te', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:15'),
(168, 'Houda Kadiri', 'houda.kadiri@eidia.ueuromed.org', 'houda.kadiri', '$2y$10$izZAOrpW9xC.2UhiW2O0POOiH2XLzFTzY16l9YhyD4UDW7q5WeOMO', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:15'),
(169, 'Houda Bennani', 'houda.bennani@eidia.ueuromed.org', 'houda.bennani', '$2y$10$j9BHE6J4OyngiGhQRhE4au01hF.Kan7vN5EHDSGoJQ9HDr5.FuZFq', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:15'),
(170, 'Ali Slaoui', 'ali.slaoui@eidia.ueuromed.org', 'ali.slaoui', '$2y$10$gO9U30IiCLp5SNL0g05G3uCubCZuTd1gtRCOr1fhT.tHrzuwOp/Zu', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:15'),
(171, 'Rania Daoudi', 'rania.daoudi2@eidia.ueuromed.org', 'rania.daoudi2', '$2y$10$TDS2HJvukCMko9krrsNRQek8L3MPZQN0iJ0feyJVKe.iEZPLHaLoy', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:15'),
(172, 'Meryem Ouazzani', 'meryem.ouazzani@eidia.ueuromed.org', 'meryem.ouazzani', '$2y$10$bDam.wjEQYhAOzodEqIEHuYAIhKN1Y1sDossIQYWh5SwXlEnQiAii', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:15'),
(173, 'Sarah Naciri', 'sarah.naciri@eidia.ueuromed.org', 'sarah.naciri', '$2y$10$.skfKnjdxz9yNlRUXAcufeYwPfs94ced7NlszhtLQAPXFstyM56rq', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:15'),
(174, 'Oussama Guedira', 'oussama.guedira@eidia.ueuromed.org', 'oussama.guedira', '$2y$10$/IOOqKfnLYlvgvdKijn3L.rH1ztSp.KafjwpTqKWtKmAuhOaeQNfu', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:15'),
(175, 'Karim Mansouri', 'karim.mansouri@eidia.ueuromed.org', 'karim.mansouri', '$2y$10$6XY7m9aEiyYSuprL83MpZOpyt8RXP4cIYyBCOo/E4ix27DtecK7k.', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:15'),
(176, 'Reda Benali', 'reda.benali@eidia.ueuromed.org', 'reda.benali', '$2y$10$XqRCztOxKLfm.DPOR/OWcuk5yIvs.g8dUNy8dMXXvYiWTaaBqJxMW', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:16'),
(177, 'Aya Slaoui', 'aya.slaoui@eidia.ueuromed.org', 'aya.slaoui', '$2y$10$z5ml5v8rP2QVv3/ccRM3Ae33LLwWLHy8dZEWz.YA6I7L6D12Jci/a', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:16'),
(178, 'Omar Slaoui', 'omar.slaoui@eidia.ueuromed.org', 'omar.slaoui', '$2y$10$QDzCwAqkQYk59mODx6SFrOPbcUj9jrsoN6B0DQ0KeVSqaY2IG/CVy', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:16'),
(179, 'Reda Chraibi', 'reda.chraibi@eidia.ueuromed.org', 'reda.chraibi', '$2y$10$kDMduryuZPzw3KNG55cHRuMoIqwA9eZq8XuHzcHfVmQmzpI3R.xJm', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:16'),
(180, 'Noura Tahiri', 'noura.tahiri@eidia.ueuromed.org', 'noura.tahiri', '$2y$10$Yhqa9dFioYtf4aO4bvWOAuf/aC4FB75uyxm1V7zVv98PjVxBhRAzC', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:16'),
(181, 'Asmaa Mansouri', 'asmaa.mansouri@eidia.ueuromed.org', 'asmaa.mansouri', '$2y$10$B1LveFa4THst5.JKOrUd.eFe8bwZhR8CB1eszYhDNkYBfG1R.P4J6', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:16'),
(182, 'Ali Zerrad', 'ali.zerrad@eidia.ueuromed.org', 'ali.zerrad', '$2y$10$SYfhumu/06vGai2.dcGQ7.eko/452/rXDQsFkl9RSTwbh101UzrBi', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:16'),
(183, 'Rim Guedira', 'rim.guedira@eidia.ueuromed.org', 'rim.guedira', '$2y$10$XdOHNMJo4GXMnZbDx0CWaOnHxaAXrlDNozSz2q8s7AtID4xHFMaUS', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:16'),
(184, 'Kenza Daoudi', 'kenza.daoudi@eidia.ueuromed.org', 'kenza.daoudi', '$2y$10$kHeVbSzibuz7kMpbeDcKWe9nKG8/x1tt2yyCEsOmIQCzmneR3RIYC', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:16'),
(185, 'Amine Mernissi', 'amine.mernissi@eidia.ueuromed.org', 'amine.mernissi', '$2y$10$xiVftpvzZHm3lKGT5wEKvOdeXD4Un466y2zql4Oi5ogm0qNWnvPUm', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:17'),
(186, 'Hamza Alami', 'hamza.alami@eidia.ueuromed.org', 'hamza.alami', '$2y$10$WxgpUsoCELxUKzko4.NyOuVj/sVsj2Hx9n2qBS1pZe3GnrKxKgrVm', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:17'),
(187, 'Anas Chaoui', 'anas.chaoui2@eidia.ueuromed.org', 'anas.chaoui2', '$2y$10$wPvHr3gx02Fi/xNMxAWVlerhRT6f0hD9kt3QCyZ2q/3eVdLZM4GJi', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:17'),
(188, 'Walid Slaoui', 'walid.slaoui@eidia.ueuromed.org', 'walid.slaoui', '$2y$10$pUzt1kjgra3hLWF21kIRJ.zQarosApjCGeIC3RB8f5KxHwD5bPbs2', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:17'),
(189, 'Omar El Amrani', 'omar.el amrani@eidia.ueuromed.org', 'omar.el amrani', '$2y$10$9Tbwjh8lT1kuPHtG3DUYGOIqI0us4vqDFpiFB3fIjmuyLR7aciAFm', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:17'),
(190, 'Amine Ouazzani', 'amine.ouazzani@eidia.ueuromed.org', 'amine.ouazzani', '$2y$10$DOaV.nNxkckhtQAZ/8qeJux9oUDdEZG3C.yZrewRjAQmB.c7UEmdO', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:17'),
(191, 'Karim Bennani', 'karim.bennani@eidia.ueuromed.org', 'karim.bennani', '$2y$10$PNt9Nt8RLXfgFzAkqVS0LObQ02sjxMvaxZMUEum/LoGNav5IqsqhW', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:17'),
(192, 'Driss Talbi', 'driss.talbi2@eidia.ueuromed.org', 'driss.talbi2', '$2y$10$wSdRJd86QUQk2XFUrGTzuu5zgrr2z03IQnlDoU8Y4mUGtxxzZaiPa', 'etudiant', 4, NULL, NULL, '2026-01-02 19:31:17'),
(193, 'Youssef Kadiri', 'youssef.kadiri@eidia.ueuromed.org', 'youssef.kadiri', '$2y$10$zKvw4sRYa6MihI7uhtitC.jpuG8ZlvsVa/CbK96RMB7767i8g1X1O', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:26'),
(194, 'Walid Tahiri', 'walid.tahiri@eidia.ueuromed.org', 'walid.tahiri', '$2y$10$zqhrgyCPahiJC4ad9WEZ9uAmvkkYe0Yo163CIcLXUpE/VyBrYRJUy', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:26'),
(195, 'Sofia Ouazzani', 'sofia.ouazzani@eidia.ueuromed.org', 'sofia.ouazzani', '$2y$10$xCs2pSN5pXoklaAYs.SGe.coSaNdGiJ1VuEMtrI0kzGb9.eo3Xx7C', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:26'),
(196, 'Houda Bennani', 'houda.bennani2@eidia.ueuromed.org', 'houda.bennani2', '$2y$10$rqEYPB9.gtE6lv0S7k0o/uiGzhgQw5E4XNXXYxFfMMhEg3hhUhMDu', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:26'),
(197, 'Taha Chraibi', 'taha.chraibi@eidia.ueuromed.org', 'taha.chraibi', '$2y$10$LixhtDAk08Pcr0xBblAcge3qEw/gsovzWuyfzrbhVv7IQyZd7LsSe', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:26'),
(198, 'Reda Kadiri', 'reda.kadiri@eidia.ueuromed.org', 'reda.kadiri', '$2y$10$lkC7yG8aLPIN/qXQYCwGvOZPbynZfuSX8mj2MGwksZfhvP2iUF9dG', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:26'),
(199, 'Kenza Naciri', 'kenza.naciri@eidia.ueuromed.org', 'kenza.naciri', '$2y$10$0FggZzPneePDZUycR1qble.DnofFq6lgVtkz809H5Y3cjGpEwkZe2', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:26'),
(200, 'Fatima Tazi', 'fatima.tazi@eidia.ueuromed.org', 'fatima.tazi', '$2y$10$j6WySWr87fA3h9CbSgTD4urXtohDpxByfu7mzJ6xf5L6xFsGv5Ws2', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:26'),
(201, 'Noura Raiss', 'noura.raiss@eidia.ueuromed.org', 'noura.raiss', '$2y$10$n.VyJm/kleuhbnNnmD4J1OCTt1qevE5AQYcbRjiCuaHL7UMHCGMoi', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:26'),
(202, 'Ismail Benjelloun', 'ismail.benjelloun@eidia.ueuromed.org', 'ismail.benjelloun', '$2y$10$64BD047xskcnVetAT6qbNudqxrEx1/3PxYvCOd2yDrX3wpT3CQ4cO', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:27'),
(203, 'Rania Chraibi', 'rania.chraibi@eidia.ueuromed.org', 'rania.chraibi', '$2y$10$mokhh67DHcRirEz7KRqa8uvpNHKQ4srSsu3PJxKAwWUUsFD0gMqBK', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:27'),
(204, 'Kaoutar Tazi', 'kaoutar.tazi@eidia.ueuromed.org', 'kaoutar.tazi', '$2y$10$O1ud21GRtq/ZM3X53eB.oO3YC3himaDY4/VVaeIH5y3GfJo0ssSJK', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:27'),
(205, 'Kenza Benali', 'kenza.benali@eidia.ueuromed.org', 'kenza.benali', '$2y$10$gFD1dLctMocgSSRy/xRvue/9IGoDpPALWwydOY9bePzkClykzd2xm', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:27'),
(206, 'Rania Tahiri', 'rania.tahiri@eidia.ueuromed.org', 'rania.tahiri', '$2y$10$Br/MQLhsBm4nP/Y5/IQbv.IwR.EeMJAz/KqBaFC.utV1xfUPMp8Mu', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:27'),
(207, 'Ismail Fassi', 'ismail.fassi@eidia.ueuromed.org', 'ismail.fassi', '$2y$10$hDm5SCDlFAA4kZrVuve55uL0AbgLrN8LubMgt7CRDjHTqPVTeNZwO', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:27'),
(208, 'Ismail Kadiri', 'ismail.kadiri@eidia.ueuromed.org', 'ismail.kadiri', '$2y$10$LbqBruH7sLFM.4viV0ceTOC8z/RqKDtnF6M0VtDSgvHc6L0xnkVk2', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:27'),
(209, 'Omar Kadiri', 'omar.kadiri@eidia.ueuromed.org', 'omar.kadiri', '$2y$10$LgAc4ZmiJqDCryspXo48hOsb1vHEW1pN2wMNUP31M2yc/DkqVI6Mm', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:27'),
(210, 'Taha Alami', 'taha.alami@eidia.ueuromed.org', 'taha.alami', '$2y$10$QHnmfg.w72grMM8gMCKs.eGwTfh.6eFDnIbFt3BPZLCTXYdofjQnK', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:27'),
(211, 'Lamia Ouazzani', 'lamia.ouazzani@eidia.ueuromed.org', 'lamia.ouazzani', '$2y$10$OIeSPSnJ/VwD9feUfox4CuX2NA1dz5yCutTb1EinAhE4zjPuCJhMq', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:28'),
(212, 'Yassine Alami', 'yassine.alami@eidia.ueuromed.org', 'yassine.alami', '$2y$10$HxUJIHSflmR6JjE3bD9dbOFNAe1XwOJs4ZZpIi2Dq3csXmvuqd8hy', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:28'),
(213, 'Yassine Filali', 'yassine.filali@eidia.ueuromed.org', 'yassine.filali', '$2y$10$8SQyi/thRZSitatqeWqR6.3YOsE/sWA3uRpkGGVUCsCFfin7iJwd.', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:28'),
(214, 'Sofia Filali', 'sofia.filali@eidia.ueuromed.org', 'sofia.filali', '$2y$10$XwGHqn1E.yEhu1QvQhB/DOUorbWU1/H6yGqOG5XIo5sXqLeLdvDG2', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:28'),
(215, 'Sofia Talbi', 'sofia.talbi@eidia.ueuromed.org', 'sofia.talbi', '$2y$10$SS/OBdHwyPFuueQCU5JyOOMIcPvL3nhxzirCCspLpcepSR.z4bThe', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:28'),
(216, 'Ghita Tazi', 'ghita.tazi@eidia.ueuromed.org', 'ghita.tazi', '$2y$10$1X2GweJcyCBwd6OrCAbb5OL/MLjfuVO0xovKXqYgpNwNOf2t1hS5.', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:28'),
(217, 'Sarah Mansouri', 'sarah.mansouri2@eidia.ueuromed.org', 'sarah.mansouri2', '$2y$10$qXmUhnCdbbfjesrrDaZGhug1jPxJBtjEOR2Or.8bwfANZVG7SIKh2', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:28'),
(218, 'Amine Benali', 'amine.benali@eidia.ueuromed.org', 'amine.benali', '$2y$10$mnUMrfQE.M7Ig/jyjZQ1yuMX.SrnO10hIu6Ywk2vswQ.KqiG93qjG', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:28'),
(219, 'Sanae Bennani', 'sanae.bennani@eidia.ueuromed.org', 'sanae.bennani', '$2y$10$/9Ar4jIMOWpXScx27mAyZ.kisfrD5hTGWLYUh7xHJOZbZJ3z0ENIS', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:28'),
(220, 'Hamza Slaoui', 'hamza.slaoui@eidia.ueuromed.org', 'hamza.slaoui', '$2y$10$QrlBhSY7/esfPtvZp10E7uGxEAZiuPG/J0oEN3qkeVQlRTPLGTivS', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:29'),
(221, 'Lamia Mernissi', 'lamia.mernissi@eidia.ueuromed.org', 'lamia.mernissi', '$2y$10$xww3fs4M4UtH6ITv70CnAuR2L52Gj7BWh8W4yGqGEjNh7n51jXlIG', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:29'),
(222, 'Youssef Idrissi', 'youssef.idrissi@eidia.ueuromed.org', 'youssef.idrissi', '$2y$10$M.tUh4YpdlVRZc4Ff7tuy.7KValls0UmNOxu8kPIorWu.hDKWDb26', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:29'),
(223, 'Anas Guedira', 'anas.guedira@eidia.ueuromed.org', 'anas.guedira', '$2y$10$orkn7XLt8a8gfH3B6rm4quVcu42zksM5UveY83hgzwue.9STfe.m.', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:29'),
(224, 'Rania Raiss', 'rania.raiss@eidia.ueuromed.org', 'rania.raiss', '$2y$10$Lg0ZJZBC3RVmAqnX7CLCaebvsdFJfi2xWt74DuBo9wQWygPN2U4Py', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:29'),
(225, 'Ismail Fassi', 'ismail.fassi2@eidia.ueuromed.org', 'ismail.fassi2', '$2y$10$j0O5.vDVx4Y4YNaRr2BVO.yhmEQKYb25/rQdX7x9iKcjbeQ8u73sq', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:29'),
(226, 'Mehdi Talbi', 'mehdi.talbi@eidia.ueuromed.org', 'mehdi.talbi', '$2y$10$fJNxBSo3cWYZMcFuVAs.zejmcW51cFUupRbQLTAAH5EohsYL3b1Ly', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:29'),
(227, 'Walid Chaoui', 'walid.chaoui@eidia.ueuromed.org', 'walid.chaoui', '$2y$10$bIZmbbKno/ULOFT7pifB4OA.8kjfibSIFw2qZUQ94bLJPf5fAMkiq', 'etudiant', 5, NULL, NULL, '2026-01-02 19:31:29'),
(228, 'Pr. Khalid Berrada', 'khalid.berrada@prof.ueuromed.org', 'p.berrada', '$2y$10$koNN7dO3FIyTS2hPboUi1u8jAIytVuN7Ma8YTEARscNW5DGIM48f.', 'prof', 5, 'BIGDATA,CYBER', NULL, '2026-01-02 19:31:41'),
(229, 'Pr. Karima Daoudi', 'karima.daoudi@prof.ueuromed.org', 'p.daoudi', '$2y$10$n2SXGqPOgBzQCQhUtCa1j.2HfWbog9rKptPRnNgk2eHBnYtobBNUK', 'prof', 3, 'AI', NULL, '2026-01-02 19:31:41'),
(230, 'Pr. Najat Mansouri', 'najat.mansouri@prof.ueuromed.org', 'p.mansouri', '$2y$10$6Jl90.5icSkeyE82LVkP1edpG3fuwpCn1YrxJlibDS5ZuwRtQ7lrm', 'prof', 6, 'ROBO', NULL, '2026-01-02 19:31:41'),
(231, 'Pr. Omar Sefrioui', 'omar.sefrioui@prof.ueuromed.org', 'p.sefrioui4', '$2y$10$Tlw5tjSensVmZLN7OAMsneeFdqoJo/wKBKZVeANu2EhWDt1yGxf6u', 'prof', 4, 'CYBER', NULL, '2026-01-02 19:31:41'),
(232, 'Pr. Hassan Mansouri', 'hassan.mansouri@prof.ueuromed.org', 'p.mansouri2', '$2y$10$RNN2h.gRV63BLKsH9YVX2unwXocXlX4eV22Zf9SUd1A6qVgFdR2re', 'prof', 2, 'BIGDATA', NULL, '2026-01-02 19:31:42'),
(233, 'Pr. Ahmed Kadiri', 'ahmed.kadiri@prof.ueuromed.org', 'p.kadiri', '$2y$10$Q2cS/BR4M.FbIWBxNDd3euWRTk2cmwXSpMLTe72kbUhTGFe1Jdoha', 'prof', 6, 'CYBER,ROBO', NULL, '2026-01-02 19:31:42'),
(234, 'Pr. Siham Sefrioui', 'siham.sefrioui@prof.ueuromed.org', 'p.sefrioui2', '$2y$10$kwxmSZznjwCUAGZtseTN2e/FB4SZTehSg6k5g5JkH9NCvsMh/c6ru', 'prof', 5, 'CYBER,BIGDATA', NULL, '2026-01-02 19:31:42'),
(235, 'Pr. Najat Tahiri', 'najat.tahiri@prof.ueuromed.org', 'p.tahiri', '$2y$10$xnapa1c.7uF/BvRNftnhBulXNDOrMMKRtOaaRn0eK8JXaaITHYtke', 'prof', 3, 'ROBO,FULL', NULL, '2026-01-02 19:31:42'),
(236, 'Pr. Redouane Kabbaj', 'redouane.kabbaj@prof.ueuromed.org', 'p.kabbaj', '$2y$10$a.S3hQrriHdqmpXRZIY2IeLZEEC8m9A5KI2p0sQzXnmBbXf3RWEcW', 'prof', 6, 'AI,ROBO', NULL, '2026-01-02 19:31:42'),
(237, 'Pr. Brahim Chraibi', 'brahim.chraibi@prof.ueuromed.org', 'p.chraibi', '$2y$10$ISC.ctZYDs5mofqZ3SlHZO5JbvkzUkYkQ6T9.wxKus0pVwxpfDDzu', 'prof', 6, 'CYBER', NULL, '2026-01-02 19:31:42'),
(238, 'Pr. Driss Zouhair', 'driss.zouhair@prof.ueuromed.org', 'p.zouhair', '$2y$10$Gp8gw54NZkyy833y1mJyIeRFQ0IGfXBXtQZyjkl9sJkoofl98.1/e', 'prof', 6, 'BIGDATA', NULL, '2026-01-02 19:31:42'),
(239, 'Pr. Layla Alami', 'layla.alami@prof.ueuromed.org', 'p.alami', '$2y$10$oVzSvRIB6Y/wrU7qygdwuObyAlmw5/YJNftvmF/4U5oKvUnYOxYYW', 'prof', 5, 'BIGDATA', NULL, '2026-01-02 19:31:42'),
(240, 'Pr. Hassan Tazi', 'hassan.tazi@prof.ueuromed.org', 'p.tazi', '$2y$10$.eNFbrUabZCOKJEDHaQS2uLeegsibbYRfIbwbDVIplKN4zXqxUAOW', 'prof', 4, 'FULL', NULL, '2026-01-02 19:31:42'),
(241, 'Pr. Fatim-Zahra Idrissi', 'fatim-zahra.idrissi@prof.ueuromed.org', 'p.idrissi', '$2y$10$jVJSIbnjQbhqx6XbQPFCMeKq8PcOcg8p7H6eEu4.9AGPzXyC6cOe2', 'prof', 2, 'BIGDATA', NULL, '2026-01-02 19:31:43'),
(242, 'Pr. Siham Tahiri', 'siham.tahiri@prof.ueuromed.org', 'p.tahiri2', '$2y$10$5kBeOLAN.D593dYceGV5MewPsSneuYh27MUsb19oJ7himyga04mZe', 'prof', 3, 'FULL', NULL, '2026-01-02 19:31:43'),
(243, 'Pr. Brahim El Amrani', 'brahim.el amrani@prof.ueuromed.org', 'p.el amrani', '$2y$10$94CDzDEGxO2tzmmyq1jpg.FkYpnZ7WsU6RWPH.uLbxIV7f5Etcnzu', 'prof', 3, 'FULL', NULL, '2026-01-02 19:31:43'),
(244, 'Pr. Najat Tazi', 'najat.tazi@prof.ueuromed.org', 'p.tazi2', '$2y$10$K2TcRNsZehZk3gRIYSxaqeTY7JYnEu2om8eU/7AqR6UPgo2SKm1be', 'prof', 3, 'ROBO', NULL, '2026-01-02 19:31:43'),
(245, 'Pr. Yassine Sefrioui', 'yassine.sefrioui@prof.ueuromed.org', 'p.sefrioui3', '$2y$10$X1IzCqtQMz7ytD4LL6GVUO/PqIMGNaw.g0/271ez1BQ/v9y34x7VC', 'prof', 4, 'ROBO', NULL, '2026-01-02 19:31:43'),
(246, 'Pr. Khalid Kabbaj', 'khalid.kabbaj@prof.ueuromed.org', 'p.kabbaj2', '$2y$10$ls7MwdVeQfFCcr6la9oYQ.RPWcI4Ifpl5pdL77dplb2A/5avfLuGy', 'prof', 3, 'ROBO', NULL, '2026-01-02 19:31:43'),
(247, 'Pr. Yassine Idrissi', 'yassine.idrissi@prof.ueuromed.org', 'p.idrissi2', '$2y$10$2V4if5XVw7RkUPcgM53hgOwRTsRIfxvM/hfvZV7ubJO3RThZiLxhy', 'prof', 2, 'AI,FULL', NULL, '2026-01-02 19:31:43'),
(248, 'Pr. Samir Zouhair', 'samir.zouhair@prof.ueuromed.org', 'p.zouhair2', '$2y$10$qO4By.nVBD91ma7myLI1yO7luI8lMuPEuNY7jk4vyJqATdI4bJQ9m', 'prof', 5, 'AI', NULL, '2026-01-02 19:31:43'),
(250, 'Pr. Layla El Amrani', 'layla.el amrani@prof.ueuromed.org', 'p.el amrani2', '$2y$10$0SPTcSVRXQz1.Nb3VsHRJe/JHoeLfxYTR2y.gRjvmRvqSDiuGE7gS', 'prof', 4, 'BIGDATA', NULL, '2026-01-02 19:31:44'),
(251, 'Pr. Latifa Idrissi', 'latifa.idrissi@prof.ueuromed.org', 'p.idrissi3', '$2y$10$9hzlUSYY5iBGL3wTK5HcFeQ7Mp7Wo26jZSA.kIWSqZom9tXePInK.', 'prof', 5, 'BIGDATA,FULL', NULL, '2026-01-02 19:31:44'),
(252, 'Pr. Fatim-Zahra Tazi', 'fatim-zahra.tazi@prof.ueuromed.org', 'p.tazi3', '$2y$10$kQuYV0eQNC63s8XXO0UYnehoE0O0sL3nAm.rY/Gov3QYG.ARjzWz6', 'prof', 5, 'CYBER,FULL', NULL, '2026-01-02 19:31:44'),
(253, 'Ihab Admin', 'ihab@admin.com', 'ihab.admin', '$2y$10$MgwDHR1eL6d8TsN7Nl/.Y.o255.dU1PsgfRRQRiXXi.xut4x/JhKW', 'coordinateur', NULL, NULL, NULL, '2026-01-02 19:45:58'),
(256, 'Mme Assistante', 'assistante@uemf.org', 'assistante.admin', '$2y$10$W83FhbK88Djqssz9LE9TBOEal8eHc0J7cGyxOLKpI1AInDkE1hhuG', 'assistante', NULL, NULL, NULL, '2026-01-02 20:16:10'),
(257, 'Monsieur le Directeur', 'directeur@uemf.org', 'directeur.general', '$2y$10$W83FhbK88Djqssz9LE9TBOEal8eHc0J7cGyxOLKpI1AInDkE1hhuG', 'directeur', NULL, NULL, NULL, '2026-01-02 20:16:10');

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
  ADD KEY `expediteur_id` (`expediteur_id`),
  ADD KEY `fk_messages_projet_cascade` (`projet_id`);

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
  ADD KEY `etudiant_id` (`etudiant_id`),
  ADD KEY `binome_id` (`binome_id`),
  ADD KEY `encadrant_id` (`encadrant_id`),
  ADD KEY `filiere_id` (`filiere_id`),
  ADD KEY `fk_pref1` (`encadrant_pref1_id`),
  ADD KEY `fk_pref2` (`encadrant_pref2_id`),
  ADD KEY `fk_pref3` (`encadrant_pref3_id`);

--
-- Indexes for table `rapports`
--
ALTER TABLE `rapports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_rapports_projet_cascade` (`projet_id`);

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
  ADD UNIQUE KEY `projet_id` (`projet_id`),
  ADD KEY `salle_id` (`salle_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `filieres`
--
ALTER TABLE `filieres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `jurys`
--
ALTER TABLE `jurys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jury_soutenance`
--
ALTER TABLE `jury_soutenance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `periodes`
--
ALTER TABLE `periodes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projets`
--
ALTER TABLE `projets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rapports`
--
ALTER TABLE `rapports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `salles`
--
ALTER TABLE `salles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `soutenances`
--
ALTER TABLE `soutenances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=258;

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
  ADD CONSTRAINT `fk_messages_projet_cascade` FOREIGN KEY (`projet_id`) REFERENCES `projets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`expediteur_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `periodes`
--
ALTER TABLE `periodes`
  ADD CONSTRAINT `periodes_ibfk_1` FOREIGN KEY (`filiere_id`) REFERENCES `filieres` (`id`);

--
-- Constraints for table `projets`
--
ALTER TABLE `projets`
  ADD CONSTRAINT `fk_pref1` FOREIGN KEY (`encadrant_pref1_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_pref2` FOREIGN KEY (`encadrant_pref2_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_pref3` FOREIGN KEY (`encadrant_pref3_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `projets_ibfk_1` FOREIGN KEY (`etudiant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `projets_ibfk_2` FOREIGN KEY (`binome_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `projets_ibfk_3` FOREIGN KEY (`encadrant_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `projets_ibfk_4` FOREIGN KEY (`filiere_id`) REFERENCES `filieres` (`id`);

--
-- Constraints for table `rapports`
--
ALTER TABLE `rapports`
  ADD CONSTRAINT `fk_rapports_projet_cascade` FOREIGN KEY (`projet_id`) REFERENCES `projets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `soutenances`
--
ALTER TABLE `soutenances`
  ADD CONSTRAINT `fk_soutenances_projet_cascade` FOREIGN KEY (`projet_id`) REFERENCES `projets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `soutenances_ibfk_2` FOREIGN KEY (`salle_id`) REFERENCES `salles` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`filiere_id`) REFERENCES `filieres` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
