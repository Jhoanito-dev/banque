CREATE DATABASE banque CHARACTER SET utf8mb4;

USE banque;

CREATE TABLE etudiant (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100),
    prenom VARCHAR(100),
    email VARCHAR(100),
    age INT
);

-- Table pour l'établissement financier
CREATE TABLE etablissement_financier (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    fonds DECIMAL(15,2) NOT NULL DEFAULT 0
);

-- Table pour les types de prêts
CREATE TABLE type_pret (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    taux DECIMAL(5,2) NOT NULL -- taux en pourcentage
);

-- Table pour les prêts
CREATE TABLE pret (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_etudiant INT NOT NULL,
    id_type_pret INT NOT NULL,
    montant DECIMAL(15,2) NOT NULL,
    date_pret DATE NOT NULL,
    FOREIGN KEY (id_etudiant) REFERENCES etudiant(id) ON DELETE CASCADE,
    FOREIGN KEY (id_type_pret) REFERENCES type_pret(id) ON DELETE CASCADE
);


INSERT INTO etudiant (nom, prenom, email, age) VALUES
('Dupont', 'Jean', 'jean.dupont@email.com', 22),
('Martin', 'Sophie', 'sophie.martin@email.com', 24),
('Durand', 'Paul', 'paul.durand@email.com', 21);


INSERT INTO etablissement_financier (nom, fonds) VALUES
('Banque Centrale', 100000.00),
('Crédit Rapide', 50000.00);

INSERT INTO type_pret (nom, taux) VALUES
('Prêt étudiant', 2.50),
('Prêt auto', 4.20),
('Prêt personnel', 5.00);

INSERT INTO pret (id_etudiant, id_type_pret, montant, date_pret) VALUES
(1, 1, 5000.00, '2024-06-01'),
(2, 2, 12000.00, '2024-06-05'),
(3, 3, 3000.00, '2024-06-10');