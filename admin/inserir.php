<?php
    session_start();

    if ($_SESSION["id_tipos_utilizador"] != 0) {
        header("Location: ../index.php");
        exit(); 
    }

    require "../ligabd.php"; 

    $user = $_POST["user"];
    $nome = $_POST["nome"];
    $email = $_POST["email"];
    $telemovel = $_POST["telemovel"];
    $idade = $_POST["idade"];
    $password = $_POST["password"];
    $id_tipos_utilizador = $_POST["id_tipos_utilizador"];

    // Verificar se o utilizador ou o email já existem
    $sql_existe = "SELECT * FROM utilizador WHERE user = '$user' OR email = '$email'";

    $existe = mysqli_query($con, $sql_existe); 

    if (!$existe) {
        $_SESSION["erro"] = "Erro ao verificar se o utilizador ou email já existem.";
        header("Location: ../index.php");
        exit();
    }

    $n_registos = mysqli_num_rows($existe);

    if ($n_registos > 0) {
        $_SESSION["erro"] = "O nome de utilizador ou o email já estão registados.";
        header("Location: utilizadores.php");
        exit();
    }

    // Inserir novo utilizador
    $sql_inserir = "INSERT INTO utilizador (idutilizador, nome, email, user, telemovel, idade, pass, ft_perfil, id_tipos_utilizador) 
                    VALUES (NULL, '$nome', '$email', '$user', '$telemovel', '$idade', PASSWORD('$password'), NULL, NULL, '$id_tipos_utilizador')";

    $inserir = mysqli_query($con, $sql_inserir); 

    if (!$inserir) {
        $_SESSION["erro"] = "Não foi possível inserir o utilizador.";
        header("Location: utilizadores.php");
        exit();
    }

    header("Location: utilizadores.php");
    exit();
?>
