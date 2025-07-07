DROP DATABASE IF EXISTS banque;

CREATE DATABASE banque CHARACTER SET utf8mb4;

USE banque;

CREATE TABLE etudiant (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100),
    prenom VARCHAR(100),
    email VARCHAR(100),
    age INT
);

-- Table pour l'etablissement financier
CREATE TABLE etablissement_financier (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    fonds DECIMAL(15,2) NOT NULL DEFAULT 0
);

-- Table pour les types de prets
CREATE TABLE type_pret (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    taux DECIMAL(5,2) NOT NULL -- taux d'intérêt en pourcentage
);

-- Table pour les prets
CREATE TABLE pret (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_etudiant INT NOT NULL,
    id_ef INT NOT NULL,
    id_type_pret INT NOT NULL,
    montant DECIMAL(15,2) NOT NULL,
    date_pret DATE NOT NULL,
    duree_mois INT NOT NULL DEFAULT 12, -- durée en mois
    assurance DECIMAL(5,2) NOT NULL DEFAULT 0, -- en % du capital initial
    delai_premier_remboursement INT NOT NULL DEFAULT 0, -- en mois
    valide BOOL NOT NULL DEFAULT 1, -- 1 = validé, 0 = non validé
    FOREIGN KEY (id_etudiant) REFERENCES etudiant(id),
    FOREIGN KEY (id_ef) REFERENCES etablissement_financier(id),
    FOREIGN KEY (id_type_pret) REFERENCES type_pret(id)
);


INSERT INTO etudiant (nom, prenom, email, age) VALUES
('Rakoto', 'Jean', 'jean.rakoto@email.com', 22),
('Randria', 'Marie', 'marie.randria@email.com', 23),
('Rasoa', 'Paul', 'paul.rasoa@email.com', 21),
('Rabe', 'Nirina', 'nirina.rabe@email.com', 24),
('Rakotondrabe', 'Hery', 'hery.rakotondrabe@email.com', 20),
('Randrianarisoa', 'Voahangy', 'voahangy.randrianarisoa@email.com', 22),
('Ramanantsoa', 'Tiana', 'tiana.ramanantsoa@email.com', 25),
('Ratsimba', 'Tahina', 'tahina.ratsimba@email.com', 19),
('Andriamalala', 'Feno', 'feno.andriamalala@email.com', 23),
('Rajaonarison', 'Sariaka', 'sariaka.rajaonarison@email.com', 21);

INSERT INTO etablissement_financier (nom, fonds) VALUES
('Banky Foibe', 5000000.00),
('Credi-Mada', 2500000.00),
('BFV-SG', 8000000.00),
('BOA Madagascar', 6000000.00),
('MCB Madagascar', 7000000.00),
('AccesBanque Madagascar', 3000000.00);

INSERT INTO type_pret (nom, taux) VALUES
('Pret etudiant', 1.50),
('Pret auto', 3.75),
('Pret personnel', 4.25),
('Pret logement', 2.80),
('Pret professionnel', 3.50),
('Pret agricole', 1.25),
('Pret PME', 2.50);

INSERT INTO pret (id_etudiant, id_ef, id_type_pret, montant, date_pret, duree_mois, assurance, delai_premier_remboursement, valide) VALUES
(1, 1, 1, 800000.00, '2024-01-15', 12, 0, 0, 1),
(2, 2, 2, 15000000.00, '2024-02-20', 12, 0, 0, 1),
(3, 3, 3, 5000000.00, '2024-03-05', 12, 0, 0, 1),
(4, 1, 1, 1200000.00, '2024-01-25', 12, 0, 0, 1),
(5, 4, 4, 25000000.00, '2024-02-10', 12, 0, 0, 1),
(6, 5, 5, 8000000.00, '2024-03-15', 12, 0, 0, 1),
(7, 6, 6, 3000000.00, '2024-01-30', 12, 0, 0, 1),
(8, 1, 7, 10000000.00, '2024-02-25', 12, 0, 0, 1),
(9, 1, 1, 900000.00, '2024-03-01', 12, 0, 0, 1),
(10, 2, 2, 18000000.00, '2024-01-10', 12, 0, 0, 1),
(1, 3, 3, 4000000.00, '2024-02-15', 12, 0, 0, 1),
(2, 4, 4, 20000000.00, '2024-03-20', 12, 0, 0, 1),
(3, 5, 5, 7000000.00, '2024-01-05', 12, 0, 0, 1),
(4, 6, 6, 3500000.00, '2024-02-28', 12, 0, 0, 1),
(5, 1, 7, 12000000.00, '2024-03-10', 12, 0, 0, 1);

-- Ajout d'étudiants
INSERT INTO etudiant (nom, prenom, email, age) VALUES
('Dupont', 'Jean', 'jean.dupont@email.com', 22),
('Martin', 'Sophie', 'sophie.martin@email.com', 23);

-- Ajout d'établissements financiers
INSERT INTO etablissement_financier (nom, fonds) VALUES
('Banque Alpha', 100000),
('Banque Beta', 50000);

-- Ajout de types de prêt
INSERT INTO type_pret (nom, taux) VALUES
('Prêt étudiant', 5.00),
('Prêt auto', 3.50);

-- Ajout de prêts (pour Banque Alpha, id=1)
INSERT INTO pret (id_etudiant, id_ef, id_type_pret, montant, date_pret, duree_mois, assurance, delai_premier_remboursement, valide) VALUES
(1, 1, 1, 10000, '2023-01-15', 12, 0, 0, 1), -- Prêt étudiant, janvier 2023
(2, 1, 2, 5000, '2023-01-20', 12, 0, 0, 1),  -- Prêt auto, janvier 2023
(1, 1, 1, 8000, '2023-02-10', 12, 0, 0, 1),  -- Prêt étudiant, février 2023
(2, 1, 2, 7000, '2023-03-05', 12, 0, 0, 1);  -- Prêt auto, mars 2023

-- Ajout de prêts pour Banque Beta (id=2)
INSERT INTO pret (id_etudiant, id_ef, id_type_pret, montant, date_pret, duree_mois, assurance, delai_premier_remboursement, valide) VALUES
(1, 2, 1, 6000, '2023-01-25', 12, 0, 0, 1),
(2, 2, 2, 4000, '2023-02-15', 12, 0, 0, 1);