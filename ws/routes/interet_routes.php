<?php
require_once __DIR__ . '/../controllers/InteretController.php';

$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Matche /interets ou /interets?... dans n'importe quel sous-dossier
if ($method === 'GET' && preg_match('#/interets($|\?)#', $uri)) {
    header('Content-Type: application/json');
    InteretController::getInteretsParMois();
    exit;
}
// Si rien n'est matché, on renvoie un tableau vide pour éviter une réponse vide
header('Content-Type: application/json');
echo json_encode([]);
exit; 