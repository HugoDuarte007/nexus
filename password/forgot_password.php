<?php
session_start();
require "../ligabd.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($con, $_POST["email"]);

    // Verificar se o email existe
    $query = "SELECT * FROM utilizador WHERE email = '$email'";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $codigo = rand(100000, 999999);
        $_SESSION['reset_code'] = $codigo;
        $_SESSION['reset_email'] = $email;
        $_SESSION['code_expiry'] = time() + 1800; // 30 minutos de validade

        // Envia o email com o código
        $assunto = "Recuperação de Senha - Nexus";
        $mensagem = "
<!DOCTYPE html>
<html lang='pt'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Recuperação de Senha</title>
</head>
<body style='font-family: Arial, sans-serif; background-color: #ffffff; color: #ffffff; padding: 20px; text-align: center;'>
    <div style='max-width: 600px; margin: auto; background-color: #0e2b3b; padding: 20px; border-radius: 10px;'>
        <img src='https://cdn.pixabay.com/photo/2025/02/05/09/29/internet-9383803_1280.png' alt='Nexus Logo' style='width: 80px; margin-bottom: 10px;'>
        <h2 style='color: #ffffff;'>Recuperação de Senha</h2>
        <p>Olá <strong>{$user['nome']}</strong>,</p>
        <p>Recebemos um pedido para redefinir a sua palavra-passe na Nexus. Aqui está o código de verificação:</p>
        <div style='background-color: #ffffff; padding: 10px; border-radius: 5px; display: inline-block; margin: 10px 0;'>
            <p style='font-size: 32px; font-weight: bold; color: #0e2b3b; margin: 0;'>{$codigo}</p>
        </div>
        <p>Este código expirará em 30 minutos. Se não solicitou esta alteração, ignore este e-mail.</p>
        <p style='color: #ccc;'>Atenciosamente, <br> Equipa Nexus</p>
    </div>
</body>
</html>
";

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: Nexus <noreply@nexus.com>" . "\r\n";

        if (mail($email, $assunto, $mensagem, $headers)) {
            $_SESSION['sucesso'] = "Código de verificação enviado para o seu email.";
            header("Location: verify_code.php");
            exit();
        } else {
            $_SESSION['erro'] = "Erro ao enviar o email. Tente novamente mais tarde.";
            header("Location: forgot_password.php");
            exit();
        }
    } else {
        $_SESSION['erro'] = "Email não registado no sistema.";
        header("Location: forgot_password.php");
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
    <title>Nexus | Recuperar Password</title>
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
    </style>
</head>

<body>
    <div class="container" style="justify-content: center;">
        <div class="form-container1">
            <h2><a href="index.php"><img src="../imagens/logo4.png"></a><br>
                Recuperar Palavra-Passe</h2><br>

            <?php if (isset($_SESSION['erro'])) {
                echo "<p style='color:red;'>" . $_SESSION['erro'] . "</p>";
                unset($_SESSION['erro']);
            }
            if (isset($_SESSION['sucesso'])) {
                echo "<p style='color:green;'>" . $_SESSION['sucesso'] . "</p>";
                unset($_SESSION['sucesso']);
            }
            ?>

            <form action="" method="post">
                <div class="form-group">
                    <div class="column">
                        <input type="email" name="email" class="input" placeholder="Email*" required>
                    </div>
                </div>
                <div class="form-group">
                    <div class="column">
                        <button class="reset-button" type="submit">Enviar Código</button>
                    </div>
                </div>
                <div class="form-group">
                    <div class="column">
                        <label for="checkbox"><a href="../login.php">Voltar ao Login</a></label>
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