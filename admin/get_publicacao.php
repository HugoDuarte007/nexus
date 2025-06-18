<?php
require "../ligabd.php";

if(isset($_GET['id']) && is_numeric($_GET['id'])) {
    $postId = $_GET['id'];
    
    $sql = "SELECT descricao, media, data FROM publicacao WHERE idpublicacao = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $postId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if($publicacao = mysqli_fetch_assoc($result)) {
        echo json_encode([
            'success' => true,
            'descricao' => $publicacao['descricao'],
            'media' => $publicacao['media'],
            'data' => $publicacao['data']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Publicação não encontrada']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
}
?>