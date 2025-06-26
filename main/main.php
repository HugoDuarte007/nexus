<?php
session_start();
require "../ligabd.php";

if (!isset($_SESSION["user"])) {
    header("Location: ../login.php");
    exit();
}

$utilizador = htmlspecialchars($_SESSION["user"]);

// Definir timezone
date_default_timezone_set('Europe/Lisbon');

// Buscar informações do utilizador logado
$query = "SELECT ft_perfil, data_nascimento FROM utilizador WHERE user = '$utilizador'";
$result = mysqli_query($con, $query);

if ($result) {
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $foto_perfil = $row['ft_perfil'] ?? null;
    $data_nascimento = $row['data_nascimento'] ?? null;
} else {
    $foto_perfil = null;
    $data_nascimento = null;
}

$foto_base64 = $foto_perfil ? "data:image/jpeg;base64," . base64_encode($foto_perfil) : "default.png";

$hoje = date("m-d");
$aniversario = $data_nascimento ? date("m-d", strtotime($data_nascimento)) : null;
$mensagem_aniversario = ($aniversario === $hoje) ? "Feliz aniversário, $utilizador! 🎉🥳" : null;

// Buscar publicações da base de dados com informações de likes e se o usuário curtiu
// Substitua a consulta SQL atual por esta:
$idutilizador_logado = $_SESSION["idutilizador"];
$sql = "SELECT p.*, u.user, u.ft_perfil,
       (SELECT COUNT(*) FROM likes WHERE idpublicacao = p.idpublicacao) as total_likes,
       (SELECT COUNT(*) FROM likes WHERE idpublicacao = p.idpublicacao AND idutilizador = $idutilizador_logado) as user_liked,
       (SELECT COUNT(*) FROM guardado WHERE idpublicacao = p.idpublicacao AND idutilizador = $idutilizador_logado) as user_saved
FROM publicacao p
JOIN utilizador u ON p.idutilizador = u.idutilizador
WHERE ? = 'para_ti' OR p.idutilizador IN (
    SELECT id_seguido FROM seguidor WHERE id_seguidor = $idutilizador_logado
)
ORDER BY p.data DESC";

// Adicione esta linha para definir o parâmetro do filtro
$filtro = isset($_GET['filtro']) && $_GET['filtro'] === 'a_seguir' ? 'a_seguir' : 'para_ti';
$sql = str_replace('?', "'$filtro'", $sql);
$publicacoes = mysqli_query($con, $sql);
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../imagens/favicon.ico" type="image/png">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" type="text/css" href="../style.css">
    <title>Nexus | Página Inicial</title>
    <style>
        /* Estilos gerais */
        .container {
            width: 700px;
            margin: auto;
            margin-top: 20px;
            margin-bottom: 100px;
        }

        .post {
            width: 700px;
            background: white;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border: 1px solid #0e2b3b;
        }

        .post-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            position: relative;
            /* Adicione esta linha */
        }

        .profile-picture {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .username {
            font-weight: bold;
            flex: 1;
            padding: 10px;
        }



        .post-time {
            color: gray;
            font-size: 0.9em;
            margin-left: auto;
            margin-right: 10px;
        }

        .post-options {
            position: relative;
        }

        .delete-btn {
            background: none;
            border: none;
            color: #e74c3c;
            cursor: pointer;
            padding: 5px 8px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: background-color 0.3s;
            margin-left: auto;
            /* Isso empurra o botão para a direita */
        }

        .delete-btn:hover {
            background-color: rgba(231, 76, 60, 0.1);
        }

        .delete-btn svg {
            width: 16px;
            height: 16px;
        }

        .post-content {
            margin-bottom: 15px;

        }

        .post-content p {
            margin: 0;
            line-height: 1.4;
            text-align: left;
            padding: 0;
            width: 100%;
        }

        /* Estilos para múltiplas mídias */
        .post-media-container {
            position: relative;
            margin-top: 10px;
            border-radius: 10px;
            overflow: hidden;
        }

        .media-grid {
            display: grid;
            gap: 2px;
            border-radius: 10px;
            overflow: hidden;
        }

        .media-grid.single {
            grid-template-columns: 1fr;
        }

        .media-grid.double {
            grid-template-columns: 1fr 1fr;
        }

        .media-grid.triple {
            grid-template-columns: 2fr 1fr;
            grid-template-rows: 1fr 1fr;
        }

        .media-grid.multiple {
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1fr 1fr;
        }

        .media-item {
            position: relative;
            cursor: pointer;
            overflow: hidden;
            background: #f0f0f0;
        }

        .media-item.first-triple {
            grid-row: span 2;
        }

        .media-item img,
        .media-item video {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .media-item:hover img,
        .media-item:hover video {
            transform: scale(1.05);
        }

        .media-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .media-item:hover .media-overlay {
            opacity: 1;
        }

        /* Estilos das ações do post */
        .post-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 10px;
            border-top: 1px solid #eee;
            margin-top: 15px;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            width: 100%;
            /* Adicionado para ocupar toda a largura */
        }

        .action-btn {
            background: none;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 8px 12px;
            border-radius: 20px;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .action-btn:hover {
            background-color: #f0f0f0;
        }

        .action-btn.liked {
            color: #e74c3c;
        }

        .save-btn {
            margin-left: auto;
            /* Isso empurrará o botão para a direita */
        }

        .save-btn {
            margin-left: auto;
            /* Isso empurrará o botão para a direita */
        }

        .action-btn.saved {
            color: #f39c12;
        }

        .like-count {
            font-weight: bold;
            margin-left: 5px;
        }

        /* Modal de visualização de imagem */
        .image-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.95);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        .image-modal-content {
            position: relative;
            max-width: 90vw;
            max-height: 90vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .image-modal img,
        .image-modal video {
            max-width: 100%;
            max-height: 90vh;
            object-fit: contain;
            border-radius: 8px;
        }

        .image-modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            border: none;
            font-size: 30px;
            cursor: pointer;
            padding: 10px 15px;
            border-radius: 50%;
            transition: background-color 0.3s;
            z-index: 2001;
        }

        .image-modal-close:hover {
            background: rgba(0, 0, 0, 0.9);
        }

        .image-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.7);
            color: white;
            border: none;
            font-size: 24px;
            cursor: pointer;
            padding: 15px 20px;
            border-radius: 50%;
            transition: all 0.3s;
            z-index: 2001;
        }

        .image-nav:hover {
            background: rgba(0, 0, 0, 0.9);
            transform: translateY(-50%) scale(1.1);
        }

        .image-nav.prev {
            left: 30px;
        }

        .image-nav.next {
            right: 30px;
        }

        .image-nav:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .image-nav:disabled:hover {
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.7);
        }

        .image-counter {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            z-index: 2001;
        }

        /* Modal de visualização de publicação */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            position: relative;
            animation: slideIn 0.3s ease;
        }

        .modal-publicacao {
            width: 700px;
            max-height: 90vh;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
        }

        .modal-header h2 {
            margin: 0;
            color: #0e2b3b;
            font-size: 1.2rem;
        }

        .close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
            padding: 5px;
            border-radius: 50%;
            transition: background-color 0.3s;
        }

        .close:hover {
            background-color: #f0f0f0;
        }

        .modal-body {
            padding: 20px;
            overflow-y: auto;
            max-height: calc(90vh - 150px);
        }

        .modal-post-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        .modal-post-header img {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
        }

        .modal-post-content {
            margin-bottom: 20px;
        }

        .modal-post-description {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 15px;
            white-space: pre-wrap;
            text-align: left;
        }

        .modal-post-media {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
            cursor: pointer;
        }

        .modal-post-video {
            width: 100%;
            max-height: 400px;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        /* Container para múltiplas mídias no modal */
        .modal-media-container {
            position: relative;
            margin-bottom: 15px;
        }

        .modal-media-viewer {
            position: relative;
            background: #f8f9fa;
            border-radius: 10px;
            overflow: hidden;
        }

        .modal-media-current {
            width: 100%;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .modal-media-current img,
        .modal-media-current video {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            border-radius: 8px;
        }

        .modal-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.7);
            color: white;
            border: none;
            font-size: 20px;
            cursor: pointer;
            padding: 12px 16px;
            border-radius: 50%;
            transition: all 0.3s;
            z-index: 10;
        }

        .modal-nav:hover {
            background: rgba(0, 0, 0, 0.9);
            transform: translateY(-50%) scale(1.1);
        }

        .modal-nav.prev {
            left: 15px;
        }

        .modal-nav.next {
            right: 15px;
        }

        .modal-nav:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .modal-nav:disabled:hover {
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.7);
        }

        .modal-counter {
            position: absolute;
            bottom: 15px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 12px;
            z-index: 10;
        }

        /* Notificação de aniversário */
        .notificacao {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            animation: slideDown 0.5s ease;
        }

        .fechar {
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            margin-left: 15px;
            padding: 0 5px;
            border-radius: 3px;
            transition: background-color 0.3s;
        }

        .fechar:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        @keyframes slideDown {
            from {
                transform: translateX(-50%) translateY(-100%);
                opacity: 0;
            }

            to {
                transform: translateX(-50%) translateY(0);
                opacity: 1;
            }
        }

        @keyframes slideIn {
            from {
                transform: scale(0.8);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .delete-comment-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 2px;
            margin-left: 10px;
        }

        .delete-comment-btn:hover {
            color: #e74c3c;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .container {
                width: 95%;
                margin-top: 10px;
            }

            .post {
                width: 100%;
            }

            .modal-publicacao {
                width: 95%;
                margin: 20px auto;
            }

            .media-grid.double,
            .media-grid.triple,
            .media-grid.multiple {
                grid-template-columns: 1fr;
                grid-template-rows: auto;
            }

            .media-item.first-triple {
                grid-row: span 1;
            }

            .image-nav,
            .modal-nav {
                padding: 10px 15px;
                font-size: 18px;
            }

            .image-nav.prev,
            .modal-nav.prev {
                left: 10px;
            }

            .image-nav.next,
            .modal-nav.next {
                right: 10px;
            }
        }

        /* Animações */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .post {
            animation: slideUp 0.5s ease;
        }

        .image-modal {
            animation: fadeIn 0.3s ease;
        }

        .action-btn.saved {
            color: #f39c12;
            /* ou qualquer outra cor que deseje para o estado guardado */
        }

        /* Estados de carregamento */
        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            color: #666;
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #0e2b3b;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Melhorias visuais */
        .post:hover {
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
            transition: all 0.3s ease;
        }

        .action-btn svg {
            width: 20px;
            height: 20px;
        }

        .post-date {
            color: #666;
            font-size: 13px;
            margin-top: 10px;
        }

        /* Estilos para os botões de filtro */
        .feed-filter {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
            position: relative;
        }

        .feed-filter-buttons {
            display: flex;
            gap: 30px;
        }

        .feed-filter-btn {
            background: transparent;
            border: none;
            padding: 10px 0;
            font-size: 16px;
            font-weight: 600;
            color: #666;
            cursor: pointer;
            position: relative;
            transition: color 0.3s ease;
        }

        .feed-filter-btn.active {
            color: #0e2b3b;
        }

        .feed-filter-indicator {
            position: absolute;
            bottom: -1px;
            left: 0;
            height: 2px;
            background-color: #0e2b3b;
            transition: all 0.3s ease;
            border-radius: 2px 2px 0 0;
        }
    </style>
</head>

<body>
    <?php if ($mensagem_aniversario): ?>
        <div class="notificacao" id="notificacao">
            <p><?= htmlspecialchars($mensagem_aniversario) ?></p>
            <button class="fechar" onclick="fecharNotificacao()">✖</button>
        </div>
    <?php endif; ?>

    <?php require '../partials/header.php'; ?>

    <div class="container">
        <div class="feed-filter">
            <div class="feed-filter-buttons">
                <button class="feed-filter-btn active" id="btnParaTi" onclick="mudarFeed('para_ti')">Para ti</button>
                <button class="feed-filter-btn" id="btnASeguir" onclick="mudarFeed('a_seguir')">A seguir</button>
                <div class="feed-filter-indicator" id="feedIndicator"></div>
            </div>
        </div>
        <?php if (mysqli_num_rows($publicacoes) > 0): ?>
            <?php while ($publicacao = mysqli_fetch_assoc($publicacoes)): ?>
                <div class="post" data-post-id="<?= $publicacao['idpublicacao'] ?>"
                    id="post_<?= $publicacao['idpublicacao'] ?>">
                    <div class="post-header">
                        <a href="../perfil/perfil.php?id=<?= $publicacao['idutilizador'] ?>" style="text-decoration: none;">
                            <img src="<?= $publicacao['ft_perfil'] ? 'data:image/jpeg;base64,' . base64_encode($publicacao['ft_perfil']) : 'default.png' ?>"
                                alt="Foto de perfil" class="profile-picture">
                        </a>
                        <a href="../perfil/perfil.php?id=<?= $publicacao['idutilizador'] ?>"
                            style="text-decoration: none; color: inherit;">
                            <?= htmlspecialchars($publicacao['user']) ?>
                        </a>
                        <span class="post-time"><?= date("d/m/Y H:i", strtotime($publicacao['data'])) ?></span>

                        <?php if ($publicacao['idutilizador'] == $_SESSION['idutilizador']): ?>
                            <button class="delete-btn" onclick="apagarPublicacao(<?= $publicacao['idpublicacao'] ?>)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                    viewBox="0 0 16 16">
                                    <path
                                        d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z" />
                                    <path fill-rule="evenodd"
                                        d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z" />
                                </svg>
                            </button>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($publicacao['descricao'])): ?>
                        <div class="post-content">
                            <p><?= nl2br(htmlspecialchars($publicacao['descricao'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php
                    // Buscar mídias da publicação
                    $idpub = $publicacao['idpublicacao'];
                    $sql_medias = "SELECT * FROM publicacao_media WHERE idpublicacao = $idpub ORDER BY ordem ASC";
                    $result_medias = mysqli_query($con, $sql_medias);
                    $medias = [];

                    while ($media = mysqli_fetch_assoc($result_medias)) {
                        $medias[] = $media;
                    }

                    // Se não houver mídias na nova tabela, verificar na tabela antiga
                    if (empty($medias) && !empty($publicacao['media'])) {
                        $extensao = strtolower(pathinfo($publicacao['media'], PATHINFO_EXTENSION));
                        $extensoes_video = ['mp4', 'mov', 'avi', 'webm'];
                        $tipo = in_array($extensao, $extensoes_video) ? 'video' : 'imagem';

                        $medias[] = [
                            'media' => $publicacao['media'],
                            'tipo' => $tipo,
                            'ordem' => 1
                        ];
                    }
                    ?>

                    <?php if (!empty($medias)): ?>
                        <div class="post-media-container">
                            <?php
                            $total_medias = count($medias);
                            $grid_class = 'single';

                            if ($total_medias == 2) {
                                $grid_class = 'double';
                            } elseif ($total_medias == 3) {
                                $grid_class = 'triple';
                            } elseif ($total_medias >= 4) {
                                $grid_class = 'multiple';
                            }
                            ?>

                            <div class="media-grid <?= $grid_class ?>" data-post-id="<?= $publicacao['idpublicacao'] ?>">
                                <?php
                                $medias_to_show = ($total_medias > 4) ? array_slice($medias, 0, 4) : $medias;
                                foreach ($medias_to_show as $index => $media):
                                    ?>
                                    <div class="media-item <?= ($grid_class == 'triple' && $index == 0) ? 'first-triple' : '' ?>"
                                        onclick="abrirModalImagem(<?= $publicacao['idpublicacao'] ?>, <?= $index ?>)">
                                        <?php if ($media['tipo'] == 'video'): ?>
                                            <video muted controls>
                                                <source src="publicacoes/<?= $media['media'] ?>" type="video/mp4">
                                                Seu navegador não suporta o elemento de vídeo.
                                            </video>
                                        <?php else: ?>
                                            <img src="publicacoes/<?= $media['media'] ?>" alt="Imagem da publicação">
                                        <?php endif; ?>

                                        <?php if ($total_medias > 4 && $index == 3): ?>
                                            <div class="media-overlay">
                                                +<?= $total_medias - 4 ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="post-actions">
                        <div class="action-buttons">
                            <button class="action-btn like-btn <?= $publicacao['user_liked'] ? 'liked' : '' ?>"
                                onclick="toggleLike(<?= $publicacao['idpublicacao'] ?>, this)">
                                <svg fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                                </svg>
                                <span class="like-count"><?= $publicacao['total_likes'] ?></span>
                            </button>

                            <button class="action-btn comment-btn"
                                onclick="abrirModalVerPublicacao(<?= $publicacao['idpublicacao'] ?>)">
                                <svg fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M21 6h-2v9H6v2c0 .55.45 1 1 1h11l4 4V7c0-.55-.45-1-1-1zm-4 6V3c0-.55-.45-1-1-1H3c-.55 0-1 .45-1 1v14l4-4h11c.55 0 1-.45 1-1z" />
                                </svg>
                                Comentar
                            </button>

                            <button class="action-btn save-btn <?= $publicacao['user_saved'] ? 'saved' : '' ?>"
                                onclick="toggleSave(<?= $publicacao['idpublicacao'] ?>, this)">
                                <svg fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17 3H7c-1.1 0-2 .9-2 2v16l7-3 7 3V5c0-1.1-.9-2-2-2z" />
                                </svg>
                                Guardar
                            </button>
                        </div>
                    </div>

                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="post">
                <p style="text-align: center; color: #666; padding: 40px;">
                    Nenhuma publicação encontrada. Comece a seguir pessoas ou crie a sua primeira publicação!
                </p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal de visualização de imagem -->
    <div id="imageModal" class="image-modal">
        <button class="image-modal-close" onclick="fecharModalImagem()">×</button>
        <button class="image-nav prev" id="prevBtn" onclick="navegarImagem(-1)">‹</button>
        <button class="image-nav next" id="nextBtn" onclick="navegarImagem(1)">›</button>
        <div class="image-counter" id="imageCounter">1 / 1</div>
        <div class="image-modal-content">
            <img id="modalImage" src="" alt="Imagem ampliada" style="display: none;">
            <video id="modalVideo" controls style="display: none;">
                <source src="" type="video/mp4">
            </video>
        </div>
    </div>

    <!-- Modal de visualização de publicação -->
    <div id="modalVerPublicacao" class="modal">
        <div class="modal-content modal-publicacao" style="width: 700px; max-height: 90vh;">
            <div class="modal-header">
                <h2>Publicação</h2>
                <button class="close" onclick="fecharPublicacao()">&times;</button>
            </div>

            <div class="modal-body" id="conteudoPublicacao" style="overflow-y: auto; max-height: calc(90vh - 150px);">
                <!-- Cabeçalho da publicação no modal -->
                <div class="modal-post-header">
                    <a href="" id="modalPerfilLink" class="flex items-center">
                        <img id="modalFtPerfil" alt="Foto de Perfil" class="profile-picture"
                            style="width: 48px; height: 48px;">
                        <span id="modalUsername" class="username" style="font-weight: 600; color: #0e2b3b;"></span>
                    </a>

                    <div class="flex-1">
                        <div class="flex items-center gap-2">

                            <span id="modalData" class="post-time" style="color: #6b7280; font-size: 0.875rem;"></span>
                        </div>
                    </div>
                </div>

                <!-- Conteúdo da publicação no modal -->
                <div class="modal-post-content">
                    <p id="modalDescricao" class="modal-post-description" style="text-align: left;"></p>

                    <!-- Container para múltiplas mídias no modal -->
                    <div id="modalMediaContainer" class="modal-media-container" style="display: none;">
                        <div class="modal-media-viewer">
                            <div class="modal-media-current">
                                <img id="modalCurrentImage" src="" style="display: none;" alt="Imagem da publicação">
                                <video id="modalCurrentVideo" controls style="display: none;" alt="Vídeo da publicação">
                                    <source src="" type="">
                                    Seu navegador não suporta o elemento de vídeo.
                                </video>
                            </div>
                            <button class="modal-nav prev" id="modalPrevBtn" onclick="navegarModalMedia(-1)">‹</button>
                            <button class="modal-nav next" id="modalNextBtn" onclick="navegarModalMedia(1)">›</button>
                            <div class="modal-counter" id="modalMediaCounter">1 / 1</div>
                        </div>
                    </div>

                    <!-- Mídia única (compatibilidade com sistema antigo) -->
                    <img id="modalImagem" src="" class="modal-post-media" style="display: none;"
                        alt="Imagem da publicação" onclick="ampliarMedia(this.src, 'image')">
                    <video id="modalVideo" controls class="modal-post-video" style="display: none;"
                        alt="Vídeo da publicação">
                        <source src="" type="">
                        Seu navegador não suporta o elemento de vídeo.
                    </video>
                </div>



                <!-- Formulário de comentário -->
                <div class="mb-6">
                    <form class="flex gap-2 items-center" method="POST" action="interacoes/comentar.php">
                        <input type="hidden" name="idpublicacao" id="idpublicacao" value="">
                        <img src="<?= $foto_base64 ?>" alt="Sua foto" class="w-10 h-10 rounded-full object-cover">
                        <div class="flex-1 relative">
                            <input type="text" name="comentario" required
                                class="w-full px-4 py-2 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Adicione um comentário...">
                        </div>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-500 text-white rounded-full hover:bg-blue-600 transition">Publicar</button>
                    </form>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Comentários</h3>
                    <div id="comentarios" class="space-y-4">
                        <!-- Comentários serão carregados aqui -->
                        <div id="comentarioTemplate" class="hidden">
                            <div class="flex gap-3">
                                <img class="comentario-ft-perfil w-10 h-10 rounded-full object-cover"
                                    alt="Foto de Perfil">
                                <div class="flex-1">
                                    <div class="bg-gray-100 rounded-lg p-3">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span
                                                class="comentario-username font-semibold text-sm text-gray-800"></span>
                                            <span class="comentario-data text-xs text-gray-500"></span>
                                            <button
                                                class="delete-comment-btn ml-auto text-red-500 hover:text-red-700 text-xs"
                                                onclick="apagarComentario(this, <?= $_SESSION['idutilizador'] ?>, '%%IDCOMENTARIO%%')"
                                                style="display: none;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12"
                                                    fill="currentColor" viewBox="0 0 16 16">
                                                    <path
                                                        d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z" />
                                                    <path fill-rule="evenodd"
                                                        d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z" />
                                                </svg>
                                            </button>
                                        </div>
                                        <p class="comentario-conteudo text-gray-800 text-sm" style="text-align:left;">
                                        </p>
                                    </div>
                                    <div class="flex gap-4 mt-1 ml-3">
                                        <button class="text-xs text-gray-500 hover:text-gray-700">Gostar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Variáveis globais para o modal de imagem
        let currentPostId = null;
        let currentImageIndex = 0;
        let currentMedias = [];
        let currentModalPostId = null;
        let modalMedias = [];
        let modalCurrentIndex = 0;

        // Função para fechar notificação de aniversário
        function fecharNotificacao() {
            const notificacao = document.getElementById('notificacao');
            if (notificacao) {
                notificacao.style.animation = 'slideUp 0.5s ease reverse';
                setTimeout(() => notificacao.remove(), 500);
            }
        }
        async function apagarComentario(button, idUtilizadorLogado, idComentario) {
            if (!confirm('Tem certeza que deseja apagar este comentário?')) {
                return;
            }

            try {
                const response = await fetch('interacoes/apagar_comentario.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `idcomentario=${idComentario}`
                });

                const data = await response.json();

                if (data.success) {
                    // Remove o elemento do comentário
                    button.closest('.flex.gap-3').remove();

                    // Se não houver mais comentários, mostra mensagem
                    const comentariosContainer = document.getElementById('comentarios');
                    if (comentariosContainer.children.length === 1) { // Apenas o template
                        const noComments = document.createElement('p');
                        noComments.textContent = 'Nenhum comentário ainda. Seja o primeiro a comentar!';
                        noComments.style.textAlign = 'center';
                        noComments.style.color = '#666';
                        noComments.style.padding = '20px';
                        comentariosContainer.appendChild(noComments);
                    }
                } else {
                    alert('Erro ao apagar comentário: ' + data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao apagar comentário');
            }
        }

        // Função para toggle do menu de opções
        function toggleOptions(postId) {
            const menu = document.getElementById(`options-${postId}`);
            const isVisible = menu.style.display === 'block';

            // Fechar todos os menus
            document.querySelectorAll('.options-menu').forEach(m => m.style.display = 'none');

            // Abrir o menu clicado se não estava visível
            if (!isVisible) {
                menu.style.display = 'block';
            }
        }

        // Fechar menus ao clicar fora
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.post-options')) {
                document.querySelectorAll('.options-menu').forEach(m => m.style.display = 'none');
            }
        });

        // Função para abrir modal de imagem
        async function abrirModalImagem(postId, startIndex = 0) {
            console.log('Abrir modal de imagem chamado para postId:', postId); // Debug
            currentPostId = postId;
            currentImageIndex = startIndex;

            try {
                const response = await fetch(`interacoes/get_medias_post.php?id=${postId}`);
                const data = await response.json();
                console.log('Dados recebidos:', data); // Debug

                if (data.success) {
                    currentMedias = data.medias;
                    console.log('Mídias carregadas:', currentMedias); // Debug
                    mostrarImagemAtual();
                    document.getElementById('imageModal').style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                } else {
                    console.error('Erro ao carregar mídias:', data.message);
                    alert('Erro ao carregar mídias: ' + data.message);
                }
            } catch (error) {
                console.error('Erro na requisição:', error);
                alert('Erro ao carregar mídias');
            }
        }

        // Função para mostrar imagem atual
        function mostrarImagemAtual() {
            if (!currentMedias || currentMedias.length === 0) return;

            const media = currentMedias[currentImageIndex];
            const modalImage = document.getElementById('modalImage');
            const modalVideo = document.getElementById('modalVideo');
            const counter = document.getElementById('imageCounter');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');

            // Atualizar contador
            counter.textContent = `${currentImageIndex + 1} / ${currentMedias.length}`;

            // Mostrar/ocultar botões de navegação
            prevBtn.disabled = currentImageIndex === 0;
            nextBtn.disabled = currentImageIndex === currentMedias.length - 1;
            prevBtn.style.display = currentMedias.length > 1 ? 'block' : 'none';
            nextBtn.style.display = currentMedias.length > 1 ? 'block' : 'none';
            counter.style.display = currentMedias.length > 1 ? 'block' : 'none';

            // Mostrar mídia
            if (media.tipo === 'video') {
                modalImage.style.display = 'none';
                modalVideo.style.display = 'block';
                modalVideo.querySelector('source').src = `publicacoes/${media.media}`;
                modalVideo.load();
            } else {
                modalVideo.style.display = 'none';
                modalImage.style.display = 'block';
                modalImage.src = `publicacoes/${media.media}`;
            }
        }

        // Função para navegar entre imagens
        function navegarImagem(direction) {
            const newIndex = currentImageIndex + direction;

            if (newIndex >= 0 && newIndex < currentMedias.length) {
                currentImageIndex = newIndex;
                mostrarImagemAtual();
            }
        }

        // Função para fechar modal de imagem
        function fecharModalImagem() {
            document.getElementById('imageModal').style.display = 'none';
            document.body.style.overflow = 'auto';

            // Pausar vídeo se estiver tocando
            const modalVideo = document.getElementById('modalVideo');
            modalVideo.pause();
            modalVideo.currentTime = 0;
        }

        async function abrirModalVerPublicacao(postId) {
            currentModalPostId = postId;

            try {
                const response = await fetch(`interacoes/get_publicacao_completa.php?id=${postId}`);
                const data = await response.json();

                if (data.success) {
                    const publicacao = document.querySelector('#post_' + postId);
                    const ftPerfil = publicacao.querySelector('.profile-picture').src;
                    // Preencher dados básicos
                    document.getElementById('modalUsername').textContent = data.user;
                    document.getElementById('modalData').textContent = data.data_formatada;
                    document.getElementById("modalFtPerfil").src = ftPerfil;
                    document.getElementById('modalPerfilLink').href = `../perfil/perfil.php?id=${data.idutilizador}`;
                    document.getElementById('idpublicacao').value = postId;

                    // Preencher descrição
                    const modalDescricao = document.getElementById('modalDescricao');
                    modalDescricao.innerHTML = data.descricao ? nl2br(htmlspecialchars(data.descricao)) : '';
                    modalDescricao.style.display = data.descricao ? 'block' : 'none';

                    // Configurar mídias
                    const modalMediaContainer = document.getElementById('modalMediaContainer');
                    const modalImagem = document.getElementById('modalImagem');
                    const modalVideo = document.getElementById('modalVideo');

                    // Sempre esconder os elementos de mídia única primeiro
                    modalImagem.style.display = 'none';
                    modalVideo.style.display = 'none';

                    if (data.medias && data.medias.length > 0) {
                        modalMedias = data.medias;
                        modalCurrentIndex = 0;

                        // Mostrar container de múltiplas mídias para qualquer quantidade
                        modalMediaContainer.style.display = 'block';
                        mostrarModalMediaAtual();
                    } else {
                        // Sem mídias
                        modalMediaContainer.style.display = 'none';
                    }

                    // Carregar comentários
                    carregarComentarios(postId);

                    // Mostrar o modal
                    document.getElementById('modalVerPublicacao').style.display = 'flex';
                    document.body.style.overflow = 'hidden';

                    // Focar no campo de comentário
                    setTimeout(() => {
                        const commentField = document.querySelector('#modalVerPublicacao input[name="comentario"]');
                        if (commentField) commentField.focus();
                    }, 300);
                } else {
                    console.error('Erro ao carregar publicação:', data.message);
                    alert('Não foi possível carregar a publicação');
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Ocorreu um erro ao carregar a publicação');
            }
        }

        // Função auxiliar para nl2br (simulação do PHP)
        function nl2br(str) {
            return str.replace(/\n/g, '<br>');
        }

        // Função auxiliar para htmlspecialchars (simulação do PHP)
        function htmlspecialchars(str) {
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
        }

        // Função para mostrar mídia atual no modal
        function mostrarModalMediaAtual() {
            if (!modalMedias || modalMedias.length === 0) return;

            const media = modalMedias[modalCurrentIndex];
            const modalCurrentImage = document.getElementById('modalCurrentImage');
            const modalCurrentVideo = document.getElementById('modalCurrentVideo');
            const modalCounter = document.getElementById('modalMediaCounter');
            const modalPrevBtn = document.getElementById('modalPrevBtn');
            const modalNextBtn = document.getElementById('modalNextBtn');

            // Atualizar contador
            modalCounter.textContent = `${modalCurrentIndex + 1} / ${modalMedias.length}`;

            // Mostrar/ocultar botões de navegação
            modalPrevBtn.disabled = modalCurrentIndex === 0;
            modalNextBtn.disabled = modalCurrentIndex === modalMedias.length - 1;
            modalPrevBtn.style.display = modalMedias.length > 1 ? 'block' : 'none';
            modalNextBtn.style.display = modalMedias.length > 1 ? 'block' : 'none';
            modalCounter.style.display = modalMedias.length > 1 ? 'block' : 'none';

            // Mostrar mídia
            if (media.tipo === 'video') {
                modalCurrentImage.style.display = 'none';
                modalCurrentVideo.style.display = 'block';
                modalCurrentVideo.querySelector('source').src = `publicacoes/${media.media}`;
                modalCurrentVideo.load();
            } else {
                modalCurrentVideo.style.display = 'none';
                modalCurrentImage.style.display = 'block';
                modalCurrentImage.src = `publicacoes/${media.media}`;
            }
        }

        // Função para navegar entre mídias no modal
        function navegarModalMedia(direction) {
            const newIndex = modalCurrentIndex + direction;

            if (newIndex >= 0 && newIndex < modalMedias.length) {
                modalCurrentIndex = newIndex;
                mostrarModalMediaAtual();
            }
        }

        // Função para fechar modal de publicação
        function fecharPublicacao() {
            document.getElementById('modalVerPublicacao').style.display = 'none';
            document.body.style.overflow = 'auto';
            currentModalPostId = null;
            modalMedias = [];
            modalCurrentIndex = 0;
        }

        // Função para ampliar mídia (compatibilidade)
        function ampliarMedia(src, type) {
            // Encontrar o índice da mídia atual
            const mediaIndex = modalMedias.findIndex(media => src.includes(media.media));
            if (mediaIndex !== -1) {
                currentPostId = currentModalPostId;
                currentMedias = modalMedias;
                currentImageIndex = mediaIndex;
                mostrarImagemAtual();
                document.getElementById('imageModal').style.display = 'flex';
            }
        }

        // Navegação por teclado
        document.addEventListener('keydown', function (e) {
            const imageModal = document.getElementById('imageModal');
            const publicacaoModal = document.getElementById('modalVerPublicacao');

            if (imageModal.style.display === 'flex') {
                switch (e.key) {
                    case 'Escape':
                        fecharModalImagem();
                        break;
                    case 'ArrowLeft':
                        navegarImagem(-1);
                        break;
                    case 'ArrowRight':
                        navegarImagem(1);
                        break;
                }
            } else if (publicacaoModal.style.display === 'flex') {
                switch (e.key) {
                    case 'Escape':
                        fecharPublicacao();
                        break;
                    case 'ArrowLeft':
                        if (modalMedias.length > 1) navegarModalMedia(-1);
                        break;
                    case 'ArrowRight':
                        if (modalMedias.length > 1) navegarModalMedia(1);
                        break;
                }
            }
        });

        // Função para carregar comentários
        async function carregarComentarios(postId) {
            try {
                const response = await fetch(`interacoes/obter_comentarios.php?idpublicacao=${postId}`);
                const comentarios = await response.json();
                const comentariosContainer = document.getElementById('comentarios');
                const template = document.getElementById('comentarioTemplate');
                const idUtilizadorLogado = <?= $_SESSION['idutilizador'] ?>;

                // Limpar comentários existentes (exceto o template)
                comentariosContainer.innerHTML = '';
                comentariosContainer.appendChild(template); // Manter o template

                if (comentarios && comentarios.length > 0) {
                    comentarios.forEach(comentario => {
                        const comentarioElement = template.cloneNode(true);
                        comentarioElement.id = '';
                        comentarioElement.classList.remove('hidden');

                        // Preencher dados do comentário
                        const imgElement = comentarioElement.querySelector('.comentario-ft-perfil');
                        imgElement.src = comentario.ft_perfil ?
                            (comentario.ft_perfil.startsWith('data:image') ?
                                comentario.ft_perfil :
                                'data:image/jpeg;base64,' + base64_encode(comentario.ft_perfil)) :
                            'default.png';

                        comentarioElement.querySelector('.comentario-username').textContent = comentario.user || 'Utilizador';
                        comentarioElement.querySelector('.comentario-data').textContent = formatarData(comentario.data);
                        comentarioElement.querySelector('.comentario-conteudo').textContent = comentario.conteudo;

                        // Configurar botão de apagar
                        const deleteBtn = comentarioElement.querySelector('.delete-comment-btn');
                        deleteBtn.setAttribute('onclick', `apagarComentario(this, ${idUtilizadorLogado}, ${comentario.idcomentario})`);

                        // Mostrar apenas se o usuário logado for o autor
                        if (comentario.idutilizador == idUtilizadorLogado) {
                            deleteBtn.style.display = 'block'; // Alterado de 'inline-block' para 'block'
                        } else {
                            deleteBtn.style.display = 'none';
                        }

                        // Remover o placeholder %%IDCOMENTARIO%% do template
                        const deleteBtnHtml = deleteBtn.outerHTML.replace('%%IDCOMENTARIO%%', comentario.idcomentario);
                        deleteBtn.outerHTML = deleteBtnHtml;

                        comentariosContainer.appendChild(comentarioElement);
                    });
                } else {
                    const noComments = document.createElement('p');
                    noComments.textContent = 'Nenhum comentário ainda. Seja o primeiro a comentar!';
                    noComments.style.textAlign = 'center';
                    noComments.style.color = '#666';
                    noComments.style.padding = '20px';
                    comentariosContainer.appendChild(noComments);
                }
            } catch (error) {
                console.error('Erro ao carregar comentários:', error);
                const comentariosContainer = document.getElementById('comentarios');
                comentariosContainer.innerHTML = `
            <p style="color: red; text-align: center; padding: 20px;">
                Erro ao carregar comentários: ${error.message}
            </p>
        `;
            }
        }

        function formatarData(dataString) {
            const data = new Date(dataString);
            return data.toLocaleString('pt-PT', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function base64_encode(str) {
            return btoa(unescape(encodeURIComponent(str)));
        }
        // Função para toggle like
        async function toggleLike(postId, button) {
            try {
                const formData = new FormData();
                formData.append('idpublicacao', postId);

                const response = await fetch('interacoes/like.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.text();
                const likeCount = button.querySelector('.like-count');
                let currentCount = parseInt(likeCount.textContent);

                if (result === 'liked') {
                    button.classList.add('liked');
                    likeCount.textContent = currentCount + 1;
                } else if (result === 'unliked') {
                    button.classList.remove('liked');
                    likeCount.textContent = Math.max(0, currentCount - 1);
                }
            } catch (error) {
                console.error('Erro ao dar like:', error);
            }
        }

        // Função para toggle save
        async function toggleSave(postId, button) {
            try {
                // Mostrar estado de carregamento
                button.disabled = true;
                const originalText = button.innerHTML;
                button.innerHTML = '<span class="spinner"></span>';

                const response = await fetch('interacoes/guardar.php', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `idpublicacao=${postId}`
                });

                // Verificar se a resposta é JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    throw new Error(`Resposta inválida: ${text.substring(0, 100)}...`);
                }

                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.message || 'Erro desconhecido');
                }

                // Atualizar visual do botão
                button.classList.toggle('saved', data.guardado);

                // Feedback visual
                button.innerHTML = data.guardado ? 'Guardado' : 'Guardar';
                setTimeout(() => {
                    button.innerHTML = originalText;
                }, 2000);

            } catch (error) {
                console.error('Erro ao guardar publicação:', error);
                alert('Erro: ' + error.message);
            } finally {
                button.disabled = false;
            }
        }

        function apagarPublicacao(idPublicacao) {
            if (confirm('Tem certeza que deseja apagar esta publicação?')) {
                const formData = new FormData();
                formData.append('idpublicacao', idPublicacao);

                fetch('interacoes/apagar_publicacao.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.querySelector(`[data-post-id="${idPublicacao}"]`).remove();
                        } else {
                            alert('Erro ao apagar publicação: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro ao apagar publicação');
                    });
            }
        }

        // Fechar modais ao clicar fora
        document.getElementById('imageModal').addEventListener('click', function (e) {
            if (e.target === this) {
                fecharModalImagem();
            }
        });

        document.getElementById('modalVerPublicacao').addEventListener('click', function (e) {
            if (e.target === this) {
                fecharPublicacao();
            }
        });

        // Auto-fechar notificação de aniversário
        if (document.getElementById('notificacao')) {
            setTimeout(fecharNotificacao, 5000);
        }

        // Lazy loading para imagens
        const observerOptions = {
            root: null,
            rootMargin: '50px',
            threshold: 0.1
        };

        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        imageObserver.unobserve(img);
                    }
                }
            });
        }, observerOptions);

        // Observar todas as imagens com data-src
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });

        // Adicionar animação de entrada para posts
        const postObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.post').forEach(post => {
            post.style.opacity = '0';
            post.style.transform = 'translateY(30px)';
            post.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            postObserver.observe(post);
        });
        // Adicione isso no seu JavaScript
        document.querySelectorAll('video').forEach(video => {
            video.addEventListener('error', function () {
                console.error('Erro ao carregar vídeo:', this.querySelector('source').src);
            });
        });
        // Variável para controlar o tipo de feed
        let currentFeed = 'para_ti';

        // Função para mudar entre os feeds
        function mudarFeed(tipo) {
            if (tipo === currentFeed) return;

            currentFeed = tipo;

            // Atualizar UI dos botões
            document.getElementById('btnParaTi').classList.toggle('active', tipo === 'para_ti');
            document.getElementById('btnASeguir').classList.toggle('active', tipo === 'a_seguir');

            // Mover o indicador
            const indicator = document.getElementById('feedIndicator');
            const activeBtn = tipo === 'para_ti' ? document.getElementById('btnParaTi') : document.getElementById('btnASeguir');

            indicator.style.width = `${activeBtn.offsetWidth}px`;
            indicator.style.left = `${activeBtn.offsetLeft}px`;

            // Filtrar as publicações
            filtrarPublicacoes();
        }

        // Função para filtrar publicações
        function filtrarPublicacoes() {
            const posts = document.querySelectorAll('.post');

            if (currentFeed === 'a_seguir') {
                // Aqui você precisará fazer uma requisição AJAX para obter as publicações das pessoas que o usuário segue
                // Por enquanto, vou apenas esconder todas e mostrar uma mensagem
                posts.forEach(post => post.style.display = 'none');

                const noPostsMsg = document.createElement('div');
                noPostsMsg.className = 'post';
                noPostsMsg.innerHTML = `
            <p style="text-align: center; color: #666; padding: 40px;">
                Nenhuma publicação encontrada das pessoas que segue. Comece a seguir mais pessoas!
            </p>
        `;

                const container = document.querySelector('.container');
                if (!document.querySelector('.no-following-posts')) {
                    container.insertBefore(noPostsMsg, container.firstChild.nextSibling);
                    noPostsMsg.classList.add('no-following-posts');
                }
            } else {
                // Mostrar todas as publicações
                posts.forEach(post => post.style.display = 'block');
                const noPostsMsg = document.querySelector('.no-following-posts');
                if (noPostsMsg) {
                    noPostsMsg.remove();
                }
            }
        }

        // Inicializar o indicador
        document.addEventListener('DOMContentLoaded', function () {
            const activeBtn = document.querySelector('.feed-filter-btn.active');
            const indicator = document.getElementById('feedIndicator');

            indicator.style.width = `${activeBtn.offsetWidth}px`;
            indicator.style.left = `${activeBtn.offsetLeft}px`;
        });
        // Atualize a função filtrarPublicacoes para:
        function filtrarPublicacoes() {
            const container = document.querySelector('.container');

            // Mostrar loading
            const loading = document.createElement('div');
            loading.className = 'post';
            loading.innerHTML = '<p style="text-align: center; color: #666; padding: 40px;">Carregando...</p>';
            container.insertBefore(loading, container.firstChild.nextSibling);

            // Fazer requisição AJAX
            fetch(`main.php?filtro=${currentFeed}`)
                .then(response => response.text())
                .then(html => {
                    // Parsear o HTML recebido
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    // Obter as novas publicações
                    const newPosts = doc.querySelectorAll('.post');

                    // Remover posts antigos (exceto o filtro e mensagens)
                    const posts = document.querySelectorAll('.container > .post:not(.no-following-posts)');
                    posts.forEach(post => post.remove());

                    // Adicionar os novos posts
                    newPosts.forEach(post => {
                        container.appendChild(post);
                    });

                    // Remover loading
                    loading.remove();
                })
                .catch(error => {
                    console.error('Erro:', error);
                    loading.innerHTML = '<p style="text-align: center; color: red; padding: 40px;">Erro ao carregar publicações</p>';
                });
        }
    </script>
</body>

</html>