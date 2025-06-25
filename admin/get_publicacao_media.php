<?php
require "../ligabd.php";

if(isset($_GET['id']) && is_numeric($_GET['id'])) {
    $postId = $_GET['id'];
    
    $sql = "SELECT media, tipo FROM publicacao_media 
            WHERE idpublicacao = ? 
            ORDER BY ordem ASC";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $postId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $medias = [];
    while($row = mysqli_fetch_assoc($result)) {
        $medias[] = $row;
    }
    
    if(count($medias) > 0) {
        echo json_encode([
            'success' => true,
            'medias' => $medias
        ]);
    } else {
        // Verificar se há mídia na tabela antiga (para compatibilidade)
        $sql_old = "SELECT media FROM publicacao WHERE idpublicacao = ?";
        $stmt_old = mysqli_prepare($con, $sql_old);
        mysqli_stmt_bind_param($stmt_old, "i", $postId);
        mysqli_stmt_execute($stmt_old);
        $result_old = mysqli_stmt_get_result($stmt_old);
        
        if($publicacao = mysqli_fetch_assoc($result_old)) {
            if(!empty($publicacao['media'])) {
                $ext = strtolower(pathinfo($publicacao['media'], PATHINFO_EXTENSION));
                $tipo = in_array($ext, ['mp4','mov','avi','webm']) ? 'video' : 'imagem';
                
                echo json_encode([
                    'success' => true,
                    'medias' => [
                        [
                            'media' => $publicacao['media'],
                            'tipo' => $tipo
                        ]
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Nenhuma media encontrada']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Publicação não encontrada']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
}
?>