<?php
session_start();
require "../../ligabd.php";

header('Content-Type: application/json');

if (!isset($_SESSION["user"])) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit();
}

$idpublicacao = (int)$_GET['id'];

try {
    // Buscar mídias da publicação
    $sql = "SELECT * FROM publicacao_media WHERE idpublicacao = ? ORDER BY ordem ASC";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $idpublicacao);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $medias = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $medias[] = [
            'media' => $row['media'],
            'tipo' => $row['tipo'],
            'ordem' => $row['ordem']
        ];
    }
    
    // Se não houver mídias na tabela publicacao_media, verificar na tabela publicacao (sistema antigo)
    if (empty($medias)) {
        $sql_old = "SELECT media FROM publicacao WHERE idpublicacao = ?";
        $stmt_old = mysqli_prepare($con, $sql_old);
        mysqli_stmt_bind_param($stmt_old, "i", $idpublicacao);
        mysqli_stmt_execute($stmt_old);
        $result_old = mysqli_stmt_get_result($stmt_old);
        
        if ($row_old = mysqli_fetch_assoc($result_old)) {
            if (!empty($row_old['media'])) {
                $extensao = strtolower(pathinfo($row_old['media'], PATHINFO_EXTENSION));
                $extensoes_video = ['mp4', 'mov', 'avi', 'webm'];
                $tipo = in_array($extensao, $extensoes_video) ? 'video' : 'imagem';
                
                $medias[] = [
                    'media' => $row_old['media'],
                    'tipo' => $tipo,
                    'ordem' => 1
                ];
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'medias' => $medias,
        'total' => count($medias)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar mídias: ' . $e->getMessage()
    ]);
}
?>