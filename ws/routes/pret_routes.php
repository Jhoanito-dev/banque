<?php
require_once __DIR__ . '/../controllers/PretController.php';

$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Simulation de prêt
if ($method === 'POST' && preg_match('#/ws/prets/simuler$#', $uri)) {
    PretController::simulerPret();
    exit;
}
// Valider/refuser un prêt
if ($method === 'POST' && preg_match('#/ws/prets/(\d+)/valider$#', $uri, $matches)) {
    PretController::validerPret($matches[1]);
    exit;
}
// Génération PDF prêt
if ($method === 'GET' && preg_match('#/ws/prets/(\d+)/pdf$#', $uri, $matches)) {
    PretController::genererPDFPret($matches[1]);
    exit;
}
// CRUD standard
if ($method === 'GET' && preg_match('#/ws/prets/?$#', $uri)) {
    PretController::getAll();
    exit;
}
if ($method === 'GET' && preg_match('#/ws/prets/(\d+)$#', $uri, $matches)) {
    PretController::getById($matches[1]);
    exit;
}
if ($method === 'POST' && preg_match('#/ws/prets/?$#', $uri)) {
    PretController::create();
    exit;
}
if ($method === 'PUT' && preg_match('#/ws/prets/(\d+)$#', $uri, $matches)) {
    PretController::update($matches[1]);
    exit;
}
if ($method === 'DELETE' && preg_match('#/ws/prets/(\d+)$#', $uri, $matches)) {
    PretController::delete($matches[1]);
    exit;
} 