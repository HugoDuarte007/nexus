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
    
    // Iniciar transação
    mysqli_autocommit($con, false);
    
    try {
        // Buscar e remover arquivos de mídia
        $sql_media = "SELECT media FROM publicacao_media WHERE idpublicacao = ?";
        $stmt_media = mysqli_prepare($con, $sql_media);
        mysqli_stmt_bind_param($stmt_media, "i", $idpublicacao);
        mysqli_stmt_execute($stmt_media);
        $result_media = mysqli_stmt_get_result($stmt_media);
        
        // Remover arquivos físicos
        while ($media = mysqli_fetch_assoc($result_media)) {
            $arquivo = "../main/publicacoes/" . $media['media'];
            if (file_exists($arquivo)) {
                unlink($arquivo);
            }
        }
        
        // Remover registros da base de dados
        $sql_delete_media = "DELETE FROM publicacao_media WHERE idpublicacao = ?";
        $stmt_delete_media = mysqli_prepare($con, $sql_delete_media);
        mysqli_stmt_bind_param($stmt_delete_media, "i", $idpublicacao);
        mysqli_stmt_execute($stmt_delete_media);
        
        // Remover comentários
        $sql_delete_comments = "DELETE FROM comentario WHERE idpublicacao = ?";
        $stmt_delete_comments = mysqli_prepare($con, $sql_delete_comments);
        mysqli_stmt_bind_param($stmt_delete_comments, "i", $idpublicacao);
        mysqli_stmt_execute($stmt_delete_comments);
        
        // Remover likes
        $sql_delete_likes = "DELETE FROM likes WHERE idpublicacao = ?";
        $stmt_delete_likes = mysqli_prepare($con, $sql_delete_likes);
        mysqli_stmt_bind_param($stmt_delete_likes, "i", $idpublicacao);
        mysqli_stmt_execute($stmt_delete_likes);
        
        // Remover dos guardados
        $sql_delete_saved = "DELETE FROM guardado WHERE idpublicacao = ?";
        $stmt_delete_saved = mysqli_prepare($con, $sql_delete_saved);
        mysqli_stmt_bind_param($stmt_delete_saved, "i", $idpublicacao);
        mysqli_stmt_execute($stmt_delete_saved);
        
        // Remover a publicação
        $sql_delete_post = "DELETE FROM publicacao WHERE idpublicacao = ?";
        $stmt_delete_post = mysqli_prepare($con, $sql_delete_post);
        mysqli_stmt_bind_param($stmt_delete_post, "i", $idpublicacao);
        mysqli_stmt_execute($stmt_delete_post);
        
        // Commit da transação
        mysqli_commit($con);
        $_SESSION["sucesso"] = "Publicação removida com sucesso!";
        
    } catch (Exception $e) {
        // Rollback em caso de erro
        mysqli_rollback($con);
        $_SESSION["erro"] = "Erro ao remover publicação: " . $e->getMessage();
    }
    
    // Restaurar autocommit
    mysqli_autocommit($con, true);
}

header("Location: publicacoes.php");
exit();
?>