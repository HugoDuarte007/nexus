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
    $nomeMedia = null;

    // Processar mídia (imagem ou vídeo)
    if (!empty($_FILES['media']['name']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        // Validações da mídia
        $extensao = strtolower(pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION));
        $extensoes_imagem = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extensoes_video = ['mp4', 'mov', 'avi', 'webm'];
        $extensoes_permitidas = array_merge($extensoes_imagem, $extensoes_video);
        
        if (!in_array($extensao, $extensoes_permitidas)) {
            die(json_encode(['success' => false, 'message' => 'Apenas imagens (JPG, PNG, GIF, WEBP) ou vídeos (MP4, MOV, AVI, WEBM) são permitidos']));
        }

        // Verificar tamanho do arquivo (50MB para vídeos, 5MB para imagens)
        $tamanho_maximo = in_array($extensao, $extensoes_video) ? 52428800 : 5242880; // 50MB ou 5MB
        if ($_FILES['media']['size'] > $tamanho_maximo) {
            $limite = in_array($extensao, $extensoes_video) ? '50MB' : '5MB';
            die(json_encode(['success' => false, 'message' => "Tamanho máximo do arquivo: $limite"]));
        }

        // Verificar se é realmente um arquivo de mídia válido
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $_FILES['media']['tmp_name']);
        finfo_close($finfo);

        $mime_types_validos = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/webm'
        ];

        if (!in_array($mime_type, $mime_types_validos)) {
            die(json_encode(['success' => false, 'message' => 'Tipo de arquivo não suportado']));
        }

        // Pasta de destino relativa ao publicar.php
        $pasta_destino = __DIR__ . "/../publicacoes/";
        if (!is_dir($pasta_destino)) {
            mkdir($pasta_destino, 0777, true);
        }

        // Gerar nome único para o arquivo
        $prefixo = in_array($extensao, $extensoes_video) ? 'vid_' : 'pub_';
        $nomeMedia = uniqid($prefixo, true) . '.' . $extensao;
        $caminhoCompleto = $pasta_destino . $nomeMedia;

        if (!move_uploaded_file($_FILES['media']['tmp_name'], $caminhoCompleto)) {
            die(json_encode(['success' => false, 'message' => 'Erro ao guardar o arquivo']));
        }

        // Verificar se o arquivo foi realmente salvo
        if (!file_exists($caminhoCompleto)) {
            die(json_encode(['success' => false, 'message' => 'Erro: arquivo não foi salvo corretamente']));
        }
    }

    // Verificar se há conteúdo para publicar
    if (empty($descricao) && empty($nomeMedia)) {
        die(json_encode(['success' => false, 'message' => 'É necessário adicionar uma descrição ou uma mídia']));
    }

    // Inserir publicação
    $stmt = $con->prepare("INSERT INTO publicacao (idutilizador, media, descricao, data) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $idutilizador, $nomeMedia, $descricao, $data);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Publicação criada com sucesso!',
            'media_type' => $nomeMedia ? (in_array($extensao, $extensoes_video ?? []) ? 'video' : 'image') : null
        ]);
    } else {
        // Se houve erro na inserção, remover o arquivo que foi salvo
        if ($nomeMedia && file_exists($caminhoCompleto)) {
            unlink($caminhoCompleto);
        }
        echo json_encode(['success' => false, 'message' => 'Erro ao publicar: ' . $stmt->error]);
    }
    
    $stmt->close();
    $con->close();
    exit();
}

header("Location: ../../main.php");
?>