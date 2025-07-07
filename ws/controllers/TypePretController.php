<?php
require_once __DIR__ . '/../models/TypePret.php';

class TypePretController {
    public static function getAll() {
        $result = TypePret::getAll();
        header('Content-Type: application/json');
        echo json_encode($result);
    }
    public static function getById($id) {
        $result = TypePret::getById($id);
        header('Content-Type: application/json');
        echo json_encode($result);
    }
    public static function create() {
        $data = json_decode(file_get_contents('php://input'));
        $id = TypePret::create($data);
        echo json_encode(["id" => $id]);
    }
    public static function update($id) {
        $data = json_decode(file_get_contents('php://input'));
        TypePret::update($id, $data);
        echo json_encode(["success" => true]);
    }
    public static function delete($id) {
        TypePret::delete($id);
        echo json_encode(["success" => true]);
    }
} 