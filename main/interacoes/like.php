<?php
session_start();
require "../ligabd.php";

// Verifica se o utilizador está autenticado
if (!isset($_SESSION["user"])) {
    http_response_code(401);
    exit("Não autenticado");
}

$utilizador = $_SESSION["user"];

// Escapa o nome de utilizador
$utilizador = mysqli_real_escape_string($con, $utilizador);

// Obter ID do utilizador
$sql_user = "SELECT idutilizador FROM utilizador WHERE user = '$utilizador'";
$result_user = mysqli_query($con, $sql_user);
$user_data = mysqli_fetch_array($result_user);
$id_utilizador = $user_data['idutilizador'] ?? null;

// Verifica se foi enviado o ID da publicação
if (!isset($_POST['id_publicacao']) || !$id_utilizador) {
    http_response_code(400);
    exit("Dados em falta");
}

$id_publicacao = intval($_POST['id_publicacao']);
$data_like = date("Y-m-d H:i:s");

// Verificar se o like já existe
$sql_check = "SELECT * FROM likes WHERE id_utilizador = $id_utilizador AND id_publicacao = $id_publicacao";
$res_check = mysqli_query($con, $sql_check);

if (mysqli_num_rows($res_check) > 0) {
    // Já existe -> remover like
    $sql_remove = "DELETE FROM likes WHERE id_utilizador = $id_utilizador AND id_publicacao = $id_publicacao";
    mysqli_query($con, $sql_remove);
    echo "unliked";
} else {
    // Inserir novo like
    $sql_insert = "INSERT INTO likes (id_utilizador, id_publicacao, data) VALUES ($id_utilizador, $id_publicacao, '$data_like')";
    mysqli_query($con, $sql_insert);
    echo "liked";
}
?>
