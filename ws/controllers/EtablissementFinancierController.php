<?php
require_once __DIR__ . '/../models/EtablissementFinancier.php';

class EtablissementFinancierController {
    public static function getAll() {
        $result = EtablissementFinancier::getAll();
        header('Content-Type: application/json');
        echo json_encode($result);
    }
    public static function getById($id) {
        $result = EtablissementFinancier::getById($id);
        header('Content-Type: application/json');
        echo json_encode($result);
    }
    public static function create() {
        $data = json_decode(file_get_contents('php://input'));
        $id = EtablissementFinancier::create($data);
        echo json_encode(["id" => $id]);
    }
    public static function update($id) {
        $data = json_decode(file_get_contents('php://input'));
        EtablissementFinancier::update($id, $data);
        echo json_encode(["success" => true]);
    }
    public static function delete($id) {
        EtablissementFinancier::delete($id);
        echo json_encode(["success" => true]);
    }
    public static function updateFonds($id) {
        $data = json_decode(file_get_contents('php://input'));
        EtablissementFinancier::updateFonds($id, $data);
        echo json_encode(["success" => true]);
    }
} 