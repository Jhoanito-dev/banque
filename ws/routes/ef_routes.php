<?php
require_once __DIR__ . '/../controllers/EtablissementFinancierController.php';

$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET' && preg_match('#/ws/efs/?$#', $uri)) {
    EtablissementFinancierController::getAll();
    exit;
}
if ($method === 'GET' && preg_match('#/ws/efs/(\d+)$#', $uri, $matches)) {
    EtablissementFinancierController::getById($matches[1]);
    exit;
}
if ($method === 'POST' && preg_match('#/ws/efs/?$#', $uri)) {
    EtablissementFinancierController::create();
    exit;
}
if ($method === 'PUT' && preg_match('#/ws/efs/(\d+)$#', $uri, $matches)) {
    EtablissementFinancierController::update($matches[1]);
    exit;
}
if ($method === 'PUT' && preg_match('#/ws/efs/(\d+)/fonds$#', $uri, $matches)) {
    EtablissementFinancierController::updateFonds($matches[1]);
    exit;
}
if ($method === 'DELETE' && preg_match('#/ws/efs/(\d+)$#', $uri, $matches)) {
    EtablissementFinancierController::delete($matches[1]);
    exit;
} 