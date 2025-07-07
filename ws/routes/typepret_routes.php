<?php
require_once __DIR__ . '/../controllers/TypePretController.php';

$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET' && preg_match('#/ws/types-pret/?$#', $uri)) {
    TypePretController::getAll();
    exit;
}
if ($method === 'GET' && preg_match('#/ws/types-pret/(\d+)$#', $uri, $matches)) {
    TypePretController::getById($matches[1]);
    exit;
}
if ($method === 'POST' && preg_match('#/ws/types-pret/?$#', $uri)) {
    TypePretController::create();
    exit;
}
if ($method === 'PUT' && preg_match('#/ws/types-pret/(\d+)$#', $uri, $matches)) {
    TypePretController::update($matches[1]);
    exit;
}
if ($method === 'DELETE' && preg_match('#/ws/types-pret/(\d+)$#', $uri, $matches)) {
    TypePretController::delete($matches[1]);
    exit;
} 