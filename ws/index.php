<?php
require 'vendor/autoload.php';
require 'db.php';

Flight::route('GET /etudiants', function() {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM etudiant");
    Flight::json($stmt->fetchAll(PDO::FETCH_ASSOC));
});

Flight::route('GET /etudiants/@id', function($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM etudiant WHERE id = ?");
    $stmt->execute([$id]);
    Flight::json($stmt->fetch(PDO::FETCH_ASSOC));
});

Flight::route('POST /etudiants', function() {
    $data = Flight::request()->data;
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO etudiant (nom, prenom, email, age) VALUES (?, ?, ?, ?)");
    $stmt->execute([$data->nom, $data->prenom, $data->email, $data->age]);
    Flight::json(['message' => 'Étudiant ajouté', 'id' => $db->lastInsertId()]);
});

Flight::route('PUT /etudiants/@id', function($id) {
    $data = Flight::request()->data;

    // Correction : si les champs sont vides, on parse le corps manuellement
    if (empty($data->nom) && empty($data->prenom) && empty($data->email) && empty($data->age)) {
        parse_str(file_get_contents('php://input'), $put_vars);
        $data = (object)$put_vars;
    }

    $db = getDB();
    $stmt = $db->prepare("UPDATE etudiant SET nom = ?, prenom = ?, email = ?, age = ? WHERE id = ?");
    $stmt->execute([$data->nom, $data->prenom, $data->email, $data->age, $id]);
    Flight::json(['message' => 'Étudiant modifié']);
});

Flight::route('DELETE /etudiants/@id', function($id) {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM etudiant WHERE id = ?");
    $stmt->execute([$id]);
    Flight::json(['message' => 'Étudiant supprimé']);
});

// --- Etablissements Financiers ---
// Liste des EF
Flight::route('GET /efs', function() {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM etablissement_financier");
    Flight::json($stmt->fetchAll(PDO::FETCH_ASSOC));
});
// Ajouter un EF
Flight::route('POST /efs', function() {
    $data = Flight::request()->data;
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO etablissement_financier (nom, fonds) VALUES (?, ?)");
    $stmt->execute([$data->nom, $data->fonds]);
    Flight::json(['message' => 'EF ajouté', 'id' => $db->lastInsertId()]);
});
// Ajouter des fonds à un EF
Flight::route('PUT /efs/@id/fonds', function($id) {
    $data = Flight::request()->data;
    $db = getDB();
    $stmt = $db->prepare("UPDATE etablissement_financier SET fonds = fonds + ? WHERE id = ?");
    $stmt->execute([$data->montant, $id]);
    Flight::json(['message' => 'Fonds ajoutés']);
});

// --- Types de prêts ---
// Liste des types de prêts
Flight::route('GET /types-pret', function() {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM type_pret");
    Flight::json($stmt->fetchAll(PDO::FETCH_ASSOC));
});
// Créer un type de prêt
Flight::route('POST /types-pret', function() {
    $data = Flight::request()->data;
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO type_pret (nom, taux) VALUES (?, ?)");
    $stmt->execute([$data->nom, $data->taux]);
    Flight::json(['message' => 'Type de prêt ajouté', 'id' => $db->lastInsertId()]);
});

// --- Prêts ---
// Liste des prêts
Flight::route('GET /prets', function() {
    $db = getDB();
    $stmt = $db->query("SELECT pret.*, etudiant.nom AS nom_etudiant, etudiant.prenom, type_pret.nom AS nom_type, type_pret.taux FROM pret JOIN etudiant ON pret.id_etudiant = etudiant.id JOIN type_pret ON pret.id_type_pret = type_pret.id");
    Flight::json($stmt->fetchAll(PDO::FETCH_ASSOC));
});
// Créer un prêt
Flight::route('POST /prets', function() {
    $data = Flight::request()->data;
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO pret (id_etudiant, id_type_pret, montant, date_pret) VALUES (?, ?, ?, ?)");
    $stmt->execute([$data->id_etudiant, $data->id_type_pret, $data->montant, $data->date_pret]);
    Flight::json(['message' => 'Prêt accordé', 'id' => $db->lastInsertId()]);
});

Flight::start();