-- ============================================================
-- PPE Puy du Fou - V1 - Jeu de données de test
-- À exécuter APRÈS schema.sql
--
-- Comptes créés :
--   Gestionnaire : admin@puydufou.fr      / password
--   Visiteur     : jean.dupont@email.fr   / password
--
-- Les deux mots de passe sont "password" (hash bcrypt ci-dessous,
-- exemple canonique de la doc PHP).
-- ============================================================

USE ppe_puy_du_fou;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE Etape;
TRUNCATE TABLE Parcours;
TRUNCATE TABLE Choisir;
TRUNCATE TABLE Visite;
TRUNCATE TABLE Seance;
TRUNCATE TABLE Spectacle;
TRUNCATE TABLE Distance;
TRUNCATE TABLE Lieu;
TRUNCATE TABLE Jours;
TRUNCATE TABLE Utilisateur;
SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- Utilisateurs
-- ------------------------------------------------------------
INSERT INTO Utilisateur (email, mot_de_passe, nom, prenom, type_profil, vitesse_marche) VALUES
('admin@puydufou.fr',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Martin',  'Sophie', 'gestionnaire', NULL),
('jean.dupont@email.fr',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dupont',  'Jean',   'visiteur',     4.50),
('marie.curie@email.fr',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Curie',   'Marie',  'visiteur',     3.80);

-- ------------------------------------------------------------
-- Lieux du parc (vrais lieux/zones du Puy du Fou)
-- 1-10  : grands lieux de spectacle
-- 11-20 : villages et zones thématiques
-- 21-30 : restaurants et points de service
-- 31-40 : hôtels et hébergements de la Cité Nocturne
-- ------------------------------------------------------------
INSERT INTO Lieu (id_lieu, nom, coordonnees_gps) VALUES
-- Grands lieux de spectacle
(1,  'Stadium Gallo-Romain',                  '46.8911,-0.9295'),
(2,  'Fort de l''An Mil',                      '46.8902,-0.9311'),
(3,  'Théâtre du Bal des Oiseaux Fantômes',   '46.8898,-0.9278'),
(4,  'Château du Puy du Fou',                  '46.8920,-0.9285'),
(5,  'Cinéscénie - Grand Parc',               '46.8885,-0.9302'),
(6,  'Théâtre du Mousquetaire de Richelieu',  '46.8893,-0.9288'),
(7,  'Théâtre du Dernier Panache',            '46.8889,-0.9305'),
(8,  'Lac des Noces de Feu',                   '46.8882,-0.9298'),
(9,  'Frégate du Mystère de La Pérouse',      '46.8906,-0.9268'),
(10, 'Le Premier Royaume',                     '46.8916,-0.9272'),
-- Villages, zones thématiques et autres lieux de visite
(11, 'Le Bourg 1900',                          '46.8908,-0.9265'),
(12, 'La Cité Médiévale',                      '46.8895,-0.9290'),
(13, 'Le Village du XVIIIe siècle',           '46.8915,-0.9270'),
(14, 'Le Camp du Drap d''Or',                  '46.8923,-0.9282'),
(15, 'Les Amoureux de Verdun',                 '46.8887,-0.9272'),
(16, 'Le Mime et l''Étoile (Théâtre)',         '46.8910,-0.9263'),
(17, 'L''Académie de Fauconnerie',             '46.8896,-0.9276'),
(18, 'Le Conservatoire Animalier',             '46.8888,-0.9282'),
(19, 'Le Grand Carrousel',                     '46.8918,-0.9275'),
(20, 'Les Orgues de Feu',                      '46.8884,-0.9296'),
(21, 'La Renaissance du Château',              '46.8921,-0.9286'),
(22, 'Le Repaire des Enfants',                 '46.8904,-0.9281'),
-- Place d'accueil et axes principaux
(23, 'Place d''Accueil - Entrée du Parc',     '46.8900,-0.9300'),
(24, 'Allée Centrale',                         '46.8902,-0.9292'),
-- Restaurants
(25, 'Le Café de la Madelon',                 '46.8909,-0.9266'),
(26, 'L''Auberge de Poste',                    '46.8907,-0.9264'),
(27, 'Le Bistrot',                             '46.8911,-0.9268'),
(28, 'Les Deux Couronnes',                     '46.8896,-0.9289'),
(29, 'Le Rendez-vous du Pêcheur',             '46.8905,-0.9269'),
(30, 'La Halle Renaissance',                   '46.8919,-0.9284'),
(31, 'Le Bouchon des Quais',                   '46.8907,-0.9267'),
(32, 'Le Banquet du Roi',                      '46.8919,-0.9286'),
-- Hôtels de la Cité Nocturne
(33, 'Hôtel Le Logis de Lescure',             '46.8930,-0.9320'),
(34, 'Hôtel La Villa Gallo-Romaine',          '46.8932,-0.9315'),
(35, 'Hôtel La Citadelle',                     '46.8934,-0.9310'),
(36, 'Hôtel Les Îles de Clovis',              '46.8928,-0.9322'),
(37, 'Hôtel Le Camp du Drap d''Or',           '46.8926,-0.9318'),
(38, 'Hôtel Le Grand Siècle',                  '46.8930,-0.9312'),
(39, 'Hôtel Les Lavandières',                  '46.8929,-0.9316'),
(40, 'Hôtel Le Préau',                         '46.8931,-0.9313');

-- ------------------------------------------------------------
-- Distances (graphe des allées du parc, en mètres)
-- Non orienté : on insère une seule arête, le moteur traite la symétrie
-- et déduit le reste par transitivité (Floyd-Warshall).
-- ------------------------------------------------------------
INSERT INTO Distance (id_lieu, id_lieu_1, distance_metres) VALUES
-- Depuis l'accueil (23)
(23, 24, 80),
(23, 1,  250),
(23, 2,  220),
(23, 5,  180),
(23, 12, 150),
(23, 7,  300),
-- Allée centrale (24) — hub
(24, 1,  180),
(24, 3,  150),
(24, 4,  200),
(24, 6,  140),
(24, 12, 120),
(24, 19, 220),
(24, 22, 100),
-- Grands lieux entre eux
(1,  2,  180),
(1,  12, 160),
(1,  20, 200),
(2,  4,  200),
(2,  12, 170),
(3,  4,  180),
(3,  17, 60),
(3,  19, 120),
(4,  19, 90),
(4,  21, 50),
(4,  13, 140),
(6,  12, 80),
(6,  28, 40),
(7,  5,  140),
(7,  20, 90),
(8,  5,  120),
(8,  20, 70),
(9,  11, 60),
(9,  16, 80),
(10, 13, 60),
(10, 19, 100),
-- Villages/zones
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
(15, 18, 90),
(15, 8,  120),
(17, 18, 100),
(18, 5,  150),
(19, 21, 60),
(19, 30, 70),
(19, 32, 75),
(21, 30, 30),
(21, 32, 35),
(22, 12, 60),
(22, 17, 80),
-- Cité nocturne (hôtels) — reliée à l'accueil par une allée commune
(23, 33, 600),
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

-- ------------------------------------------------------------
-- Spectacles (vrais spectacles du Puy du Fou)
-- ------------------------------------------------------------
INSERT INTO Spectacle (id_spectacle, libelle, description, duree_spectacle, duree_attente, id_lieu) VALUES
(1, 'Le Signe du Triomphe',         'Reconstitution grandiose des jeux du cirque romain dans le Stadium Gallo-Romain.', '00:35:00', '00:20:00', 1),
(2, 'Les Vikings',                  'Une attaque viking spectaculaire sur le Fort de l''An Mil.',                       '00:30:00', '00:20:00', 2),
(3, 'Le Bal des Oiseaux Fantômes',  'Ballet d''oiseaux libres au coeur d''un château fort en ruines.',                 '00:30:00', '00:25:00', 3),
(4, 'Le Secret de la Lance',        'Une jeune bergère sauve son château grâce au secret d''une lance magique.',       '00:30:00', '00:20:00', 4),
(5, 'Mousquetaire de Richelieu',    'Cape, épée, flamenco et chevaux dans un théâtre couvert grandiose.',             '00:35:00', '00:20:00', 6),
(6, 'Le Dernier Panache',           'L''épopée d''un héros vendéen dans un théâtre tournant unique au monde.',         '00:30:00', '00:25:00', 7),
(7, 'Les Noces de Feu',             'Spectacle nocturne aquatique et pyrotechnique sur le lac.',                       '00:25:00', '00:15:00', 8),
(8, 'Le Mystère de La Pérouse',     'Embarquez sur la frégate de l''explorateur dans un voyage immersif.',             '00:25:00', '00:20:00', 9),
(9, 'Le Premier Royaume',           'L''aventure de Clovis, fondateur de la France, dans un parcours immersif.',       '00:25:00', '00:20:00', 10),
(10,'Le Mime et l''Étoile',          'Un hommage poétique au cinéma muet et au music-hall des années 30.',             '00:25:00', '00:15:00', 16),
(11,'Les Amoureux de Verdun',        'Une plongée immersive dans les tranchées de la Grande Guerre.',                  '00:25:00', '00:15:00', 15);

-- ------------------------------------------------------------
-- Jours d'ouverture
-- ------------------------------------------------------------
INSERT INTO Jours (id_jours, heure_ouverture, heure_fermeture) VALUES
('2026-04-11', '09:30:00', '19:30:00'),
('2026-04-12', '09:30:00', '19:30:00'),
('2026-04-18', '09:30:00', '20:00:00'),
('2026-04-19', '09:30:00', '20:00:00');

-- ------------------------------------------------------------
-- Séances du 2026-04-11
-- ------------------------------------------------------------
INSERT INTO Seance (date_seance, heure_debut, heure_fin, id_spectacle) VALUES
-- Le Signe du Triomphe
('2026-04-11', '10:30:00', '11:05:00', 1),
('2026-04-11', '14:00:00', '14:35:00', 1),
('2026-04-11', '16:30:00', '17:05:00', 1),
-- Les Vikings
('2026-04-11', '11:15:00', '11:45:00', 2),
('2026-04-11', '14:45:00', '15:15:00', 2),
('2026-04-11', '17:30:00', '18:00:00', 2),
-- Le Bal des Oiseaux Fantômes
('2026-04-11', '10:00:00', '10:30:00', 3),
('2026-04-11', '13:30:00', '14:00:00', 3),
('2026-04-11', '16:00:00', '16:30:00', 3),
-- Le Secret de la Lance
('2026-04-11', '11:30:00', '12:00:00', 4),
('2026-04-11', '15:30:00', '16:00:00', 4),
('2026-04-11', '18:15:00', '18:45:00', 4),
-- Mousquetaire de Richelieu
('2026-04-11', '12:15:00', '12:50:00', 5),
('2026-04-11', '15:00:00', '15:35:00', 5),
('2026-04-11', '17:45:00', '18:20:00', 5),
-- Le Dernier Panache
('2026-04-11', '11:00:00', '11:30:00', 6),
('2026-04-11', '14:30:00', '15:00:00', 6),
('2026-04-11', '17:00:00', '17:30:00', 6),
-- Le Mystère de La Pérouse
('2026-04-11', '10:45:00', '11:10:00', 8),
('2026-04-11', '13:45:00', '14:10:00', 8),
('2026-04-11', '16:45:00', '17:10:00', 8),
-- Le Premier Royaume
('2026-04-11', '12:00:00', '12:25:00', 9),
('2026-04-11', '15:45:00', '16:10:00', 9),
-- Le Mime et l'Étoile
('2026-04-11', '13:00:00', '13:25:00', 10),
('2026-04-11', '16:15:00', '16:40:00', 10),
-- Les Amoureux de Verdun
('2026-04-11', '12:30:00', '12:55:00', 11),
('2026-04-11', '15:15:00', '15:40:00', 11),
('2026-04-11', '18:30:00', '18:55:00', 11);

-- Quelques séances pour le 2026-04-12 aussi
INSERT INTO Seance (date_seance, heure_debut, heure_fin, id_spectacle) VALUES
('2026-04-12', '10:30:00', '11:05:00', 1),
('2026-04-12', '14:00:00', '14:35:00', 1),
('2026-04-12', '11:15:00', '11:45:00', 2),
('2026-04-12', '14:45:00', '15:15:00', 2),
('2026-04-12', '10:00:00', '10:30:00', 3),
('2026-04-12', '13:30:00', '14:00:00', 3),
('2026-04-12', '11:30:00', '12:00:00', 4),
('2026-04-12', '15:30:00', '16:00:00', 4);

-- ------------------------------------------------------------
-- Visite de démo (Jean Dupont, 11 avril, 4 spectacles)
-- ------------------------------------------------------------
INSERT INTO Visite (id_visite, date_visite, vitesse_marche, id_utilisateur) VALUES
(1, '2026-04-11', 4.50, 2);

INSERT INTO Choisir (id_visite, id_spectacle) VALUES
(1, 1),  -- Signe du Triomphe
(1, 2),  -- Vikings
(1, 3),  -- Bal des Oiseaux
(1, 5);  -- Mousquetaire de Richelieu
