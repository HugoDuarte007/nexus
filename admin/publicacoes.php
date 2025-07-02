<?php
session_start();
require "../ligabd.php";

// Verifica se o utilizador está autenticado e é admin
if (!isset($_SESSION["user"]) || $_SESSION["id_tipos_utilizador"] != 0) {
    header("Location: ../logout.php");
    exit();
}

// Configurações de paginação
$porPagina = 6;
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

        .sucesso {
            color: green;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
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
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
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
            max-height: 100%;
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
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 10px;
            text-transform: uppercase;
        }

        /* Modal de confirmação - estilo do utilizadores.php */
        .modal-confirmacao {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-confirmacao-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 300px;
            border-radius: 10px;
            text-align: center;
        }

        .modal-confirmacao-buttons {
            margin-top: 20px;
        }

        .modal-confirmacao-buttons button {
            margin: 0 10px;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-confirmar {
            background-color: #dc3545;
            color: white;
        }

        .btn-cancelar {
            background-color: #6c757d;
            color: white;
        }

        /* Estilo da tabela como em utilizadores.php */
        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ccc;
            vertical-align: top;
        }

        th {
            background-color: #0e2b3b;
            color: white;
            font-size: 1.1rem;
            text-align: center;
        }

        td {
            font-size: 0.9rem;
        }

        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 0.9rem;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            resize: vertical;
            min-height: 60px;
        }

        textarea:focus {
            outline: none;
            border-color: #0e2b3b;
            box-shadow: 0 0 5px #0e2b3b;
        }

        button {
            padding: 10px 15px;
            font-size: 1rem;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            background-color: #0e2b3b;
            color: white;
            transition: 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            border: 1px solid #0e2b3b;
            margin: 5px;
        }

        button:hover {
            background-color: white;
            color: #0e2b3b;
        }

        .paginacao a {
            padding: 8px 12px;
            background-color: #0e2b3b;
            color: white;
            border: 1px solid #0e2b3b;
            text-decoration: none;
            border-radius: 20px;
            transition: background-color 0.3s, color 0.3s;
            margin: 0 5px;
        }

        .paginacao a:hover {
            background-color: white;
            color: #0e2b3b;
        }

        .footer-container {
            text-align: center;
            padding: 10px 0;
        }

        .footerbutton {
            padding: 10px 20px;
            font-size: 1rem;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            background-color: transparent;
            color: white;
            transition: 0.3s ease;
            display: inline-block;
            margin: 0 5px;
        }

        .footerbutton:hover {
            background-color: white;
            color: #0e2b3b;
        }
    </style>
</head>

<body>
    <h1><img src="../imagens/logo.png" alt="Logo"> Gestão de Publicações</h1>

    <?php if (isset($_SESSION["erro"])): ?>
        <div class="erro"><?= $_SESSION["erro"] ?></div>
        <?php unset($_SESSION["erro"]); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION["sucesso"])): ?>
        <div class="sucesso"><?= $_SESSION["sucesso"] ?></div>
        <?php unset($_SESSION["sucesso"]); ?>
    <?php endif; ?>

    <!-- Modal de confirmação para remoção -->
    <div id="modalConfirmacao" class="modal-confirmacao">
        <div class="modal-confirmacao-content">
            <h3>Confirmar Remoção</h3>
            <p>Tem a certeza que deseja remover esta publicação?</p>
            <p><strong>Esta ação irá remover:</strong></p>
            <ul style="text-align: left; margin: 10px 0;">
                <li>Todas as mídias associadas</li>
                <li>Todos os comentários</li>
                <li>Todas as reações</li>
            </ul>
            <p style="color: red; font-weight: bold;">Esta ação não pode ser desfeita!</p>
            <div class="modal-confirmacao-buttons">
                <button type="button" class="btn-cancelar" onclick="fecharModal()">Cancelar</button>
                <button type="button" class="btn-confirmar" onclick="confirmarRemocao()">Remover</button>
            </div>
        </div>
    </div>

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
                    <td style="text-align: center;"><?= $reg['idpublicacao'] ?></td>
                    <td style="text-align: center;"><?= htmlspecialchars($reg['nome']) ?></td>
                    <td><textarea name="descricao" <?= empty($reg['descricao'] ?? "") ? "disabled" : "" ?>
                            style="width: 100%; resize:none; justify-content:center; "><?= htmlspecialchars($reg['descricao'] ?? "") ?></textarea></td>
                    <td style="text-align: center;"><?= $reg['data'] ?></td>
                    <td style="text-align: center;">
                        <?php if ($reg['total_media'] > 0): ?>
                            <button type="button" class="view-btn"
                                onclick="loadPostDetails(<?= $reg['idpublicacao'] ?>, '<?= isset($reg['descricao']) ? htmlspecialchars(addslashes($reg['descricao'])) : '' ?>')">
                                Ver (<?= $reg['total_media'] ?? 0 ?>)
                            </button>
                        <?php else: ?>
                            (sem media)
                        <?php endif; ?>
                    </td>
                    <td style="text-align: center;">
                        <button type="submit" onclick="gravar('form<?= $reg['idpublicacao'] ?>')">Gravar</button>
                        <button type="button" onclick="confirmarRemover('form<?= $reg['idpublicacao'] ?>')">Remover</button>
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
        <div class="footer-container">
            <a href="utilizadores.php"><button class="footerbutton">Utilizadores</button></a>
            <a href="publicacoes.php"><button class="footerbutton">Publicações</button></a>
            <a href="estatistica.php"><button class="footerbutton">Estatística</button></a>
            <a href="admin_choice.php"><button class="footerbutton">Sair</button></a>
        </div>
    </footer>

    <script>
        // Variável para armazenar o formulário a ser removido
        var formParaRemover = "";
        
        // Função para abrir modal de confirmação
        function confirmarRemover(idForm) {
            formParaRemover = idForm;
            document.getElementById('modalConfirmacao').style.display = 'block';
        }
        
        // Função para confirmar a remoção
        function confirmarRemocao() {
            if (formParaRemover) {
                document.getElementById(formParaRemover).action = "remover_publicacao.php";
                document.getElementById(formParaRemover).submit();
            }
        }
        
        // Função para fechar o modal
        function fecharModal() {
            document.getElementById('modalConfirmacao').style.display = 'none';
            formParaRemover = "";
        }
        
        // Função para gravar alterações
        function gravar(idForm) {
            document.getElementById(idForm).action = "gravar_publicacao.php";
            document.getElementById(idForm).submit();
        }

        // Funções para abrir e fechar modais
        function openModal(modalId) {
            document.getElementById(modalId).style.display = "block";
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = "none";
        }

        // Fechar modal quando clicar fora do conteúdo
        window.onclick = function (event) {
            if (event.target.classList.contains("modal") || event.target.classList.contains("modal-confirmacao")) {
                event.target.style.display = "none";
            }
        }

        // Função para carregar os detalhes da publicação
        function loadPostDetails(postId, postTitle) {
            // Abrir o modal
            openModal('postModal');

            // Atualizar o título
            document.getElementById('postModalTitle').textContent = 'Publicação #' + postId;

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
                        document.querySelector('#postModalContent .loading').textContent = 'Erro ao carregar a publicação: ' + (response.message || 'Erro desconhecido');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Erro AJAX:', error);
                    document.querySelector('#postModalContent .loading').textContent = 'Erro ao carregar a publicação. Verifique a conexão.';
                }
            });
        }
    </script>
</body>

</html>