DROP DATABASE IF EXISTS banque;
CREATE DATABASE banque CHARACTER SET utf8mb4;
USE banque;

-- Tables de base
CREATE TABLE etudiant (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100),
    prenom VARCHAR(100),
    email VARCHAR(100),
    age INT
);

CREATE TABLE etablissement_financier (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    fonds DECIMAL(15,2) NOT NULL DEFAULT 0
);

CREATE TABLE type_pret (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    taux DECIMAL(5,2) NOT NULL
);

CREATE TABLE pret (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_etudiant INT NOT NULL,
    id_ef INT NOT NULL,
    id_type_pret INT NOT NULL,
    montant DECIMAL(15,2) NOT NULL,
    date_pret DATE NOT NULL,
    duree_mois INT NOT NULL DEFAULT 12,
    assurance DECIMAL(5,2) NOT NULL DEFAULT 0,
    delai_premier_remboursement INT NOT NULL DEFAULT 0,
    valide BOOL NOT NULL DEFAULT 1,
    FOREIGN KEY (id_etudiant) REFERENCES etudiant(id),
    FOREIGN KEY (id_ef) REFERENCES etablissement_financier(id),
    FOREIGN KEY (id_type_pret) REFERENCES type_pret(id)
);

-- Données malgaches de base
INSERT INTO etudiant (nom, prenom, email, age) VALUES
('Rakoto', 'Jean', 'jean.rakoto@mail.mg', 22),
('Randria', 'Marie', 'marie.randria@mail.mg', 23);

INSERT INTO etablissement_financier (nom, fonds) VALUES
('Banky Foibe', 10000000.00),
('BFV-SG', 50000000.00);

INSERT INTO type_pret (nom, taux) VALUES
('Prêt étudiant', 1.50),
('Prêt véhicule', 3.75),
('Prêt à taux zéro', 0.00),  -- Spécial pour tests
('Prêt spécial', 20.00);     -- Taux élevé pour tests

-- Données de test critiques pour les calculs
INSERT INTO pret (id_etudiant, id_ef, id_type_pret, montant, date_pret, duree_mois, assurance, delai_premier_remboursement, valide) VALUES
-- Cas standard (vérifier calculs de base)
(1, 1, 1, 1000000, '2024-01-01', 12, 1.5, 0, 1),

-- Cas taux 0% (doit avoir 0 d'intérêts)
(1, 1, 3, 500000, '2024-01-01', 6, 0, 0, 1),

-- Durée 1 mois (remboursement immédiat)
(2, 2, 2, 2000000, '2024-01-01', 1, 0, 0, 1),

-- Délai de 3 mois avant premier remboursement
(1, 2, 1, 1500000, '2024-01-01', 24, 2, 3, 1),

-- Assurance maximale (10%)
(2, 1, 2, 3000000, '2024-01-01', 36, 10, 0, 1),

-- Prêt non validé (doit être ignoré dans les calculs)
(1, 1, 1, 500000, '2024-01-01', 12, 1, 0, 0),

-- Taux élevé (20%)
(2, 2, 4, 750000, '2024-01-01', 24, 0, 0, 1);

-- Vérification des fonds initiaux
UPDATE etablissement_financier SET fonds = fonds - (
    SELECT SUM(montant) FROM pret WHERE id_ef = 1 AND valide = 1
) WHERE id = 1;

UPDATE etablissement_financier SET fonds = fonds - (
    SELECT SUM(montant) FROM pret WHERE id_ef = 2 AND valide = 1
) WHERE id = 2;