<?php
session_start();
require "../../ligabd.php";
require "../notificacoes/criar_notificacao.php";

header('Content-Type: application/json');

if (!isset($_SESSION["user"])) {
    echo json_encode(['success' => false, 'error' => 'Não autenticado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit();
}

if (!isset($_POST['idpublicacao']) || !isset($_POST['comentario'])) {
    echo json_encode(['success' => false, 'error' => 'Dados incompletos']);
    exit();
}

$utilizador = $_SESSION["user"];
$idpublicacao = intval($_POST['idpublicacao']);
$conteudo = mysqli_real_escape_string($con, $_POST['comentario']);

// Buscar ID do utilizador
$query = "SELECT idutilizador, ft_perfil FROM utilizador WHERE user = '$utilizador'";
$result = mysqli_query($con, $query);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    echo json_encode(['success' => false, 'error' => 'Utilizador não encontrado']);
    exit();
}

$idutilizador = $user['idutilizador'];

// Inserir comentário
$sql = "INSERT INTO comentario (idutilizador, idpublicacao, conteudo, data) 
        VALUES ($idutilizador, $idpublicacao, '$conteudo', NOW())";

if (mysqli_query($con, $sql)) {
    $idcomentario = mysqli_insert_id($con);
    
    // Buscar o dono da publicação para criar notificação
    $sql_owner = "SELECT idutilizador FROM publicacao WHERE idpublicacao = ?";
    $stmt_owner = mysqli_prepare($con, $sql_owner);
    mysqli_stmt_bind_param($stmt_owner, "i", $idpublicacao);
    mysqli_stmt_execute($stmt_owner);
    $result_owner = mysqli_stmt_get_result($stmt_owner);
    
    if ($owner = mysqli_fetch_assoc($result_owner)) {
        // Truncar conteúdo para a notificação
        $conteudo_truncado = strlen($conteudo) > 50 ? substr($conteudo, 0, 50) . '...' : $conteudo;
        criarNotificacao($owner['idutilizador'], $idutilizador, 'comentario', $idpublicacao, $conteudo_truncado);
    }
    
    // Retornar dados do novo comentário
    $novoComentario = [
        'idcomentario' => $idcomentario,
        'idutilizador' => $idutilizador,
        'conteudo' => $conteudo,
        'data' => date('Y-m-d H:i:s'),
        'user' => $utilizador,
        'ft_perfil' => $user['ft_perfil'] ? "data:image/jpeg;base64," . base64_encode($user['ft_perfil']) : "default.png"
    ];
    
    echo json_encode(['success' => true, 'comentario' => $novoComentario]);
} else {
    echo json_encode(['success' => false, 'error' => mysqli_error($con)]);
}
?>