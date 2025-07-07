<?php
require_once __DIR__ . '/../models/Pret.php';

class InteretController {
    // GET /interets?ef=1&debut=2023-01-01&fin=2023-12-31
    public static function getInteretsParMois() {
        $id_ef = isset($_GET['ef']) ? intval($_GET['ef']) : null;
        $date_debut = isset($_GET['debut']) ? $_GET['debut'] : null;
        $date_fin = isset($_GET['fin']) ? $_GET['fin'] : null;
        header('Content-Type: application/json');
        if (!$id_ef || !$date_debut || !$date_fin) {
            http_response_code(400);
            echo json_encode(["error" => "Paramètres manquants (ef, debut, fin)"]);
            exit;
        }
        $result = Pret::getInteretsParMois($id_ef, $date_debut, $date_fin);
        echo json_encode($result);
        exit;
    }
} 