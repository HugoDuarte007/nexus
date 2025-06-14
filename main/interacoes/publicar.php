<?php
session_start();
date_default_timezone_set('Europe/Lisbon');
require "../../ligabd.php";

// Verificar autenticação
if (!isset($_SESSION["user"])) {
    header("Location: ../../login.php");
    exit();
}

// Obter ID do utilizador
$utilizador = $_SESSION["user"];
$stmt = $con->prepare("SELECT idutilizador FROM utilizador WHERE user = ?");
$stmt->bind_param("s", $utilizador);
$stmt->execute();
$result_user = $stmt->get_result();

if ($result_user->num_rows === 0) {
    die(json_encode(['success' => false, 'message' => 'Utilizador não encontrado!']));
}

$row = $result_user->fetch_assoc();
$idutilizador = $row["idutilizador"];

// Processar submissão
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar dados
    $descricao = !empty($_POST['descricao']) ? trim($con->real_escape_string($_POST['descricao'])) : null;
    $data = date("Y-m-d H:i:s");
    $nomeImagem = null;

    // Processar imagem
    if (!empty($_FILES['imagem']['name']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        // Validações da imagem
        $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
        $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($extensao, $extensoes_permitidas)) {
            die(json_encode(['success' => false, 'message' => 'Apenas imagens JPG, PNG ou GIF são permitidas']));
        }

        if ($_FILES['imagem']['size'] > 5242880) { // 5MB
            die(json_encode(['success' => false, 'message' => 'Tamanho máximo da imagem: 5MB']));
        }

        // Pasta de destino relativa ao publicar.php
        $pasta_destino = __DIR__ . "/../publicacoes/";
        if (!is_dir($pasta_destino)) {
            mkdir($pasta_destino, 0777, true);
        }

        $nomeImagem = uniqid('pub_', true) . '.' . $extensao;
        $caminhoCompleto = $pasta_destino . $nomeImagem;

        if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoCompleto)) {
            die(json_encode(['success' => false, 'message' => 'Erro ao guardar a imagem']));
        }
    }

    // Inserir publicação
    $stmt = $con->prepare("INSERT INTO publicacao (idutilizador, media, descricao, data) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $idutilizador, $nomeImagem, $descricao, $data);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao publicar']);
    }
    
    $stmt->close();
    $con->close();
    exit();
}

header("Location: ../../main.php");
?>