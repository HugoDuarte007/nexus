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
$idutilizador_logado = $_SESSION["idutilizador"];
$sql = "SELECT p.*, u.user, u.ft_perfil,
               (SELECT COUNT(*) FROM likes WHERE idpublicacao = p.idpublicacao) as total_likes,
               (SELECT COUNT(*) FROM likes WHERE idpublicacao = p.idpublicacao AND idutilizador = $idutilizador_logado) as user_liked,
               (SELECT COUNT(*) FROM guardado WHERE idpublicacao = p.idpublicacao AND idutilizador = $idutilizador_logado) as user_saved
        FROM publicacao p
        JOIN utilizador u ON p.idutilizador = u.idutilizador
        ORDER BY p.data DESC";

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
        }

        .post-time {
            color: gray;
            font-size: 0.9em;
        }

        .post-options {
            position: relative;
        }

        .options-btn {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            transition: background-color 0.3s;
        }

        .options-btn:hover {
            background-color: #f0f0f0;
        }

        .options-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 100;
            min-width: 150px;
        }

        .options-menu button {
            display: block;
            width: 100%;
            padding: 10px 15px;
            border: none;
            background: none;
            text-align: left;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .options-menu button:hover {
            background-color: #f5f5f5;
        }

        .options-menu button.delete-btn {
            color: #e74c3c;
        }

        .post-content {
            margin-bottom: 15px;
        }

        .post-content p {
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: pre-wrap;
            margin: 0;
            line-height: 1.4;
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
            height: 100%;
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

            .image-nav, .modal-nav {
                padding: 10px 15px;
                font-size: 18px;
            }

            .image-nav.prev, .modal-nav.prev {
                left: 10px;
            }

            .image-nav.next, .modal-nav.next {
                right: 10px;
            }
        }

        /* Animações */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
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
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
        <?php if (mysqli_num_rows($publicacoes) > 0): ?>
            <?php while ($publicacao = mysqli_fetch_assoc($publicacoes)): ?>
                <div class="post" data-post-id="<?= $publicacao['idpublicacao'] ?>">
                    <div class="post-header">
                        <a href="../perfil/perfil.php?id=<?= $publicacao['idutilizador'] ?>" style="text-decoration: none;">
                            <img src="<?= $publicacao['ft_perfil'] ? 'data:image/jpeg;base64,' . base64_encode($publicacao['ft_perfil']) : 'default.png' ?>" 
                                 alt="Foto de perfil" class="profile-picture">
                        </a>
                        <div class="username">
                            <a href="../perfil/perfil.php?id=<?= $publicacao['idutilizador'] ?>" style="text-decoration: none; color: inherit;">
                                <?= htmlspecialchars($publicacao['user']) ?>
                            </a>
                        </div>
                        <span class="post-time"><?= date("d/m/Y H:i", strtotime($publicacao['data'])) ?></span>
                        
                        <div class="post-options">
                            <button class="options-btn" onclick="toggleOptions(<?= $publicacao['idpublicacao'] ?>)">⋯</button>
                            <div class="options-menu" id="options-<?= $publicacao['idpublicacao'] ?>">
                                <button onclick="abrirModalVerPublicacao(<?= $publicacao['idpublicacao'] ?>)">Ver publicação</button>
                                <?php if ($publicacao['idutilizador'] == $_SESSION['idutilizador']): ?>
                                    <button class="delete-btn" onclick="apagarPublicacao(<?= $publicacao['idpublicacao'] ?>)">Apagar</button>
                                <?php endif; ?>
                            </div>
                        </div>
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
                                            <video muted>
                                                <source src="publicacoes/<?= $media['media'] ?>" type="video/mp4">
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
                                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                </svg>
                                <span class="like-count"><?= $publicacao['total_likes'] ?></span>
                            </button>
                            
                            <button class="action-btn comment-btn" onclick="abrirModalVerPublicacao(<?= $publicacao['idpublicacao'] ?>)">
                                <svg fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M21 6h-2v9H6v2c0 .55.45 1 1 1h11l4 4V7c0-.55-.45-1-1-1zm-4 6V3c0-.55-.45-1-1-1H3c-.55 0-1 .45-1 1v14l4-4h11c.55 0 1-.45 1-1z"/>
                                </svg>
                                Comentar
                            </button>
                            
                            <button class="action-btn save-btn <?= $publicacao['user_saved'] ? 'saved' : '' ?>" 
                                    onclick="toggleSave(<?= $publicacao['idpublicacao'] ?>, this)">
                                <svg fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17 3H7c-1.1 0-2 .9-2 2v16l7-3 7 3V5c0-1.1-.9-2-2-2z"/>
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
                    <a href="" id="modalPerfilLink">
                        <img id="modalFtPerfil" alt="Foto de Perfil" class="profile-picture" style="width: 48px; height: 48px;">
                    </a>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span id="modalUsername" class="username" style="font-weight: 600; color: #0e2b3b;"></span>
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
                    <img id="modalImagem" src="" class="modal-post-media" style="display: none;" alt="Imagem da publicação" onclick="ampliarMedia(this.src, 'image')">
                    <video id="modalVideo" controls class="modal-post-video" style="display: none;" alt="Vídeo da publicação">
                        <source src="" type="">
                        Seu navegador não suporta o elemento de vídeo.
                    </video>
                </div>

                <!-- Ações da publicação -->
                <div class="flex justify-between items-center px-4 py-2 border-t border-b border-gray-100 mb-4">
                    <button class="flex items-center gap-1 text-gray-600 hover:text-blue-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Gostar</span>
                    </button>
                    <button class="flex items-center gap-1 text-gray-600 hover:text-green-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 0v12h8V4H6z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Guardar</span>
                    </button>
                    <button class="flex items-center gap-1 text-gray-600 hover:text-purple-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path
                                d="M15 8a3 3 0 10-2.977-2.63l-4.94 2.47a3 3 0 100 4.319l4.94 2.47a3 3 0 10.895-1.789l-4.94-2.47a3.027 3.027 0 000-.74l4.94-2.47C13.456 7.68 14.19 8 15 8z" />
                        </svg>
                        <span>Partilhar</span>
                    </button>
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
                        <!-- Template de comentário (hidden) -->
                        <div id="comentarioTemplate" class="hidden">
                            <div class="flex gap-3">
                                <img class="comentario-ft-perfil w-10 h-10 rounded-full object-cover" alt="Foto de Perfil">
                                <div class="flex-1">
                                    <div class="bg-gray-100 rounded-lg p-3">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="comentario-username font-semibold text-sm text-gray-800"></span>
                                            <span class="comentario-data text-xs text-gray-500"></span>
                                        </div>
                                        <p class="comentario-conteudo text-gray-800 text-sm" style="text-align:left;"></p>
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
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.post-options')) {
                document.querySelectorAll('.options-menu').forEach(m => m.style.display = 'none');
            }
        });

        // Função para abrir modal de imagem
        async function abrirModalImagem(postId, startIndex = 0) {
            currentPostId = postId;
            currentImageIndex = startIndex;
            
            try {
                // Buscar mídias da publicação
                const response = await fetch(`interacoes/get_medias_post.php?id=${postId}`);
                const data = await response.json();
                
                if (data.success) {
                    currentMedias = data.medias;
                    mostrarImagemAtual();
                    document.getElementById('imageModal').style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                } else {
                    console.error('Erro ao carregar mídias:', data.message);
                }
            } catch (error) {
                console.error('Erro na requisição:', error);
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

        // Função para abrir modal de ver publicação
        async function abrirModalVerPublicacao(postId) {
            currentModalPostId = postId;
            
            try {
                // Buscar dados da publicação
                const response = await fetch(`interacoes/get_publicacao_completa.php?id=${postId}`);
                const data = await response.json();
                
                if (data.success) {
                    // Preencher dados básicos
                    document.getElementById('modalUsername').textContent = data.user;
                    document.getElementById('modalData').textContent = data.data_formatada;
                    document.getElementById('modalFtPerfil').src = data.ft_perfil ? 'data:image/jpeg;base64,' + data.ft_perfil : 'default.png';
                    document.getElementById('modalPerfilLink').href = `../perfil/perfil.php?id=${data.idutilizador}`;
                    document.getElementById('idpublicacao').value = postId;
                    
                    // Preencher descrição
                    const modalDescricao = document.getElementById('modalDescricao');
                    if (data.descricao) {
                        modalDescricao.innerHTML = data.descricao;
                        modalDescricao.style.display = 'block';
                    } else {
                        modalDescricao.style.display = 'none';
                    }
                    
                    // Limpar mídias anteriores
                    document.getElementById('modalImagem').style.display = 'none';
                    document.getElementById('modalVideo').style.display = 'none';
                    document.getElementById('modalMediaContainer').style.display = 'none';
                    
                    // Configurar mídias
                    if (data.medias && data.medias.length > 0) {
                        modalMedias = data.medias;
                        modalCurrentIndex = 0;
                        
                        if (data.medias.length === 1) {
                            // Uma única mídia - usar o sistema antigo
                            const media = data.medias[0];
                            if (media.tipo === 'video') {
                                const modalVideo = document.getElementById('modalVideo');
                                modalVideo.querySelector('source').src = `publicacoes/${media.media}`;
                                modalVideo.load();
                                modalVideo.style.display = 'block';
                            } else {
                                const modalImagem = document.getElementById('modalImagem');
                                modalImagem.src = `publicacoes/${media.media}`;
                                modalImagem.style.display = 'block';
                            }
                        } else {
                            // Múltiplas mídias - usar o novo sistema com navegação
                            document.getElementById('modalMediaContainer').style.display = 'block';
                            mostrarModalMediaAtual();
                        }
                    }
                    
                    // Carregar comentários
                    carregarComentarios(postId);
                    
                    document.getElementById('modalVerPublicacao').style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                } else {
                    console.error('Erro ao carregar publicação:', data.message);
                }
            } catch (error) {
                console.error('Erro na requisição:', error);
            }
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
        document.addEventListener('keydown', function(e) {
            const imageModal = document.getElementById('imageModal');
            const publicacaoModal = document.getElementById('modalVerPublicacao');
            
            if (imageModal.style.display === 'flex') {
                switch(e.key) {
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
                switch(e.key) {
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
                
                // Limpar comentários existentes (exceto o template)
                const existingComments = comentariosContainer.querySelectorAll(':not(#comentarioTemplate)');
                existingComments.forEach(comment => comment.remove());
                
                if (comentarios.length > 0) {
                    comentarios.forEach(comentario => {
                        const comentarioElement = template.cloneNode(true);
                        comentarioElement.id = '';
                        comentarioElement.classList.remove('hidden');
                        
                        comentarioElement.querySelector('.comentario-ft-perfil').src = comentario.ft_perfil !== 'default.png' ? comentario.ft_perfil : 'default.png';
                        comentarioElement.querySelector('.comentario-username').textContent = comentario.user;
                        comentarioElement.querySelector('.comentario-data').textContent = comentario.data;
                        comentarioElement.querySelector('.comentario-conteudo').textContent = comentario.conteudo;
                        
                        comentariosContainer.appendChild(comentarioElement);
                    });
                } else {
                    const noComments = document.createElement('p');
                    noComments.style.textAlign = 'center';
                    noComments.style.color = '#666';
                    noComments.style.padding = '20px';
                    noComments.textContent = 'Nenhum comentário ainda. Seja o primeiro a comentar!';
                    comentariosContainer.appendChild(noComments);
                }
            } catch (error) {
                console.error('Erro ao carregar comentários:', error);
                const errorMsg = document.createElement('p');
                errorMsg.style.textAlign = 'center';
                errorMsg.style.color = '#e74c3c';
                errorMsg.textContent = 'Erro ao carregar comentários';
                document.getElementById('comentarios').appendChild(errorMsg);
            }
        }

        // Função para toggle like
        async function toggleLike(postId, button) {
            try {
                const formData = new FormData();
                formData.append('id_publicacao', postId);
                
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
                const formData = new FormData();
                formData.append('idpublicacao', postId);
                
                const response = await fetch('interacoes/guardar.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    if (data.guardado) {
                        button.classList.add('saved');
                    } else {
                        button.classList.remove('saved');
                    }
                } else {
                    console.error('Erro ao guardar:', data.message);
                }
            } catch (error) {
                console.error('Erro ao guardar publicação:', error);
            }
        }

        // Função para apagar publicação
        async function apagarPublicacao(postId) {
            if (!confirm('Tem certeza que deseja apagar esta publicação? Esta ação não pode ser desfeita.')) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('id_publicacao', postId);
                
                const response = await fetch('interacoes/apagar_publicacao.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const postElement = document.querySelector(`[data-post-id="${postId}"]`);
                    if (postElement) {
                        postElement.style.animation = 'slideUp 0.5s ease reverse';
                        setTimeout(() => postElement.remove(), 500);
                    }
                } else {
                    alert('Erro ao apagar publicação: ' + data.message);
                }
            } catch (error) {
                console.error('Erro ao apagar publicação:', error);
                alert('Erro ao apagar publicação');
            }
        }

        // Fechar modais ao clicar fora
        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalImagem();
            }
        });

        document.getElementById('modalVerPublicacao').addEventListener('click', function(e) {
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
    </script>
</body>

</html>