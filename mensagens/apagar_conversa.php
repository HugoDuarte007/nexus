<?php
session_start();
require "../ligabd.php";

if (!isset($_SESSION["user"])) {
    header("Location: ../login.php");
    exit();
}

$response = ['success' => false, 'message' => ''];

$id_utilizador = $_SESSION['idutilizador'];
$destinatario = isset($_POST['destinatario']) ? (int)$_POST['destinatario'] : null;

if (!$destinatario) {
    $response['message'] = 'Destinatário inválido';
    echo json_encode($response);
    exit();
}

try {
    // Primeiro, obtemos todas as mensagens trocadas entre os dois usuários
    $query_mensagens = "SELECT m.idmensagem 
                       FROM mensagem m
                       JOIN listadestinatarios ld ON m.idmensagem = ld.idmensagem
                       WHERE (m.idremetente = ? AND ld.iddestinatario = ?)
                       OR (m.idremetente = ? AND ld.iddestinatario = ?)";
    
    $stmt = mysqli_prepare($con, $query_mensagens);
    mysqli_stmt_bind_param($stmt, "iiii", $id_utilizador, $destinatario, $destinatario, $id_utilizador);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    // Depois, apagamos os registros da lista de destinatários e as mensagens
    mysqli_begin_transaction($con);
    
    while ($msg = mysqli_fetch_assoc($result)) {
        // Apaga da lista de destinatários
        $query_delete_ld = "DELETE FROM listadestinatarios WHERE idmensagem = ?";
        $stmt_ld = mysqli_prepare($con, $query_delete_ld);
        mysqli_stmt_bind_param($stmt_ld, "i", $msg['idmensagem']);
        mysqli_stmt_execute($stmt_ld);
        
        // Apaga a mensagem
        $query_delete_msg = "DELETE FROM mensagem WHERE idmensagem = ?";
        $stmt_msg = mysqli_prepare($con, $query_delete_msg);
        mysqli_stmt_bind_param($stmt_msg, "i", $msg['idmensagem']);
        mysqli_stmt_execute($stmt_msg);
    }
    
    mysqli_commit($con);
    $response['success'] = true;
    $response['message'] = 'Conversa apagada com sucesso';
} catch (Exception $e) {
    mysqli_rollback($con);
    $response['message'] = 'Erro ao apagar conversa: ' . $e->getMessage();
}

echo json_encode($response);
?>