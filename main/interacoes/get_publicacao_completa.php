<?php
require "../../ligabd.php";

$id = $_GET['id'] ?? 0;

// Sua consulta SQL aqui
$query = "SELECT p.*, u.user, u.ft_perfil, 
          DATE_FORMAT(p.data, '%d/%m/%Y %H:%i') as data_formatada
          FROM publicacao p
          JOIN utilizador u ON p.idutilizador = u.idutilizador
          WHERE p.idpublicacao = ?";

$stmt = $con->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Processar dados se necessário
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'idutilizador' => $row['idutilizador'],
        'user' => $row['user'],
        'ft_perfil' => $row['ft_perfil'],
        'data_formatada' => $row['data_formatada'],
        'descricao' => $row['descricao'],
        // outros campos necessários
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Publicação não encontrada'
    ]);
}