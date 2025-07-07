<?php
require_once __DIR__ . '/../db.php';

class EtablissementFinancier {
    public static function getAll() {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM etablissement_financier");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM etablissement_financier WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($data) {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO etablissement_financier (nom, fonds) VALUES (?, ?)");
        $stmt->execute([$data->nom, $data->fonds]);
        return $db->lastInsertId();
    }

    public static function update($id, $data) {
        $db = getDB();
        $stmt = $db->prepare("UPDATE etablissement_financier SET nom = ?, fonds = ? WHERE id = ?");
        $stmt->execute([$data->nom, $data->fonds, $id]);
    }

    public static function updateFonds($id, $data) {
        $db = getDB();
        $stmt = $db->prepare("UPDATE etablissement_financier SET fonds = fonds + ? WHERE id = ?");
        $stmt->execute([$data->montant, $id]);
    }

    public static function delete($id) {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM etablissement_financier WHERE id = ?");
        $stmt->execute([$id]);
    }
} 