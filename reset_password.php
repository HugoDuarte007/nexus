<?php
require "ligabd.php"; // Certifique-se que este arquivo contém a conexão com o banco de dados

$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $token = trim($_POST['token']);
    $nova_password = trim($_POST['password']);

    if (empty($email) || empty($token) || empty($nova_password)) {
        $mensagem = "Por favor, preencha todos os campos.";
    } else {
        // Validar o token
        $secret = "chave_secreta_segura"; // Deve ser a mesma usada para gerar o token
        $valid_token = hash_hmac('sha256', $email . strtotime($_GET['timestamp']), $secret);

        if (hash_equals($valid_token, $token)) {
            // Atualizar a palavra-passe no banco de dados
            $nova_password_hash = password_hash($nova_password, PASSWORD_DEFAULT);
            $sql = "UPDATE utilizador SET password = ? WHERE email = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param('ss', $nova_password_hash, $email);

            if ($stmt->execute()) {
                $mensagem = "Sua palavra-passe foi redefinida com sucesso.";
            } else {
                $mensagem = "Erro ao redefinir a palavra-passe.";
            }
        } else {
            $mensagem = "Token inválido ou expirado.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Palavra-passe</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Redefinir Palavra-passe</h2>
        <?php if (!empty($mensagem)): ?>
            <p class="mensagem"><?php echo $mensagem; ?></p>
        <?php endif; ?>
        <form action="reset_password.php" method="post">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($_GET['email']); ?>">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
            <div>
                <input type="password" name="password" placeholder="Nova Palavra-passe" required>
            </div>
            <button type="submit">Redefinir</button>
        </form>
    </div>
</body>
</html>
