<?php
session_start();
require "../ligabd.php";

if (!isset($_SESSION["user"]) || $_SESSION["id_tipos_utilizador"] != 0) {
    header("Location: ../logout.php");
    exit();
}

// Verifica se o ID da publicação foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION["erro"] = "ID de publicação inválido.";
    header("Location: publicacoes.php");
    exit();
}

$idpublicacao = (int)$_GET['id'];

// Processar remoção de mídia se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remover_media'])) {
    $idmedia = (int)$_POST['idmedia'];
    
    // Primeiro obtem se o nome do ficheiro para apagar do servidor
    $sql = "SELECT media FROM publicacao_media WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $idmedia);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $media = mysqli_fetch_assoc($result);
    
    if ($media) {
        // Apagar o ficheiro do servidor
        $filepath = "../main/publicacoes/" . $media['media'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        
        // Apagar o registo da base de dados
        $sql = "DELETE FROM publicacao_media WHERE id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "i", $idmedia);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION["sucesso"] = "Mídia removida com sucesso.";
        } else {
            $_SESSION["erro"] = "Erro ao remover mídia da base de dados.";
        }
    } else {
        $_SESSION["erro"] = "Mídia não encontrada.";
    }
    
    // Redirecionar para evitar reenvio do formulário
    header("Location: gerir_publicacao_media.php?id=" . $idpublicacao);
    exit();
}

// Obter informações da publicação
$sql_publicacao = "SELECT p.*, u.nome 
                   FROM publicacao p 
                   JOIN utilizador u ON p.idutilizador = u.idutilizador 
                   WHERE p.idpublicacao = ?";
$stmt = mysqli_prepare($con, $sql_publicacao);
mysqli_stmt_bind_param($stmt, "i", $idpublicacao);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$publicacao = mysqli_fetch_assoc($result); 

if (!$publicacao) {
    $_SESSION["erro"] = "Publicação não encontrada.";
    header("Location: publicacoes.php");
    exit();
}

// Obter todas as mídias da publicação
$sql_media = "SELECT * FROM publicacao_media WHERE idpublicacao = ? ORDER BY tipo";
$stmt = mysqli_prepare($con, $sql_media);
mysqli_stmt_bind_param($stmt, "i", $idpublicacao);
mysqli_stmt_execute($stmt);
$medias = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Nexus | Gerir Mídia da Publicação</title>
    <link rel="icon" href="../imagens/favicon.ico" type="image/png">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #0e2b3b;
            text-align: center;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        h1 img {
            width: 50px;
            height: auto;
            margin-right: 15px;
        }
        
        .erro {
            color: red;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .sucesso {
            color: green;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .publicacao-info {
            background-color: #f0f8ff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .publicacao-info p {
            margin: 5px 0;
        }
        
        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .media-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
        }
        
        .media-item img {
            width: 100%;
            height: auto;
            display: block;
        }
        
        .media-item video {
            width: 100%;
            display: block;
        }
        
        .media-info {
            padding: 10px;
            background-color: #f9f9f9;
        }
        
        .media-actions {
            display: flex;
            justify-content: center;
            padding: 10px;
        }
        
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            margin: 0 5px;
        }
        
        .btn-remover {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-remover:hover {
            background-color: #c82333;
        }
        
        .btn-voltar {
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            display: inline-block;
            padding: 10px 15px;
            margin-top: 20px;
        }
        
        .btn-voltar:hover {
            background-color: #5a6268;
        }
        
        .media-type {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(0,0,0,0.7);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
        
        .no-media {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><img src="../imagens/logo.png" alt="Logo"> Gerir Mídia da Publicação #<?= $idpublicacao ?></h1>
        
        <?php if (isset($_SESSION["erro"])): ?>
            <div class="erro"><?= $_SESSION["erro"] ?></div>
            <?php unset($_SESSION["erro"]); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION["sucesso"])): ?>
            <div class="sucesso"><?= $_SESSION["sucesso"] ?></div>
            <?php unset($_SESSION["sucesso"]); ?>
        <?php endif; ?>
        
        <div class="publicacao-info">
            <p><strong>Utilizador:</strong> <?= htmlspecialchars($publicacao['nome']) ?></p>
            <p><strong>Data:</strong> <?= $publicacao['data'] ?></p>
            <p><strong>Descrição:</strong> <?= !empty($publicacao['descricao']) ? htmlspecialchars($publicacao['descricao']) : 'Sem descrição' ?></p>
        </div>
        
        <h2>Mídias Associadas</h2>
        
        <?php if (mysqli_num_rows($medias) > 0): ?>
            <div class="media-grid">
                <?php while ($media = mysqli_fetch_assoc($medias)): ?>
                    <div class="media-item">
                        <?php if ($media['tipo'] === 'imagem'): ?>
                            <img src="../main/publicacoes/<?= htmlspecialchars($media['media']) ?>" alt="Imagem da publicação">
                        <?php elseif ($media['tipo'] === 'video'): ?>
                            <video controls>
                                <source src="../main/publicacoes/<?= htmlspecialchars($media['media']) ?>" type="video/mp4">
                                Seu navegador não suporta o elemento de vídeo.
                            </video>
                        <?php endif; ?>
                        
                        <div class="media-type"><?= strtoupper($media['tipo']) ?></div>
                        
                        <div class="media-info">
                            <p><strong>Ficheiro:</strong> <?= htmlspecialchars($media['media']) ?></p>
                            <p><strong>Tipo:</strong> <?= ucfirst($media['tipo']) ?></p>
                        </div>
                        
                        <form method="post" class="media-actions">
                            <input type="hidden" name="idmedia" value="<?= $media['id'] ?>">
                            <button type="submit" name="remover_media" class="btn btn-remover" 
                                    onclick="return confirm('Tem a certeza que deseja remover esta mídia?')">
                                Remover
                            </button>
                        </form>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-media">
                <p>Esta publicação não tem mídias associadas.</p>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="publicacoes.php" class="btn btn-voltar">Voltar à Lista de Publicações</a>
        </div>
    </div>
</body>
</html>