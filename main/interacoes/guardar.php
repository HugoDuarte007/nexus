<?php
session_start();
require "../../ligabd.php";

// Verificar se o utilizador está autenticado
if (!isset($_SESSION["idutilizador"])) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit();
}

// Verificar se o ID da publicação foi enviado
if (!isset($_POST['idpublicacao'])) {
    echo json_encode(['success' => false, 'message' => 'ID da publicação não fornecido']);
    exit();
}

$idutilizador = $_SESSION["idutilizador"];
$idpublicacao = (int)$_POST['idpublicacao'];

// Verificar se a publicação já está guardada
$check = "SELECT * FROM guardado WHERE idutilizador = ? AND idpublicacao = ?";
$stmt = $con->prepare($check);
$stmt->bind_param("ii", $idutilizador, $idpublicacao);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Remover dos guardados
    $delete = "DELETE FROM guardado WHERE idutilizador = ? AND idpublicacao = ?";
    $stmt = $con->prepare($delete);
    $stmt->bind_param("ii", $idutilizador, $idpublicacao);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'guardado' => false]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao remover dos guardados']);
    }
} else {
    // Guardar a publicação
    $insert = "INSERT INTO guardado (idutilizador, idpublicacao, data_guardado) VALUES (?, ?, NOW())";
    $stmt = $con->prepare($insert);
    $stmt->bind_param("ii", $idutilizador, $idpublicacao);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'guardado' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao guardar publicação']);
    }
}
?>