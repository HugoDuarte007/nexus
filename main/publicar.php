<?php
session_start();
date_default_timezone_set('Europe/Lisbon'); 
require "../ligabd.php";

// Redirecionar se não estiver autenticado
if (!isset($_SESSION["user"])) {
    header("Location: ../login.php");
    exit();
}

// Buscar ID do utilizador
$utilizador = $_SESSION["user"];
$sql_user = "SELECT idutilizador FROM utilizador WHERE user = '$utilizador'";
$result_user = $con->query($sql_user);

if ($result_user->num_rows > 0) {
    $row = $result_user->fetch_assoc();
    $idutilizador = $row["idutilizador"];
} else {
    die("Erro: Utilizador não encontrado!");
}

// Submissão do formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $descricao = isset($_POST['descricao']) ? $con->real_escape_string($_POST['descricao']) : NULL;
    $data = date("Y-m-d H:i:s");
    $caminhoImagem = NULL;

    // Verificar se imagem foi enviada
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0) {
        $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
        $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($extensao, $extensoes_permitidas)) {
            die("Tipo de arquivo não permitido.");
        }

        $pasta_destino = __DIR__ . "/../main/publicacoes/";
        if (!is_dir($pasta_destino)) {
            mkdir($pasta_destino, 0777, true);
        }

        // Contar quantos ficheiros "nexus_" já existem
        $ficheiros = glob($pasta_destino . "nexus_*.$extensao");
        $novoNumero = count($ficheiros) + 1;
        $nomeImagem = "nexus_" . $novoNumero . "." . $extensao;
        $caminhoImagem = "publicacoes/" . $nomeImagem;

        $destinoCompleto = $pasta_destino . $nomeImagem;

        if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $destinoCompleto)) {
            die("Erro ao guardar a imagem.");
        }
    }

    // Inserir na BD
    $sql = "INSERT INTO publicacao (idutilizador, media, descricao, data, likes)
            VALUES ('$idutilizador', " . ($nomeImagem ? "'$nomeImagem'" : "NULL") . ", '$descricao', '$data', 0)";

    if ($con->query($sql) === TRUE) {
        header("Location: ../main/main.php");
        exit();
    } else {
        echo "Erro ao publicar: " . $con->error;
    }
}

$con->close();
?>
