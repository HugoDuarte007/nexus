<?php
header('Content-Type: application/json');

require "../../ligabd.php";

// Verificar se a conexão foi estabelecida
if (!$con) {
    echo json_encode(["error" => "Falha na conexão com o banco de dados"]);
    exit();
}

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
        throw new Exception("Erro na preparação da query: " . mysqli_error($con));
    }

    mysqli_stmt_bind_param($stmt, "i", $idpublicacao);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $comentarios = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $row["ft_perfil"] = $row["ft_perfil"] ? "data:image/jpeg;base64," . base64_encode($row["ft_perfil"]) : "default.png";
        $comentarios[] = $row;
    }

    echo json_encode($comentarios);
    
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>