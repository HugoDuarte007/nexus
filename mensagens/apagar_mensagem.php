<?php
session_start();
require "../ligabd.php";

header('Content-Type: application/json');

if (!isset($_SESSION["user"])) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

if (!isset($_POST['idmensagem'])) {
    echo json_encode(['success' => false, 'message' => 'ID da mensagem não fornecido']);
    exit();
}

$utilizador = htmlspecialchars($_SESSION["user"]);
$idmensagem = (int)$_POST['idmensagem'];

// Buscar id do utilizador
$query = "SELECT idutilizador FROM utilizador WHERE user = '$utilizador'";
$result = mysqli_query($con, $query);
$user_data = mysqli_fetch_assoc($result);
$id_utilizador = $user_data['idutilizador'];

try {
    // Verificar se a mensagem pertence ao usuário logado
    $query_verificar = "SELECT idremetente FROM mensagem WHERE idmensagem = ?";
    $stmt = mysqli_prepare($con, $query_verificar);
    mysqli_stmt_bind_param($stmt, "i", $idmensagem);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        if ($row['idremetente'] != $id_utilizador) {
            echo json_encode(['success' => false, 'message' => 'Não autorizado a apagar esta mensagem']);
            exit();
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Mensagem não encontrada']);
        exit();
    }

    // Iniciar transação
    mysqli_begin_transaction($con);
    
    // Apagar da lista de destinatários
    $query_delete_ld = "DELETE FROM listadestinatarios WHERE idmensagem = ?";
    $stmt_ld = mysqli_prepare($con, $query_delete_ld);
    mysqli_stmt_bind_param($stmt_ld, "i", $idmensagem);
    
    if (!mysqli_stmt_execute($stmt_ld)) {
        throw new Exception("Erro ao apagar da lista de destinatários");
    }
    
    // Apagar a mensagem
    $query_delete_msg = "DELETE FROM mensagem WHERE idmensagem = ?";
    $stmt_msg = mysqli_prepare($con, $query_delete_msg);
    mysqli_stmt_bind_param($stmt_msg, "i", $idmensagem);
    
    if (!mysqli_stmt_execute($stmt_msg)) {
        throw new Exception("Erro ao apagar mensagem");
    }
    
    // Commit da transação
    mysqli_commit($con);
    
    echo json_encode(['success' => true, 'message' => 'Mensagem apagada com sucesso']);

} catch (Exception $e) {
    // Rollback em caso de erro
    mysqli_rollback($con);
    echo json_encode(['success' => false, 'message' => 'Erro ao apagar mensagem: ' . $e->getMessage()]);
}
?>