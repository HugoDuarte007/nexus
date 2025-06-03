<?php
require "ligabd.php"; // Certifique-se que este arquivo contém a conexão com o banco de dados

$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $mensagem = "Por favor, insira o seu email.";
    } else {
        // Verificar se o email existe na base de dados
        $sql = "SELECT * FROM utilizador WHERE email = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            // Gerar o token único
            $secret = "chave_secreta_segura"; // Altere isso para uma chave segura
            $token = hash_hmac('sha256', $email . time(), $secret);

            // Criar o link de redefinição
            $reset_link = "https://seusite.com/reset_password.php?email=" . urlencode($email) . "&token=" . urlencode($token);

            // Simular envio de email (substitua por um sistema de email real)
            // mail($email, "Redefinir palavra-passe", "Clique no link para redefinir sua palavra-passe: $reset_link");
            
            echo "<p>Link de redefinição: <a href='$reset_link'>$reset_link</a></p>"; // Apenas para testes

            $mensagem = "Um link de redefinição foi enviado para o seu email.";
        } else {
            $mensagem = "O email fornecido não está registrado.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esqueceu a Palavra-passe</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Recuperar Palavra-passe</h2>
        <p>Insira o seu email para receber um link de redefinição.</p>
        <?php if (!empty($mensagem)): ?>
            <p class="mensagem"><?php echo $mensagem; ?></p>
        <?php endif; ?>
        <form action="forgot_password.php" method="post">
            <div>
                <input type="email" name="email" placeholder="Seu Email" required>
            </div>
            <button type="submit">Enviar Link</button>
        </form>
    </div>
</body>
</html>
