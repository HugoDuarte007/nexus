<?php
session_start();
require "../ligabd.php";

// Verifica se o utilizador está autenticado e é admin
if (!isset($_SESSION["user"]) || $_SESSION["id_tipos_utilizador"] != 0) {
    header("Location: ../logout.php");
    exit();
}

// Paginação
$porPagina = 5;
$paginaAtual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($paginaAtual - 1) * $porPagina;

// Total de publicações
$sqlTotal = "SELECT COUNT(*) as total FROM publicacao";
$resultTotal = mysqli_query($con, $sqlTotal);
$totalPublicacoes = mysqli_fetch_assoc($resultTotal)['total'];
$totalPaginas = ceil($totalPublicacoes / $porPagina);

// Buscar publicações com nome do utilizador
$sql = "SELECT p.*, u.nome FROM publicacao p
        JOIN utilizador u ON p.idutilizador = u.idutilizador
        ORDER BY p.data DESC
        LIMIT $porPagina OFFSET $offset";
$resultado = mysqli_query($con, $sql);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
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

        .post-img {
            width: 120px;
            height: auto;
            border-radius: 10px;
        }
        .footerbutton {
            margin: 0 10px;
        }
    </style>
    <script>
        function remover(idForm) {
            document.getElementById(idForm).action = "remover_publicacao.php";
        }
        function gravar(idForm) {
            document.getElementById(idForm).action = "gravar_publicacao.php";
        }
    </script>
</head>
<body>
    <h1><img src="../imagens/logo.png" alt="Logo"> Gestão de Publicações</h1>

    <table>
        <tr>
            <th>Utilizador</th>
            <th>Imagem</th>
            <th>Descrição</th>
            <th>Data</th>
            <th>Likes</th>
            <th>Ações</th>
        </tr>
        <?php while ($reg = mysqli_fetch_assoc($resultado)): ?>
            <form id="form<?= $reg['idpublicacao'] ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="idpublicacao" value="<?= $reg['idpublicacao'] ?>">
                <tr>
                    <td><?= htmlspecialchars($reg['nome']) ?></td>
                    <td>
                        <?php if ($reg['media']): ?>
                            <img class="post-img" src="../main/publicacoes/<?= $reg['media'] ?>" alt="Imagem">
                        <?php else: ?>
                            (sem imagem)
                        <?php endif; ?>
                    </td>
                    <td><input type="text" name="descricao" value="<?= htmlspecialchars($reg['descricao']) ?>" style="width: 100%;"></td>
                    <td><?= $reg['data'] ?></td>
                    <td><?= $reg['likes'] ?></td>
                    <td>
                        <button type="submit" onclick="gravar('form<?= $reg['idpublicacao'] ?>')">Gravar</button>
                        <button type="submit" onclick="remover('form<?= $reg['idpublicacao'] ?>')">Remover</button>
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
            <div style="height:100px;">

            </div>
    <footer>
        <div class="footer-container" style="text-align: center;">
            <a href="utilizadores.php"><button class="footerbutton">Utilizadores</button></a>
            <a href="publicacoes.php"><button class="footerbutton">Publicações</button></a>
            <a href="estatistica.php"><button class="footerbutton">Estatística</button></a>
            <a href="admin_choice.php"><button class="footerbutton">Sair</button></a>
        </div>
    </footer>
</body>
</html>
