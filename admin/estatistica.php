<?php
session_start();
require "../ligabd.php";

// Verifica se o utilizador está autenticado e é admin
if (!isset($_SESSION["user"]) || $_SESSION["id_tipos_utilizador"] != 0) {
    header("Location: ../logout.php");
    exit();
}

// Buscar estatísticas
$sqlUsers = "SELECT COUNT(*) as total FROM utilizador";
$resultUsers = mysqli_query($con, $sqlUsers);
$totalUsers = mysqli_fetch_assoc($resultUsers)['total'];

$sqlPosts = "SELECT COUNT(DISTINCT p.idpublicacao) as total 
             FROM publicacao p
             LEFT JOIN publicacao_media pm ON p.idpublicacao = pm.idpublicacao";
$resultPosts = mysqli_query($con, $sqlPosts);
$totalPosts = mysqli_fetch_assoc($resultPosts)['total'];

$sqlComments = "SELECT COUNT(*) as total FROM comentario";
$resultComments = mysqli_query($con, $sqlComments);
$totalComments = mysqli_fetch_assoc($resultComments)['total'];

$sqlLikes = "SELECT COUNT(*) as total FROM likes";
$resultLikes = mysqli_query($con, $sqlLikes);
$totalLikes = mysqli_fetch_assoc($resultLikes)['total'];

// Estatísticas de media
$sqlMediaStats = "SELECT 
                 COUNT(*) as total_media,
                 SUM(CASE WHEN tipo = 'imagem' THEN 1 ELSE 0 END) as total_imagens,
                 SUM(CASE WHEN tipo = 'video' THEN 1 ELSE 0 END) as total_videos
                 FROM publicacao_media";
$resultMediaStats = mysqli_query($con, $sqlMediaStats);
$mediaStats = mysqli_fetch_assoc($resultMediaStats);

// Buscar todos os utilizadores para o modal
$sqlAllUsers = "SELECT idutilizador, nome, email, data_registo FROM utilizador ORDER BY data_registo DESC";
$resultAllUsers = mysqli_query($con, $sqlAllUsers);

// Buscar todas as publicações para o modal
$sqlAllPosts = "SELECT p.idpublicacao, p.descricao, p.data, u.nome as autor, 
                COUNT(pm.id) as total_media
                FROM publicacao p 
                JOIN utilizador u ON p.idutilizador = u.idutilizador
                LEFT JOIN publicacao_media pm ON p.idpublicacao = pm.idpublicacao
                GROUP BY p.idpublicacao
                ORDER BY p.data DESC";
$resultAllPosts = mysqli_query($con, $sqlAllPosts);

// Buscar últimos registos
$sqlRecentUsers = "SELECT nome, data_registo FROM utilizador ORDER BY data_registo DESC LIMIT 5";
$resultRecentUsers = mysqli_query($con, $sqlRecentUsers);

// Buscar atividade recente (CORRIGIDO)
// Buscar atividade recente (versão corrigida)
$sqlRecentActivity = "(SELECT 
                        u.nome, 
                        u.idutilizador,
                        'publicou' as acao,
                        p.data as data_acao,
                        p.idpublicacao,
                        p.descricao as post_descricao,
                        NULL as comentario_id,
                        NULL as like_id
                      FROM publicacao p
                      JOIN utilizador u ON p.idutilizador = u.idutilizador
                      ORDER BY p.data DESC
                      LIMIT 5)
                      
                      UNION ALL
                      
                      (SELECT 
                        u.nome, 
                        u.idutilizador,
                        'comentou' as acao,
                        c.data as data_acao,
                        c.idpublicacao,
                        NULL as post_descricao,
                        c.idcomentario as comentario_id,
                        NULL as like_id
                      FROM comentario c
                      JOIN utilizador u ON c.idutilizador = u.idutilizador
                      ORDER BY c.data DESC
                      LIMIT 5)
                      
                      UNION ALL
                      
                      (SELECT 
                        u.nome, 
                        u.idutilizador,
                        'curtiu' as acao,
                        l.data as data_acao,
                        l.idpublicacao,
                        NULL as post_descricao,
                        NULL as comentario_id,
                        l.id as like_id
                      FROM likes l
                      JOIN utilizador u ON l.idutilizador = u.idutilizador
                      ORDER BY l.data DESC
                      LIMIT 5)
                      
                      ORDER BY data_acao DESC
                      LIMIT 5";
$resultRecentActivity = mysqli_query($con, $sqlRecentActivity);

// Buscar atividade recente melhorada
$sqlRecentActivity = "
    (SELECT 
        u.nome, 
        u.idutilizador,
        'publicou' as acao,
        p.data as data_acao,
        p.idpublicacao,
        SUBSTRING(p.descricao, 1, 50) as descricao_resumo,
        'publicacao' as tipo_acao
    FROM publicacao p
    JOIN utilizador u ON p.idutilizador = u.idutilizador
    ORDER BY p.data DESC
    LIMIT 10)
    
    UNION ALL
    
    (SELECT 
        u.nome, 
        u.idutilizador,
        'comentou' as acao,
        c.data as data_acao,
        c.idpublicacao,
        CONCAT('\"', SUBSTRING(c.conteudo, 1, 30), '...\"') as descricao_resumo,
        'comentario' as tipo_acao
    FROM comentario c
    JOIN utilizador u ON c.idutilizador = u.idutilizador
    ORDER BY c.data DESC
    LIMIT 10)
    
    UNION ALL
    
    (SELECT 
        u.nome, 
        u.idutilizador,
        'curtiu' as acao,
        l.data as data_acao,
        l.idpublicacao,
        'uma publicação' as descricao_resumo,
        'like' as tipo_acao
    FROM likes l
    JOIN utilizador u ON l.idutilizador = u.idutilizador
    ORDER BY l.data DESC
    LIMIT 10)
    
    ORDER BY data_acao DESC
    LIMIT 15";
$resultRecentActivity = mysqli_query($con, $sqlRecentActivity);
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../imagens/favicon.ico" type="image/png">
    <title>Nexus | Estatísticas</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        h1 {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: #0e2b3b;
            margin-bottom: 20px;
        }

        h1 img {
            width: 70px;
            height: auto;
            margin-right: 10px;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            /* 4 colunas de tamanho igual */
            gap: 20px;
            width: 90%;
            margin: 20px auto;
        }

        .stat-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            cursor: pointer;
            transition: transform 0.3s;
            width: 100%;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card h3 {
            color: #0e2b3b;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .stat-card .value {
            font-size: 2rem;
            font-weight: bold;
            color: #0e2b3b;
        }

        .data-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            width: 90%;
            margin: 30px auto;
        }

        .data-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .data-card h2 {
            color: #0e2b3b;
            border-bottom: 2px solid #0e2b3b;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            position: relative;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #0e2b3b;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-weight: bold;
        }

        .activity-details {
            flex: 1;
        }

        .activity-time {
            color: #7f8c8d;
            font-size: 0.8rem;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            width: 80%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: black;
        }

        .modal-title {
            color: #0e2b3b;
            margin-bottom: 20px;
            font-size: 1.5rem;
            border-bottom: 2px solid #0e2b3b;
            padding-bottom: 10px;
        }

        .modal-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .modal-table th,
        .modal-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .modal-table th {
            background-color: #0e2b3b;
            color: white;
        }

        .modal-table tr:hover {
            background-color: #f5f5f5;
        }

        .view-btn {
            background-color: #0e2b3b;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: background-color 0.3s;
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
        }

        .view-btn:hover {
            background-color: #1a4b6b;
        }

        .post-content {
            margin-top: 20px;
        }

        .post-description {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 20px;
            white-space: pre-line;
        }

        .post-media {
            max-width: 100%;
            max-height: 400px;
            margin-bottom: 20px;
            border-radius: 8px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .post-video {
            width: 100%;
            max-height: 400px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .post-date {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-top: 10px;
            text-align: right;
        }

        .loading {
            text-align: center;
            padding: 20px;
            font-style: italic;
            color: #7f8c8d;
        }

        /* Estilos para múltiplas mídias */
        .media-container {
            display: grid;
            gap: 10px;
            margin-bottom: 15px;
        }

        .media-container.single {
            grid-template-columns: 1fr;
        }

        .media-container.double {
            grid-template-columns: 1fr 1fr;
        }

        .media-container.multiple {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        }

        .media-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
        }

        .media-item img,
        .media-item video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
        }

        .media-type-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 10px;
            text-transform: uppercase;
        }

        .recent-activity-container {
            position: relative;
            height: 100%;
        }

        .activity-scroll-box {
            max-height: 400px;
            overflow-y: auto;
            padding-right: 10px;
            scrollbar-width: thin;
            scrollbar-color: #0e2b3b #f1f1f1;
        }

        /* Estilização da barra de rolagem para WebKit */
        .activity-scroll-box::-webkit-scrollbar {
            width: 8px;
        }

        .activity-scroll-box::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .activity-scroll-box::-webkit-scrollbar-thumb {
            background-color: #0e2b3b;
            border-radius: 4px;
        }

        .activity-entry {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
            position: relative;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #0e2b3b;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-weight: bold;
        }

        .activity-info {
            flex: 1;
            position: relative;
        }

        .activity-header {
            margin-bottom: 4px;
        }

        .activity-preview {
            color: #666;
            font-size: 0.9em;
        }

        .activity-time {
            color: #7f8c8d;
            font-size: 0.8rem;
        }

        .view-activity-btn {
            background-color: #0e2b3b;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: background-color 0.3s;
            position: absolute;
            right: 0;
            top: 0;
        }

        .view-activity-btn:hover {
            background-color: #1a4b6b;
        }
    </style>
</head>

<body>
    <h1><img src="../imagens/logo.png" alt="Logo"> Estatísticas da Plataforma</h1>

    <div class="stats-container">
        <div class="stat-card" onclick="openModal('usersModal')">
            <h3>Total de Utilizadores</h3>
            <div class="value"><?= $totalUsers ?></div>
        </div>
        <div class="stat-card" onclick="openModal('postsModal')">
            <h3>Publicações</h3>
            <div class="value"><?= $totalPosts ?></div>
        </div>
        <div class="stat-card">
            <h3>Comentários</h3>
            <div class="value"><?= $totalComments ?></div>
        </div>
        <div class="stat-card">
            <h3>Likes</h3>
            <div class="value"><?= $totalLikes ?></div>
        </div>
    </div>

    <div class="data-section">
        <div class="data-card">
            <h2>Últimos Registos</h2>
            <?php while ($user = mysqli_fetch_assoc($resultRecentUsers)): ?>
                <div class="activity-item">
                    <div class="activity-icon"><?= substr($user['nome'], 0, 1) ?></div>
                    <div class="activity-details">
                        <strong><?= htmlspecialchars($user['nome']) ?></strong>
                        <div class="activity-time">Registado em: <?= $user['data_registo'] ?></div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="data-card recent-activity-container">
            <h2>Atividade Recente</h2>
            <div class="activity-scroll-box">
                <?php while ($activity = mysqli_fetch_assoc($resultRecentActivity)): ?>
                    <div class="activity-entry">
                        <div class="user-avatar"><?= substr($activity['nome'], 0, 1) ?></div>
                        <div class="activity-info">
                            <div class="activity-header">
                                <strong><?= htmlspecialchars($activity['nome']) ?></strong> <?= $activity['acao'] ?>
                                <?php if (!empty($activity['descricao_resumo']) && $activity['descricao_resumo'] !== 'uma publicação'): ?>
                                    <br><span
                                        class="activity-preview"><?= htmlspecialchars($activity['descricao_resumo']) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($activity['idpublicacao']): ?>
                                <button class="view-activity-btn"
                                    onclick="event.stopPropagation(); loadPostDetails(<?= $activity['idpublicacao'] ?>, '<?= htmlspecialchars(addslashes($activity['nome'])) ?>')">
                                    Ver
                                </button>
                            <?php endif; ?>
                            <div class="activity-time"><?= $activity['data_acao'] ?></div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <!-- Modal para Utilizadores -->
    <div id="usersModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('usersModal')">&times;</span>
            <h2 class="modal-title">Todos os Utilizadores</h2>
            <table class="modal-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Data de Registo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = mysqli_fetch_assoc($resultAllUsers)): ?>
                        <tr>
                            <td><?= $user['idutilizador'] ?></td>
                            <td><?= htmlspecialchars($user['nome']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= $user['data_registo'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para Publicações -->
    <div id="postsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('postsModal')">&times;</span>
            <h2 class="modal-title">Todas as Publicações</h2>
            <table class="modal-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Descrição</th>
                        <th>Autor</th>
                        <th>Data</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($post = mysqli_fetch_assoc($resultAllPosts)): ?>
                        <tr>
                            <td><?= $post['idpublicacao'] ?></td>
                            <td><?= htmlspecialchars(substr($post['descricao'], 0, 50) ?? "") ?>...</td>
                            <td><?= htmlspecialchars($post['autor']) ?></td>
                            <td><?= $post['data'] ?></td>
                            <td><button class="view-btn"
                                    onclick="loadPostDetails(<?= $post['idpublicacao'] ?>, '<?= htmlspecialchars(addslashes($post['descricao'])) ?>')">Ver</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para Visualizar Publicação -->
    <div id="postModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('postModal')">&times;</span>
            <h2 class="modal-title" id="postModalTitle">Detalhes da Publicação</h2>
            <div id="postModalContent">
                <div class="loading">A carregar publicação...</div>
                <div class="post-content" style="display:none;">
                    <p class="post-description" id="postDescription"></p>
                    <div id="postMediaContainer" class="media-container"></div>
                    <p class="post-date" id="postDate"></p>
                </div>
            </div>
        </div>
    </div>

    <div style="height:100px;"></div>

    <footer>
        <div class="footer-container" style="text-align: center;">
            <a href="utilizadores.php"><button class="footerbutton">Utilizadores</button></a>
            <a href="publicacoes.php"><button class="footerbutton">Publicações</button></a>
            <a href="estatistica.php"><button class="footerbutton">Estatística</button></a>
            <a href="admin_choice.php"><button class="footerbutton">Sair</button></a>
        </div>
    </footer>

    <script>
        // Funções para abrir e fechar modais
        function openModal(modalId) {
            document.getElementById(modalId).style.display = "block";
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = "none";
        }

        // Fechar modal quando clicar fora do conteúdo
        window.onclick = function (event) {
            if (event.target.classList.contains("modal")) {
                event.target.style.display = "none";
            }
        }

        function loadPostDetails(postId, postTitle, event) {
            // Prevenir propagação do evento se existir
            if (event) {
                event.stopPropagation();
            }

            // Abrir o modal
            openModal('postModal');
            document.getElementById('postModalTitle').textContent = 'Publicação #' + postId + (postTitle ? ' - ' + postTitle : '');

            // Mostrar loading e esconder conteúdo
            document.querySelector('#postModalContent .loading').style.display = 'block';
            document.querySelector('#postModalContent .post-content').style.display = 'none';

            // Fazer chamada AJAX para buscar os detalhes da publicação
            $.ajax({
                url: 'get_publicacao.php',
                type: 'GET',
                data: { id: postId },
                dataType: 'json',
                success: function (response) {
                    console.log('Resposta da publicação:', response); // Debug
                    if (response.success) {
                        // Preencher os dados da publicação
                        document.getElementById('postDescription').textContent = response.descricao || 'Sem descrição';
                        document.getElementById('postDate').textContent = 'Publicado em: ' + response.data;

                        // Limpar e adicionar media
                        const mediaContainer = document.getElementById('postMediaContainer');
                        mediaContainer.innerHTML = '';

                        if (response.medias && response.medias.length > 0) {
                            // Definir classe do container baseado no número de mídias
                            if (response.medias.length === 1) {
                                mediaContainer.className = 'media-container single';
                            } else if (response.medias.length === 2) {
                                mediaContainer.className = 'media-container double';
                            } else {
                                mediaContainer.className = 'media-container multiple';
                            }

                            response.medias.forEach((media, index) => {
                                const mediaItem = document.createElement('div');
                                mediaItem.className = 'media-item';

                                let mediaHtml = '';
                                if (media.tipo === 'imagem') {
                                    mediaHtml = `
                                <img src="../main/publicacoes/${media.media}" alt="Imagem da publicação" class="post-media">
                                <div class="media-type-badge">IMG</div>
                            `;
                                } else if (media.tipo === 'video') {
                                    mediaHtml = `
                                <video controls class="post-video">
                                    <source src="../main/publicacoes/${media.media}" type="video/mp4">
                                    Seu navegador não suporta o elemento de vídeo.
                                </video>
                                <div class="media-type-badge">VID</div>
                            `;
                                }

                                mediaItem.innerHTML = mediaHtml;
                                mediaContainer.appendChild(mediaItem);
                            });
                        } else {
                            mediaContainer.innerHTML = '<p style="text-align: center; color: #666;">Nenhuma mídia encontrada</p>';
                        }

                        // Esconder loading e mostrar conteúdo
                        document.querySelector('#postModalContent .loading').style.display = 'none';
                        document.querySelector('#postModalContent .post-content').style.display = 'block';
                    } else {
                        console.error('Erro na resposta:', response);
                        document.querySelector('#postModalContent .loading').textContent = 'Erro ao carregar a publicação: ' + (response.message || 'Publicação não encontrada');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Erro AJAX:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                    document.querySelector('#postModalContent .loading').textContent = 'Erro ao carregar a publicação. Verifique a conexão.';
                }
            });
        }
        // Prevenir fechamento acidental do modal
        document.addEventListener('DOMContentLoaded', function () {
            // Adicionar event listeners para os botões de fechar
            document.querySelectorAll('.close').forEach(function (closeBtn) {
                closeBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const modal = this.closest('.modal');
                    if (modal) {
                        modal.style.display = 'none';
                    }
                });
            });

            // Prevenir fechamento quando clicar no conteúdo do modal
            document.querySelectorAll('.modal-content').forEach(function (content) {
                content.addEventListener('click', function (e) {
                    e.stopPropagation();
                });
            });
        });
    </script>
</body>

</html>