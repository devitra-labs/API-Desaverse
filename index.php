<?php

include_once 'config/cors.php';
include_once 'controller/AuthController.php';

// Ambil URL Request
// Contoh URL: localhost/my-api/index.php?action=register
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Ambil Data JSON dari React/Postman
$data = json_decode(file_get_contents("php://input"));

$auth = new AuthController();

switch ($action) {
    case 'register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth->register($data);
        } else {
            echo json_encode(['message' => 'Method not allowed.']);
        }
        break;

    case 'login';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth->login($data);
        } else {
            echo json_encode(['message' => 'Method not allowed.']);
        }
        break;
    
    default:
        http_response_code(404);
        echo json_encode(['message' => 'Endpoint tidak ditemukan. Coba ?action=login']);
        break;
}