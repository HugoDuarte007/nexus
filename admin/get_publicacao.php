<?php
require "../ligabd.php";

$id = $_GET['id'] ?? 0;

$sql = "SELECT p.*, u.nome as autor 
        FROM publicacao p 
        JOIN utilizador u ON p.idutilizador = u.idutilizador 
        WHERE p.idpublicacao = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$publicacao = mysqli_fetch_assoc($result);

if ($publicacao) {
    // Buscar mídias associadas
    $sqlMedia = "SELECT * FROM publicacao_media WHERE idpublicacao = ?";
    $stmtMedia = mysqli_prepare($con, $sqlMedia);
    mysqli_stmt_bind_param($stmtMedia, "i", $id);
    mysqli_stmt_execute($stmtMedia);
    $resultMedia = mysqli_stmt_get_result($stmtMedia);
    $medias = mysqli_fetch_all($resultMedia, MYSQLI_ASSOC);

    echo json_encode([
        'success' => true,
        'descricao' => $publicacao['descricao'],
        'data' => $publicacao['data'],
        'medias' => $medias
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Publicação não encontrada'
    ]);
}
?>