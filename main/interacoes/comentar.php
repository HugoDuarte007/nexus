<?php
session_start();
require "../ligabd.php";

header('Content-Type: application/json');

if (!isset($_SESSION["user"])) {
    echo json_encode(['success' => false, 'error' => 'Não autenticado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit();
}

if (!isset($_POST['idpublicacao']) || !isset($_POST['comentario'])) {
    echo json_encode(['success' => false, 'error' => 'Dados incompletos']);
    exit();
}

$utilizador = $_SESSION["user"];
$idpublicacao = intval($_POST['idpublicacao']);
$conteudo = mysqli_real_escape_string($con, $_POST['comentario']);

// Buscar ID do utilizador
$query = "SELECT idutilizador FROM utilizador WHERE user = '$utilizador'";
$result = mysqli_query($con, $query);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    echo json_encode(['success' => false, 'error' => 'Utilizador não encontrado']);
    exit();
}

$idutilizador = $user['idutilizador'];

// Inserir comentário
$sql = "INSERT INTO comentario (idutilizador, idpublicacao, conteudo, data) 
        VALUES ($idutilizador, $idpublicacao, '$conteudo', NOW())";

if (mysqli_query($con, $sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => mysqli_error($con)]);
}