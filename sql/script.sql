CREATE DATABASE banque CHARACTER SET utf8mb4;
USE banque;

-- Table pour les fonds de l'établissement
CREATE TABLE fonds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    montant DECIMAL(10,2),
    date_depot DATE
);

-- Table pour les types de prêt
CREATE TABLE type_pret (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50),
    taux DECIMAL(5,2)
);

-- Table pour les clients et prêts
CREATE TABLE client (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100),
    email VARCHAR(100)
);

CREATE TABLE pret (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT,
    type_pret_id INT,
    montant DECIMAL(10,2),
    date_debut DATE,
    FOREIGN KEY (client_id) REFERENCES client(id),
    FOREIGN KEY (type_pret_id) REFERENCES type_pret(id)
);



INSERT INTO type_pret (nom, taux) VALUES 
('Prêt personnel', 5.5),
('Prêt immobilier', 2.75);

INSERT INTO client (nom, email) VALUES 
('Jhoanito', 'jhoanito@ad.com'),
('Bob', 'bob@ad.com');