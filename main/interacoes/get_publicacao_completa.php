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

$id = (int)$_GET['id'];
$idutilizador_atual = $_SESSION['idutilizador'];

try {
    // Buscar dados da publicação com informações do utilizador
    $query = "SELECT p.*, u.user, u.ft_perfil, u.nome,
              DATE_FORMAT(p.data, '%d/%m/%Y às %H:%i') as data_formatada
              FROM publicacao p
              JOIN utilizador u ON p.idutilizador = u.idutilizador
              WHERE p.idpublicacao = ?";

    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // Converter foto de perfil para base64 se existir
        $foto_perfil = null;
        if ($row['ft_perfil']) {
            $foto_perfil = "data:image/jpeg;base64," . base64_encode($row['ft_perfil']);
        }

        // Buscar mídias da publicação (nova tabela)
        $sql_media = "SELECT * FROM publicacao_media WHERE idpublicacao = ? ORDER BY ordem ASC";
        $stmt_media = mysqli_prepare($con, $sql_media);
        mysqli_stmt_bind_param($stmt_media, "i", $id);
        mysqli_stmt_execute($stmt_media);
        $result_media = mysqli_stmt_get_result($stmt_media);
        
        $medias = [];
        while ($media_row = mysqli_fetch_assoc($result_media)) {
            $medias[] = [
                'media' => $media_row['media'],
                'tipo' => $media_row['tipo'],
                'ordem' => $media_row['ordem']
            ];
        }
        
        // Se não houver mídias na nova tabela, verificar na tabela antiga
        if (empty($medias) && !empty($row['media'])) {
            $extensao = strtolower(pathinfo($row['media'], PATHINFO_EXTENSION));
            $extensoes_video = ['mp4', 'mov', 'avi', 'webm'];
            $tipo = in_array($extensao, $extensoes_video) ? 'video' : 'imagem';
            
            $medias[] = [
                'media' => $row['media'],
                'tipo' => $tipo,
                'ordem' => 1
            ];
        }

        // Contar likes
        $sql_likes = "SELECT COUNT(*) as total_likes FROM likes WHERE idpublicacao = ?";
        $stmt_likes = mysqli_prepare($con, $sql_likes);
        mysqli_stmt_bind_param($stmt_likes, "i", $id);
        mysqli_stmt_execute($stmt_likes);
        $result_likes = mysqli_stmt_get_result($stmt_likes);
        $likes_data = mysqli_fetch_assoc($result_likes);

        // Verificar se o usuário atual curtiu
        $sql_user_like = "SELECT COUNT(*) as user_liked FROM likes WHERE idpublicacao = ? AND idutilizador = ?";
        $stmt_user_like = mysqli_prepare($con, $sql_user_like);
        mysqli_stmt_bind_param($stmt_user_like, "ii", $id, $idutilizador_atual);
        mysqli_stmt_execute($stmt_user_like);
        $result_user_like = mysqli_stmt_get_result($stmt_user_like);
        $user_like_data = mysqli_fetch_assoc($result_user_like);

        // Verificar se está guardado
        $sql_guardado = "SELECT COUNT(*) as is_saved FROM guardado WHERE idpublicacao = ? AND idutilizador = ?";
        $stmt_guardado = mysqli_prepare($con, $sql_guardado);
        mysqli_stmt_bind_param($stmt_guardado, "ii", $id, $idutilizador_atual);
        mysqli_stmt_execute($stmt_guardado);
        $result_guardado = mysqli_stmt_get_result($stmt_guardado);
        $guardado_data = mysqli_fetch_assoc($result_guardado);

        // Buscar comentários
        $sql_comentarios = "SELECT c.conteudo, c.data, u.user, u.ft_perfil, u.idutilizador
                           FROM comentario c
                           JOIN utilizador u ON c.idutilizador = u.idutilizador
                           WHERE c.idpublicacao = ?
                           ORDER BY c.data ASC";
        
        $stmt_comentarios = mysqli_prepare($con, $sql_comentarios);
        mysqli_stmt_bind_param($stmt_comentarios, "i", $id);
        mysqli_stmt_execute($stmt_comentarios);
        $result_comentarios = mysqli_stmt_get_result($stmt_comentarios);
        
        $comentarios = [];
        while ($comentario_row = mysqli_fetch_assoc($result_comentarios)) {
            $foto_comentario = null;
            if ($comentario_row['ft_perfil']) {
                $foto_comentario = "data:image/jpeg;base64," . base64_encode($comentario_row['ft_perfil']);
            }
            
            $comentarios[] = [
                'conteudo' => $comentario_row['conteudo'],
                'data' => $comentario_row['data'],
                'user' => $comentario_row['user'],
                'ft_perfil' => $foto_comentario,
                'idutilizador' => $comentario_row['idutilizador']
            ];
        }

        echo json_encode([
            'success' => true,
            'idpublicacao' => $row['idpublicacao'],
            'idutilizador' => $row['idutilizador'],
            'user' => $row['user'],
            'nome' => $row['nome'],
            'ft_perfil' => $foto_perfil,
            'data_formatada' => $row['data_formatada'],
            'descricao' => $row['descricao'],
            'medias' => $medias,
            'total_likes' => (int)$likes_data['total_likes'],
            'user_liked' => $user_like_data['user_liked'] > 0,
            'is_saved' => $guardado_data['is_saved'] > 0,
            'is_owner' => $row['idutilizador'] == $idutilizador_atual,
            'comentarios' => $comentarios
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Publicação não encontrada'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar publicação: ' . $e->getMessage()
    ]);
}
?>