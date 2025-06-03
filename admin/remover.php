<?php

session_start(); 
if(!isset($_POST["botaoRemover"]) || !isset($_SESSION["user"]) || $_SESSION["id_tipos_utilizador"] != 0)
{
    header("Location: ../index.php");
    exit();
}

if($_POST["user"] == "admin"){
    header("Location: ../index.php");
    exit();
}

require "../ligabd.php"; 

$sql_remover = "DELETE FROM utilizador WHERE user='".$_POST["user"]."'";

$resultado = mysqli_query($con,$sql_remover);

if(!$resultado){
    $_SESSION["erro"] = "Não foi possivel remover o utilizador.";
    header("Location: ../index.php");
    exit();
}

header("Location: utilizadores.php");

?>