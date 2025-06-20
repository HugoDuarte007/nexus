<?php
session_start();
require "../ligabd.php";

header('Content-Type: application/json');

if (!isset($_SESSION["user"])) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit();
}

$utilizador = htmlspecialchars($_SESSION["user"]);

// Buscar id do utilizador
$query = "SELECT idutilizador FROM utilizador WHERE user = '$utilizador'";
$result = mysqli_query($con, $query);
$user_data = mysqli_fetch_assoc($result);
$id_utilizador = $user_data['idutilizador'];

try {
    // Contar mensagens não lidas
    $query_total = "SELECT COUNT(*) as total 
                    FROM mensagem m
                    JOIN listadestinatarios ld ON m.idmensagem = ld.idmensagem
                    WHERE ld.iddestinatario = ? AND ld.lida = 0";
    
    $stmt = mysqli_prepare($con, $query_total);
    mysqli_stmt_bind_param($stmt, "i", $id_utilizador);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $total_nao_lidas = mysqli_fetch_assoc($result)['total'];

    // Buscar mensagens não lidas por conversa
    $query_conversas = "SELECT m.idremetente, u.user, u.ft_perfil, COUNT(*) as nao_lidas
                        FROM mensagem m
                        JOIN listadestinatarios ld ON m.idmensagem = ld.idmensagem
                        JOIN utilizador u ON m.idremetente = u.idutilizador
                        WHERE ld.iddestinatario = ? AND ld.lida = 0
                        GROUP BY m.idremetente, u.user, u.ft_perfil";
    
    $stmt = mysqli_prepare($con, $query_conversas);
    mysqli_stmt_bind_param($stmt, "i", $id_utilizador);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $conversas_nao_lidas = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $conversas_nao_lidas[$row['idremetente']] = $row['nao_lidas'];
    }

    echo json_encode([
        'success' => true,
        'total_nao_lidas' => $total_nao_lidas,
        'conversas_nao_lidas' => $conversas_nao_lidas
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar mensagens: ' . $e->getMessage()
    ]);
}
?>