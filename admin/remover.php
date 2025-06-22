<?php
session_start(); 

// Verificar se o utilizador está autenticado e é admin
if(!isset($_POST["botaoRemover"]) || !isset($_SESSION["user"]) || $_SESSION["id_tipos_utilizador"] != 0) {
    header("Location: ../index.php");
    exit();
}

// Impedir remoção do admin principal
if($_POST["user"] == "admin") {
    $_SESSION["erro"] = "Não é possível remover o utilizador admin principal.";
    header("Location: utilizadores.php");
    exit();
}

require "../ligabd.php"; 

$user = mysqli_real_escape_string($con, $_POST["user"]);

// Buscar o ID do utilizador
$sql_get_id = "SELECT idutilizador FROM utilizador WHERE user = '$user'";
$result_id = mysqli_query($con, $sql_get_id);

if(!$result_id || mysqli_num_rows($result_id) == 0) {
    $_SESSION["erro"] = "Utilizador não encontrado.";
    header("Location: utilizadores.php");
    exit();
}

$row = mysqli_fetch_assoc($result_id);
$idutilizador = $row['idutilizador'];

// Iniciar transação para garantir consistência
mysqli_autocommit($con, false);

try {
    // 1. Remover likes dados pelo utilizador
    $sql1 = "DELETE FROM likes WHERE idutilizador = $idutilizador";
    if(!mysqli_query($con, $sql1)) {
        throw new Exception("Erro ao remover likes do utilizador: " . mysqli_error($con));
    }

    // 2. Remover likes nas publicações do utilizador
    $sql2 = "DELETE l FROM likes l 
             INNER JOIN publicacao p ON l.idpublicacao = p.idpublicacao 
             WHERE p.idutilizador = $idutilizador";
    if(!mysqli_query($con, $sql2)) {
        throw new Exception("Erro ao remover likes das publicações do utilizador: " . mysqli_error($con));
    }

    // 3. Remover comentários do utilizador
    $sql3 = "DELETE FROM comentario WHERE idutilizador = $idutilizador";
    if(!mysqli_query($con, $sql3)) {
        throw new Exception("Erro ao remover comentários do utilizador: " . mysqli_error($con));
    }

    // 4. Remover comentários nas publicações do utilizador
    $sql4 = "DELETE c FROM comentario c 
             INNER JOIN publicacao p ON c.idpublicacao = p.idpublicacao 
             WHERE p.idutilizador = $idutilizador";
    if(!mysqli_query($con, $sql4)) {
        throw new Exception("Erro ao remover comentários das publicações do utilizador: " . mysqli_error($con));
    }

    // 5. Remover publicações guardadas pelo utilizador
    $sql5 = "DELETE FROM guardado WHERE idutilizador = $idutilizador";
    if(!mysqli_query($con, $sql5)) {
        throw new Exception("Erro ao remover publicações guardadas: " . mysqli_error($con));
    }

    // 6. Remover publicações guardadas de outros utilizadores das publicações deste utilizador
    $sql6 = "DELETE g FROM guardado g 
             INNER JOIN publicacao p ON g.idpublicacao = p.idpublicacao 
             WHERE p.idutilizador = $idutilizador";
    if(!mysqli_query($con, $sql6)) {
        throw new Exception("Erro ao remover guardados das publicações do utilizador: " . mysqli_error($con));
    }

    // 7. Remover mensagens enviadas pelo utilizador (destinatários primeiro)
    $sql7 = "DELETE ld FROM listadestinatarios ld 
             INNER JOIN mensagem m ON ld.idmensagem = m.idmensagem 
             WHERE m.idremetente = $idutilizador";
    if(!mysqli_query($con, $sql7)) {
        throw new Exception("Erro ao remover destinatários das mensagens enviadas: " . mysqli_error($con));
    }

    // 8. Remover mensagens enviadas pelo utilizador
    $sql8 = "DELETE FROM mensagem WHERE idremetente = $idutilizador";
    if(!mysqli_query($con, $sql8)) {
        throw new Exception("Erro ao remover mensagens enviadas: " . mysqli_error($con));
    }

    // 9. Remover mensagens recebidas pelo utilizador
    $sql9 = "DELETE FROM listadestinatarios WHERE iddestinatario = $idutilizador";
    if(!mysqli_query($con, $sql9)) {
        throw new Exception("Erro ao remover mensagens recebidas: " . mysqli_error($con));
    }

    // 10. Remover relacionamentos de seguidor (onde o utilizador segue outros)
    $sql10 = "DELETE FROM seguidor WHERE id_seguidor = $idutilizador";
    if(!mysqli_query($con, $sql10)) {
        throw new Exception("Erro ao remover seguidores do utilizador: " . mysqli_error($con));
    }

    // 11. Remover relacionamentos de seguidor (onde outros seguem o utilizador)
    $sql11 = "DELETE FROM seguidor WHERE id_seguido = $idutilizador";
    if(!mysqli_query($con, $sql11)) {
        throw new Exception("Erro ao remover utilizadores que seguem este utilizador: " . mysqli_error($con));
    }

    // 12. Remover banimentos do utilizador
    $sql12 = "DELETE FROM banidos WHERE idutilizador = $idutilizador";
    if(!mysqli_query($con, $sql12)) {
        throw new Exception("Erro ao remover banimentos: " . mysqli_error($con));
    }

    // 13. Buscar e remover arquivos de mídia das publicações
    $sql_media = "SELECT media FROM publicacao WHERE idutilizador = $idutilizador AND media IS NOT NULL";
    $result_media = mysqli_query($con, $sql_media);
    
    if($result_media) {
        while($media_row = mysqli_fetch_assoc($result_media)) {
            $media_file = "../main/publicacoes/" . $media_row['media'];
            if(file_exists($media_file)) {
                unlink($media_file); // Remove o arquivo físico
            }
        }
    }

    // 14. Remover publicações do utilizador
    $sql13 = "DELETE FROM publicacao WHERE idutilizador = $idutilizador";
    if(!mysqli_query($con, $sql13)) {
        throw new Exception("Erro ao remover publicações: " . mysqli_error($con));
    }

    // 15. Finalmente, remover o utilizador
    $sql_remover = "DELETE FROM utilizador WHERE user = '$user'";
    if(!mysqli_query($con, $sql_remover)) {
        throw new Exception("Erro ao remover utilizador: " . mysqli_error($con));
    }

    // Se chegou até aqui, commit da transação
    mysqli_commit($con);
    
    $_SESSION["sucesso"] = "Utilizador removido com sucesso!";
    
} catch (Exception $e) {
    // Rollback em caso de erro
    mysqli_rollback($con);
    $_SESSION["erro"] = $e->getMessage();
}

// Restaurar autocommit
mysqli_autocommit($con, true);

header("Location: utilizadores.php");
exit();
?>