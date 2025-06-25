<?php
session_start();
require "../ligabd.php";

// Verifica se o utilizador está autenticado e é admin
if (!isset($_SESSION["user"]) || $_SESSION["id_tipos_utilizador"] != 0) {
    header("Location: ../logout.php");
    exit();
}

// Configurações de paginação
$porPagina = 5;
$paginaAtual = isset($_GET['pagina']) ? max(1, (int) $_GET['pagina']) : 1;
$offset = ($paginaAtual - 1) * $porPagina;

// Filtros e ordenação
$filtroTipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$ordenacao = isset($_GET['ordenar']) ? $_GET['ordenar'] : 'p.data DESC';

// Construir query base
$sqlBase = "SELECT p.*, u.nome, 
                   COUNT(pm.id) as total_media
            FROM publicacao p
            JOIN utilizador u ON p.idutilizador = u.idutilizador
            LEFT JOIN publicacao_media pm ON p.idpublicacao = pm.idpublicacao";

// Adicionar filtros
$where = [];
if ($filtroTipo) {
    $where[] = "pm.tipo = '$filtroTipo'";
}

$sqlWhere = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$sqlGroup = " GROUP BY p.idpublicacao";

// Total de publicações
$sqlTotal = $sqlBase . $sqlWhere . $sqlGroup;
$resultTotal = mysqli_query($con, "SELECT COUNT(*) as total FROM ($sqlTotal) as subquery");
$totalPublicacoes = mysqli_fetch_assoc($resultTotal)['total'];
$totalPaginas = ceil($totalPublicacoes / $porPagina);

// Query principal com ordenação e paginação
$sql = $sqlBase . $sqlWhere . $sqlGroup . " ORDER BY $ordenacao LIMIT $porPagina OFFSET $offset";
$resultado = mysqli_query($con, $sql);
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <meta charset="UTF-8">
    <link rel="icon" href="../imagens/favicon.ico" type="image/png">
    <title>Nexus | Gestão de Publicações</title>
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

        .erro {
            color: red;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }

        .paginacao a {
            padding: 8px 12px;
            background-color: #0e2b3b;
            color: white;
            border: 1px solid #0e2b3b;
            text-decoration: none;
            border-radius: 20px;
            transition: background-color 0.3s, color 0.3s;
        }

        .paginacao a:hover {
            background-color: white;
            color: #0e2b3b;
        }

        .footerbutton {
            margin: 0 10px;
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

        .view-btn {
            background-color: #0e2b3b;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: background-color 0.3s;
        }

        .view-btn:hover {
            background-color: #1a4b6b;
        }
    </style>
</head>

<body>
    <h1><img src="../imagens/logo.png" alt="Logo"> Gestão de Publicações</h1>

    

    <table>
        <tr>
            <th>ID</th>
            <th>Utilizador</th>
            <th>Descrição</th>
            <th>Data</th>
            <th>Media</th>
            <th>Ações</th>
        </tr>
        <?php while ($reg = mysqli_fetch_assoc($resultado)): ?>
            <form id="form<?= $reg['idpublicacao'] ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="idpublicacao" value="<?= $reg['idpublicacao'] ?>">
                <tr>
                    <td><?= $reg['idpublicacao'] ?></td>
                    <td><?= htmlspecialchars($reg['nome']) ?></td>
                    <td><textarea name="descricao"
                            style="width: 100%;"><?= htmlspecialchars($reg['descricao']) ?></textarea></td>
                    <td><?= $reg['data'] ?></td>
                    <td>
                        <?php if ($reg['total_media'] > 0): ?>
                            <button class="view-btn"
                                onclick="loadPostDetails(<?= $reg['idpublicacao'] ?>, '<?= htmlspecialchars(addslashes($reg['descricao'])) ?>')">
                                Ver (<?= $reg['total_media'] ?>)
                            </button>
                        <?php else: ?>
                            (sem media)
                        <?php endif; ?>
                    </td>
                    <td>
                        <button type="submit" onclick="gravar('form<?= $reg['idpublicacao'] ?>')">Gravar</button>
                        <button type="submit" onclick="remover('form<?= $reg['idpublicacao'] ?>')">Remover</button>
                        <a href="gerir_publicacao_media.php?id=<?= $reg['idpublicacao'] ?>">
                            <button type="button">Gerir Media</button>
                        </a>
                    </td>
                </tr>
            </form>
        <?php endwhile; ?>
    </table>

    <div class="paginacao" style="text-align: center; margin: 20px;">
        <?php if ($paginaAtual > 1): ?>
            <a href="?pagina=<?= $paginaAtual - 1 ?>">⬅ Anterior</a>
        <?php endif; ?>
        Página <?= $paginaAtual ?> de <?= $totalPaginas ?>
        <?php if ($paginaAtual < $totalPaginas): ?>
            <a href="?pagina=<?= $paginaAtual + 1 ?>">Próxima ➡</a>
        <?php endif; ?>
    </div>

    <!-- Modal para Visualizar Publicação -->
    <div id="postModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('postModal')">&times;</span>
            <h2 class="modal-title" id="postModalTitle"></h2>
            <div id="postModalContent">
                <div class="loading">A carregar publicação...</div>
                <div class="post-content" style="display:none;">
                    <p class="post-description" id="postDescription"></p>
                    <div id="postMediaContainer"></div>
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
            if (event.target.className === "modal") {
                event.target.style.display = "none";
            }
        }

        // Função para carregar os detalhes da publicação
        function loadPostDetails(postId, postTitle) {
            // Abrir o modal
            event.stopPropagation(); // Adicione esta linha para evitar que o evento se propague
            openModal('postModal');

            // Atualizar o título
            document.getElementById('postModalTitle').textContent = postTitle;

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
                    if (response.success) {
                        // Preencher os dados da publicação
                        document.getElementById('postDescription').textContent = response.descricao;
                        document.getElementById('postDate').textContent = 'Publicado em: ' + response.data;

                        // Limpar e adicionar media
                        const mediaContainer = document.getElementById('postMediaContainer');
                        mediaContainer.innerHTML = '';

                        if (response.medias && response.medias.length > 0) {
                            response.medias.forEach(media => {
                                let mediaHtml = '';
                                if (media.tipo === 'imagem') {
                                    mediaHtml = `<img src="../main/publicacoes/${media.media}" alt="Imagem da publicação" class="post-media">`;
                                } else if (media.tipo === 'video') {
                                    mediaHtml = `<video controls class="post-video">
                                                    <source src="../main/publicacoes/${media.media}" type="video/mp4">
                                                    Seu navegador não suporta o elemento de vídeo.
                                                </video>`;
                                }

                                if (mediaHtml) {
                                    mediaContainer.innerHTML += mediaHtml;
                                }
                            });
                        }

                        // Esconder loading e mostrar conteúdo
                        document.querySelector('#postModalContent .loading').style.display = 'none';
                        document.querySelector('#postModalContent .post-content').style.display = 'block';
                    } else {
                        document.querySelector('#postModalContent .loading').textContent = 'Erro ao carregar a publicação.';
                    }
                },
                error: function () {
                    document.querySelector('#postModalContent .loading').textContent = 'Erro ao carregar a publicação.';
                }
            });
        }

        function remover(idForm) {
            document.getElementById(idForm).action = "remover_publicacao.php";
        }

        function gravar(idForm) {
            document.getElementById(idForm).action = "gravar_publicacao.php";
        }

        function aplicarFiltros() {
            const tipo = document.getElementById('filtroTipo').value;
            const ordenacao = document.getElementById('ordenacao').value;
            let url = 'publicacoes.php?';

            if (tipo) url += `tipo=${tipo}&`;
            if (ordenacao) url += `ordenar=${ordenacao}`;

            window.location.href = url;
        }
    </script>
</body>

</html>