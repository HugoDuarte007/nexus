<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="imagens/favicon.ico" type="image/png">
    <title>Nexus | Confirm Code</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        img {
            width: 40px;
            height: auto;
            margin-right: 10px;

        }

        .link-button {
            background: none;
            border: none;
            color: rgb(28, 80, 109);
            text-decoration: underline;
            cursor: pointer;
            font-size: 14px;
            padding: 0;
            font-family: inherit;
        }

        .link-button:hover {
            color: #005cbf;
            text-decoration: none;
        }
    </style>
    </style>
</head>

<body>
    <div class="container" style="justify-content: center;">
        <img src="imagens/fixo_portatil_telemovel.png" alt="platforms" class="platforms">
        <div class="form-container">
            <h2><a href="index.php"><img src="imagens/logo4.png"></a><br>
                Verificar Código</h2><br>

            <?php
            if (isset($_SESSION["erro"])) {
                echo "<p style='color:red;'>" . $_SESSION["erro"] . "</p>";
                unset($_SESSION["erro"]);
            }
            ?>

            <form action="inserir.php" method="post">
                <div class="form-group">
                    <div class="column">
                        <input type="text" name="codigoInserido" class="input"
                            placeholder="Insira o código enviado por email*" required>
                    </div>
                </div>
                <div class="form-group">

                    <div class="column">
                        <button type="submit" id="botaoRegistar" name="verificarCodigo" class="buttons">Verificar
                            Código</button>
                    </div>
                </div>


            </form>
        </div>
    </div>
</body>
<footer>
    © 2025 Nexus. Todos os direitos reservados.
</footer>

</html>