<?php
$host = '';
$usuario = '';
$senha = '';
$banco = '';

// Cria uma conexão usando MySQLi
$conexao = new mysqli($host, $usuario, $senha, $banco);

// Verifica a conexão
if ($conexao->connect_error) {
    die('Erro na conexão com o banco de dados: ' . $conexao->connect_error);
}
