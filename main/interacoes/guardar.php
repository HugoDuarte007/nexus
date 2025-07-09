<?php
session_start();
require "../../ligabd.php";
require "../notificacoes/criar_notificacao.php";

// Desativar exibição de erros para o cliente
ini_set('display_errors', 0);
error_reporting(0);

// Forçar cabeçalho JSON
header('Content-Type: application/json');

try {
    // Verificar se o usuário está logado
    if (!isset($_SESSION['idutilizador'])) {
        throw new Exception('Utilizador não autenticado');
    }

    // Verificar se o ID da publicação foi enviado
    if (!isset($_POST['idpublicacao'])) {
        throw new Exception('ID da publicação não especificado');
    }

    $idutilizador = $_SESSION['idutilizador'];
    $idpublicacao = intval($_POST['idpublicacao']);

    // Verificar se a publicação já está guardada
    $checkQuery = "SELECT 1 FROM guardado WHERE idutilizador = ? AND idpublicacao = ? LIMIT 1";
    $stmt = mysqli_prepare($con, $checkQuery);
    if (!$stmt) throw new Exception('Erro ao preparar consulta: ' . mysqli_error($con));
    
    mysqli_stmt_bind_param($stmt, "ii", $idutilizador, $idpublicacao);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $jaGuardado = (mysqli_stmt_num_rows($stmt) > 0);
    mysqli_stmt_close($stmt);

    if ($jaGuardado) {
        // Remover dos guardados
        $deleteQuery = "DELETE FROM guardado WHERE idutilizador = ? AND idpublicacao = ?";
        $stmt = mysqli_prepare($con, $deleteQuery);
        if (!$stmt) throw new Exception('Erro ao preparar consulta de exclusão');
        
        mysqli_stmt_bind_param($stmt, "ii", $idutilizador, $idpublicacao);
        $result = mysqli_stmt_execute($stmt);
        if (!$result) throw new Exception('Erro ao remover dos guardados');
        
        echo json_encode(['success' => true, 'guardado' => false]);
    } else {
        // Adicionar aos guardados
        $insertQuery = "INSERT INTO guardado (idutilizador, idpublicacao, data_guardado) VALUES (?, ?, NOW())";
        $stmt = mysqli_prepare($con, $insertQuery);
        if (!$stmt) throw new Exception('Erro ao preparar consulta de inserção');
        
        mysqli_stmt_bind_param($stmt, "ii", $idutilizador, $idpublicacao);
        $result = mysqli_stmt_execute($stmt);
        if (!$result) throw new Exception('Erro ao adicionar aos guardados');
        
        // Buscar o dono da publicação para criar notificação
        $sql_owner = "SELECT idutilizador FROM publicacao WHERE idpublicacao = ?";
        $stmt_owner = mysqli_prepare($con, $sql_owner);
        mysqli_stmt_bind_param($stmt_owner, "i", $idpublicacao);
        mysqli_stmt_execute($stmt_owner);
        $result_owner = mysqli_stmt_get_result($stmt_owner);
        
        if ($owner = mysqli_fetch_assoc($result_owner)) {
            criarNotificacao($owner['idutilizador'], $idutilizador, 'save', $idpublicacao);
        }
        
        echo json_encode(['success' => true, 'guardado' => true]);
    }
} catch (Exception $e) {
    // Log do erro (opcional)
    error_log('Erro em guardar.php: ' . $e->getMessage());
    
    // Retornar erro em formato JSON
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        mysqli_stmt_close($stmt);
    }
    if (isset($con)) {
        mysqli_close($con);
    }
}
?>