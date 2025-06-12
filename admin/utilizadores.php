<?php
session_start();
require "../ligabd.php";
require "../partials/paises.php";

// Verifica se o utilizador está autenticado e é admin
if (!isset($_SESSION["user"]) || $_SESSION["id_tipos_utilizador"] != 0) {
    header("Location: ../logout.php");
    exit();
}

// Define paginação
$utilizadoresPorPagina = 5;
$paginaAtual = isset($_GET['pagina']) ? max(1, (int) $_GET['pagina']) : 1;
$offset = ($paginaAtual - 1) * $utilizadoresPorPagina;

// Define ordenação
$search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
$order = isset($_GET['order']) ? $_GET['order'] : 'A-Z';
$orderBy = "nome ASC";
if ($order == "Z-A") {
    $orderBy = "nome DESC";
} elseif ($order == "data") {
    $orderBy = "data_registo DESC";
}

// Conta o número total de utilizadores
$sqlTotal = "SELECT COUNT(*) as total FROM utilizador";
$resultTotal = mysqli_query($con, $sqlTotal);
$totalUtilizadores = mysqli_fetch_assoc($resultTotal)['total'];
$totalPaginas = ceil($totalUtilizadores / $utilizadoresPorPagina);

// Obtém utilizadores da página atual
$sql = "SELECT * FROM utilizador, tipos_utilizador
        WHERE utilizador.id_tipos_utilizador = tipos_utilizador.id_tipos_utilizador
        ORDER BY $orderBy
        LIMIT $utilizadoresPorPagina OFFSET $offset";
$resultado = mysqli_query($con, $sql);
if (!$resultado) {
    $_SESSION["erro"] = "Não foi possível obter os dados dos utilizadores.";
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../imagens/favicon.ico" type="image/png">
    <title>Nexus | Gerir Utilizadores</title>
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
    </style>
</head>

<body><br>
    <h1><img src="../imagens/logo.png" alt="Logo"> Gestão de Utilizadores</h1>
    <script>
        var botaoAcao = "";
        function remover(idForm) {
            document.getElementById(idForm).action = "remover.php";
            botaoAcao = "remover";
        }
        function gravar(idForm) {
            document.getElementById(idForm).action = "gravar.php";
            botaoAcao = "gravar";
        }
        function banir(idForm) {
            document.getElementById(idForm).action = "banir.php";
            botaoAcao = "banir";
        }
        function acao() {
            return botaoAcao !== "";
        }
    </script>

    <table>
        <tr>
            <th>Nome</th>
            <th>Username</th>
            <th>Palavra-passe</th>
            <th>Email</th>
            <th>Telemóvel</th>
            <th>Idade</th>
            <th>País</th>
            <th>Tipo de Utilizador</th>
            <th></th>
            <th>Ações</th>
            <th></th>
        </tr>
        <form id="formInserir" action="inserir.php" method="post" onsubmit="return true">
            <tr>
                <td><input name="nome" type="text" placeholder="Nome" required></td>
                <td><input name="user" type="text" placeholder="Username" required></td>
                <td><input name="password" type="password" placeholder="Password" required></td>
                <td><input name="email" type="email" placeholder="Email" required></td>
                <td><input name="telemovel" type="text" placeholder="Telemóvel" required></td>
                <td><input name='data_nascimento' type='date' value='<?= $registo["data_nascimento"] ?>'></td>
                <td>
                    <select name='pais' required>
                        <?php foreach ($paises as $pais): ?>
                            <option value="<?= $pais ?>"><?= htmlspecialchars($pais) ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <select name="id_tipos_utilizador">
                        <option value="0">Administrador</option>
                        <option value="1">Utilizador</option>
                    </select>
                </td>
                <td colspan="3" style="text-align: center;">
                    <button id='botaoRegistar' type="submit">Adicionar utilizador</button>
                </td>
            </tr>
        </form>

        <?php while ($registo = mysqli_fetch_array($resultado)): ?>
            <form id='form<?= $registo["idutilizador"] ?>' action='' method='post' enctype='multipart/form-data'
                onsubmit='return acao()'>
                <tr>
                    <td hidden>
                        <input name='idutilizador' type='hidden' value='<?= $registo["idutilizador"] ?>'>
                    </td>
                    <td><input name='nome' type='text' value='<?= $registo["nome"] ?>' required></td>
                    <td><input name='user' type='text' value='<?= $registo["user"] ?>'></td>
                    <td><input name='password' type='password'></td>
                    <td><input readonly name='email' type='email' value='<?= $registo["email"] ?>' required></td>
                    <td><input name='telemovel' type='text' value='<?= $registo["telemovel"] ?>' required></td>
                    <td><input name='data_nascimento' type='date' value='<?= $registo["data_nascimento"] ?>'></td>
                    <td>
                        <select name='pais' required>
                            <?php foreach ($paises as $pais): ?>
                                <option value="<?= $pais ?>" <?= $registo["pais"] == $pais ? "selected" : "" ?>>
                                    <?= htmlspecialchars($pais) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>

                    <td>
                        <select name='id_tipos_utilizador'>
                            <option value='0' <?= $registo["id_tipos_utilizador"] == 0 ? "selected" : "" ?>>Administrador
                            </option>
                            <option value='1' <?= $registo["id_tipos_utilizador"] == 1 ? "selected" : "" ?>>Utilizador</option>
                        </select>
                    </td>
                    <td>
                        <button id='botaoRemover' name='botaoRemover'
                            onclick='remover("form<?= $registo["idutilizador"] ?>")' <?= $registo["id_tipos_utilizador"] == 0 ? "disabled" : "" ?>>Remover</button>
                    </td>
                    <td>
                        <button id='botaoGravar' name='botaoGravar'
                            onclick='gravar("form<?= $registo["idutilizador"] ?>")'>Gravar</button>
                    </td>
                    <td>
                        <button id='botaoBanir' name='botaoBanir' onclick='banir("form<?= $registo["idutilizador"] ?>")'
                            <?= $registo["id_tipos_utilizador"] == 0 ? "disabled" : "" ?>>Banir</button>
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
        <div class="footer-container">
            <a href="utilizadores.php"><button class="footerbutton">Utilizadores</button></a>
            <a href="publicacoes.php"><button class="footerbutton">Publicações</button></a>
            <a href="estatistica.php"><button class="footerbutton">Estatística</button></a>
            <a href="admin_choice.php"><button class="footerbutton">Sair</button></a>
        </div>
    </footer>
</body>

</html>