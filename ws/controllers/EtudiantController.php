<?php
require_once __DIR__ . '/../db.php';

class EtudiantController {
    public static function getAll() {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM etudiant");
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        header('Content-Type: application/json');
        echo json_encode($result);
    }
    public static function getById($id) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM etudiant WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        header('Content-Type: application/json');
        echo json_encode($result);
    }
    public static function create() {
        $data = json_decode(file_get_contents('php://input'));
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO etudiant (nom, prenom, email, age) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data->nom, $data->prenom, $data->email, $data->age]);
        echo json_encode(["id" => $db->lastInsertId()]);
    }
    public static function update($id) {
        $data = json_decode(file_get_contents('php://input'));
        $db = getDB();
        $stmt = $db->prepare("UPDATE etudiant SET nom = ?, prenom = ?, email = ?, age = ? WHERE id = ?");
        $stmt->execute([$data->nom, $data->prenom, $data->email, $data->age, $id]);
        echo json_encode(["success" => true]);
    }
    public static function delete($id) {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM etudiant WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(["success" => true]);
    }
} 