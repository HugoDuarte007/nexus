<?php
session_start();
require "../../ligabd.php";

if (!isset($_SESSION["user"])) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idPublicacao = $_POST['idpublicacao'] ?? null;
    
    if (!$idPublicacao) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        exit();
    }

    // Verifica se a publicação pertence ao usuário
    $query = "SELECT idutilizador FROM publicacao WHERE idpublicacao = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $idPublicacao);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Publicação não encontrada']);
        exit();
    }

    $row = $result->fetch_assoc();
    $idUtilizador = $row['idutilizador'];
    $idSessao = $_SESSION['idutilizador'];

    if ($idUtilizador != $idSessao) {
        echo json_encode(['success' => false, 'message' => 'Não autorizado']);
        exit();
    }

    // Primeiro deleta os comentários e likes associados
    $con->query("DELETE FROM comentario WHERE idpublicacao = $idPublicacao");
    $con->query("DELETE FROM likes WHERE idpublicacao = $idPublicacao");

    // Depois deleta a publicação
    if ($con->query("DELETE FROM publicacao WHERE idpublicacao = $idPublicacao")) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao deletar']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
}