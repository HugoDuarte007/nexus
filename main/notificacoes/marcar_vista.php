<?php
session_start();
require "../../ligabd.php";

header('Content-Type: application/json');

if (!isset($_SESSION["user"])) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit();
}

$id_utilizador = $_SESSION['idutilizador'];

try {
    if (isset($_POST['id_notificacao'])) {
        // Marcar uma notificação específica como vista
        $id_notificacao = (int)$_POST['id_notificacao'];
        
        $query = "UPDATE notificacoes SET vista = 1 
                  WHERE id_notificacao = ? AND id_utilizador = ?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "ii", $id_notificacao, $id_utilizador);
        
    } else {
        // Marcar todas as notificações como vistas
        $query = "UPDATE notificacoes SET vista = 1 WHERE id_utilizador = ?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "i", $id_utilizador);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar']);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro: ' . $e->getMessage()
    ]);
}
?>