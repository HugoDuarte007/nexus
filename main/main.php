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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../imagens/favicon.ico" type="image/png">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" type="text/css" href="../style.css">
    <title>Nexus | Página Inicial</title>
    <style>
        /* Estilos gerais */
        .posts {
            justify-content: center;
            display: flex;
            align-items: center;
            flex-direction: column;
            width: 100%;
            margin-top: 20px;
        }

        .post {
            width: 100%;
            max-width: 600px;
            background: white;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border: 1px solid;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .post:hover {
            transform: translateY(-5px);
        }

        .post-header {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .profile-picture {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .username {
            font-weight: bold;
        }

        .post-time {
            color: gray;
            font-size: 0.9em;
            margin-left: auto;
        }

        .post-content p {
            text-align: left;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: pre-wrap;
            padding-left: 50px;
            padding-right: 25px;
        }

        .post-image {
            float: left;
            width: 100%;
            max-width: 400px;
            object-fit: cover;
            border-radius: 10px;
            margin-top: 10px;
            margin-left: 51px;
            margin-right: 15px;
            margin-bottom: 15px;
        }

        /* Modal de publicação */
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
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            width: 600px;
            max-width: 90%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border: 1px solid #0e2b3b;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h2 {
            margin: 0;
        }

        .modal-header .close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
        }

        /* Modal de visualização de publicação */
        .modal-publicacao {
            width: 700px;
            max-width: 95%;
        }

        .modal-publicacao .post-content {
            margin-bottom: 20px;
        }

        .modal-publicacao .post-image {
            max-width: 100%;
            margin-left: 0;
        }

        .comentarios-container {
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .comentario-form {
            display: flex;
            margin-top: 20px;
        }

        .comentario-input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 20px;
            margin-right: 10px;
        }

        .comentario-submit {
            padding: 10px 20px;
            background-color: #0e2b3b;
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
        }

        .comentario-submit:hover {
            background-color: #1a3d4d;
        }

        .comentario {
            display: flex;
            margin-bottom: 15px;
            align-items: flex-start;
        }

        .comentario-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .comentario-conteudo {
            flex: 1;
        }

        .comentario-autor {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .comentario-texto {
            word-wrap: break-word;
        }

        /* Notificação de Aniversário */
        .notificacao {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #4CAF50;
            color: white;
            padding: 15px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 15px;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }

        .notificacao.mostrar {
            opacity: 1;
        }

        .notificacao .fechar {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            font-weight: bold;
        }

        .post-actions {
            display: flex;
            width: 100%;
            margin-top: 15px;
            gap: 10px;
        }

        .action-button {
            flex: 1;
            background-color: white;
            border-radius: 10px;
            padding: 10px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .action-button:hover {
            background-color: #e6f0f5;
        }

        .action-button svg {
            width: 24px;
            height: 24px;
            fill: #0e2b3b;
        }

        .rotated-icon {
            transform: rotate(-40deg);
            transform-origin: center;
            transition: transform 0.3s ease;
        }

        .message-button:hover .rotated-icon {
            transform: rotate(0deg);
        }

        .guardar-button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
            margin-left: 49%;
            top: 10px;
        }

        .guardar-button:hover {
            background-color: #e6f0f5;
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

    <header>
        <nav class="navbar">
            <a href="../main/main.php" style="color:white;text-decoration: none;">
                <h1 class="logo">Nexus</h1>
            </a>
            <div class="search-container">
                <input type="text" id="searchInput" class="search-bar" placeholder="Pesquisar utilizadores..."
                    onkeyup="searchUsers()">
            </div>
            <button class="styled-button message-button" title="Mensagens"
                onclick="window.location.href='../mensagens/mensagens.php'">
                <svg xmlns="http://www.w3.org/2000/svg" height="28" viewBox="0 0 24 24" width="28" fill="white"
                    class="rotated-icon">
                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" />
                </svg>
            </button>
            <button class="styled-button" title="Publicar" style="font-size:33px;" onclick="abrirModal()">+</button>
            <button class="styled-button" title="Perfil" onclick="window.location.href='../perfil/perfil.php'">
                <div class="user-info">
                    <span><?php echo htmlspecialchars($utilizador); ?></span>
                    <img src="<?php echo $foto_base64; ?>" alt="Foto de Perfil" class="profile-picture">
                </div>
            </button>
        </nav>
    </header>

    <!-- Modal para criar publicação -->
    <div id="modalPublicacao" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Criar Publicação</h2>
                <button class="close" onclick="fecharModal()">✖</button>
            </div>
            <div class="modal-body">
                <form action="publicar.php" method="post" id="publicacaoForm" enctype="multipart/form-data">
                    <textarea id="descricao" name="descricao" placeholder="Em que está a pensar?" required></textarea>

                    <!-- Botão para escolher imagem -->
                    <label for="imagemInput" title="Adicionar imagem"
                        style="cursor: pointer; display: inline-block; margin-top: 10px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="#0e2b3b"
                            viewBox="0 0 24 24">
                            <path d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 
                     2 0 0 0 2 2h14a2 2 0 0 0 2-2ZM8.5 
                     13.5 11 17l3.5-4.5 4.5 6H5l3.5-4.5Zm.5-3A2 
                     2 0 1 0 7 8a2 2 0 0 0 2 2Z" />
                        </svg>
                    </label>
                    <input type="file" id="imagemInput" name="imagem" accept="image/*" style="display:none"
                        onchange="preverImagem()">

                    <!-- Pré-visualização -->
                    <div id="previewContainer" style="margin-top: 10px; display: none;">
                        <img id="previewImagem" src="#" alt="Pré-visualização da imagem"
                            style="max-width: 100%; max-height: 300px; border-radius: 10px;" />
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="botao">Publicar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para visualizar publicação -->
    <div id="modalVerPublicacao" class="modal">
        <div class="modal-content modal-publicacao">
            <div class="modal-header">
                <h2>Publicação</h2>
                <button class="close" onclick="fecharModalVerPublicacao()">✖</button>
            </div>
            <div class="modal-body" id="conteudoPublicacao">
                <!-- Conteúdo da publicação será inserido aqui via JavaScript -->
            </div>
        </div>
    </div>

    <div class="posts">
        <?php while ($pub = mysqli_fetch_assoc($publicacoes)): ?>
            <div class="post" onclick="abrirModalPublicacao(<?= $pub['idpublicacao'] ?>)">
                <div class="post-header">
                    <img src="<?= $pub['ft_perfil'] ? 'data:image/jpeg;base64,' . base64_encode($pub['ft_perfil']) : 'default.png'; ?>"
                        alt="Foto de Perfil" class="profile-picture">
                    <span class="username"><?= htmlspecialchars($pub['user']); ?></span>
                    <span class="post-time"
                        style="margin-left: 10px;"><?= date("d/m/Y H:i", strtotime($pub['data'])); ?></span>

                    <button class="guardar-button" title="Guardar" style="width: auto;" onclick="event.stopPropagation()">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                            <path fill="#0e2b3b" d="M17 3H7a2 2 0 0 0-2 2v16l7-3 7 3V5a2 2 0 0 0-2-2z" />
                        </svg>
                    </button>
                </div>

                <div class="post-content">
                    <p class="post-descricao"><?= nl2br(htmlspecialchars($pub['descricao'])); ?></p>
                    <?php if (!empty($pub['media'])): ?>
                        <img src="publicacoes/<?= htmlspecialchars($pub['media']); ?>" class="post-image"
                            alt="Imagem da publicação">
                    <?php endif; ?>
                </div>

                <div class="post-actions">
                    <button class="action-button" title="Comentar" onclick="event.stopPropagation()">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path fill="#0e2b3b"
                                d="M20 2H4a2 2 0 0 0-2 2v15.17L5.17 16H20a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z" />
                        </svg>
                        <span style="margin-left: 5px;">3</span>
                    </button>
                    <button class="action-button" title="Republicar" onclick="event.stopPropagation()">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path fill="#0e2b3b"
                                d="M23 7l-5-5v3H6c-1.1 0-2 .9-2 2v5h2V7h12v3l5-5zM1 17l5 5v-3h12c1.1 0 2-.9 2-2v-5h-2v5H6v-3l-5 5z" />
                        </svg>
                        <span style="margin-left: 5px;">2</span>
                    </button>
                    <button class="action-button" title="Gostar" onclick="event.stopPropagation()">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path fill="#0e2b3b" d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 
        2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41 0.81 
        4.5 2.09C13.09 3.81 14.76 3 16.5 
        3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 
        6.86-8.55 11.54L12 21.35z" />
                        </svg>
                        <span style="margin-left: 5px;">5</span>
                    </button>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <script>
        // Função para abrir o modal de visualização da publicação
        function abrirModalPublicacao(idPublicacao) {
            // Aqui você pode fazer uma requisição AJAX para buscar os detalhes completos da publicação
            // ou usar os dados já carregados na página (simplificado neste exemplo)
            
            // Encontra a publicação clicada
            const publicacao = document.querySelector(`.post[onclick="abrirModalPublicacao(${idPublicacao})"]`);
            
            // Clona o conteúdo da publicação para o modal
            const conteudo = publicacao.cloneNode(true);
            
            // Remove o evento de clique para evitar recursão
            conteudo.removeAttribute('onclick');
            
            // Adiciona a seção de comentários
            const comentariosHTML = `
                <div class="comentarios-container">
                    <h3>Comentários</h3>
                    
                    <div class="comentario">
                        <img src="<?= $foto_base64 ?>" alt="Avatar" class="comentario-avatar">
                        <div class="comentario-conteudo">
                            <div class="comentario-autor"><?= htmlspecialchars($utilizador) ?></div>
                            <div class="comentario-texto">Esta publicação é incrível!</div>
                        </div>
                    </div>
                    
                    <div class="comentario">
                        <img src="default.png" alt="Avatar" class="comentario-avatar">
                        <div class="comentario-conteudo">
                            <div class="comentario-autor">OutroUtilizador</div>
                            <div class="comentario-texto">Concordo plenamente!</div>
                        </div>
                    </div>
                    
                    <form class="comentario-form">
                        <input type="text" class="comentario-input" placeholder="Adicione um comentário...">
                        <button type="submit" class="comentario-submit">Comentar</button>
                    </form>
                </div>
            `;
            
            conteudo.innerHTML += comentariosHTML;
            
            // Adiciona o conteúdo ao modal
            document.getElementById('conteudoPublicacao').innerHTML = '';
            document.getElementById('conteudoPublicacao').appendChild(conteudo);
            
            // Exibe o modal
            document.getElementById("modalVerPublicacao").style.display = "flex";
        }

        function fecharModalVerPublicacao() {
            document.getElementById("modalVerPublicacao").style.display = "none";
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
                    console.log(data);
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

        document.addEventListener("DOMContentLoaded", function () {
            setTimeout(() => {
                let notificacao = document.getElementById("notificacao");
                if (notificacao) {
                    notificacao.classList.add("mostrar");
                }
            }, 1500);
        });

        function abrirModal() {
            document.getElementById("modalPublicacao").style.display = "flex";
        }

        function fecharModal() {
            document.getElementById("modalPublicacao").style.display = "none";
        }

        function fecharNotificacao() {
            let notificacao = document.getElementById("notificacao");
            if (notificacao) {
                notificacao.style.display = "none";
            }
        }

        function searchUsers() {
            let input = document.getElementById("searchInput").value.toLowerCase().trim();
            let users = document.querySelectorAll(".username");
            let post_descricaos = document.querySelectorAll(".post-descricao");

            users.forEach(user => {
                let post = user.closest(".post");
                let descricao = post.querySelector(".post-descricao");

                post.style.display = "none";

                if (descricao.textContent.toLowerCase().includes(input)) {
                    post.style.display = "block";
                }
            });

            post_descricaos.forEach(descricao => {
                let post = descricao.closest(".post");

                if (descricao.textContent.toLowerCase().includes(input)) {
                    post.style.display = "block";
                }
            })
        }
    </script>
</body>

</html> 