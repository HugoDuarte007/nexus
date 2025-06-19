<?php
session_start();
require "../ligabd.php";

if (!isset($_SESSION["user"])) {
    header("Location: ../login.php");
    exit();
}

$iduser = htmlspecialchars($_SESSION["idutilizador"]);
$idperfil = htmlspecialchars($_SESSION["idutilizador"]);

if (isset($_GET["id"])) {
    $idperfil = $_GET["id"];
}

$isFollowing = false;
if ($iduser != $idperfil) {
    $checkFollow = "SELECT * FROM seguidor WHERE id_seguidor = '$iduser' AND id_seguido = '$idperfil'";
    $followResult = mysqli_query($con, $checkFollow);
    $isFollowing = mysqli_num_rows($followResult) > 0;
}

// Obter dados do perfil visualizado
$query = "SELECT * FROM utilizador WHERE idutilizador = '$idperfil'";
$result = mysqli_query($con, $query);
if ($result) {
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $perfil_foto_perfil = $row['ft_perfil'] ?? null;
    $perfil_foto_capa = $row['ft_capa'] ?? null;
    $perfil_nome = $row['nome'] ?? "Nome não disponível";
    $perfil_utilizador = $row['user'] ?? "Username não disponível";
    $perfil_telemovel = $row['telemovel'] ?? null;
    $perfil_data_nascimento = $row['data_nascimento'] ?? null;
    $perfil_pais = $row['pais'] ?? null;

    setlocale(LC_TIME, 'pt_PT.UTF-8', 'Portuguese_Portugal', 'Portuguese');
    $perfil_data_registo = isset($row['data_registo']) ? strftime("dia %e de %B de %Y", strtotime($row['data_registo'])) : "um dia.";

    // Formatar data de nascimento
    $perfil_data_nascimento_formatada = $perfil_data_nascimento ? strftime("%e de %B de %Y", strtotime($perfil_data_nascimento)) : null;
    $idade = $perfil_data_nascimento ? date_diff(date_create($perfil_data_nascimento), date_create('today'))->y : null;
} else {
    $perfil_foto_perfil = null;
    $perfil_foto_capa = null;
    $perfil_nome = "Erro ao carregar nome";
}

$perfil_foto_base64 = $perfil_foto_perfil ? "data:image/jpeg;base64," . base64_encode($perfil_foto_perfil) : "default.png";
$perfil_foto_capa_base64 = $perfil_foto_capa ? "data:image/jpeg;base64," . base64_encode($perfil_foto_capa) : "capa_default.jpg";

// Obter dados do usuário logado
$query = "SELECT * FROM utilizador WHERE idutilizador = '$iduser'";
$result = mysqli_query($con, $query);
if ($result) {
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $foto_perfil = $row['ft_perfil'] ?? null;
    $foto_capa = $row['ft_capa'] ?? null;
    $nome = $row['nome'] ?? "Nome não disponível";
    $utilizador = $row['user'] ?? "Username não disponível";

    setlocale(LC_TIME, 'pt_PT.UTF-8', 'Portuguese_Portugal', 'Portuguese');
    $data = isset($row['data_registo']) ? strftime("dia %e de %B de %Y", strtotime($row['data_registo'])) : "um dia.";
} else {
    $foto_perfil = null;
    $foto_capa = null;
    $nome = "Erro ao carregar nome";
}

$querySeguidores = "SELECT COUNT(*) as total FROM seguidor WHERE id_seguido = '$idperfil'";
$resultSeguidores = mysqli_query($con, $querySeguidores);
$totalSeguidores = mysqli_fetch_assoc($resultSeguidores)['total'];

$querySeguindo = "SELECT COUNT(*) as total FROM seguidor WHERE id_seguidor = '$idperfil'";
$resultSeguindo = mysqli_query($con, $querySeguindo);
$totalSeguindo = mysqli_fetch_assoc($resultSeguindo)['total'];

// Obter contagem de publicações
$queryPublicacoes = "SELECT COUNT(*) as total FROM publicacao WHERE idutilizador = '$idperfil'";
$resultPublicacoes = mysqli_query($con, $queryPublicacoes);
$totalPublicacoes = mysqli_fetch_assoc($resultPublicacoes)['total'];

// Obter publicações do perfil
$queryPublicacoes = "SELECT p.*, u.user, u.ft_perfil 
                    FROM publicacao p
                    JOIN utilizador u ON p.idutilizador = u.idutilizador
                    WHERE p.idutilizador = '$idperfil'
                    ORDER BY p.data DESC";
$resultPublicacoes = mysqli_query($con, $queryPublicacoes);
$publicacoes = [];
if ($resultPublicacoes) {
    $publicacoes = mysqli_fetch_all($resultPublicacoes, MYSQLI_ASSOC);
}

$foto_base64 = $foto_perfil ? "data:image/jpeg;base64," . base64_encode($foto_perfil) : "default.png";
$foto_capa_base64 = $foto_capa ? "data:image/jpeg;base64," . base64_encode($foto_capa) : "capa_default.jpg";

// Função para verificar se é vídeo
function isVideo($filename) {
    if (empty($filename)) return false;
    $videoExtensions = ['mp4', 'webm', 'ogg', 'avi', 'mov'];
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($extension, $videoExtensions);
}

// Função para verificar se é imagem
function isImage($filename) {
    if (empty($filename)) return false;
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($extension, $imageExtensions);
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../imagens/favicon.ico" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Nexus | Perfil</title>
    <style>
        :root {
            --primary-color: #0e2b3b;
            --secondary-color: #1a5276;
            --accent-color: #2980b9;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .cover-container {
            position: relative;
            width: 100%;
            height: 350px;
            overflow: hidden;
            background-color: var(--secondary-color);
        }

        .cover-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .cover-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .cover-container:hover .cover-overlay {
            opacity: 1;
        }

        .profile-main {
            max-width: 1200px;
            margin: -100px auto 30px;
            position: relative;
            padding: 0 20px;
        }

        .profile-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            position: relative;
        }

        .profile-picture-container {
            position: relative;
            margin-top: -100px;
            z-index: 2;
            width: 200px;
            height: 200px;
        }

        .profile-picture {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            border: 5px solid white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        .profile-picture-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .profile-picture-container:hover .profile-picture-overlay {
            opacity: 1;
        }

        .profile-picture-container:hover .profile-picture {
            transform: scale(1.05);
        }

        .profile-info {
            text-align: center;
            margin-top: 20px;
            width: 100%;
        }

        .profile-name {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            color: var(--dark-color);
        }

        .profile-username {
            font-size: 18px;
            color: var(--accent-color);
            margin: 5px 0;
        }

        .profile-stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin: 20px 0;
        }

        .stat-item {
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .stat-item:hover {
            transform: scale(1.05);
        }

        .stat-number {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
        }

        .stat-label {
            font-size: 14px;
            color: #666;
        }

        .profile-bio {
            max-width: 600px;
            margin: 0 auto;
            color: #555;
            line-height: 1.6;
        }

        .profile-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .detail-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }

        .detail-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .detail-item {
            display: flex;
            align-items: center;
            margin: 12px 0;
        }

        .detail-icon {
            width: 24px;
            height: 24px;
            margin-right: 10px;
            color: var(--accent-color);
        }

        .detail-text {
            flex: 1;
        }

        .btn-edit {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: var(--accent-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .btn-edit:hover {
            background-color: var(--primary-color);
            transform: rotate(90deg);
        }

        #fileInput,
        #fileInputCapa {
            display: none;
        }

        /* Estilos compactos para posts no perfil */
        .perfil-posts {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .perfil-post {
            background: white;
            padding: 12px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            display: flex;
            flex-direction: column;
            height: auto;
        }

        .perfil-post-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        .perfil-post-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }

        .perfil-post-user {
            font-weight: 600;
            font-size: 14px;
        }

        .perfil-post-time {
            color: #6b7280;
            font-size: 12px;
            margin-left: auto;
        }

        .perfil-post-content {
            font-size: 14px;
            line-height: 1.4;
            margin-bottom: 8px;
            word-break: break-word;
        }

        /* Container da mídia para tamanho fixo */
        .perfil-post-media-container {
            width: 100%;
            height: 250px;
            overflow: hidden;
            border-radius: 6px;
            margin-top: 8px;
            background-color: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Imagem dentro do container */
        .perfil-post-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Vídeo dentro do container */
        .perfil-post-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 6px;
        }

        .perfil-post-actions {
            display: flex;
            justify-content: space-around;
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid #f3f4f6;
        }

        .perfil-post-action {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            color: #4b5563;
            cursor: pointer;
        }

        .perfil-post-action svg {
            width: 16px;
            height: 16px;
        }

        @media (max-width: 768px) {
            .cover-container {
                height: 250px;
            }

            .profile-picture {
                width: 140px;
                height: 140px;
            }

            .profile-name {
                font-size: 24px;
            }

            .profile-username {
                font-size: 16px;
            }

            .profile-stats {
                gap: 15px;
            }

            .stat-number {
                font-size: 20px;
            }

            .perfil-posts {
                grid-template-columns: 1fr;
            }
        }

        .delete-post-btn {
            background: none;
            border: none;
            color: #dc3545;
            cursor: pointer;
            padding: 4px;
            margin-left: 10px;
            transition: all 0.2s;
        }

        .delete-post-btn:hover {
            color: #a71d2a;
            transform: scale(1.1);
        }

        .delete-post-btn svg {
            width: 16px;
            height: 16px;
            vertical-align: middle;
        }

        /* Indicador de tipo de mídia */
        .media-type-indicator {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .perfil-post-media-container {
            position: relative;
        }

        /* Estilos do Modal de Seguidores */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            max-height: 80vh;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #f8f9fa;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6b7280;
            transition: color 0.2s;
            padding: 5px;
            border-radius: 50%;
        }

        .close:hover {
            color: #1f2937;
            background-color: #f3f4f6;
        }

        .modal-body {
            padding: 0;
            max-height: 60vh;
            overflow-y: auto;
        }

        .user-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .user-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #f3f4f6;
            transition: background-color 0.2s;
            text-decoration: none;
            color: inherit;
        }

        .user-item:hover {
            background-color: #f8f9fa;
        }

        .user-item:last-child {
            border-bottom: none;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
            border: 2px solid #e5e7eb;
        }

        .user-info {
            flex: 1;
        }

        .user-name {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 2px;
        }

        .user-username {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .follow-btn {
            padding: 8px 16px;
            border: 1px solid var(--accent-color);
            background-color: transparent;
            color: var(--accent-color);
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .follow-btn:hover {
            background-color: var(--accent-color);
            color: white;
        }

        .follow-btn.following {
            background-color: #e5e7eb;
            color: #6b7280;
            border-color: #e5e7eb;
        }

        .follow-btn.following:hover {
            background-color: #dc3545;
            color: white;
            border-color: #dc3545;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #d1d5db;
        }

        .loading {
            text-align: center;
            padding: 20px;
            color: #6b7280;
        }
    </style>
</head>

<body>
    <?php require '../partials/header.php'; ?>

    <div class="cover-container" onclick="document.getElementById('fileInputCapa').click();">
        <img src="<?php echo $perfil_foto_capa_base64; ?>" alt="Foto de Capa" class="cover-photo">
        <?php if ($perfil_utilizador == $utilizador): ?>
            <div class="cover-overlay">
                <i class="fas fa-camera fa-2x" style="color: white;"></i>
            </div>
            <input type="file" id="fileInputCapa" accept="image/*"
                onchange="uploadImage('fileInputCapa', 'upload_capa.php')">
        <?php endif; ?>
    </div>

    <div class="profile-main">
        <div class="profile-header">
            <?php if ($perfil_utilizador == $utilizador): ?>
                <button class="btn-edit" onclick="window.location.href='editar_perfil.php'">
                    <i class="fas fa-cog"></i>
                </button>
            <?php endif; ?>

            <div class="profile-picture-container" onclick="document.getElementById('fileInput').click();">
                <img src="<?php echo $perfil_foto_base64; ?>" alt="Foto de Perfil" class="profile-picture">
                <?php if ($perfil_utilizador == $utilizador): ?>
                    <div class="profile-picture-overlay">
                        <i class="fas fa-camera fa-2x" style="color: white;"></i>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($perfil_utilizador == $utilizador): ?>
                <input type="file" id="fileInput" accept="image/*" onchange="uploadImage('fileInput', 'upload_foto.php')">
            <?php endif; ?>

            <div class="profile-info">
                <h1 class="profile-name"><?php echo htmlspecialchars($perfil_nome); ?></h1>
                <p class="profile-username">@<?php echo htmlspecialchars($perfil_utilizador); ?></p>

                <?php if ($iduser != $idperfil): ?>
                    <button id="botaoSeguir" onclick="seguirUtilizador(<?= $idperfil ?>)" class="<?= $isFollowing ? 'bg-gray-200 text-gray-800' : 'bg-blue-500 text-white hover:bg-blue-600' ?> 
                       px-6 py-2 rounded-full text-sm font-medium mt-3 transition-colors duration-300">
                        <?= $isFollowing ? 'Seguindo ✓' : 'Seguir' ?>
                    </button>
                <?php endif; ?>
                <div class="profile-stats">
                    <div class="stat-item">
                        <div class="stat-number"><?= $totalPublicacoes ?></div>
                        <div class="stat-label">Publicações</div>
                    </div>
                    <div class="stat-item" onclick="abrirModalSeguidores()">
                        <div class="stat-number"><?= $totalSeguidores ?></div>
                        <div class="stat-label">Seguidores</div>
                    </div>
                    <div class="stat-item" onclick="abrirModalSeguindo()">
                        <div class="stat-number"><?= $totalSeguindo ?></div>
                        <div class="stat-label">Seguindo</div>
                    </div>
                </div>

                <p class="profile-bio">Utilizador da NEXUS desde <?php echo $perfil_data_registo ?></p>
            </div>
        </div>

        <div class="profile-details">
            <div class="detail-card">
                <h3 class="detail-title">Publicações</h3>
                <?php if (count($publicacoes) > 0): ?>
                    <div class="perfil-posts">
                        <?php foreach ($publicacoes as $pub): ?>
                            <?php
                            $sql = "SELECT * FROM comentario WHERE idpublicacao = " . $pub['idpublicacao'];
                            $comentarios = mysqli_fetch_all(mysqli_query($con, $sql), MYSQLI_ASSOC);

                            $sql1 = "SELECT * FROM likes WHERE idpublicacao = " . $pub['idpublicacao'];
                            $like = mysqli_fetch_all(mysqli_query($con, $sql1), MYSQLI_ASSOC);
                            ?>

                            <div class="perfil-post flex flex-col justify-between" id="post_<?= $pub['idpublicacao'] ?>">
                                <div>
                                    <div class="perfil-post-header">
                                        <img src="<?= $pub['ft_perfil'] ? 'data:image/jpeg;base64,' . base64_encode($pub['ft_perfil']) : 'default.png'; ?>"
                                            alt="Foto de Perfil" class="perfil-post-avatar">
                                        <span class="perfil-post-user"><?= htmlspecialchars($pub['user']); ?></span>
                                        <span class="perfil-post-time"><?= date("d/m/Y H:i", strtotime($pub['data'])); ?></span>
    
                                        <?php if ($perfil_utilizador == $utilizador): ?>
                                            <button class="delete-post-btn" onclick="confirmarDelete(<?= $pub['idpublicacao'] ?>)">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                                    viewBox="0 0 16 16">
                                                    <path
                                                        d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z" />
                                                    <path fill-rule="evenodd"
                                                        d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z" />
                                                </svg>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <div class="perfil-post-content">
                                        <?= nl2br(htmlspecialchars($pub['descricao'])); ?>
                                    </div>
    
                                    <?php if (!empty($pub['media'])): ?>
                                        <div class="perfil-post-media-container">
                                            <?php if (isVideo($pub['media'])): ?>
                                                <div class="media-type-indicator">Vídeo</div>
                                                <video class="perfil-post-video" controls preload="metadata">
                                                    <source src="../main/publicacoes/<?= htmlspecialchars($pub['media']); ?>" type="video/<?= pathinfo($pub['media'], PATHINFO_EXTENSION); ?>">
                                                    Seu navegador não suporta o elemento de vídeo.
                                                </video>
                                            <?php elseif (isImage($pub['media'])): ?>
                                                <div class="media-type-indicator">Imagem</div>
                                                <img src="../main/publicacoes/<?= htmlspecialchars($pub['media']); ?>"
                                                    class="perfil-post-image" alt="Imagem da publicação">
                                            <?php else: ?>
                                                <div class="media-type-indicator">Arquivo</div>
                                                <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #6b7280;">
                                                    <i class="fas fa-file fa-3x"></i>
                                                    <p style="margin-left: 10px;">Arquivo: <?= htmlspecialchars($pub['media']); ?></p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div>
                                    <div class="perfil-post-actions">
                                        <div class="perfil-post-action" onclick="abrirPublicacao(<?= $pub['idpublicacao'] ?>)">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M20 2H4a2 2 0 0 0-2 2v15.17L5.17 16H20a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z" />
                                            </svg>
                                            <span><?= count($comentarios) ?></span>
                                        </div>
                                        <div class="perfil-post-action">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                                <path
                                                    d="M23 7l-5-5v3H6c-1.1 0-2 .9-2 2v5h2V7h12v3l5-5zM1 17l5 5v-3h12c1.1 0 2-.9 2-2v-5h-2v5H6v-3l-5 5z" />
                                            </svg>
                                            <span>0</span>
                                        </div>
                                        <div class="perfil-post-action">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                                <path
                                                    d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41 0.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                                            </svg>
                                            <span><?= count($like) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="detail-item">
                        <i class="fas fa-info-circle detail-icon"></i>
                        <div class="detail-text">
                            Nenhuma publicação encontrada.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal de Seguidores -->
    <div id="modalSeguidores" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Seguidores</h2>
                <button class="close" onclick="fecharModal('modalSeguidores')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="loading" id="loadingSeguidores">A carregar seguidores...</div>
                <ul class="user-list" id="listaSeguidores"></ul>
            </div>
        </div>
    </div>

    <!-- Modal de Seguindo -->
    <div id="modalSeguindo" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Seguindo</h2>
                <button class="close" onclick="fecharModal('modalSeguindo')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="loading" id="loadingSeguindo">A carregar utilizadores...</div>
                <ul class="user-list" id="listaSeguindo"></ul>
            </div>
        </div>
    </div>

    <script>
        function uploadImage(inputId, uploadUrl) {
            let fileInput = document.getElementById(inputId);
            if (fileInput.files.length === 0) return;

            let formData = new FormData();
            formData.append("file", fileInput.files[0]);

            fetch(uploadUrl, {
                method: "POST",
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (uploadUrl === 'upload_foto.php') {
                            document.querySelector('.profile-picture').src = URL.createObjectURL(fileInput.files[0]);
                        } else if (uploadUrl === 'upload_capa.php') {
                            document.querySelector('.cover-photo').src = URL.createObjectURL(fileInput.files[0]);
                        }
                    } else {
                        alert("Erro ao atualizar a imagem: " + data.message);
                    }
                })
                .catch(error => console.error("Erro:", error));
        }

        function seguirUtilizador(idSeguido) {
            fetch('../main/interacoes/seguir.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id_seguido=' + encodeURIComponent(idSeguido)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const botao = document.getElementById('botaoSeguir');
                        if (botao) {
                            botao.textContent = data.seguindo ? 'A seguir ✓' : 'Seguir';
                            botao.className = data.seguindo ?
                                'bg-gray-200 text-gray-800 px-6 py-2 rounded-full text-sm font-medium mt-3 transition-colors duration-300' :
                                'bg-blue-500 text-white hover:bg-blue-600 px-6 py-2 rounded-full text-sm font-medium mt-3 transition-colors duration-300';

                            const statSeguidores = document.querySelectorAll('.stat-item')[1].querySelector('.stat-number');
                            if (statSeguidores) {
                                statSeguidores.textContent = data.seguindo ?
                                    parseInt(statSeguidores.textContent) + 1 :
                                    parseInt(statSeguidores.textContent) - 1;
                            }
                        }
                    } else {
                        console.error('Erro:', data.message);
                    }
                })
                .catch(error => console.error('Erro na requisição:', error));
        }

        function abrirPublicacao(pubid) {
            // Implemente a mesma função que está no main.php ou ajuste conforme necessário
            console.log('Abrir publicação:', pubid);
            // window.location.href = 'publicacao.php?id=' + pubid;
        }
        
        function confirmarDelete(idPublicacao) {
            if (confirm('Tem certeza que deseja excluir esta publicação?')) {
                deletarPublicacao(idPublicacao);
            }
        }

        function deletarPublicacao(idPublicacao) {
            fetch('../main/interacoes/apagar_publicacao.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id_publicacao=' + idPublicacao
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove a publicação da página
                        document.getElementById('post_' + idPublicacao).remove();

                        // Atualiza o contador de publicações
                        const contador = document.querySelector('.stat-number');
                        if (contador) {
                            contador.textContent = parseInt(contador.textContent) - 1;
                        }
                    } else {
                        alert('Erro ao deletar: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao conectar com o servidor');
                });
        }

        // Funções para os modais de seguidores
        function abrirModalSeguidores() {
            document.getElementById('modalSeguidores').style.display = 'block';
            carregarSeguidores();
        }

        function abrirModalSeguindo() {
            document.getElementById('modalSeguindo').style.display = 'block';
            carregarSeguindo();
        }

        function fecharModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function carregarSeguidores() {
            const loading = document.getElementById('loadingSeguidores');
            const lista = document.getElementById('listaSeguidores');
            
            loading.style.display = 'block';
            lista.innerHTML = '';

            fetch(`get_seguidores.php?id=<?= $idperfil ?>&tipo=seguidores`)
                .then(response => response.json())
                .then(data => {
                    loading.style.display = 'none';
                    
                    if (data.success && data.users.length > 0) {
                        data.users.forEach(user => {
                            const userItem = criarItemUtilizador(user);
                            lista.appendChild(userItem);
                        });
                    } else {
                        lista.innerHTML = `
                            <div class="empty-state">
                                <i class="fas fa-users"></i>
                                <p>Nenhum seguidor encontrado</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    loading.style.display = 'none';
                    lista.innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>Erro ao carregar seguidores</p>
                        </div>
                    `;
                });
        }

        function carregarSeguindo() {
            const loading = document.getElementById('loadingSeguindo');
            const lista = document.getElementById('listaSeguindo');
            
            loading.style.display = 'block';
            lista.innerHTML = '';

            fetch(`get_seguidores.php?id=<?= $idperfil ?>&tipo=seguindo`)
                .then(response => response.json())
                .then(data => {
                    loading.style.display = 'none';
                    
                    if (data.success && data.users.length > 0) {
                        data.users.forEach(user => {
                            const userItem = criarItemUtilizador(user);
                            lista.appendChild(userItem);
                        });
                    } else {
                        lista.innerHTML = `
                            <div class="empty-state">
                                <i class="fas fa-user-plus"></i>
                                <p>Não está a seguir ninguém</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    loading.style.display = 'none';
                    lista.innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>Erro ao carregar utilizadores</p>
                        </div>
                    `;
                });
        }

        function criarItemUtilizador(user) {
            const li = document.createElement('li');
            
            const link = document.createElement('a');
            link.href = `perfil.php?id=${user.idutilizador}`;
            link.className = 'user-item';
            
            link.innerHTML = `
                <img src="${user.ft_perfil ? 'data:image/jpeg;base64,' + user.ft_perfil : 'default.png'}" 
                     alt="Foto de perfil" class="user-avatar">
                <div class="user-info">
                    <div class="user-name">${user.nome}</div>
                    <div class="user-username">@${user.user}</div>
                </div>
            `;

            // Adicionar botão de seguir apenas se não for o próprio utilizador
            if (user.idutilizador != <?= $iduser ?>) {
                const followBtn = document.createElement('button');
                followBtn.className = user.is_following ? 'follow-btn following' : 'follow-btn';
                followBtn.textContent = user.is_following ? 'Seguindo' : 'Seguir';
                followBtn.onclick = function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    seguirUtilizadorModal(user.idutilizador, followBtn);
                };
                
                link.appendChild(followBtn);
            }
            
            li.appendChild(link);
            return li;
        }

        function seguirUtilizadorModal(idSeguido, button) {
            fetch('../main/interacoes/seguir.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id_seguido=' + encodeURIComponent(idSeguido)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        button.textContent = data.seguindo ? 'Seguindo' : 'Seguir';
                        button.className = data.seguindo ? 'follow-btn following' : 'follow-btn';
                    } else {
                        console.error('Erro:', data.message);
                    }
                })
                .catch(error => console.error('Erro na requisição:', error));
        }

        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            const modalSeguidores = document.getElementById('modalSeguidores');
            const modalSeguindo = document.getElementById('modalSeguindo');
            
            if (event.target === modalSeguidores) {
                modalSeguidores.style.display = 'none';
            }
            if (event.target === modalSeguindo) {
                modalSeguindo.style.display = 'none';
            }
        }
    </script>

</body>

</html>