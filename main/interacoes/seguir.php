<?php
session_start();
require "../../ligabd.php";
require "../notificacoes/criar_notificacao.php";

// Verificar se o usuário está logado
if (!isset($_SESSION["idutilizador"])) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit();
}

// Verificar se o ID do usuário a ser seguido foi enviado
if (!isset($_POST['id_seguido'])) {
    echo json_encode(['success' => false, 'message' => 'ID do usuário não fornecido']);
    exit();
}

$idSeguidor = $_SESSION["idutilizador"];
$idSeguido = $_POST['id_seguido'];

// Verificar se não está tentando seguir a si mesmo
if ($idSeguidor == $idSeguido) {
    echo json_encode(['success' => false, 'message' => 'Não pode seguir a si mesmo']);
    exit();
}

// Verificar se já está seguindo
$check = "SELECT * FROM seguidor WHERE id_seguidor = ? AND id_seguido = ?";
$stmt = $con->prepare($check);
$stmt->bind_param("ii", $idSeguidor, $idSeguido);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Deixar de seguir
    $delete = "DELETE FROM seguidor WHERE id_seguidor = ? AND id_seguido = ?";
    $stmt = $con->prepare($delete);
    $stmt->bind_param("ii", $idSeguidor, $idSeguido);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'seguindo' => false]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao deixar de seguir']);
    }
} else {
    // Seguir
    $insert = "INSERT INTO seguidor (id_seguidor, id_seguido, data_seguido) VALUES (?, ?, NOW())";
    $stmt = $con->prepare($insert);
    $stmt->bind_param("ii", $idSeguidor, $idSeguido);
    
    if ($stmt->execute()) {
        // Criar notificação de novo seguidor
        criarNotificacao($idSeguido, $idSeguidor, 'seguir', 0);
        
        echo json_encode(['success' => true, 'seguindo' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao seguir usuário']);
    }
}
?>