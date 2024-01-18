<?php
require '../database.php';

define("TOKEN_FIXO", "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IlVzw6FyaW8gQVBJIiwiaWF0IjoyMDI0MDExMTE1NDB9.Dw4-6kkqRgeLsRQT1vAwttdiLWHzbY0ysow1f18IMC4");
define("SEM_REGISTROS", "Sem registros para visualização");
define("FALHA", "Não foi possível executar esta operação");
define("DADO_EXISTENTE", "Existe um carro com o mesmo nome");
define("REGISTROS_REMOVIDO", "Registro removido com sucesso!");
define("CHAVE_SECRETA", "20240111");

$TabelaBase = "carros";

function validateToken($token)
{
    return ($token === TOKEN_FIXO);
}

function show()
{
    global $conexao, $TabelaBase;

    $resultado = $conexao->query("SELECT * FROM $TabelaBase");

    if ($resultado) {
        if ($resultado->num_rows > 0) {
            return $resultado->fetch_all(MYSQLI_ASSOC);
        }
    } else {
        die('Erro na listagem de carros: ' . $conexao->error);
    }

    return ["message" => SEM_REGISTROS];
}

function getById($id)
{
    global $conexao, $TabelaBase;

    $query = "SELECT * FROM $TabelaBase WHERE id = ?";
    $stmt = $conexao->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado) {
        if ($resultado->num_rows > 0) {
            return $resultado->fetch_assoc();
        }
    } else {
        die('Erro ao obter detalhes do carro: ' . $conexao->error);
    }

    return ["message" => SEM_REGISTROS, "exist" => false];
}

function insert($nome, $descricao)
{
    global $conexao, $TabelaBase;

    // Verificar se o nome do registro já existe para não gerar duplicidade
    $query = "SELECT * FROM $TabelaBase WHERE nome = ?";
    $stmt = $conexao->prepare($query);
    $stmt->bind_param('s', $nome);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $mensagem = DADO_EXISTENTE . ": $nome";
        return ["message" => $mensagem];
    }

    $query = "INSERT INTO $TabelaBase (nome, descricao, token) VALUES (?, ?, UUID())";
    $stmt = $conexao->prepare($query);
    $stmt->bind_param('ss', $nome, $descricao);

    if ($stmt->execute()) {
        $novoId = $conexao->insert_id;
        return getById($novoId);
    } else {
        die('Erro ao adicionar novo carro: ' . $conexao->error);
    }

    return ["messag" => FALHA];
}

function edit($id, $nome, $descricao)
{
    global $conexao, $TabelaBase;

    $query = "UPDATE $TabelaBase SET nome = ?, descricao = ? WHERE id = ?";
    $stmt = $conexao->prepare($query);
    $stmt->bind_param('ssi', $nome, $descricao, $id);

    if ($stmt->execute()) {
        return getById($id);
    } else {
        die('Erro ao atualizar carro: ' . $conexao->error);
    }

    return ["message" => FALHA . " " . $conexao->error];
}

function delete($id)
{
    global $conexao, $TabelaBase;

    // vamos verificar se o carro ainda existe em banco de dados para retornar a mensagem ao front
    $deletado = getById($id);

    if (!$deletado['id']) {
        return ['success' => false, 'message' => "Registro não existe para remover."];
    }

    $query = "DELETE FROM $TabelaBase WHERE id = ?";
    $stmt = $conexao->prepare($query);
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        return ['success' => true, 'message' => REGISTROS_REMOVIDO];
    } else {
        die('Erro ao excluir carro: ' . $conexao->error);
    }

    return ["messag" => FALHA];
}

function editSituation($id, $situacao)
{
    global $conexao, $TabelaBase;

    $query = "UPDATE $TabelaBase SET situacao = ? WHERE id = ?";
    $stmt = $conexao->prepare($query);
    $stmt->bind_param('si', $situacao, $id);

    if ($stmt->execute()) {
        return getById($id);
    } else {
        die('Erro ao atualizar carro: ' . $conexao->error);
    }

    return ["messag" => FALHA];
}

function generateBearerToken($user_id, $username, $password)
{
    $token = hash('sha256', $user_id . $username . CHAVE_SECRETA);
    return $token;
}


function pre($lista, $saida = 0)
{

    echo "<pre>";
    print_r($lista);
    echo "</pre>";

    if ($saida == 1) {
        exit;
    }
}
