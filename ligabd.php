<?php
$servername = "localhost";
$username = "root";
$pass = "";

$con = mysqli_connect($servername, $username, $pass);

if (!$con) {
    die("Erro ao conectar ao MySQL" . mysqli_connect());
}

$escolheBD = mysqli_select_db($con, 'nexus');

if (!$escolheBD) {
    echo "Erro: não foi possivel aceder à Base de Dados!";
    exit();
}