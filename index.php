<?php

include_once 'config/cors.php';
include_once 'controller/AuthController.php';
include_once __DIR__ . '/bootstrap.php';

$pdo = get_pdo(); // DB connection

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

    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth->login($data);
        } else {
            echo json_encode(['message' => 'Method not allowed.']);
        }
        break;

    case 'bmkg_prakiraan':
        if (!isset($_GET['adm4']) || empty($_GET['adm4'])) {
            http_response_code(400);
            echo json_encode(['message' => 'adm4 parameter is required']);
            break;
        }
        require_once __DIR__ . '/Service/BmkgService.php';
        try {
            $bmkg = new BmkgService();
            $adm4 = $_GET['adm4'];
            $data = $bmkg->fetchPrakiraanCuacaPublic($adm4);
            header('Content-Type: application/json');
            echo json_encode($data);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'BMKG error: ' . $e->getMessage()]);
        }
        break;

    case 'bmkg_nowcast':
        require_once __DIR__ . '/Service/BmkgService.php';
        try {
            $bmkg = new BmkgService();
            $data = $bmkg->fetchNowcastAlerts();
            header('Content-Type: application/json');
            echo json_encode($data);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'BMKG error: ' . $e->getMessage()]);
        }
        break;

    case 'bmkg_prakiraan_desa':
        if (!isset($_GET['desa']) || empty($_GET['desa'])) {
            http_response_code(400);
            echo json_encode(['message' => 'desa parameter is required']);
            break;
        }
        require_once __DIR__ . '/Service/BmkgService.php';
        try {
            $bmkg = new BmkgService();
            $desa = $_GET['desa'];
            $adm4 = $bmkg->resolveAdmin4ForDesa($desa);
            if (!$adm4) {
                http_response_code(404);
                echo json_encode(['message' => 'adm4 tidak ditemukan untuk desa ini']);
                break;
            }
            $prakiraan = $bmkg->fetchPrakiraanCuacaPublic($adm4);
            $nowcast = $bmkg->fetchNowcastAlerts();
            $response = [
                'desa' => $desa,
                'adm4' => $adm4,
                'prakiraan' => $prakiraan,
                'nowcast_alerts' => $nowcast
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'BMKG error: ' . $e->getMessage()]);
        }
        break;

    case 'bmkg_latest':
        if (!isset($_GET['adm4']) || empty($_GET['adm4'])) {
            http_response_code(400);
            echo json_encode(['message' => 'adm4 parameter is required']);
            break;
        }
        // Use DB models to fetch latest entries by adm4
        try {
            require_once __DIR__ . '/app/Models/BmkgPrakiraanModel.php';
            require_once __DIR__ . '/app/Models/BmkgNowcastModel.php';
            $prakiraanModel = new BmkgPrakiraanModel($pdo);
            $nowcastModel = new BmkgNowcastModel($pdo);
            $adm4Val = $_GET['adm4'];
            $prakiraanLatest = $prakiraanModel->getLatestByAdm4($adm4Val);
            $nowcastLatest  = $nowcastModel->getLatestByAdm4($adm4Val);
            $resp = [
                'adm4' => $adm4Val,
                'prakiraan_latest' => $prakiraanLatest,
                'nowcast_latest' => $nowcastLatest
            ];
            header('Content-Type: application/json');
            echo json_encode($resp);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'BMKG latest error: ' . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['message' => 'Endpoint tidak ditemukan. Coba ?action=login']);
        break;
}
?>