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
        if ($request[0] == 'list' && count($request) == 1) {
            echo json_encode(show());
        } elseif ($request[0] == 'list' && isset($request[1])) {
            echo json_encode(getById($request[1]));
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint não encontrado']);
        }
        break;
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if ($request[0] == 'new' && isset($input['nome'], $input['sobrenome'])) {
            echo json_encode(insert($input['nome'], $input['sobrenome']));
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Parâmetros inválidos']);
        }
        break;
    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        if ($request[0] == 'update' && isset($request[1], $input['nome'], $input['sobrenome'])) {
            echo json_encode(edit($request[1], $input['nome'], $input['sobrenome']));
        }
        // Segmentado a situação para futura implementação
        elseif (($request[0] == 'situation' && $request[1] == 'active') && isset($request[2], $input['situacao'])) {
            echo json_encode(editSituation($request[2], $input['situacao']));
        }
        // Segmentado a situação para futura implementação
        elseif (($request[0] == 'situation' && $request[1] == 'inactive') && isset($request[2], $input['situacao'])) {

            echo json_encode(editSituation($request[2], $input['situacao']));
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Parâmetros inválidos']);
        }
        break;
    case 'DELETE':
        if ($request[0] == 'delete' && isset($request[1])) {
            echo json_encode(delete($request[1]));
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
