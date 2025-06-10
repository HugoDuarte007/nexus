<?php

session_start();

?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../imagens/favicon.ico" type="image/png">
    <link rel="stylesheet" type="text/css" href="style.css">
    <title>Nexus | Admin</title>
    <style>
        footer {
            background-color: transparent;
            font-size: 16px;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="logo">
            <img class="logo" src="../imagens/logo.png" alt="Nexus Logo">
        </div>
        <h1>Bem-vindo de volta</h1>
        <div class="buttons">
            <a href="utilizadores.php"><button class="admin">Página de administração</button></a>
            <a href="../main/main.php"><button class="nexus">Nexus App</button></a>
            <button class="Voltar" onclick="document.getElementById('logoutForm').submit();">Logout</button>

            <!-- Adiciona o formulário de logout -->
            <form id="logoutForm" action="../logout.php" method="POST" style="display: none;">
                <input type="hidden" name="botaoLogout" value="true">
            </form>
        </div>

        <div class="flex-1"></div>
    </div>

    <footer>
        © 2025 Nexus. Todos os direitos reservados.
    </footer>
</body>

</html>