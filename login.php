<?php

session_start();
require "ligabd.php";

if (isset($_SESSION["user"])) {
    header("Location: main/main.php");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $p_email = $_POST["email"];
    $p_password = $_POST["pass"];

    // Verificar se o utilizador está banido
    $query_banido = "SELECT * FROM utilizador 
                     INNER JOIN banidos ON utilizador.idutilizador = banidos.idutilizador 
                     WHERE utilizador.email = '$p_email'";
    $result_banido = mysqli_query($con, $query_banido);

    if (mysqli_num_rows($result_banido) > 0) {
        $_SESSION['sucesso'] = "Conta foi banida. Contacte-nos para mais informações.";
        header("Location: index.php");
        exit();
    }

    // Verificar credenciais
    $query = "SELECT * FROM utilizador WHERE email = '$p_email' && pass=password('$p_password');";
    $result = mysqli_query($con, $query);

    if ($registo = mysqli_fetch_array($result)) {
        $_SESSION = $registo;

        if ($_SESSION["id_tipos_utilizador"] == 0) {
            header("Location: admin/admin_choice.php");
        } elseif ($_SESSION["id_tipos_utilizador"] == 1) {
            header("Location: main/main.php");
        } else {
            $_SESSION['sucesso'] = "Tipo de utilizador inválido.";
            header("Location: index.php");
        }
    } else {
        $_SESSION['sucesso'] = "Credenciais inválidas. Tente novamente.";
        header("Location: index.php");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="imagens/favicon.ico" type="image/png">
    <link rel="stylesheet" type="text/css" href="style.css">
    <title>Nexus | Login</title>
    <style>
        .form-container1 {
            margin: 0 auto;
            margin-top: 20px;
            margin-bottom: 30px;
            margin-right: 50%%;
            background-color: rgba(0, 0, 0, 0.8);
            border-radius: 40px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            color: white;
            animation: slideUpFadeIn 0.8s ease-out forwards;
            padding: 10px;
        }

        .separator {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 20px 0;
            color: white;
        }

        .separator::before,
        .separator::after {
            content: '';
            flex: 1;
            height: 1px;
            background-color: #ccc;
            margin: 0 20px;
        }

        .login-button,
        .google-button {
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

        .google-button {
            border-radius: 20px;
        }

        .google-button img {
            width: 20px;
            height: auto;
        }

        .login-button:hover,
        .google-button:hover {
            background-color: black;
            color: white;
        }


        .create-account,
        .form-group a {
            font-size: 14px;
            color: #2679a5;
            font-weight: bold;
            text-decoration: none;
        }

        .create-account a:hover,
        .form-group a:hover {
            text-decoration: underline;

        }

        img {
            width: 40px;
            height: auto;
            margin-right: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="form-container1">
            <h2><a href="index.php"><img src="imagens/logo4.png"></a><br>
                Iniciar Sessão</h2><br>

            <form action="" method="post">
                <div class="form-group">
                    <div class="column">
                        <input type="email" name="email" class="input" placeholder="Email*" required>
                    </div>
                </div>
                <div class="form-group">
                    <div class="column">
                        <div style="position: relative;">
                            <input type="password" name="pass" id="password" class="input" placeholder="Palavra-Passe*"
                                required>
                            <button type="button" id="togglePassword"
                                style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #555; font-size: 12px; cursor: pointer;">
                                Mostrar
                            </button>
                        </div>
                    </div>
                </div>
                <?php if (isset($_SESSION['erro'])) {
                    echo "<p>" . $_SESSION['erro'] . "</p>";
                    unset($_SESSION['erro']);
                } ?>
                <div class="form-group">
                    <div class="column">
                        <button class="login-button" type="submit" class="buttons">Iniciar Sessão</button>
                    </div>
                </div>
                <div class="form-group">
                    <div class="column">
                        <label for="checkbox"><a href="forgot_password.php" target="_blank">Esqueceu-se da
                                palavra-passe?</a></label>
                    </div>
                </div>
                <div class="separator">
                    <span>ou</span>
                </div><br>

                <div class="google-login">
                    <button class="google-button">
                        <img src="imagens/google.png" alt="Google Icon">
                        Iniciar Sessão com Google
                    </button>
                </div><br>

                <div class="form-group">
                    <div class="column">
                        <label for="checkbox">Não tem conta? <a href="signup.php">Registe-se</a></label>
                    </div>
                </div>
            </form>

        </div>
    </div>
</body>
<footer>
    © 2025 Nexus. Todos os direitos reservados.
</footer>
<script>
    document.getElementById("togglePassword").addEventListener("click", function () {
        const passwordInput = document.getElementById("password");
        const isPassword = passwordInput.type === "password";

        passwordInput.type = isPassword ? "text" : "password";
        this.textContent = isPassword ? "Ocultar" : "Mostrar";
    });
</script>

</html>