<?php
require 'database.php';

define("TOKEN_FIXO", "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IlVzw6FyaW8gQVBJIiwiaWF0IjoyMDI0MDExMTE1NDB9.Dw4-6kkqRgeLsRQT1vAwttdiLWHzbY0ysow1f18IMC4");
define("SEM_REGISTROS", "Sem registros para visualização");
define("DADO_EXISTENTE", "Existe um carro com o mesmo nome");
define("REGISTROS_REMOVIDO", "Registro removido com sucesso!");

function validateToken($token)
{
    return ($token === TOKEN_FIXO);
}

function listCars()
{
    global $conexao;

    try {
        $stmt = $conexao->prepare("SELECT * FROM carros");
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($resultados) > 0) {
            return $resultados;
        } else {
            return ["message" => SEM_REGISTROS];
        }
    } catch (PDOException $e) {
        die('Erro na listagem de carros: ' . $e->getMessage());
    }

    return null;
}

function getCar($id)
{
    global $conexao;

    try {

        $stmt = $conexao->prepare("SELECT * FROM carros WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $carro = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$carro) {
            return ["message" => SEM_REGISTROS, "exist" => false];
        }

        return $carro;
    } catch (PDOException $e) {
        die('Erro ao obter detalhes do carro: ' . $e->getMessage());
    }

    return null;
}

function newCar($nome, $descricao)
{
    global $conexao;

    try {

        // Tratamento para evitar dupicidade em descrição
        $stmt = $conexao->prepare("SELECT * FROM carros WHERE nome = :nome");
        $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
        $stmt->execute();

        $existe = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existe) {
            $mensagem = DADO_EXISTENTE . ": $nome";
            return ["message" => $mensagem];
        }

        $stmt = $conexao->prepare("INSERT INTO carros (nome, descricao) VALUES (:nome, :descricao)");
        $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
        $stmt->bindParam(':descricao', $descricao, PDO::PARAM_STR);
        $stmt->execute();

        $novoId = $conexao->lastInsertId();

        return getCar($novoId);
    } catch (PDOException $e) {
        die('Erro ao adicionar novo carro: ' . $e->getMessage());
    }

    return null;
}

function updateCar($id, $nome, $descricao)
{
    global $conexao;

    try {
        $stmt = $conexao->prepare("UPDATE carros SET nome = :nome, descricao = :descricao WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
        $stmt->bindParam(':descricao', $descricao, PDO::PARAM_STR);
        $stmt->execute();

        return getCar($id);
    } catch (PDOException $e) {
        die('Erro ao atualizar carro: ' . $e->getMessage());
    }

    return null;
}

function deleteCar($id)
{
    global $conexao;

    try {
        // vamos verificar se o carro ainda existe em banco de dados para retornar a mensagem ao front
        $deletado = getCar($id);

        if (!$deletado['exists']) {
            return ['success' => false, 'message' => "Registro não existe para remover."];
        }

        $stmt = $conexao->prepare("DELETE FROM carros WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return ['success' => true, 'message' => REGISTROS_REMOVIDO];
    } catch (PDOException $e) {
        die('Erro ao excluir carro: ' . $e->getMessage());
    }

    return null;
}

function updateCarSituation($id, $situacao)
{
    global $conexao;

    try {
        $stmt = $conexao->prepare("UPDATE carros SET situacao = :situacao WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':situacao', $situacao, PDO::PARAM_STR);
        $stmt->execute();

        return getCar($id);
    } catch (PDOException $e) {
        die('Erro ao atualizar carro: ' . $e->getMessage());
    }

    return null;
}

function generateBearerToken($user_id, $username, $password)
{

    // Exemplo simples: Concatenando user_id, username e uma chave secreta para formar o token
    $token = hash('sha256', $user_id . $username . 'sua_chave_secreta');

    // Você deve armazenar o token no banco de dados ou em outra forma segura
    // Certifique-se de ajustar conforme necessário para a segurança da sua aplicação

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
