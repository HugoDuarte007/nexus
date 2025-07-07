<?php
session_start();


if (isset($_SESSION["idutilizador"])) {
    header("Location: main/main.php");
}

if (isset($_SESSION["sucesso"])) {
    $mensagem = $_SESSION["sucesso"];
    unset($_SESSION["sucesso"]);
} else {
    $mensagem = null;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = htmlspecialchars($_POST["name"]);
    $email = htmlspecialchars($_POST["email"]);
    $subject = htmlspecialchars($_POST["subject"]);
    $message = htmlspecialchars($_POST["message"]);

    $to = "geral.nexusapp@gmail.com";
    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    $emailBody = "Recebeu um novo pedido de contacto:\n\n";
    $emailBody .= "Nome: $name\n";
    $emailBody .= "Email: $email\n";
    $emailBody .= "Assunto: $subject\n";
    $emailBody .= "Mensagem:\n$message\n";

    if (mail($to, $subject, $emailBody, $headers)) {
        $_SESSION["sucesso"] = "Mensagem enviada com sucesso!";
        header("Location: " . $_SERVER["PHP_SELF"]);
        exit();
    } else {
        echo "<p style='color: red;'>Ocorreu um erro. Por favor tente novamente</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="imagens/favicon.ico" type="image/png">
    <title>Nexus</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        .notificacao {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #4CAF50;
            color: white;
            padding: 15px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
            z-index: 1000;
        }

        .notificacao .progress-bar {
            margin-top: 10px;
            height: 5px;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            animation: progress 3s linear forwards;
        }

        @keyframes progress {
            from {
                width: 100%;
            }

            to {
                width: 0%;
            }
        }

        footer {
            background-color: #0e2b3b;
            padding: 30px;
            color: white;
            text-align: center;
            border-top: 1px solid #ddd;            
        }
        

        .footer-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-form {
            flex: 1;
            max-width: 700px;
            margin: 10px;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            align-items: center;
            text-align: left;
        }

        .footer-form h2 {
            font-size: 22px;
            color: white;
            margin-bottom: 15px;
        }

        .footer-form p {
            font-size: 14px;
            color: #ccc;
            margin-bottom: 20px;
        }

        .footer-form form .form-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 15px;
        }

        .footer-form form textarea.input {
            background-color: white;
            border: 1px solid #0e2b3b;
            border-radius: 5px;
            padding: 10px;
            width: 100%;
            box-sizing: border-box;
            font-size: 14px;
            color: #333;
            resize: none;
            /* Impede o redimensionamento */
        }
    </style>
</head>

<body>
    <?php if ($mensagem): ?>
        <div class="notificacao" id="notificacao">
            <p><?= htmlspecialchars($mensagem) ?></p>
            <div class="progress-bar"></div>
        </div>
    <?php endif; ?>
    
    <div class="container" style="justify-content: center;">
        <div alt="Background" class="background"></div>
        <div class="logo">
            <img class="logo" src="imagens/logo.png" alt="Nexus Logo">
        </div>
        <h1>Connecting People, Transforming Worlds</h1>
        <div class="buttons">
            <a href="login.php"><button class="Login">Entrar</button></a>
            <a href="signup.php"><button class="Registar">Registar</button></a>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const notificacao = document.getElementById("notificacao");
            if (notificacao) {
                setTimeout(() => {
                    notificacao.style.opacity = 1;

                    setTimeout(() => {
                        notificacao.style.opacity = 0;
                        setTimeout(() => notificacao.remove(), 500);
                    }, 4000);
                }, 1000);
            }
        });
    </script>

    <footer>
        <div class="footer-container">
            <div class="footer-form">
                <h2 style="color: #0e2b3b;">Contacte-nos</h2>
                <p style="color: #0e2b3b;">Connecting People, Transforming Worlds.</p>
                <form method="POST">
                    <div class="form-group">
                        <textarea class="input" type="text" name="name" placeholder="Nome" rows="1" required></textarea>
                        <textarea class="input" type="email" name="email" placeholder="Email" rows="1"
                            required></textarea>

                        <textarea class="input" type="text" name="subject" placeholder="Assunto" rows="1"
                            required></textarea>
                        <textarea class="input" name="message" placeholder="Mensagem" rows="4" required></textarea>
                    </div>
                    <button id="botaoRegistar" type="submit">Enviar</button>
                </form>
            </div>
        </div>
        
        <p xmlns:cc="http://creativecommons.org/ns#" xmlns:dct="http://purl.org/dc/terms/"><span
                property="dct:title">Nexus</span> by <span property="cc:attributionName">Hugo Duarte</span> is licensed
            under <a href="https://creativecommons.org/licenses/by/4.0/?ref=chooser-v1" target="_blank"
                rel="license noopener noreferrer" style="display:inline-block;">CC BY 4.0<img
                    style="height:22px!important;margin-left:3px;vertical-align:text-bottom;"
                    src="https://mirrors.creativecommons.org/presskit/icons/cc.svg?ref=chooser-v1" alt=""><img
                    style="height:22px!important;margin-left:3px;vertical-align:text-bottom;"
                    src="https://mirrors.creativecommons.org/presskit/icons/by.svg?ref=chooser-v1" alt=""></a></p>
    </footer>

</body>

</html>