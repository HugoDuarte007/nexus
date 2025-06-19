<?php
session_start();
require "../ligabd.php";

if (!isset($_SESSION["user"])) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit();
}

$idperfil = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$iduser = $_SESSION["idutilizador"];

if (!$idperfil || !in_array($tipo, ['seguidores', 'seguindo'])) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit();
}

try {
    if ($tipo === 'seguidores') {
        // Buscar seguidores do perfil
        $query = "SELECT u.idutilizador, u.nome, u.user, u.ft_perfil,
                         CASE WHEN s2.id_seguidor IS NOT NULL THEN 1 ELSE 0 END as is_following
                  FROM seguidor s
                  JOIN utilizador u ON s.id_seguidor = u.idutilizador
                  LEFT JOIN seguidor s2 ON s2.id_seguidor = ? AND s2.id_seguido = u.idutilizador
                  WHERE s.id_seguido = ?
                  ORDER BY u.nome ASC";
        
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "ii", $iduser, $idperfil);
    } else {
        // Buscar quem o perfil segue
        $query = "SELECT u.idutilizador, u.nome, u.user, u.ft_perfil,
                         CASE WHEN s2.id_seguidor IS NOT NULL THEN 1 ELSE 0 END as is_following
                  FROM seguidor s
                  JOIN utilizador u ON s.id_seguido = u.idutilizador
                  LEFT JOIN seguidor s2 ON s2.id_seguidor = ? AND s2.id_seguido = u.idutilizador
                  WHERE s.id_seguidor = ?
                  ORDER BY u.nome ASC";
        
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "ii", $iduser, $idperfil);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Converter foto para base64 se existir
        if ($row['ft_perfil']) {
            $row['ft_perfil'] = base64_encode($row['ft_perfil']);
        }
        $users[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'users' => $users,
        'total' => count($users)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar dados: ' . $e->getMessage()
    ]);
}
?>