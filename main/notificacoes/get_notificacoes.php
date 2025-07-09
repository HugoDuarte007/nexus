<?php
session_start();
require "../../ligabd.php";

// Forçar cabeçalho JSON e desativar exibição de erros
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(0);

if (!isset($_SESSION["user"])) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit();
}

$id_utilizador = $_SESSION['idutilizador'];

try {
    // Verificar se a tabela existe
    $check_table = "SHOW TABLES LIKE 'notificacoes'";
    $table_result = mysqli_query($con, $check_table);
    
    if (mysqli_num_rows($table_result) == 0) {
        // Criar tabela se não existir
        $create_table = "CREATE TABLE IF NOT EXISTS notificacoes (
            id_notificacao INT AUTO_INCREMENT PRIMARY KEY,
            id_utilizador INT NOT NULL,
            id_remetente INT NOT NULL,
            tipo ENUM('like', 'comentario', 'save', 'seguir') NOT NULL,
            id_publicacao INT NOT NULL,
            conteudo TEXT,
            vista BOOLEAN DEFAULT FALSE,
            data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_utilizador) REFERENCES utilizador(idutilizador) ON DELETE CASCADE,
            FOREIGN KEY (id_remetente) REFERENCES utilizador(idutilizador) ON DELETE CASCADE,
            FOREIGN KEY (id_publicacao) REFERENCES publicacao(idpublicacao) ON DELETE CASCADE
        )";
        
        if (!mysqli_query($con, $create_table)) {
            throw new Exception("Erro ao criar tabela de notificações");
        }
    }
    
    // Buscar notificações
    $query = "SELECT n.*, 
                     u.user as remetente_user, 
                     u.ft_perfil as remetente_foto,
                     p.descricao as publicacao_descricao
              FROM notificacoes n
              JOIN utilizador u ON n.id_remetente = u.idutilizador
              LEFT JOIN publicacao p ON n.id_publicacao = p.idpublicacao
              WHERE n.id_utilizador = ? 
              ORDER BY n.data_criacao DESC 
              LIMIT 20";
    
    $stmt = mysqli_prepare($con, $query);
    if (!$stmt) {
        throw new Exception("Erro na preparação da query: " . mysqli_error($con));
    }
    
    mysqli_stmt_bind_param($stmt, "i", $id_utilizador);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $notificacoes = [];
    $nao_vistas = 0;
    
    while ($row = mysqli_fetch_assoc($result)) {
        // Converter foto para base64 se existir
        if ($row['remetente_foto']) {
            $row['remetente_foto'] = base64_encode($row['remetente_foto']);
        }
        
        // Contar não vistas
        if (!$row['vista']) {
            $nao_vistas++;
        }
        
        // Formatar tempo
        $row['tempo_formatado'] = formatarTempo($row['data_criacao']);
        
        $notificacoes[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'notificacoes' => $notificacoes,
        'nao_vistas' => $nao_vistas
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar notificações: ' . $e->getMessage()
    ]);
}

function formatarTempo($data) {
    $agora = new DateTime();
    $tempo = new DateTime($data);
    $diff = $agora->diff($tempo);
    
    if ($diff->days > 0) {
        return $diff->days . ' dia' . ($diff->days > 1 ? 's' : '') . ' atrás';
    } elseif ($diff->h > 0) {
        return $diff->h . ' hora' . ($diff->h > 1 ? 's' : '') . ' atrás';
    } elseif ($diff->i > 0) {
        return $diff->i . ' minuto' . ($diff->i > 1 ? 's' : '') . ' atrás';
    } else {
        return 'Agora mesmo';
    }
}
?>