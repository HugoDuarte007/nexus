<?php
session_start();
require "../ligabd.php";

if (!isset($_SESSION["user"])) {
    header("Location: ../login.php");
    exit();
}

$utilizador = htmlspecialchars($_SESSION["user"]);

$query = "SELECT ft_perfil FROM utilizador WHERE user = '$utilizador'";
$result = mysqli_query($con, $query);

if ($result) {
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $foto_perfil = $row['ft_perfil'] ?? null;
} else {
    $foto_perfil = null;
}

$foto_base64 = $foto_perfil ? "data:image/jpeg;base64," . base64_encode($foto_perfil) : "default.png";


?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../imagens/favicon.ico" type="image/png">
    <link rel="stylesheet" type="text/css" href="../main/style.css">
    <title>Nexus | Perfil</title>
</head>
<header>
    <nav class="navbar">
        <a href="../main/main.php" style="color:white;text-decoration: none;">
            <h1 class="logo">Nexus</h1>
        </a>
        <button class="styled-button" onclick="window.location.href='perfil.php'">
            <div class="user-info">
                <span><?php echo htmlspecialchars($utilizador); ?></span>
                <img src="<?php echo $foto_base64; ?>" alt="Foto de Perfil" class="profile-picture">
            </div>
        </button>
    </nav>
</header>

<body>

</body>

</html>