<?php
session_start(); 

// Verificar se o utilizador está autenticado e é admin
if(!isset($_POST["botaoRemover"]) || !isset($_SESSION["user"]) || $_SESSION["id_tipos_utilizador"] != 0) {
    header("Location: ../index.php");
    exit();
}

// Impedir remoção do admin principal
if($_POST["user"] == "admin") {
    $_SESSION["erro"] = "Não é possível remover o utilizador admin principal.";
    header("Location: utilizadores.php");
    exit();
}

require "../ligabd.php"; 

$user = mysqli_real_escape_string($con, $_POST["user"]);

// Buscar o ID do utilizador
$sql_get_id = "SELECT idutilizador FROM utilizador WHERE user = '$user'";
$result_id = mysqli_query($con, $sql_get_id);

if(!$result_id || mysqli_num_rows($result_id) == 0) {
    $_SESSION["erro"] = "Utilizador não encontrado.";
    header("Location: utilizadores.php");
    exit();
}

$row = mysqli_fetch_assoc($result_id);
$idutilizador = $row['idutilizador'];

// Iniciar transação para garantir consistência
mysqli_autocommit($con, false);

try {
    // 15. Finalmente, remover o utilizador
    $sql_remover = "DELETE FROM utilizador WHERE user = '$user'";
    if(!mysqli_query($con, $sql_remover)) {
        throw new Exception("Erro ao remover utilizador: " . mysqli_error($con));
    }

    // Se chegou até aqui, commit da transação
    mysqli_commit($con);
    
    $_SESSION["sucesso"] = "Utilizador removido com sucesso!";
    
} catch (Exception $e) {
    // Rollback em caso de erro
    mysqli_rollback($con);
    $_SESSION["erro"] = $e->getMessage();
}

// Restaurar autocommit
mysqli_autocommit($con, true);

header("Location: utilizadores.php");
exit();
?>