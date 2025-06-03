<?php

if(isset($_GET["id"])){
    header("Location: perfil.php?id=$id");
    exit();
}


header("Location: perfil.php");
exit();
?>