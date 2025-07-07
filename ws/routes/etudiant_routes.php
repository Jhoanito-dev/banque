<?php
require_once __DIR__ . '/../controllers/EtudiantController.php';

$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET' && preg_match('#/ws/etudiants/?$#', $uri)) {
    EtudiantController::getAll();
    exit;
}
if ($method === 'GET' && preg_match('#/ws/etudiants/(\d+)$#', $uri, $matches)) {
    EtudiantController::getById($matches[1]);
    exit;
}
if ($method === 'POST' && preg_match('#/ws/etudiants/?$#', $uri)) {
    EtudiantController::create();
    exit;
}
if ($method === 'PUT' && preg_match('#/ws/etudiants/(\d+)$#', $uri, $matches)) {
    EtudiantController::update($matches[1]);
    exit;
}
if ($method === 'DELETE' && preg_match('#/ws/etudiants/(\d+)$#', $uri, $matches)) {
    EtudiantController::delete($matches[1]);
    exit;
} 