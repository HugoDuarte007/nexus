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

// Buscar publicações da base de dados
$sql = "SELECT p.*, u.user, u.ft_perfil 
        FROM publicacao p
        JOIN utilizador u ON p.idutilizador = u.idutilizador
        ORDER BY p.data DESC";  // Ordenar pela data de publicação mais recente

$publicacoes = mysqli_query($con, $sql);

if (!$publicacoes) {
    die("Erro na query das publicações: " . mysqli_error($con));
} elseif (mysqli_num_rows($publicacoes) == 0) {
    echo "<p style='text-align:center;'>Sem publicações encontradas.</p>";
}

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
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>Nexus | Página Inicial</title>
    <style>
        /* Estilos gerais */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #1f2937;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        /* Container de posts */
        .posts {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            margin-top: 20px;
            padding-bottom: 60px;
        }

        /* Post individual */
        .post {
            width: 100%;
            max-width: 600px;
            background: white;
            padding: 16px;
            margin-bottom: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .post:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        /* Cabeçalho do post */
        .post-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .profile-picture {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e5e7eb;
        }

        .username {
            font-weight: 600;
            color: #1f2937;
        }

        .post-time {
            color: #6b7280;
            font-size: 0.8em;
            margin-left: auto;
        }

        /* Conteúdo do post */
        .post-content {
            margin-left: 52px;
        }

        .post-content p {
            text-align: left;
            word-wrap: break-word;
            white-space: pre-wrap;
            color: #374151;
            margin-bottom: 12px;
            font-size: 0.95rem;
        }

        .post-image {
            width: 100%;
            max-height: 500px;
            object-fit: contain;
            border-radius: 8px;
            margin-top: 10px;
            margin-bottom: 10px;
            background-color: #f3f4f6;
        }

        /* Ações do post */
        .post-actions {
            display: flex;
            width: 100%;
            margin-top: 12px;
            gap: 8px;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
        }

        .action-button {
            flex: 1;
            background-color: white;
            border-radius: 8px;
            padding: 8px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            color: #4b5563;
            font-weight: 500;
        }

        .action-button:hover {
            background-color: #f3f4f6;
            color: #1f2937;
        }

        .action-button svg {
            width: 20px;
            height: 20px;
            margin-right: 6px;
        }

        .guardar-button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px;
            border-radius: 8px;
            transition: all 0.2s ease;
            margin-left: auto;
        }

        .guardar-button:hover {
            background-color: #f3f4f6;
        }

        .guardar-button svg {
            width: 20px;
            height: 20px;
        }

        /* Modal geral */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 600px;
            max-width: 95%;
            max-height: 90vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Modal de visualização de publicação */
        .modal-publicacao {
            width: 700px;
        }

        .modal-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: #0e2b3b;
        }

        .close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6b7280;
            transition: color 0.2s;
        }

        .close:hover {
            color: #1f2937;
        }

        .modal-body {
            padding: 20px;
            overflow-y: auto;
            flex: 1;
        }

        /* Formulário de comentário */
        .comment-form {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-bottom: 20px;
        }

        .comment-input {
            flex: 1;
            padding: 10px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 24px;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .comment-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .comment-submit {
            padding: 10px 20px;
            background-color: #0e2b3b;
            color: white;
            border: none;
            border-radius: 24px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.2s;
        }

        .comment-submit:hover {
            background-color: #1a3d4d;
        }

        /* Lista de comentários */
        .comments-container {
            margin-top: 20px;
        }

        .comments-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 16px;
        }

        .comment {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
        }

        .comment-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
        }

        .comment-content {
            flex: 1;
        }

        .comment-header {
            display: flex;
            align-items: center;
            margin-bottom: 4px;
        }

        .comment-author {
            font-weight: 600;
            font-size: 0.9rem;
            color: #1f2937;
            margin-right: 8px;
        }

        .comment-time {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .comment-text {
            font-size: 0.9rem;
            color: #374151;
            line-height: 1.5;
        }

        .comment-actions {
            display: flex;
            gap: 12px;
            margin-top: 6px;
            font-size: 0.8rem;
        }

        .comment-action {
            color: #6b7280;
            cursor: pointer;
            transition: color 0.2s;
        }

        .comment-action:hover {
            color: #1f2937;
            text-decoration: underline;
        }

        /* Notificação de aniversário */
        .notificacao {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #4CAF50;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 12px;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }

        .notificacao.mostrar {
            opacity: 1;
        }

        .notificacao .fechar {
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            margin-left: 8px;
        }

        /* Ações da publicação no modal */
        .post-actions-modal {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            margin: 16px 0;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
        }

        .post-action {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #6b7280;
            cursor: pointer;
            transition: color 0.2s;
            background: none;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
        }

        .post-action:hover {
            color: #1f2937;
            background-color: #f3f4f6;
        }

        .post-action svg {
            width: 18px;
            height: 18px;
        }

        /* Responsividade */
        @media (max-width: 640px) {
            .post {
                border-radius: 0;
                border-left: none;
                border-right: none;
            }

            .modal-content {
                max-height: 100vh;
                height: 100vh;
                max-width: 100%;
                border-radius: 0;
            }

            .post-content {
                margin-left: 0;
                padding-left: 52px;
            }
        }

        /* Animações */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .post {
            animation: fadeIn 0.3s ease-out forwards;
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


    <!-- Modal para visualizar publicação -->
    <!-- Modal para visualizar publicação -->
    <div id="modalVerPublicacao" class="modal">
        <div class="modal-content modal-publicacao" style="width: 700px; max-height: 90vh;">
            <div class="modal-header" style="border-bottom: 1px solid #e5e7eb; padding-bottom: 1rem;">
                <h2 style="font-size: 1.25rem; font-weight: 600; color: #0e2b3b;">Publicação</h2>
                <button class="close" onclick="fecharPublicacao()"
                    style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6b7280;">&times;</button>
            </div>

            <div class="modal-body" id="conteudoPublicacao" style="overflow-y: auto; max-height: calc(90vh - 150px);">
                <!-- Cabeçalho da publicação -->
                <div class="flex items-center gap-3 mb-4">
                    <img id="ft_perfil" alt="Foto de Perfil" class="profile-picture" style="width: 48px; height: 48px;">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span id="username" class="username" style="font-weight: 600; color: #0e2b3b;"></span>
                            <span id="data" class="post-time" style="color: #6b7280; font-size: 0.875rem;"></span>
                        </div>
                    </div>
                </div>

                <!-- Conteúdo da publicação -->
                <div class="post-content mb-4" style="margin-left: 60px;">
                    <p id="descricao" class="text-gray-800 mb-3" style="white-space: pre-wrap; word-break: break-word;">
                    </p>
                    <img id="imagem" src="" class="rounded-lg w-full max-h-96 object-contain mx-auto"
                        style="display: none;" alt="Imagem da publicação">
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

                <!-- Lista de comentários -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Comentários</h3>
                    <div id="comentarios" class="space-y-4">
                        <!-- Template de comentário (hidden) -->
                        <div id="comentario" class="hidden">
                            <div class="flex gap-3">
                                <img id="ft_perfil" alt="Foto de Perfil" class="w-10 h-10 rounded-full object-cover">
                                <div class="flex-1">
                                    <div class="bg-gray-100 rounded-lg p-3">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span id="username" class="font-semibold text-sm text-gray-800"></span>
                                            <span id="data" class="text-xs text-gray-500"></span>
                                        </div>
                                        <p id="descricao" class="text-gray-800 text-sm"></p>
                                    </div>
                                    <div class="flex gap-4 mt-1 ml-3">
                                        <button class="text-xs text-gray-500 hover:text-gray-700">Gostar</button>
                                        <button class="text-xs text-gray-500 hover:text-gray-700">Responder</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="posts">
        <?php while ($pub = mysqli_fetch_assoc($publicacoes)): ?>

            <?php

            $sql = "SELECT * FROM comentario WHERE idpublicacao = " . $pub['idpublicacao'];
            $comentarios = mysqli_fetch_all(mysqli_query($con, $sql), MYSQLI_ASSOC);

            $sql1 = "SELECT * FROM likes WHERE idpublicacao = " . $pub['idpublicacao'];
            $like = mysqli_fetch_all(mysqli_query($con, $sql1), MYSQLI_ASSOC);

            ?>

            <div class="post" id="post_<?= $pub['idpublicacao'] ?>">
                <div class="post-header">
                    <img id="ft_perfil"
                        src="<?= $pub['ft_perfil'] ? 'data:image/jpeg;base64,' . base64_encode($pub['ft_perfil']) : 'default.png'; ?>"
                        alt="Foto de Perfil" class="profile-picture">
                    <span id="username" class="username"><?= htmlspecialchars($pub['user']); ?></span>
                    <p id="data" class="post-time" style="max-height: 20px;">
                        <?= date("d/m/Y H:i", strtotime($pub['data'])); ?>
                    </p>


                </div>

                <div class="post-content">
                    <p class="post-descricao" id="descricao"><?= nl2br(htmlspecialchars($pub['descricao'])); ?></p>
                    <img id="imagem" src="publicacoes/<?= htmlspecialchars($pub['media']); ?>" class="post-image"
                        alt="Imagem da publicação" style="display: <?= empty($pub['media']) ? 'none' : 'block' ?>">
                </div>

                <div class="post-actions">
                    <button class="action-button" title="Comentar" onclick="abrirPublicacao(<?= $pub['idpublicacao'] ?>)">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path fill="#0e2b3b"
                                d="M20 2H4a2 2 0 0 0-2 2v15.17L5.17 16H20a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z" />
                        </svg>
                        <span style="margin-left: 5px;"><?= count($comentarios) ?></span>
                    </button>
                    <button class="action-button" title="Republicar">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path fill="#0e2b3b"
                                d="M23 7l-5-5v3H6c-1.1 0-2 .9-2 2v5h2V7h12v3l5-5zM1 17l5 5v-3h12c1.1 0 2-.9 2-2v-5h-2v5H6v-3l-5 5z" />
                        </svg>
                        <span style="margin-left: 5px;">2</span>
                    </button>
                    <button class="action-button like-button" title="Gostar" data-post-id="<?= $pub['idpublicacao'] ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path fill="<?= in_array($utilizador, array_column($like, 'user')) ? '#ff0000' : '#0e2b3b' ?>"
                                d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41 0.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                        </svg>
                        <span class="like-count" style="margin-left: 5px;"><?= count($like) ?></span>
                    </button>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <script>
        const modalVerPublicacao = document.getElementById('modalVerPublicacao');
        const modalComentarios = modalVerPublicacao.querySelector('#comentarios');
        const ComentarioTemplate = modalComentarios.querySelector('#comentario');

        function abrirPublicacao(pubid) {
            const publicacao = document.querySelector('#post_' + pubid);

            // Buscar elementos da publicação
            const ft_perfil = publicacao.querySelector('#ft_perfil').src;
            const username = publicacao.querySelector('#username').innerText;
            const data = publicacao.querySelector('#data').innerText;
            const descricao = publicacao.querySelector('#descricao').innerText;
            const imagem = publicacao.querySelector('#imagem').src;
            const mostrarImagem = publicacao.querySelector('#imagem').style.display !== "none";

            // Preencher dados no modal
            document.getElementById("ft_perfil").src = ft_perfil;
            document.getElementById("username").innerText = username;
            document.getElementById("data").innerText = data;
            document.getElementById("descricao").innerText = descricao;

            const imagemModal = document.getElementById("imagem");
            if (mostrarImagem) {
                imagemModal.src = imagem;
                imagemModal.style.display = "block";
            } else {
                imagemModal.style.display = "none";
            }

            // Definir o id da publicação no formulário
            document.getElementById("idpublicacao").value = pubid;

            // Limpa e carrega comentários
            carregarComentarios(pubid);

            modalVerPublicacao.style.display = 'flex';
        }

        function carregarComentario(data) {
            var comentario = ComentarioTemplate.cloneNode(true);
            modalComentarios.appendChild(comentario);

            // console.log(data);

            comentario.classList.remove('hidden');
            comentario.querySelector('#ft_perfil').src = data["ft_perfil"];
            comentario.querySelector('#username').innerHTML = data["user"];
            comentario.querySelector('#data').innerHTML = data["data"];
            comentario.querySelector('#descricao').innerHTML = data['conteudo'];

            return comentario;
        }

        function clearComentarios() {
            var Comentarios = Array.from(modalComentarios.children);

            Comentarios.forEach(comentario => {
                if (comentario.classList.contains('hidden')) {
                    return;
                }

                modalComentarios.removeChild(comentario);
            });
        }

        function carregarComentarios(pubid) {

            clearComentarios();

            fetch(`interacoes/obter_comentarios.php?idpublicacao=${pubid}`)
                .then(response => response.text())
                .then(data => {
                    JSON.parse(data).forEach(comentario => {
                        carregarComentario(comentario);
                    });
                })
                .catch(error => {
                    console.error('Erro ao carregar comentários:', error);
                    modalComentarios.innerHTML = '<p style="color:red;">Erro ao carregar comentários.</p>';
                });
        }

        function fecharPublicacao() {
            modalVerPublicacao.style.display = 'none';
        }
    </script>

    <script>
        function abrirModal() {
            document.getElementById("modalPublicacao").style.display = "flex";
        }

        function fecharModal() {
            document.getElementById("modalPublicacao").style.display = "none";
        }

        function darLike(idPublicacao) {
            fetch('../interacoes/like.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'id_publicacao=' + encodeURIComponent(idPublicacao)
            })
                .then(response => response.text())
                .then(data => {
                    // console.log(data);
                })
                .catch(error => {
                    console.error('Erro ao dar like:', error);
                });
        }

        function preverImagem() {
            const input = document.getElementById('imagemInput');
            const preview = document.getElementById('previewImagem');
            const container = document.getElementById('previewContainer');

            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function (e) {
                    preview.src = e.target.result;
                    container.style.display = "block";
                }

                reader.readAsDataURL(input.files[0]);
            } else {
                container.style.display = "none";
                preview.src = "#";
            }
        }
        // Função para enviar a publicação via AJAX
        function enviarPublicacao(e) {
            e.preventDefault();

            const form = document.getElementById('publicacaoForm');
            const formData = new FormData(form);

            fetch('interacoes/publicar.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        fecharModal();
                        // Atualizar a página ou adicionar a nova publicação dinamicamente
                        window.location.reload();
                    } else {
                        alert(data.message || 'Erro ao publicar');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro na comunicação com o servidor');
                });
        }

        // Adicionar evento ao formulário
        document.getElementById('publicacaoForm').addEventListener('submit', enviarPublicacao);
        document.addEventListener("DOMContentLoaded", function () {
            setTimeout(() => {
                let notificacao = document.getElementById("notificacao");
                if (notificacao) {
                    notificacao.classList.add("mostrar");
                }
            }, 1500);
        });

        function fecharNotificacao() {
            let notificacao = document.getElementById("notificacao");
            if (notificacao) {
                notificacao.style.display = "none";
            }
        }
        ocument.querySelectorAll('.guardar-button').forEach(button => {
            button.addEventListener('click', function () {
                // Obter o ID da publicação do elemento pai
                const postElement = this.closest('.post');
                const idpublicacao = postElement.id.split('_')[1];

                fetch('guardar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `idpublicacao=${idpublicacao}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const svgPath = this.querySelector('path');
                            if (data.guardado) {
                                svgPath.setAttribute('fill', '#ff0000');
                                this.setAttribute('title', 'Remover dos guardados');
                            } else {
                                // Estilo para quando não está guardado
                                svgPath.setAttribute('fill', '#0e2b3b');
                                this.setAttribute('title', 'Guardar');
                            }
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => console.error('Erro:', error));
            });
        });
    </script>
</body>

</html>