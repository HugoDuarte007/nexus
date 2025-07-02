<?php
session_start();

if ($_SESSION["id_tipos_utilizador"] != 0) {
    header("Location: ../index.php");
    exit();
}

require "../ligabd.php";

$idutilizador = $_POST["idutilizador"];

$sql_existe = "SELECT * FROM banidos WHERE idutilizador = '$idutilizador'";
$existe = mysqli_query($con, $sql_existe);

if (!$existe) {
    $_SESSION["erro"] = "Erro ao verificar estado do utilizador.";
    header("Location: utilizadores.php");
    exit();
}

$n_registos = mysqli_num_rows($existe);

$sql_email = "SELECT email FROM utilizador WHERE idutilizador = '$idutilizador'";
$resultado_email = mysqli_query($con, $sql_email);

if (!$resultado_email || mysqli_num_rows($resultado_email) == 0) {
    $_SESSION["erro"] = "Erro ao obter o e-mail do utilizador.";
    header("Location: utilizadores.php");
    exit();
}

$dados_utilizador = mysqli_fetch_assoc($resultado_email);
$email_utilizador = $dados_utilizador["email"];

if ($n_registos > 0) {
    $sql_desbanir = "DELETE FROM banidos WHERE idutilizador = '$idutilizador'";
    $desbanir = mysqli_query($con, $sql_desbanir);

    if (!$desbanir) {
        $_SESSION["erro"] = "Não foi possível desbanir o utilizador.";
        header("Location: utilizadores.php");
        exit();
    }

    $_SESSION["sucesso"] = "Utilizador desbanido com sucesso.";
    $_SESSION["banido"] = false; // Adiciona esta linha
} else {
    $sql_banir = "INSERT INTO banidos (idbanimento, idutilizador) VALUES (NULL, '$idutilizador')";
    $banir = mysqli_query($con, $sql_banir);

    if (!$banir) {
        $_SESSION["erro"] = "Não foi possível banir o utilizador.";
        header("Location: utilizadores.php");
        exit();
    }

    $_SESSION["sucesso"] = "Utilizador banido com sucesso.";
    $_SESSION["banido"] = true; // Adiciona esta linha
}

header("Location: utilizadores.php");
exit();
?>