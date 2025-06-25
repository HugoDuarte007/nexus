<?php
session_start();
require "../../ligabd.php";

// Verifica se o utilizador está autenticado
if (!isset($_SESSION["user"])) {
    http_response_code(401);
    exit("Não autenticado");
}

$utilizador = $_SESSION["user"];
$idutilizador_atual = $_SESSION["idutilizador"];

// Verifica se foi enviado o ID da publicação
if (!isset($_POST['idpublicacao'])) {
    http_response_code(400);
    exit("Dados em falta");
}

$idpublicacao = intval($_POST['idpublicacao']);
$data_like = date("Y-m-d H:i:s");

// Verificar se o like já existe
$sql_check = "SELECT * FROM likes WHERE idutilizador = ? AND idpublicacao = ?";
$stmt_check = mysqli_prepare($con, $sql_check);
mysqli_stmt_bind_param($stmt_check, "ii", $idutilizador_atual, $idpublicacao);
mysqli_stmt_execute($stmt_check);
$res_check = mysqli_stmt_get_result($stmt_check);

if (mysqli_num_rows($res_check) > 0) {
    // Já existe -> remover like
    $sql_remove = "DELETE FROM likes WHERE idutilizador = ? AND idpublicacao = ?";
    $stmt_remove = mysqli_prepare($con, $sql_remove);
    mysqli_stmt_bind_param($stmt_remove, "ii", $idutilizador_atual, $idpublicacao);
    mysqli_stmt_execute($stmt_remove);
    echo "unliked";
} else {
    // Inserir novo like
    $sql_insert = "INSERT INTO likes (idutilizador, idpublicacao, data) VALUES (?, ?, ?)";
    $stmt_insert = mysqli_prepare($con, $sql_insert);
    mysqli_stmt_bind_param($stmt_insert, "iis", $idutilizador_atual, $idpublicacao, $data_like);
    mysqli_stmt_execute($stmt_insert);
    echo "liked";
}
?>