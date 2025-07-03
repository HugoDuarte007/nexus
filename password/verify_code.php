<?php
session_start();
require "../ligabd.php";

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

// Verificar se o código expirou
if (time() > $_SESSION['code_expiry']) {
    $_SESSION['erro'] = "O código expirou. Por favor, solicite um novo.";
    header("Location: forgot_password.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_code = $_POST["code"];

    if ($entered_code == $_SESSION['reset_code']) {
        header("Location: reset_password.php");
        exit();
    } else {
        $_SESSION['erro'] = "Código inválido. Tente novamente.";
        header("Location: verify_code.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../imagens/favicon.ico" type="image/png">
    <link rel="stylesheet" type="text/css" href="../style.css">
    <title>Nexus | Verificar Código</title>
    <style>
        .form-container1 {
            margin: 20px auto;
            background-color: rgba(0, 0, 0, 0.8);
            border-radius: 40px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            color: white;
            animation: slideUpFadeIn 0.8s ease-out forwards;
            padding: 20px;
        }

        .verify-button {
            padding: 10px 20px;
            font-size: 1rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            background-color: white;
            color: black;
            transition: 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            width: 100%;
        }

        .verify-button:hover {
            background-color: black;
            color: white;
        }

        h2 img {
            width: 40px;
            height: auto;
            margin-right: 10px;
        }
    </style>
</head>

<body>
    <div class="container" style="justify-content: center;">
        <div class="form-container1">
            <h2><a href="index.php"><img src="../imagens/logo4.png"></a><br>
                Verificar Código</h2><br>

            <p>Foi enviado um código de 6 dígitos para o seu email.</p>

            <?php if (isset($_SESSION['erro'])) {
                echo "<p style='color:red;'>" . $_SESSION['erro'] . "</p>";
                unset($_SESSION['erro']);
            } ?>

            <form action="" method="post">
                <div class="form-group">
                    <div class="column">
                        <input type="text" name="code" class="input" placeholder="Código de Verificação*" required
                            maxlength="6">
                    </div>
                </div>
                <div class="form-group">
                    <div class="column">
                        <button class="verify-button" type="submit">Verificar</button>
                    </div>
                </div>
                <div class="form-group">
                    <div class="column">
                        <label for="checkbox"><a href="forgot_password.php">Reenviar Código</a></label>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <footer>
        © 2025 Nexus. Todos os direitos reservados.
    </footer>
</body>

</html>