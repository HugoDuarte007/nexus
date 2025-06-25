<?php
session_start();
require "../../ligabd.php";

header('Content-Type: application/json');

if (!isset($_GET["idpublicacao"])) {
    echo json_encode(["error" => "ID da publicação não fornecido"]);
    exit();
}

$idpublicacao = intval($_GET["idpublicacao"]);

try {
    $sql = "SELECT c.conteudo, c.data, u.user, u.ft_perfil 
            FROM comentario c
            JOIN utilizador u ON c.idutilizador = u.idutilizador
            WHERE c.idpublicacao = ?
            ORDER BY c.data ASC";

    $stmt = mysqli_prepare($con, $sql);

    if (!$stmt) {
        echo json_encode(["error" => "Erro na preparação da query: " . mysqli_error($con)]);
        exit();
    }

    mysqli_stmt_bind_param($stmt, "i", $idpublicacao);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $comentarios = [];

    while ($row = mysqli_fetch_assoc($result)) {
        // Converter foto de perfil para base64 se existir
        if ($row["ft_perfil"]) {
            $row["ft_perfil"] = "data:image/jpeg;base64," . base64_encode($row["ft_perfil"]);
        } else {
            $row["ft_perfil"] = "../imagens/default.png";
        }
        
        $comentarios[] = $row;
    }

    echo json_encode($comentarios);

} catch (Exception $e) {
    echo json_encode(["error" => "Erro ao buscar comentários: " . $e->getMessage()]);
}
?>