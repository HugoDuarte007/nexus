<?php
require "../ligabd.php";

if(isset($_GET['id']) && is_numeric($_GET['id'])) {
    $postId = $_GET['id'];
    
    // Buscar dados básicos da publicação
    $sql = "SELECT p.*, u.nome 
            FROM publicacao p
            JOIN utilizador u ON p.idutilizador = u.idutilizador
            WHERE p.idpublicacao = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $postId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if($publicacao = mysqli_fetch_assoc($result)) {
        // Buscar mídias associadas
        $sql_medias = "SELECT media, tipo 
                       FROM publicacao_media 
                       WHERE idpublicacao = ? 
                       ORDER BY ordem ASC";
        $stmt_medias = mysqli_prepare($con, $sql_medias);
        mysqli_stmt_bind_param($stmt_medias, "i", $postId);
        mysqli_stmt_execute($stmt_medias);
        $result_medias = mysqli_stmt_get_result($stmt_medias);
        
        $medias = [];
        while($media = mysqli_fetch_assoc($result_medias)) {
            $medias[] = $media;
        }
        
        // Se não houver mídias na nova tabela, verificar na coluna antiga (para compatibilidade)
        if(empty($medias) && !empty($publicacao['media'])) {
            $ext = strtolower(pathinfo($publicacao['media'], PATHINFO_EXTENSION));
            $tipo = in_array($ext, ['mp4','mov','avi','webm']) ? 'video' : 'imagem';
            
            $medias[] = [
                'media' => $publicacao['media'],
                'tipo' => $tipo
            ];
        }
        
        echo json_encode([
            'success' => true,
            'descricao' => $publicacao['descricao'],
            'data' => $publicacao['data'],
            'medias' => $medias,
            'idutilizador' => $publicacao['idutilizador'],
            'nome' => $publicacao['nome']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Publicação não encontrada']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
}
?>