<?php
session_start();
require "../../ligabd.php";

header('Content-Type: application/json');

if (!isset($_SESSION['idutilizador'])) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$idComentario = isset($_POST['idcomentario']) ? intval($_POST['idcomentario']) : null;

if (!$idComentario || $idComentario <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID do comentário inválido']);
    exit;
}

try {
    // Verificar se o comentário existe e se o usuário tem permissão
    $query = "SELECT c.idutilizador, p.idutilizador as idautor 
              FROM comentario c
              JOIN publicacao p ON c.idpublicacao = p.idpublicacao
              WHERE c.idcomentario = ?";
    $stmt = mysqli_prepare($con, $query);

    if (!$stmt) {
        throw new Exception("Erro na preparação da query: " . mysqli_error($con));
    }

    mysqli_stmt_bind_param($stmt, "i", $idComentario);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 0) {
        echo json_encode(['success' => false, 'message' => 'Comentário não encontrado']);
        exit;
    }

    $comentario = mysqli_fetch_assoc($result);

    // Verificar permissões
    if (
        $comentario['idutilizador'] != $_SESSION['idutilizador'] &&
        $comentario['idautor'] != $_SESSION['idutilizador']
    ) {
        echo json_encode(['success' => false, 'message' => 'Não autorizado']);
        exit;
    }

    // Apagar o comentário
    $query = "DELETE FROM comentario WHERE idcomentario = ?";
    $stmt = mysqli_prepare($con, $query);

    if (!$stmt) {
        throw new Exception("Erro na preparação da query: " . mysqli_error($con));
    }

    mysqli_stmt_bind_param($stmt, "i", $idComentario);
    $success = mysqli_stmt_execute($stmt);

    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception("Erro ao executar a query: " . mysqli_error($con));
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro: ' . $e->getMessage()
    ]);
}