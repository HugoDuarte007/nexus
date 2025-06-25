<?php
require "../../ligabd.php";

// Log para verificar se o script está sendo chamado
error_log("obter_comentarios.php chamado com idpublicacao: " . ($_GET["idpublicacao"] ?? 'não definido'));

if (!isset($_GET["idpublicacao"])) {
    error_log("Erro: idpublicacao não definido");
    echo json_encode(["error" => "idpublicacao não definido"]);
    exit();
}

$idpublicacao = intval($_GET["idpublicacao"]);
error_log("ID Publicação recebido: " . $idpublicacao);

$sql = "SELECT c.idcomentario, c.conteudo, c.data, u.user, u.ft_perfil 
        FROM comentario c
        JOIN utilizador u ON c.idutilizador = u.idutilizador
        WHERE c.idpublicacao = ?
        ORDER BY c.data ASC";

error_log("Query SQL: " . $sql);

$stmt = mysqli_prepare($con, $sql);

if (!$stmt) {
    $error = mysqli_error($con);
    error_log("Erro na preparação da query: " . $error);
    echo json_encode(["error" => "Erro na preparação da query: " . $error]);
    exit();
}

mysqli_stmt_bind_param($stmt, "i", $idpublicacao);
$executeResult = mysqli_stmt_execute($stmt);

if (!$executeResult) {
    $error = mysqli_stmt_error($stmt);
    error_log("Erro na execução da query: " . $error);
    echo json_encode(["error" => "Erro na execução: " . $error]);
    exit();
}

$result = mysqli_stmt_get_result($stmt);
$comentarios = [];

while ($row = mysqli_fetch_assoc($result)) {
    $row["ft_perfil"] = $row["ft_perfil"] ? "data:image/jpeg;base64," . base64_encode($row["ft_perfil"]) : "default.png";
    $comentarios[] = $row;
}

error_log("Total de comentários encontrados: " . count($comentarios));
echo json_encode($comentarios);