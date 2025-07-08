<?php
session_start();
require "../ligabd.php";

if (!isset($_SESSION["user"])) {
    echo json_encode(["success" => false, "message" => "Não autenticado"]);
    exit();
}

$utilizador = htmlspecialchars($_SESSION["user"]); 

if ($_FILES["file"]["error"] !== UPLOAD_ERR_OK) {
    echo json_encode(["success" => false, "message" => "Erro no upload: " . $_FILES["file"]["error"]]);
    exit();
}

$imagem = file_get_contents($_FILES["file"]["tmp_name"]); 

// Usar prepared statements para evitar injeção de SQL
$query = "UPDATE utilizador SET ft_perfil = ? WHERE user = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "ss", $imagem, $utilizador);

if (mysqli_stmt_execute($stmt)) {
    // Atualizar TODOS os dados na sessão
    $query = "SELECT * FROM utilizador WHERE user = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "s", $utilizador);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    
    $_SESSION = array_merge($_SESSION, $user);
    
    echo json_encode(["success" => true, "session_updated" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Erro na atualização: " . mysqli_error($con)]);
}

mysqli_stmt_close($stmt);
mysqli_close($con);
?>