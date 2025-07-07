<?php
require 'vendor/autoload.php';
require 'db.php';

// Route pour ajouter un fond
Flight::route('POST /fonds', function() {
    $data = Flight::request()->data;
    $stmt = Flight::db()->prepare("INSERT INTO fonds (montant, date_depot) VALUES (?, NOW())");
    $stmt->execute([$data['montant']]);
    Flight::json(["success" => true]);
});

// Route pour lister les types de prêt
Flight::route('GET /types-pret', function() {
    $stmt = Flight::db()->query("SELECT * FROM type_pret");
    Flight::json($stmt->fetchAll(PDO::FETCH_ASSOC));
});

// Suite :
// Gestion des clients
Flight::route('GET /clients', function() {
    $stmt = Flight::db()->query("SELECT * FROM client");
    Flight::json($stmt->fetchAll(PDO::FETCH_ASSOC));
});

Flight::route('POST /clients', function() {
    $data = Flight::request()->data;
    $stmt = Flight::db()->prepare("INSERT INTO client (nom, email) VALUES (?, ?)");
    $stmt->execute([$data['nom'], $data['email']]);
    Flight::json(["success" => true]);
});

// Gestion des prêts (avec calcul d'intérêts)
Flight::route('POST /prets', function() {
    $data = Flight::request()->data;
    $stmt = Flight::db()->prepare("
        INSERT INTO pret (client_id, type_pret_id, montant, date_debut) 
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$data['clientId'], $data['typePretId'], $data['montant']]);
    Flight::json(["success" => true]);
});

Flight::route('GET /prets', function() {
    $stmt = Flight::db()->query("
        SELECT p.id, c.nom AS client_nom, t.nom AS type_pret_nom, 
               p.montant, t.taux, p.date_debut
        FROM pret p
        JOIN client c ON p.client_id = c.id
        JOIN type_pret t ON p.type_pret_id = t.id
    ");
    Flight::json($stmt->fetchAll(PDO::FETCH_ASSOC));
});

// Démarrer l'API
Flight::start();