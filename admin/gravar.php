<?php
session_start();

if (!isset($_SESSION["user"]) || !isset($_POST["botaoGravar"]) || $_SESSION["id_tipos_utilizador"] != 0) {
    header("Location: ../index.php");
    exit();
}

// Impedir edição não autorizada
if ($_POST["user"] == "admin" && $_SESSION["utilizador"] != "admin") {
    header("Location: ../index.php");
    exit();
}

require "../ligabd.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = test_input($_POST["nome"]);
    $email = test_input($_POST["email"]);
    $user = test_input($_POST["user"]);
    $telemovel = test_input($_POST["telemovel"]);
    $data_nascimento = test_input($_POST["data_nascimento"]);
    $password = test_input($_POST["password"]);
    $id_tipos_utilizador = test_input($_POST["id_tipos_utilizador"]);
}

function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$g_pass = "pass = password('$password'),";

if ($password == "") {
    $g_pass = "";
}

$gravar = "
    UPDATE utilizador SET 
        nome = '$nome', 
        email = '$email', 
        user = '$user', 
        telemovel = '$telemovel', 
        data_nascimento = '$data_nascimento', 
        $g_pass
        id_tipos_utilizador = '$id_tipos_utilizador'
    WHERE 
        email = '$email'
";

$resultado_gravar = mysqli_query($con, $gravar);

if (!$resultado_gravar) {
    $_SESSION["erro"] = "Não foi possível atualizar os dados.";
    header("Location: ../index.php");
    exit();
}

header("Location: utilizadores.php");
?>