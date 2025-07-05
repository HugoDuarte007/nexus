<?php
session_start();
require "../ligabd.php";

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if ($new_password !== $confirm_password) {
        $_SESSION['erro'] = "As palavras-passe não coincidem.";
        header("Location: reset_password.php");
        exit();
    }

    // Validar força da senha (igual ao cadastro)
    if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>]).{8,}$/', $new_password)) {
        $_SESSION['erro'] = "A palavra-passe deve conter pelo menos 8 caracteres, incluindo maiúscula, minúscula, número e caractere especial.";
        header("Location: reset_password.php");
        exit();
    }

    // Atualizar senha no banco de dados
    $email = $_SESSION['reset_email'];
    $query = "UPDATE utilizador SET pass=password('$new_password') WHERE email='$email'";

    if (mysqli_query($con, $query)) {
        // Limpar variáveis de sessão
        unset($_SESSION['reset_email']);
        unset($_SESSION['reset_code']);
        unset($_SESSION['code_expiry']);

        $_SESSION['sucesso'] = "Palavra-passe alterada com sucesso!";
        header("Location: ../login.php");
        exit();
    } else {
        $_SESSION['erro'] = "Erro ao atualizar a palavra-passe. Tente novamente.";
        header("Location: reset_password.php");
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
    <title>Nexus | Redefinir Password</title>
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

        .reset-button {
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

        .reset-button:hover {
            background-color: black;
            color: white;
        }

        h2 img {
            width: 40px;
            height: auto;
            margin-right: 10px;
        }

        .password-requirements {
            font-size: 12px;
            color: #aaa;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <div class="container" style="justify-content: center;">
        <div class="form-container1">
            <h2><a href="../index.php"><img src="../imagens/logo4.png"></a><br>
                Redefinir Palavra-Passe</h2><br>

            <?php if (isset($_SESSION['erro'])) {
                echo "<p style='color:red;'>" . $_SESSION['erro'] . "</p>";
                unset($_SESSION['erro']);
            } ?>

            <form action="" method="post">
                <div class="form-group">
                    <div class="column">
                        <div style="position: relative;">
                            <input type="password" name="password" id="password" class="input"
                                placeholder="Nova Palavra-Passe*" required>
                            <button type="button" id="togglePassword"
                                style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #555; font-size: 12px; cursor: pointer;">
                                Mostrar
                            </button>
                        </div>
                        <div class="password-requirements">
                            A palavra-passe deve conter pelo menos 8 caracteres, incluindo maiúscula, minúscula, número
                            e caractere especial.
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="column">
                        <input type="password" name="confirm_password" class="input"
                            placeholder="Confirmar Nova Palavra-Passe*" required>
                    </div>
                </div>
                <div class="form-group">
                    <div class="column">
                        <button class="reset-button" type="submit">Redefinir Palavra-Passe</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
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
</body>

</html>