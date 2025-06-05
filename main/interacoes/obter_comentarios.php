<?php
require "../../ligabd.php";

if (!isset($_GET["idpublicacao"])) {
    echo json_encode([]);
    exit();
}

$idpublicacao = intval($_GET["idpublicacao"]);

$sql = "SELECT c.conteudo, c.data, u.user, u.ft_perfil 
        FROM comentario c
        JOIN utilizador u ON c.idutilizador = u.idutilizador
        WHERE c.idpublicacao = ?
        ORDER BY c.data ASC";

$stmt = mysqli_prepare($con, $sql);

if (!$stmt) {
    // Mostra erro de SQL
    echo json_encode(["error" => "Erro na preparação da query: " . mysqli_error($con)]);
    exit();
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
