<?php
session_start();
require "../../ligabd.php";

if (!isset($_SESSION["user"])) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit();
}

if (!isset($_POST['idpublicacao'])) {
    echo json_encode(['success' => false, 'message' => 'ID da publicação não fornecido']);
    exit();
}

$idPublicacao = $_POST['idpublicacao'];
$idUtilizador = $_SESSION["idutilizador"];

// Verificar se a publicação está guardada pelo utilizador
$query = "DELETE FROM guardado WHERE idpublicacao = ? AND idutilizador = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "ii", $idPublicacao, $idUtilizador);
mysqli_stmt_execute($stmt);

if (mysqli_stmt_affected_rows($stmt) > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Publicação não estava guardada ou erro ao remover']);
}

mysqli_stmt_close($stmt);
mysqli_close($con);
?>