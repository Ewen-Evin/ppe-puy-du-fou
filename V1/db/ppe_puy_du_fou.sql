-- ============================================================
-- PPE Puy du Fou - Dump complet (structure + données)
-- Coordonnées GPS corrigées (Les Épesses, Vendée)
-- Import : mysql -u root -p < ppe_puy_du_fou.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS `ppe_puy_du_fou`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ppe_puy_du_fou`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ppe_puy_du_fou`
--

-- --------------------------------------------------------

--
-- Table structure for table `choisir`
--

DROP TABLE IF EXISTS `etape`;
DROP TABLE IF EXISTS `parcours`;
DROP TABLE IF EXISTS `choisir`;
DROP TABLE IF EXISTS `seance`;
DROP TABLE IF EXISTS `spectacle`;
DROP TABLE IF EXISTS `distance`;
DROP TABLE IF EXISTS `lieu`;
DROP TABLE IF EXISTS `visite`;
DROP TABLE IF EXISTS `utilisateur`;
DROP TABLE IF EXISTS `jours`;

CREATE TABLE `choisir` (
  `id_spectacle` int NOT NULL,
  `id_visite` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `choisir`
--

INSERT INTO `choisir` (`id_spectacle`, `id_visite`) VALUES
(6, 6),
(9, 6),
(11, 6),
(6, 7),
(9, 7),
(11, 7);

-- --------------------------------------------------------

--
-- Table structure for table `distance`
--

CREATE TABLE `distance` (
  `id_lieu` int NOT NULL,
  `id_lieu_1` int NOT NULL,
  `distance_metres` int NOT NULL
) ;

--
-- Dumping data for table `distance`
--

INSERT INTO `distance` (`id_lieu`, `id_lieu_1`, `distance_metres`) VALUES
(1, 2, 180),
(1, 12, 160),
(1, 20, 200),
(2, 4, 200),
(2, 12, 170),
(3, 4, 180),
(3, 17, 60),
(3, 19, 120),
(4, 13, 140),
(4, 19, 90),
(4, 21, 50),
(6, 12, 80),
(6, 28, 40),
(7, 5, 140),
(7, 20, 90),
(8, 5, 120),
(8, 20, 70),
(9, 11, 60),
(9, 16, 80),
(10, 13, 60),
(10, 19, 100),
(11, 13, 110),
(11, 16, 50),
(11, 25, 30),
(11, 26, 25),
(11, 27, 40),
(11, 29, 35),
(11, 31, 45),
(13, 14, 80),
(13, 19, 130),
(14, 19, 110),
(15, 8, 120),
(15, 18, 90),
(17, 18, 100),
(18, 5, 150),
(19, 21, 60),
(19, 30, 70),
(19, 32, 75),
(21, 30, 30),
(21, 32, 35),
(22, 12, 60),
(22, 17, 80),
(23, 1, 250),
(23, 2, 220),
(23, 5, 180),
(23, 7, 300),
(23, 12, 150),
(23, 24, 80),
(23, 33, 600),
(24, 1, 180),
(24, 3, 150),
(24, 4, 200),
(24, 6, 140),
(24, 12, 120),
(24, 19, 220),
(24, 22, 100),
(33, 34, 60),
(33, 35, 90),
(33, 36, 70),
(33, 37, 110),
(33, 38, 80),
(33, 39, 100),
(33, 40, 75),
(34, 35, 50),
(35, 38, 40),
(36, 37, 55),
(38, 40, 45),
(39, 40, 50);

-- --------------------------------------------------------

--
-- Table structure for table `etape`
--

CREATE TABLE `etape` (
  `id_etape` int NOT NULL,
  `id_parcours` int NOT NULL,
  `id_seance` int NOT NULL,
  `ordre` int NOT NULL,
  `heure_arrivee` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `etape`
--

INSERT INTO `etape` (`id_etape`, `id_parcours`, `id_seance`, `ordre`, `heure_arrivee`) VALUES
(131, 41, 23, 1, '15:45:00'),
(132, 41, 18, 2, '16:10:00'),
(133, 41, 28, 3, '17:30:00'),
(134, 42, 22, 1, '12:00:00'),
(135, 42, 17, 2, '12:25:00'),
(136, 42, 27, 3, '15:00:00'),
(137, 43, 26, 1, '12:30:00'),
(138, 43, 17, 2, '12:55:00'),
(139, 43, 23, 3, '15:00:00'),
(140, 44, 17, 1, '14:30:00'),
(141, 44, 23, 2, '15:00:00'),
(142, 44, 28, 3, '16:10:00'),
(143, 45, 16, 1, '11:00:00'),
(144, 45, 22, 2, '11:30:00'),
(145, 45, 27, 3, '12:25:00'),
(146, 46, 26, 1, '12:30:00'),
(147, 46, 23, 2, '12:55:00'),
(148, 46, 18, 3, '16:10:00'),
(149, 47, 16, 1, '11:00:00'),
(150, 47, 26, 2, '11:30:00'),
(151, 47, 23, 3, '12:55:00'),
(152, 48, 22, 1, '12:00:00'),
(153, 48, 27, 2, '12:25:00'),
(154, 48, 18, 3, '15:40:00'),
(155, 49, 22, 1, '12:00:00'),
(156, 49, 17, 2, '12:25:00'),
(157, 49, 28, 3, '15:00:00'),
(158, 50, 22, 1, '12:00:00'),
(159, 50, 18, 2, '12:25:00'),
(160, 50, 28, 3, '17:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `jours`
--

CREATE TABLE `jours` (
  `id_jours` date NOT NULL,
  `heure_ouverture` time NOT NULL,
  `heure_fermeture` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jours`
--

INSERT INTO `jours` (`id_jours`, `heure_ouverture`, `heure_fermeture`) VALUES
('2026-04-11', '09:30:00', '19:30:00'),
('2026-04-12', '09:30:00', '19:30:00'),
('2026-04-18', '09:30:00', '20:00:00'),
('2026-04-19', '09:30:00', '20:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `lieu`
--

CREATE TABLE `lieu` (
  `id_lieu` int NOT NULL,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `coordonnees_gps` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_lieu` enum('spectacle','zone','restaurant','hotel','accueil','allee') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'zone'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lieu`
--

INSERT INTO `lieu` (`id_lieu`, `nom`, `coordonnees_gps`, `type_lieu`) VALUES
(1, 'Stadium Gallo-Romain', '46.8888,-0.9328', 'spectacle'),
(2, 'Fort de l\'An Mil', '46.8872,-0.9362', 'spectacle'),
(3, 'Théâtre du Bal des Oiseaux Fantômes', '46.8895,-0.9338', 'spectacle'),
(4, 'Château du Puy du Fou', '46.8912,-0.9315', 'spectacle'),
(5, 'Cinéscénie - Grand Parc', '46.8852,-0.9372', 'spectacle'),
(6, 'Théâtre du Mousquetaire de Richelieu', '46.8882,-0.9342', 'spectacle'),
(7, 'Théâtre du Dernier Panache', '46.8868,-0.9355', 'spectacle'),
(8, 'Lac des Noces de Feu', '46.8858,-0.9368', 'spectacle'),
(9, 'Frégate du Mystère de La Pérouse', '46.8898,-0.9322', 'spectacle'),
(10, 'Le Premier Royaume', '46.8905,-0.9308', 'spectacle'),
(11, 'Le Bourg 1900', '46.8892,-0.9318', 'zone'),
(12, 'La Cité Médiévale', '46.8878,-0.9345', 'zone'),
(13, 'Le Village du XVIIIe siècle', '46.8900,-0.9312', 'zone'),
(14, 'Le Camp du Drap d\'Or', '46.8908,-0.9305', 'zone'),
(15, 'Les Amoureux de Verdun', '46.8865,-0.9352', 'zone'),
(16, 'Le Mime et l\'Étoile (Théâtre)', '46.8890,-0.9325', 'zone'),
(17, 'L\'Académie de Fauconnerie', '46.8885,-0.9335', 'zone'),
(18, 'Le Conservatoire Animalier', '46.8875,-0.9348', 'zone'),
(19, 'Le Grand Carrousel', '46.8902,-0.9320', 'zone'),
(20, 'Les Orgues de Feu', '46.8862,-0.9365', 'zone'),
(21, 'La Renaissance du Château', '46.8910,-0.9318', 'zone'),
(22, 'Le Repaire des Enfants', '46.8880,-0.9332', 'zone'),
(23, 'Place d\'Accueil - Entrée du Parc', '46.8848,-0.9368', 'accueil'),
(24, 'Allée Centrale', '46.8870,-0.9352', 'allee'),
(25, 'Le Café de la Madelon', '46.8893,-0.9320', 'restaurant'),
(26, 'L\'Auberge de Poste', '46.8868,-0.9358', 'restaurant'),
(27, 'Le Bistrot', '46.8888,-0.9330', 'restaurant'),
(28, 'Les Deux Couronnes', '46.8880,-0.9342', 'restaurant'),
(29, 'Le Rendez-vous du Pêcheur', '46.8895,-0.9315', 'restaurant'),
(30, 'La Halle Renaissance', '46.8908,-0.9310', 'restaurant'),
(31, 'Le Bouchon des Quais', '46.8862,-0.9360', 'restaurant'),
(32, 'Le Banquet du Roi', '46.8905,-0.9325', 'restaurant'),
(33, 'Hôtel Le Logis de Lescure', '46.8928,-0.9300', 'hotel'),
(34, 'Hôtel La Villa Gallo-Romaine', '46.8932,-0.9288', 'hotel'),
(35, 'Hôtel La Citadelle', '46.8925,-0.9312', 'hotel'),
(36, 'Hôtel Les Îles de Clovis', '46.8920,-0.9325', 'hotel'),
(37, 'Hôtel Le Camp du Drap d\'Or', '46.8935,-0.9295', 'hotel'),
(38, 'Hôtel Le Grand Siècle', '46.8930,-0.9305', 'hotel'),
(39, 'Hôtel Les Lavandières', '46.8922,-0.9318', 'hotel'),
(40, 'Hôtel Le Préau', '46.8918,-0.9330', 'hotel');

-- --------------------------------------------------------

--
-- Table structure for table `parcours`
--

CREATE TABLE `parcours` (
  `id_parcours` int NOT NULL,
  `id_visite` int NOT NULL,
  `duree` time NOT NULL,
  `est_complet` tinyint(1) NOT NULL DEFAULT '0',
  `est_favori` tinyint(1) NOT NULL DEFAULT '0',
  `temps_attente` time NOT NULL DEFAULT '00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `parcours`
--

INSERT INTO `parcours` (`id_parcours`, `id_visite`, `duree`, `est_complet`, `est_favori`, `temps_attente`) VALUES
(41, 6, '03:10:00', 1, 1, '01:50:00'),
(42, 6, '03:40:00', 1, 0, '02:20:00'),
(43, 6, '03:40:00', 1, 0, '02:20:00'),
(44, 6, '04:25:00', 1, 0, '03:05:00'),
(45, 6, '04:40:00', 1, 0, '03:20:00'),
(46, 6, '05:00:00', 1, 0, '03:40:00'),
(47, 6, '05:10:00', 1, 0, '03:50:00'),
(48, 6, '05:30:00', 1, 0, '04:10:00'),
(49, 6, '06:55:00', 1, 0, '05:35:00'),
(50, 6, '06:55:00', 1, 0, '05:35:00');

-- --------------------------------------------------------

--
-- Table structure for table `seance`
--

CREATE TABLE `seance` (
  `id_seance` int NOT NULL,
  `date_seance` date NOT NULL,
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL,
  `id_spectacle` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `seance`
--

INSERT INTO `seance` (`id_seance`, `date_seance`, `heure_debut`, `heure_fin`, `id_spectacle`) VALUES
(1, '2026-04-11', '10:30:00', '11:05:00', 1),
(2, '2026-04-11', '14:00:00', '14:35:00', 1),
(3, '2026-04-11', '16:30:00', '17:05:00', 1),
(4, '2026-04-11', '11:15:00', '11:45:00', 2),
(5, '2026-04-11', '14:45:00', '15:15:00', 2),
(6, '2026-04-11', '17:30:00', '18:00:00', 2),
(7, '2026-04-11', '10:00:00', '10:30:00', 3),
(8, '2026-04-11', '13:30:00', '14:00:00', 3),
(9, '2026-04-11', '16:00:00', '16:30:00', 3),
(10, '2026-04-11', '11:30:00', '12:00:00', 4),
(11, '2026-04-11', '15:30:00', '16:00:00', 4),
(12, '2026-04-11', '18:15:00', '18:45:00', 4),
(13, '2026-04-11', '12:15:00', '12:50:00', 5),
(14, '2026-04-11', '15:00:00', '15:35:00', 5),
(15, '2026-04-11', '17:45:00', '18:20:00', 5),
(16, '2026-04-11', '11:00:00', '11:30:00', 6),
(17, '2026-04-11', '14:30:00', '15:00:00', 6),
(18, '2026-04-11', '17:00:00', '17:30:00', 6),
(19, '2026-04-11', '10:45:00', '11:10:00', 8),
(20, '2026-04-11', '13:45:00', '14:10:00', 8),
(21, '2026-04-11', '16:45:00', '17:10:00', 8),
(22, '2026-04-11', '12:00:00', '12:25:00', 9),
(23, '2026-04-11', '15:45:00', '16:10:00', 9),
(24, '2026-04-11', '13:00:00', '13:25:00', 10),
(25, '2026-04-11', '16:15:00', '16:40:00', 10),
(26, '2026-04-11', '12:30:00', '12:55:00', 11),
(27, '2026-04-11', '15:15:00', '15:40:00', 11),
(28, '2026-04-11', '18:30:00', '18:55:00', 11),
(29, '2026-04-12', '10:30:00', '11:05:00', 1),
(30, '2026-04-12', '14:00:00', '14:35:00', 1),
(31, '2026-04-12', '11:15:00', '11:45:00', 2),
(32, '2026-04-12', '14:45:00', '15:15:00', 2),
(33, '2026-04-12', '10:00:00', '10:30:00', 3),
(34, '2026-04-12', '13:30:00', '14:00:00', 3),
(35, '2026-04-12', '11:30:00', '12:00:00', 4),
(36, '2026-04-12', '15:30:00', '16:00:00', 4);

-- --------------------------------------------------------

--
-- Table structure for table `spectacle`
--

CREATE TABLE `spectacle` (
  `id_spectacle` int NOT NULL,
  `libelle` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `duree_spectacle` time NOT NULL,
  `duree_attente` time NOT NULL DEFAULT '00:00:00',
  `id_lieu` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `spectacle`
--

INSERT INTO `spectacle` (`id_spectacle`, `libelle`, `description`, `duree_spectacle`, `duree_attente`, `id_lieu`) VALUES
(1, 'Le Signe du Triomphe', 'Reconstitution grandiose des jeux du cirque romain dans le Stadium Gallo-Romain.', '00:35:00', '00:20:00', 1),
(2, 'Les Vikings', 'Une attaque viking spectaculaire sur le Fort de l\'An Mil.', '00:30:00', '00:20:00', 2),
(3, 'Le Bal des Oiseaux Fantômes', 'Ballet d\'oiseaux libres au coeur d\'un château fort en ruines.', '00:30:00', '00:25:00', 3),
(4, 'Le Secret de la Lance', 'Une jeune bergère sauve son château grâce au secret d\'une lance magique.', '00:30:00', '00:20:00', 4),
(5, 'Mousquetaire de Richelieu', 'Cape, épée, flamenco et chevaux dans un théâtre couvert grandiose.', '00:35:00', '00:20:00', 6),
(6, 'Le Dernier Panache', 'L\'épopée d\'un héros vendéen dans un théâtre tournant unique au monde.', '00:30:00', '00:25:00', 7),
(7, 'Les Noces de Feu', 'Spectacle nocturne aquatique et pyrotechnique sur le lac.', '00:25:00', '00:15:00', 8),
(8, 'Le Mystère de La Pérouse', 'Embarquez sur la frégate de l\'explorateur dans un voyage immersif.', '00:25:00', '00:20:00', 9),
(9, 'Le Premier Royaume', 'L\'aventure de Clovis, fondateur de la France, dans un parcours immersif.', '00:25:00', '00:20:00', 10),
(10, 'Le Mime et l\'Étoile', 'Un hommage poétique au cinéma muet et au music-hall des années 30.', '00:25:00', '00:15:00', 16),
(11, 'Les Amoureux de Verdun', 'Une plongée immersive dans les tranchées de la Grande Guerre.', '00:25:00', '00:15:00', 15);

-- --------------------------------------------------------

--
-- Table structure for table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id_utilisateur` int NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mot_de_passe` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_profil` enum('visiteur','gestionnaire') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'visiteur',
  `vitesse_marche` decimal(5,2) DEFAULT '4.00',
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `utilisateur`
--

INSERT INTO `utilisateur` (`id_utilisateur`, `email`, `mot_de_passe`, `nom`, `prenom`, `type_profil`, `vitesse_marche`, `date_creation`) VALUES
(1, 'admin@puydufou.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Martin', 'Sophie', 'gestionnaire', NULL, '2026-04-08 13:33:41'),
(2, 'jean.dupont@email.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dupont', 'Jean', 'visiteur', '4.50', '2026-04-08 13:33:41'),
(3, 'marie.curie@email.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Curie', 'Marie', 'visiteur', '3.80', '2026-04-08 13:33:41');

-- --------------------------------------------------------

--
-- Table structure for table `visite`
--

CREATE TABLE `visite` (
  `id_visite` int NOT NULL,
  `nom_visite` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_visite` date NOT NULL,
  `vitesse_marche` decimal(5,2) NOT NULL DEFAULT '4.00',
  `id_utilisateur` int NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `visite`
--

INSERT INTO `visite` (`id_visite`, `nom_visite`, `date_visite`, `vitesse_marche`, `id_utilisateur`, `date_creation`) VALUES
(6, 'test', '2026-04-11', '4.50', 2, '2026-04-27 14:18:30'),
(7, 'test2', '2026-04-11', '4.50', 2, '2026-04-27 14:19:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `choisir`
--
ALTER TABLE `choisir`
  ADD PRIMARY KEY (`id_spectacle`,`id_visite`),
  ADD KEY `id_visite` (`id_visite`);

--
-- Indexes for table `distance`
--
ALTER TABLE `distance`
  ADD PRIMARY KEY (`id_lieu`,`id_lieu_1`),
  ADD KEY `id_lieu_1` (`id_lieu_1`);

--
-- Indexes for table `etape`
--
ALTER TABLE `etape`
  ADD PRIMARY KEY (`id_etape`),
  ADD UNIQUE KEY `uk_parcours_ordre` (`id_parcours`,`ordre`),
  ADD KEY `id_seance` (`id_seance`),
  ADD KEY `idx_etape_parcours` (`id_parcours`);

--
-- Indexes for table `jours`
--
ALTER TABLE `jours`
  ADD PRIMARY KEY (`id_jours`);

--
-- Indexes for table `lieu`
--
ALTER TABLE `lieu`
  ADD PRIMARY KEY (`id_lieu`);

--
-- Indexes for table `parcours`
--
ALTER TABLE `parcours`
  ADD PRIMARY KEY (`id_parcours`),
  ADD KEY `idx_parcours_visite` (`id_visite`);

--
-- Indexes for table `seance`
--
ALTER TABLE `seance`
  ADD PRIMARY KEY (`id_seance`),
  ADD KEY `idx_seance_date` (`date_seance`),
  ADD KEY `idx_seance_spectacle` (`id_spectacle`);

--
-- Indexes for table `spectacle`
--
ALTER TABLE `spectacle`
  ADD PRIMARY KEY (`id_spectacle`),
  ADD KEY `idx_spectacle_lieu` (`id_lieu`);

--
-- Indexes for table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id_utilisateur`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_utilisateur_email` (`email`);

--
-- Indexes for table `visite`
--
ALTER TABLE `visite`
  ADD PRIMARY KEY (`id_visite`),
  ADD KEY `idx_visite_user` (`id_utilisateur`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `etape`
--
ALTER TABLE `etape`
  MODIFY `id_etape` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=161;

--
-- AUTO_INCREMENT for table `lieu`
--
ALTER TABLE `lieu`
  MODIFY `id_lieu` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `parcours`
--
ALTER TABLE `parcours`
  MODIFY `id_parcours` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `seance`
--
ALTER TABLE `seance`
  MODIFY `id_seance` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `spectacle`
--
ALTER TABLE `spectacle`
  MODIFY `id_spectacle` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id_utilisateur` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `visite`
--
ALTER TABLE `visite`
  MODIFY `id_visite` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `choisir`
--
ALTER TABLE `choisir`
  ADD CONSTRAINT `choisir_ibfk_1` FOREIGN KEY (`id_spectacle`) REFERENCES `spectacle` (`id_spectacle`) ON DELETE CASCADE,
  ADD CONSTRAINT `choisir_ibfk_2` FOREIGN KEY (`id_visite`) REFERENCES `visite` (`id_visite`) ON DELETE CASCADE;

--
-- Constraints for table `distance`
--
ALTER TABLE `distance`
  ADD CONSTRAINT `distance_ibfk_1` FOREIGN KEY (`id_lieu`) REFERENCES `lieu` (`id_lieu`) ON DELETE CASCADE,
  ADD CONSTRAINT `distance_ibfk_2` FOREIGN KEY (`id_lieu_1`) REFERENCES `lieu` (`id_lieu`) ON DELETE CASCADE;

--
-- Constraints for table `etape`
--
ALTER TABLE `etape`
  ADD CONSTRAINT `etape_ibfk_1` FOREIGN KEY (`id_parcours`) REFERENCES `parcours` (`id_parcours`) ON DELETE CASCADE,
  ADD CONSTRAINT `etape_ibfk_2` FOREIGN KEY (`id_seance`) REFERENCES `seance` (`id_seance`);

--
-- Constraints for table `parcours`
--
ALTER TABLE `parcours`
  ADD CONSTRAINT `parcours_ibfk_1` FOREIGN KEY (`id_visite`) REFERENCES `visite` (`id_visite`) ON DELETE CASCADE;

--
-- Constraints for table `seance`
--
ALTER TABLE `seance`
  ADD CONSTRAINT `seance_ibfk_1` FOREIGN KEY (`id_spectacle`) REFERENCES `spectacle` (`id_spectacle`) ON DELETE CASCADE,
  ADD CONSTRAINT `seance_ibfk_2` FOREIGN KEY (`date_seance`) REFERENCES `jours` (`id_jours`);

--
-- Constraints for table `spectacle`
--
ALTER TABLE `spectacle`
  ADD CONSTRAINT `spectacle_ibfk_1` FOREIGN KEY (`id_lieu`) REFERENCES `lieu` (`id_lieu`);

--
-- Constraints for table `visite`
--
ALTER TABLE `visite`
  ADD CONSTRAINT `visite_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;
SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
