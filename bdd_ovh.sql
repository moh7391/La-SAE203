-- =====================================================================
--  Base complete pour OVH (structure + donnees).
--  A coller dans l'onglet SQL de phpMyAdmin OVH, APRES avoir selectionne
--  ta base "ijtebowcompte17" dans la colonne de gauche.
--  Pas de "USE" ni de "CREATE DATABASE" : on travaille dans la base OVH.
--  Noms de tables en minuscules (important sur le serveur Linux d'OVH).
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS inscription;
DROP TABLE IF EXISTS contenir;
DROP TABLE IF EXISTS creneau;
DROP TABLE IF EXISTS participant;
DROP TABLE IF EXISTS element_expo;
DROP TABLE IF EXISTS salle;
DROP TABLE IF EXISTS administrateur;

-- ---------- STRUCTURE DES TABLES ----------

CREATE TABLE administrateur (
    id_admin INT PRIMARY KEY AUTO_INCREMENT,
    login VARCHAR(50) NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    nom VARCHAR(50) NOT NULL
);

CREATE TABLE salle (
    id_salle INT PRIMARY KEY AUTO_INCREMENT,
    nom_salle VARCHAR(50) NOT NULL,
    description TEXT
);

CREATE TABLE element_expo (
    id_element INT PRIMARY KEY AUTO_INCREMENT,
    titre VARCHAR(150) NOT NULL,
    description TEXT
);

CREATE TABLE creneau (
    id_creneau INT PRIMARY KEY AUTO_INCREMENT,
    date_creneau DATE NOT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    jauge INT NOT NULL,
    id_admin INT,
    id_salle INT NOT NULL,
    FOREIGN KEY (id_admin) REFERENCES administrateur(id_admin),
    FOREIGN KEY (id_salle) REFERENCES salle(id_salle)
);

CREATE TABLE participant (
    id_participant INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    telephone VARCHAR(20)
);

CREATE TABLE inscription (
    id_inscription INT PRIMARY KEY AUTO_INCREMENT,
    id_creneau INT NOT NULL,
    id_participant INT NOT NULL,
    date_inscription DATE NOT NULL,
    FOREIGN KEY (id_creneau) REFERENCES creneau(id_creneau),
    FOREIGN KEY (id_participant) REFERENCES participant(id_participant)
);

CREATE TABLE contenir (
    id_salle INT NOT NULL,
    id_element INT NOT NULL,
    PRIMARY KEY (id_salle, id_element),
    FOREIGN KEY (id_salle) REFERENCES salle(id_salle),
    FOREIGN KEY (id_element) REFERENCES element_expo(id_element)
);

-- ---------- DONNEES ----------

-- Administrateur (login: admin / mot de passe: admin123)
INSERT INTO administrateur (login, mot_de_passe, nom) VALUES
('admin', '$2y$10$0mzEX9lYSNSXgKxwO4eW/eD0YqhPFA6FJ0wgx8mIdYDu63n4IPrB6', 'Administrateur Expo');

-- Salles
INSERT INTO salle (nom_salle, description) VALUES
('Salle 002', 'Salle d''exposition n002 - oeuvres multimedia interactives.'),
('Salle 001', 'Salle d''exposition n001 - oeuvres multimedia interactives.'),
('Salle 005', 'Salle d''exposition n005 - oeuvres multimedia interactives.'),
('Salle 021', 'Salle d''exposition n021 - oeuvres multimedia interactives.');

-- Elements d'exposition
INSERT INTO element_expo (titre, description) VALUES
('oeuvre1',  'Serie de cliches modifiables par le visiteur.'),
('oeuvre2',  'Serie de cliches modifiables par le visiteur.'),
('oeuvre3',  'Serie de cliches modifiables par le visiteur.'),
('oeuvre4',  'Serie de cliches modifiables par le visiteur.'),
('oeuvre5',  'Serie de cliches modifiables par le visiteur.'),
('oeuvre6',  'Serie de cliches modifiables par le visiteur.'),
('oeuvre7',  'Serie de cliches modifiables par le visiteur.'),
('oeuvre8',  'Serie de cliches modifiables par le visiteur.'),
('oeuvre9',  'Serie de cliches modifiables par le visiteur.'),
('oeuvre10', 'Serie de cliches modifiables par le visiteur.'),
('oeuvre11', 'Serie de cliches modifiables par le visiteur.'),
('oeuvre12', 'Serie de cliches modifiables par le visiteur.');

-- Creneaux (14 horaires x 4 salles, jauge 12)
INSERT INTO creneau (date_creneau, heure_debut, heure_fin, jauge, id_admin, id_salle) VALUES
('2026-06-18','15:00:00','15:30:00',12,1,1),
('2026-06-18','15:30:00','16:00:00',12,1,1),
('2026-06-18','16:00:00','16:30:00',12,1,1),
('2026-06-18','16:30:00','17:00:00',12,1,1),
('2026-06-18','17:00:00','17:30:00',12,1,1),
('2026-06-18','17:30:00','18:00:00',12,1,1),
('2026-06-18','18:00:00','18:30:00',12,1,1),
('2026-06-18','19:00:00','19:30:00',12,1,1),
('2026-06-18','19:30:00','20:00:00',12,1,1),
('2026-06-18','20:00:00','20:30:00',12,1,1),
('2026-06-19','09:30:00','10:00:00',12,1,1),
('2026-06-19','10:00:00','10:30:00',12,1,1),
('2026-06-19','10:30:00','11:00:00',12,1,1),
('2026-06-19','11:00:00','11:30:00',12,1,1),
('2026-06-18','15:00:00','15:30:00',12,1,2),
('2026-06-18','15:30:00','16:00:00',12,1,2),
('2026-06-18','16:00:00','16:30:00',12,1,2),
('2026-06-18','16:30:00','17:00:00',12,1,2),
('2026-06-18','17:00:00','17:30:00',12,1,2),
('2026-06-18','17:30:00','18:00:00',12,1,2),
('2026-06-18','18:00:00','18:30:00',12,1,2),
('2026-06-18','19:00:00','19:30:00',12,1,2),
('2026-06-18','19:30:00','20:00:00',12,1,2),
('2026-06-18','20:00:00','20:30:00',12,1,2),
('2026-06-19','09:30:00','10:00:00',12,1,2),
('2026-06-19','10:00:00','10:30:00',12,1,2),
('2026-06-19','10:30:00','11:00:00',12,1,2),
('2026-06-19','11:00:00','11:30:00',12,1,2),
('2026-06-18','15:00:00','15:30:00',12,1,3),
('2026-06-18','15:30:00','16:00:00',12,1,3),
('2026-06-18','16:00:00','16:30:00',12,1,3),
('2026-06-18','16:30:00','17:00:00',12,1,3),
('2026-06-18','17:00:00','17:30:00',12,1,3),
('2026-06-18','17:30:00','18:00:00',12,1,3),
('2026-06-18','18:00:00','18:30:00',12,1,3),
('2026-06-18','19:00:00','19:30:00',12,1,3),
('2026-06-18','19:30:00','20:00:00',12,1,3),
('2026-06-18','20:00:00','20:30:00',12,1,3),
('2026-06-19','09:30:00','10:00:00',12,1,3),
('2026-06-19','10:00:00','10:30:00',12,1,3),
('2026-06-19','10:30:00','11:00:00',12,1,3),
('2026-06-19','11:00:00','11:30:00',12,1,3),
('2026-06-18','15:00:00','15:30:00',12,1,4),
('2026-06-18','15:30:00','16:00:00',12,1,4),
('2026-06-18','16:00:00','16:30:00',12,1,4),
('2026-06-18','16:30:00','17:00:00',12,1,4),
('2026-06-18','17:00:00','17:30:00',12,1,4),
('2026-06-18','17:30:00','18:00:00',12,1,4),
('2026-06-18','18:00:00','18:30:00',12,1,4),
('2026-06-18','19:00:00','19:30:00',12,1,4),
('2026-06-18','19:30:00','20:00:00',12,1,4),
('2026-06-18','20:00:00','20:30:00',12,1,4),
('2026-06-19','09:30:00','10:00:00',12,1,4),
('2026-06-19','10:00:00','10:30:00',12,1,4),
('2026-06-19','10:30:00','11:00:00',12,1,4),
('2026-06-19','11:00:00','11:30:00',12,1,4);

-- Participants (exemples)
INSERT INTO participant (nom, prenom, email, telephone) VALUES
('Martin', 'Lea',     'lea.martin@example.com',     '0612345678'),
('Dubois', 'Hugo',    'hugo.dubois@example.com',    '0623456789'),
('Bernard','Camille', 'camille.bernard@example.com','0634567890'),
('Petit',  'Noah',    'noah.petit@example.com',     '0645678901'),
('Robert', 'Emma',    'emma.robert@example.com',    '0656789012');

-- Inscriptions (exemples : 3 au creneau 1, 1 au creneau 2, 1 au creneau 15)
INSERT INTO inscription (id_creneau, id_participant, date_inscription) VALUES
(1, 1, '2026-05-20'),
(1, 2, '2026-05-20'),
(1, 3, '2026-05-21'),
(2, 4, '2026-05-21'),
(15, 5, '2026-05-22');

-- Contenir (une oeuvre par salle)
INSERT INTO contenir (id_salle, id_element) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4);

SET FOREIGN_KEY_CHECKS = 1;
