<?php
session_start();
require "../ligabd.php";

// Verificar se o utilizador está autenticado
if (!isset($_SESSION["user"])) {
    header("Location: ../login.php");
    exit();
}

$iduser = $_SESSION["idutilizador"];
$msg = "";

// Atualizar perfil se formulário for submetido
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = mysqli_real_escape_string($con, $_POST["nome"]);
    $user = mysqli_real_escape_string($con, $_POST["user"]);
    $email = mysqli_real_escape_string($con, $_POST["email"]);

    $query = "UPDATE utilizador SET nome='$nome', user='$user', email='$email' WHERE idutilizador = '$iduser'";
    if (mysqli_query($con, $query)) {
        $msg = "Perfil atualizado com sucesso!";
    } else {
        $msg = "Erro ao atualizar perfil: " . mysqli_error($con);
    }
}

// Buscar dados atuais
$query = "SELECT nome, user, email FROM utilizador WHERE idutilizador = '$iduser'";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);

?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../imagens/favicon.ico" type="image/png">
    <link rel="stylesheet" type="text/css" href="../main/style.css">
    <title>Nexus | Editar Perfil</title>
    <style>
        .editar-form {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background-color: #f5f5f5;
            border-radius: 10px;
        }

        .editar-form h2 {
            text-align: center;
        }

        .editar-form label {
            display: block;
            margin-top: 15px;
        }

        .editar-form input[type="text"],
        .editar-form input[type="email"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .editar-form button {
            margin-top: 20px;
            width: 100%;
            padding: 10px;
            background-color: #0e2b3b;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .editar-form .mensagem {
            margin-top: 15px;
            text-align: center;
            color: green;
        }
    </style>
</head>

<body>
    <?php require '../partials/header.php'; ?>

    <form method="POST" class="editar-form">
        <h2>Editar Perfil</h2>

        <label for="nome">Nome:</label>
        <input type="text" name="nome" id="nome" value="<?php echo htmlspecialchars($row['nome']); ?>" required>

        <label for="user">Username:</label>
        <input type="text" name="user" id="user" value="<?php echo htmlspecialchars($row['user']); ?>" required>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($row['email']); ?>" required>

        <button type="submit">Guardar Alterações</button>

        <?php if (!empty($msg)) echo "<p class='mensagem'>$msg</p>"; ?>
    </form>
</body>

</html>
