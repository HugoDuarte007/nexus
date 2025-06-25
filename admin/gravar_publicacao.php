<?php
session_start();
require "../ligabd.php";

// Verificar se o utilizador está autenticado e é admin
if (!isset($_SESSION["user"]) || $_SESSION["id_tipos_utilizador"] != 0) {
    header("Location: ../logout.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["idpublicacao"])) {
    $idpublicacao = (int)$_POST["idpublicacao"];
    $descricao = mysqli_real_escape_string($con, $_POST["descricao"]);
    
    // Atualizar a descrição da publicação
    $sql = "UPDATE publicacao SET descricao = ? WHERE idpublicacao = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "si", $descricao, $idpublicacao);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION["sucesso"] = "Publicação atualizada com sucesso!";
    } else {
        $_SESSION["erro"] = "Erro ao atualizar publicação: " . mysqli_error($con);
    }
    
    mysqli_stmt_close($stmt);
}

header("Location: publicacoes.php");
exit();
?>