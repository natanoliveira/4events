<?php
require 'functions.php';

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));

// echo pre($request);
// echo pre($method);

// $routes = [
//     'list' => 'listCars',
//     'one' => 'getCar',
//     'new' => 'newCar',
//     'update' => 'updateCar',
//     'delete' => 'deleteCar',
// ];

// Executa a operação de geração de token
if ($method == 'POST' && $request[1] == 'generate-token') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['user_id'], $input['username'], $input['password'])) {
        $token = generateBearerToken($input['user_id'], $input['username'], $input['password']);
        echo json_encode(['token' => $token]);
        exit();
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Parâmetros inválidos']);
        exit();
    }
}

// Verifica a autenticação para os outros endpoints
$headers = getallheaders();
$token = isset($headers['Authorization']) ? trim(str_replace('Bearer', '', $headers['Authorization'])) : null;
if (!validateToken($token)) {
    http_response_code(401);
    echo json_encode(['error' => 'Acesso não autorizado']);
    exit();
}

// echo pre($request);
// echo pre($method);

switch ($method) {
    case 'GET':
        if ($request[1] == 'list') {
            echo json_encode(listCars());
        } elseif ($request[1] == 'one' && isset($request[2])) {
            echo json_encode(getCar($request[2]));
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint não encontrado']);
        }
        break;
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if ($request[1] == 'new' && isset($input['nome'], $input['descricao'])) {
            echo json_encode(newCar($input['nome'], $input['descricao']));
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Parâmetros inválidos']);
        }
        break;
    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        if ($request[1] == 'update' && isset($request[2], $input['nome'], $input['descricao'])) {
            echo json_encode(updateCar($request[2], $input['nome'], $input['descricao']));
        }
        // Segmentado a situação para futura implementação
        elseif (($request[1] == 'situation' && $request[2] == 'active') && isset($request[3], $input['situacao'])) {
            echo json_encode(updateCarSituation($request[2], $input['situacao']));
        }
        // Segmentado a situação para futura implementação
        elseif (($request[1] == 'situation' && $request[2] == 'inactive') && isset($request[3], $input['situacao'])) {
            echo json_encode(updateCarSituation($request[2], $input['situacao']));
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Parâmetros inválidos']);
        }
        break;
    case 'DELETE':
        if ($request[1] == 'delete' && isset($request[2])) {
            echo json_encode(deleteCar($request[2]));
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Parâmetros inválidos']);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
        break;
}
