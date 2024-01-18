<?php
$host = 'natanoliveira.com.br';
$usuario = 'natan561_usu4events';
$senha = '#_I#)BhD~r+D';
$banco = 'natan561_4events';

// try {
//     $conexao = new PDO("mysql:host=$host;dbname=$banco;charset=utf8", $usuario, $senha);
//     $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// } catch (PDOException $e) {
//     die('Erro na conexão com o banco de dados: ' . $e->getMessage());
// }

// Cria uma conexão usando MySQLi
$conexao = new mysqli($host, $usuario, $senha, $banco);

// Verifica a conexão
if ($conexao->connect_error) {
    die('Erro na conexão com o banco de dados: ' . $conexao->connect_error);
}
