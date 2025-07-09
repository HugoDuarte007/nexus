<?php
require "../../ligabd.php";

function criarNotificacao($id_utilizador, $id_remetente, $tipo, $id_publicacao, $conteudo = null) {
    global $con;
    
    // Não criar notificação para si mesmo
    if ($id_utilizador == $id_remetente) {
        return false;
    }
    
    try {
        // Verificar se já existe uma notificação similar recente (últimas 24h)
        $query_check = "SELECT id_notificacao FROM notificacoes 
                        WHERE id_utilizador = ? AND id_remetente = ? 
                        AND tipo = ? AND id_publicacao = ? 
                        AND data_criacao > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        
        $stmt_check = mysqli_prepare($con, $query_check);
        mysqli_stmt_bind_param($stmt_check, "iisi", $id_utilizador, $id_remetente, $tipo, $id_publicacao);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        
        // Se já existe, não criar duplicata
        if (mysqli_num_rows($result_check) > 0) {
            return false;
        }
        
        // Criar nova notificação
        $query = "INSERT INTO notificacoes (id_utilizador, id_remetente, tipo, id_publicacao, conteudo) 
                  VALUES (?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "iisis", $id_utilizador, $id_remetente, $tipo, $id_publicacao, $conteudo);
        
        return mysqli_stmt_execute($stmt);
        
    } catch (Exception $e) {
        error_log("Erro ao criar notificação: " . $e->getMessage());
        return false;
    }
}
?>