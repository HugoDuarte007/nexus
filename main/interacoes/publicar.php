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
    $medias_salvas = [];

    // Verificar se há conteúdo para publicar
    $tem_media = !empty($_FILES['media']['name'][0]);
    if (empty($descricao) && !$tem_media) {
        die(json_encode(['success' => false, 'message' => 'É necessário adicionar uma descrição ou uma mídia']));
    }

    // Pasta de destino
    $pasta_destino = __DIR__ . "/../publicacoes/";
    if (!is_dir($pasta_destino)) {
        mkdir($pasta_destino, 0777, true);
    }

    // Processar múltiplas mídias
    if ($tem_media) {
        $total_files = count($_FILES['media']['name']);
        
        if ($total_files > 10) {
            die(json_encode(['success' => false, 'message' => 'Máximo de 10 arquivos permitidos']));
        }

        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES['media']['error'][$i] === UPLOAD_ERR_OK) {
                // Validações da mídia
                $extensao = strtolower(pathinfo($_FILES['media']['name'][$i], PATHINFO_EXTENSION));
                $extensoes_imagem = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $extensoes_video = ['mp4', 'mov', 'avi', 'webm'];
                $extensoes_permitidas = array_merge($extensoes_imagem, $extensoes_video);
                
                if (!in_array($extensao, $extensoes_permitidas)) {
                    // Limpar arquivos já salvos em caso de erro
                    foreach ($medias_salvas as $arquivo) {
                        if (file_exists($pasta_destino . $arquivo['nome'])) {
                            unlink($pasta_destino . $arquivo['nome']);
                        }
                    }
                    die(json_encode(['success' => false, 'message' => 'Apenas imagens (JPG, PNG, GIF, WEBP) ou vídeos (MP4, MOV, AVI, WEBM) são permitidos']));
                }

                // Verificar tamanho do arquivo
                $tamanho_maximo = in_array($extensao, $extensoes_video) ? 52428800 : 5242880; // 50MB ou 5MB
                if ($_FILES['media']['size'][$i] > $tamanho_maximo) {
                    foreach ($medias_salvas as $arquivo) {
                        if (file_exists($pasta_destino . $arquivo['nome'])) {
                            unlink($pasta_destino . $arquivo['nome']);
                        }
                    }
                    $limite = in_array($extensao, $extensoes_video) ? '50MB' : '5MB';
                    die(json_encode(['success' => false, 'message' => "Tamanho máximo do arquivo: $limite"]));
                }

                // Verificar MIME type
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $_FILES['media']['tmp_name'][$i]);
                finfo_close($finfo);

                $mime_types_validos = [
                    'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                    'video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/webm'
                ];

                if (!in_array($mime_type, $mime_types_validos)) {
                    foreach ($medias_salvas as $arquivo) {
                        if (file_exists($pasta_destino . $arquivo['nome'])) {
                            unlink($pasta_destino . $arquivo['nome']);
                        }
                    }
                    die(json_encode(['success' => false, 'message' => 'Tipo de arquivo não suportado']));
                }

                // Gerar nome único para o arquivo
                $prefixo = in_array($extensao, $extensoes_video) ? 'vid_' : 'pub_';
                $nomeMedia = uniqid($prefixo, true) . '.' . $extensao;
                $caminhoCompleto = $pasta_destino . $nomeMedia;

                if (!move_uploaded_file($_FILES['media']['tmp_name'][$i], $caminhoCompleto)) {
                    foreach ($medias_salvas as $arquivo) {
                        if (file_exists($pasta_destino . $arquivo['nome'])) {
                            unlink($pasta_destino . $arquivo['nome']);
                        }
                    }
                    die(json_encode(['success' => false, 'message' => 'Erro ao guardar o arquivo']));
                }

                $medias_salvas[] = [
                    'nome' => $nomeMedia,
                    'tipo' => in_array($extensao, $extensoes_video) ? 'video' : 'imagem',
                    'ordem' => $i + 1
                ];
            }
        }
    }

    // Inserir publicação
    $stmt = $con->prepare("INSERT INTO publicacao (idutilizador, descricao, data) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $idutilizador, $descricao, $data);

    if ($stmt->execute()) {
        $idpublicacao = $con->insert_id;
        
        // Inserir mídias na tabela publicacao_media
        if (!empty($medias_salvas)) {
            $stmt_media = $con->prepare("INSERT INTO publicacao_media (idpublicacao, media, tipo, ordem) VALUES (?, ?, ?, ?)");
            
            foreach ($medias_salvas as $media) {
                $stmt_media->bind_param("issi", $idpublicacao, $media['nome'], $media['tipo'], $media['ordem']);
                if (!$stmt_media->execute()) {
                    // Em caso de erro, remover arquivos salvos
                    foreach ($medias_salvas as $arquivo) {
                        if (file_exists($pasta_destino . $arquivo['nome'])) {
                            unlink($pasta_destino . $arquivo['nome']);
                        }
                    }
                    die(json_encode(['success' => false, 'message' => 'Erro ao salvar informações da mídia']));
                }
            }
            $stmt_media->close();
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Publicação criada com sucesso!',
            'total_medias' => count($medias_salvas)
        ]);
    } else {
        // Se houve erro na inserção, remover arquivos salvos
        foreach ($medias_salvas as $arquivo) {
            if (file_exists($pasta_destino . $arquivo['nome'])) {
                unlink($pasta_destino . $arquivo['nome']);
            }
        }
        echo json_encode(['success' => false, 'message' => 'Erro ao publicar: ' . $stmt->error]);
    }
    
    $stmt->close();
    $con->close();
    exit();
}

header("Location: ../../main.php");
?>