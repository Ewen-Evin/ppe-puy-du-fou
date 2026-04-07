-- ============================================================
-- PPE Puy du Fou - V1 - Schéma MySQL
-- Basé sur le MCD V0 (V0/ppe_info/bdd/mcd) avec ajustements V1
-- ============================================================

DROP DATABASE IF EXISTS ppe_puy_du_fou;
CREATE DATABASE ppe_puy_du_fou
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE ppe_puy_du_fou;

-- ------------------------------------------------------------
-- Utilisateur
-- ------------------------------------------------------------
CREATE TABLE Utilisateur (
    id_utilisateur   INT AUTO_INCREMENT,
    email            VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe     VARCHAR(255) NOT NULL,            -- bcrypt
    nom              VARCHAR(50)  NOT NULL,
    prenom           VARCHAR(50)  NOT NULL,
    type_profil      ENUM('visiteur','gestionnaire') NOT NULL DEFAULT 'visiteur',
    vitesse_marche   DECIMAL(5,2) DEFAULT 4.00,        -- km/h
    date_creation    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_utilisateur),
    INDEX idx_utilisateur_email (email)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Lieu (point du parc)
-- ------------------------------------------------------------
CREATE TABLE Lieu (
    id_lieu          INT AUTO_INCREMENT,
    nom              VARCHAR(100) NOT NULL,
    coordonnees_gps  VARCHAR(50)  NOT NULL,
    PRIMARY KEY (id_lieu)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Distance (arête entre deux lieux, graphe non orienté)
-- Stockée une seule fois, le moteur traitera la symétrie + transitivité
-- ------------------------------------------------------------
CREATE TABLE Distance (
    id_lieu          INT NOT NULL,
    id_lieu_1        INT NOT NULL,
    distance_metres  INT NOT NULL,
    PRIMARY KEY (id_lieu, id_lieu_1),
    FOREIGN KEY (id_lieu)   REFERENCES Lieu(id_lieu)   ON DELETE CASCADE,
    FOREIGN KEY (id_lieu_1) REFERENCES Lieu(id_lieu_1) ON DELETE CASCADE,
    CHECK (id_lieu <> id_lieu_1),
    CHECK (distance_metres > 0)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Jours d'ouverture du parc
-- ------------------------------------------------------------
CREATE TABLE Jours (
    id_jours         DATE NOT NULL,
    heure_ouverture  TIME NOT NULL,
    heure_fermeture  TIME NOT NULL,
    PRIMARY KEY (id_jours)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Spectacle
-- ------------------------------------------------------------
CREATE TABLE Spectacle (
    id_spectacle     INT AUTO_INCREMENT,
    libelle          VARCHAR(100) NOT NULL,
    description      TEXT,
    duree_spectacle  TIME NOT NULL,
    duree_attente    TIME NOT NULL DEFAULT '00:00:00',
    id_lieu          INT NOT NULL,
    PRIMARY KEY (id_spectacle),
    FOREIGN KEY (id_lieu) REFERENCES Lieu(id_lieu),
    INDEX idx_spectacle_lieu (id_lieu)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Seance (une représentation d'un spectacle à un instant donné)
-- ------------------------------------------------------------
CREATE TABLE Seance (
    id_seance        INT AUTO_INCREMENT,
    date_seance      DATE NOT NULL,
    heure_debut      TIME NOT NULL,
    heure_fin        TIME NOT NULL,
    id_spectacle     INT  NOT NULL,
    PRIMARY KEY (id_seance),
    FOREIGN KEY (id_spectacle) REFERENCES Spectacle(id_spectacle) ON DELETE CASCADE,
    FOREIGN KEY (date_seance)  REFERENCES Jours(id_jours),
    INDEX idx_seance_date (date_seance),
    INDEX idx_seance_spectacle (id_spectacle)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Visite (une session de visite d'un visiteur pour une date)
-- ------------------------------------------------------------
CREATE TABLE Visite (
    id_visite        INT AUTO_INCREMENT,
    date_visite      DATE NOT NULL,
    vitesse_marche   DECIMAL(5,2) NOT NULL DEFAULT 4.00,
    id_utilisateur   INT NOT NULL,
    date_creation    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_visite),
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE,
    INDEX idx_visite_user (id_utilisateur)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- choisir : spectacles sélectionnés par le visiteur pour la visite
-- ------------------------------------------------------------
CREATE TABLE Choisir (
    id_spectacle     INT NOT NULL,
    id_visite        INT NOT NULL,
    PRIMARY KEY (id_spectacle, id_visite),
    FOREIGN KEY (id_spectacle) REFERENCES Spectacle(id_spectacle) ON DELETE CASCADE,
    FOREIGN KEY (id_visite)    REFERENCES Visite(id_visite)       ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Parcours (proposition d'ordonnancement calculée par le moteur)
-- ------------------------------------------------------------
CREATE TABLE Parcours (
    id_parcours      INT AUTO_INCREMENT,
    id_visite        INT NOT NULL,
    duree            TIME NOT NULL,
    est_complet      BOOLEAN NOT NULL DEFAULT FALSE,
    temps_attente    TIME NOT NULL DEFAULT '00:00:00',
    PRIMARY KEY (id_parcours),
    FOREIGN KEY (id_visite) REFERENCES Visite(id_visite) ON DELETE CASCADE,
    INDEX idx_parcours_visite (id_visite)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Etape (séance ordonnée dans un parcours)
-- ------------------------------------------------------------
CREATE TABLE Etape (
    id_etape         INT AUTO_INCREMENT,
    id_parcours      INT NOT NULL,
    id_seance        INT NOT NULL,
    ordre            INT NOT NULL,
    heure_arrivee    TIME,
    PRIMARY KEY (id_etape),
    FOREIGN KEY (id_parcours) REFERENCES Parcours(id_parcours) ON DELETE CASCADE,
    FOREIGN KEY (id_seance)   REFERENCES Seance(id_seance),
    UNIQUE KEY uk_parcours_ordre (id_parcours, ordre),
    INDEX idx_etape_parcours (id_parcours)
) ENGINE=InnoDB;
