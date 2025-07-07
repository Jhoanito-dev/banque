<?php
require 'vendor/autoload.php';
require 'db.php';

// Récupère le chemin de la requête (ex: /ws/interets)
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$basePath = rtrim($scriptName, '/');
$path = substr($requestUri, strlen($basePath));
$path = strtok($path, '?'); // retire les paramètres GET

// Harmonisation : toutes les routes personnalisées
switch (true) {
    case preg_match('#^/etudiants#', $path):
        require_once __DIR__ . '/routes/etudiant_routes.php';
        exit;
    case preg_match('#^/efs#', $path):
        require_once __DIR__ . '/routes/ef_routes.php';
        exit;
    case preg_match('#^/types-pret#', $path):
        require_once __DIR__ . '/routes/typepret_routes.php';
        exit;
    case preg_match('#^/prets#', $path):
        require_once __DIR__ . '/routes/pret_routes.php';
        exit;
    case preg_match('#^/interets#', $path):
        require_once __DIR__ . '/routes/interet_routes.php';
        exit;
    default:
        // Ici, tu peux ajouter des routes Flight si besoin
        Flight::start();
        exit;
}