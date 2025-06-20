<?php
session_start();
require "../ligabd.php";

header('Content-Type: application/json');

if (!isset($_SESSION["user"])) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit();
}

if (!isset($_POST['id_remetente'])) {
    echo json_encode(['success' => false, 'message' => 'ID do remetente não fornecido']);
    exit();
}

$utilizador = htmlspecialchars($_SESSION["user"]);
$id_remetente = (int)$_POST['id_remetente'];

// Buscar id do utilizador
$query = "SELECT idutilizador FROM utilizador WHERE user = '$utilizador'";
$result = mysqli_query($con, $query);
$user_data = mysqli_fetch_assoc($result);
$id_utilizador = $user_data['idutilizador'];

try {
    // Marcar todas as mensagens do remetente como lidas
    $query = "UPDATE listadestinatarios ld
              JOIN mensagem m ON ld.idmensagem = m.idmensagem
              SET ld.lida = 1
              WHERE ld.iddestinatario = ? AND m.idremetente = ? AND ld.lida = 0";
    
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "ii", $id_utilizador, $id_remetente);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao marcar como lida']);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro: ' . $e->getMessage()
    ]);
}
?>