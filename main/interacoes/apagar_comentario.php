<?php
session_start();
require "../ligabd.php";

if (!isset($_SESSION['idutilizador'])) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$idComentario = filter_input(INPUT_POST, 'idcomentario', FILTER_VALIDATE_INT);

if (!$idComentario) {
    echo json_encode(['success' => false, 'message' => 'ID do comentário inválido']);
    exit;
}

// Verificar se o comentário pertence ao usuário logado
$query = "SELECT idutilizador FROM comentario WHERE idcomentario = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "i", $idComentario);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$comentario = mysqli_fetch_assoc($result);

if (!$comentario) {
    echo json_encode(['success' => false, 'message' => 'Comentário não encontrado']);
    exit;
}

if ($comentario['idutilizador'] != $_SESSION['idutilizador']) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

// Apagar o comentário
$query = "DELETE FROM comentario WHERE idcomentario = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "i", $idComentario);
$success = mysqli_stmt_execute($stmt);

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao apagar comentário']);
}