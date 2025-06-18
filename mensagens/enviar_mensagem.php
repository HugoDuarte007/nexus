<?php
session_start();
require "../ligabd.php";

if (!isset($_SESSION["user"])) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $destinatario = (int)$_POST['destinatario'];
    $mensagem = trim($_POST['mensagem']);
    
    if (empty($mensagem)) {
        echo json_encode(['success' => false, 'message' => 'A mensagem não pode estar vazia']);
        exit();
    }
    
    // Buscar id do remetente
    $utilizador = htmlspecialchars($_SESSION["user"]);
    $query = "SELECT idutilizador FROM utilizador WHERE user = '$utilizador'";
    $result = mysqli_query($con, $query);
    $user_data = mysqli_fetch_assoc($result);
    $remetente = $user_data['idutilizador'];
    
    // Inserir mensagem
    $mensagem = mysqli_real_escape_string($con, $mensagem);
    $query = "INSERT INTO mensagem (idremetente, mensagem, dataenvio) 
              VALUES ($remetente, '$mensagem', NOW())";
    
    if (mysqli_query($con, $query)) {
        $id_mensagem = mysqli_insert_id($con);
        
                
        $query_dest = "INSERT INTO listadestinatarios (idmensagem, iddestinatario, lda) 
                       VALUES ($id_mensagem, $destinatario, 1)";
        
        if (mysqli_query($con, $query_dest)) {
            echo json_encode([
                'success' => true,
                'mensagem' => htmlspecialchars($mensagem),
                'hora' => date("H:i")
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao adicionar destinatário']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao enviar mensagem']);
    }
} else {
    header("Location: messages.php");
}
?>