<?php
session_start();
require "../ligabd.php";

if (!isset($_SESSION["user"])) {
    header("Location: ../login.php");
    exit();
}
function formatarDataEmPortugues($dataString, $prefixo = '', $sufixo = '')
{
    if (!$dataString)
        return null;

    $meses = [
        1 => 'janeiro',
        2 => 'fevereiro',
        3 => 'março',
        4 => 'abril',
        5 => 'maio',
        6 => 'junho',
        7 => 'julho',
        8 => 'agosto',
        9 => 'setembro',
        10 => 'outubro',
        11 => 'novembro',
        12 => 'dezembro'
    ];

    $data = new DateTime($dataString);
    $dia = (int) $data->format('d'); // sem zero à esquerda
    $mes = $meses[(int) $data->format('m')];
    $ano = $data->format('Y');

    return trim("$prefixo $dia de $mes de $ano $sufixo");
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
    $perfil_data_registo = isset($row['data_registo'])
        ? formatarDataEmPortugues($row['data_registo'], 'dia')
        : "um dia.";
    // Formatar data de nascimento
    $perfil_data_nascimento_formatada = $perfil_data_nascimento
        ? formatarDataEmPortugues($perfil_data_nascimento)
        : null;
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
    $data = isset($row['data_registo'])
        ? formatarDataEmPortugues($row['data_registo'], 'dia')
        : "um dia.";
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

// Verificar se está visualizando publicações normais ou guardadas
$viewingSaved = isset($_GET['view']) && $_GET['view'] === 'saved';
$publicacoesTitle = $viewingSaved ? 'Publicações Guardadas' : 'Publicações';

// Obter contagem de publicações
$queryPublicacoes = "SELECT COUNT(*) as total FROM publicacao WHERE idutilizador = '$idperfil'";
$resultPublicacoes = mysqli_query($con, $queryPublicacoes);
$totalPublicacoes = mysqli_fetch_assoc($resultPublicacoes)['total'];

// Obter contagem de publicações guardadas
$querySavedPosts = "SELECT COUNT(*) as total FROM guardado WHERE idutilizador = '$idperfil'";
$resultSavedPosts = mysqli_query($con, $querySavedPosts);
$totalSavedPosts = mysqli_fetch_assoc($resultSavedPosts)['total'];

// Obter publicações do perfil (normais ou guardadas)
if ($viewingSaved) {
    // Publicações guardadas
    $queryPublicacoes = "SELECT p.*, u.user, u.ft_perfil 
                        FROM publicacao p
                        JOIN utilizador u ON p.idutilizador = u.idutilizador
                        JOIN guardado g ON p.idpublicacao = g.idpublicacao
                        WHERE g.idutilizador = '$idperfil'
                        ORDER BY g.data_guardado DESC";
} else {
    // Publicações normais
    $queryPublicacoes = "SELECT p.*, u.user, u.ft_perfil 
                        FROM publicacao p
                        JOIN utilizador u ON p.idutilizador = u.idutilizador
                        WHERE p.idutilizador = '$idperfil'
                        ORDER BY p.data DESC";
}

$resultPublicacoes = mysqli_query($con, $queryPublicacoes);
$publicacoes = [];
if ($resultPublicacoes) {
    $publicacoes = mysqli_fetch_all($resultPublicacoes, MYSQLI_ASSOC);
}

$foto_base64 = $foto_perfil ? "data:image/jpeg;base64," . base64_encode($foto_perfil) : "default.png";
$foto_capa_base64 = $foto_capa ? "data:image/jpeg;base64," . base64_encode($foto_capa) : "capa_default.jpg";

// Função para verificar se é vídeo
function isVideo($filename)
{
    if (empty($filename))
        return false;
    $videoExtensions = ['mp4', 'webm', 'ogg', 'avi', 'mov'];
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($extension, $videoExtensions);
}

// Função para verificar se é imagem
function isImage($filename)
{
    if (empty($filename))
        return false;
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
    <link rel="stylesheet" type="text/css" href="style.css">
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
                        <?= $isFollowing ? 'A seguir ✓' : 'Seguir' ?>
                    </button>
                <?php endif; ?>
                <?php if ($iduser != $idperfil): ?>
                    <button onclick="window.location.href='../mensagens/mensagens.php?destinatario=<?= $idperfil ?>'"
                        class="bg-green-500 text-white hover:bg-green-600 px-6 py-2 rounded-full text-sm font-medium mt-3 transition-colors duration-300 ml-2">
                        <i class="fas fa-envelope"></i> Mensagem
                    </button>
                <?php endif; ?>
                <div class="profile-stats">
                    <div class="stat-item">
                        <div class="stat-number"><?= $totalPublicacoes ?></div>
                        <!-- Sempre mostra o total de publicações -->
                        <div class="stat-label">Publicações</div>
                    </div>
                    <div class="stat-item" onclick="abrirModalSeguidores()">
                        <div class="stat-number"><?= $totalSeguidores ?></div>
                        <div class="stat-label">Seguidores</div>
                    </div>
                    <div class="stat-item" onclick="abrirModalSeguindo()">
                        <div class="stat-number"><?= $totalSeguindo ?></div>
                        <div class="stat-label">A seguir</div>
                    </div>
                </div>

                <p class="profile-bio">Utilizador da NEXUS desde <?php echo $perfil_data_registo ?></p>
            </div>
        </div>

        <div class="profile-details">
            <div class="detail-card">
                <div class="flex gap-4 mb-4">
                    <button
                        onclick="window.location.href='perfil.php<?= $idperfil != $iduser ? '?id=' . $idperfil : '' ?>'"
                        class="<?= !$viewingSaved ? 'bg-[#0e2b3b] text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' ?> px-4 py-2 rounded-lg font-medium transition-colors">
                        Publicações
                    </button>
                    <?php if ($idperfil == $iduser): ?>
                        <button
                            onclick="window.location.href='perfil.php<?= $idperfil != $iduser ? '?id=' . $idperfil : '' ?>?view=saved'"
                            class="<?= $viewingSaved ? 'bg-[#0e2b3b] text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' ?> px-4 py-2 rounded-lg font-medium transition-colors">
                            Publicações Guardadas (<?= $totalSavedPosts ?>)
                        </button>
                    <?php endif; ?>
                </div>

                <?php if (count($publicacoes) > 0): ?>
                    <div class="perfil-posts">
                        <?php foreach ($publicacoes as $pub): ?>
                            <?php
                            $sql = "SELECT * FROM comentario WHERE idpublicacao = " . $pub['idpublicacao'];
                            $comentarios = mysqli_fetch_all(mysqli_query($con, $sql), MYSQLI_ASSOC);

                            $sql1 = "SELECT * FROM likes WHERE idpublicacao = " . $pub['idpublicacao'];
                            $like = mysqli_fetch_all(mysqli_query($con, $sql1), MYSQLI_ASSOC);

                            // Buscar mídias da publicação
                            $idpub = $pub['idpublicacao'];
                            $sql_medias = "SELECT * FROM publicacao_media WHERE idpublicacao = $idpub ORDER BY ordem ASC";
                            $result_medias = mysqli_query($con, $sql_medias);
                            $medias = [];

                            while ($media = mysqli_fetch_assoc($result_medias)) {
                                $medias[] = $media;
                            }

                            // Se não houver mídias na nova tabela, verificar na tabela antiga
                            if (empty($medias) && !empty($pub['media'])) {
                                $extensao = strtolower(pathinfo($pub['media'], PATHINFO_EXTENSION));
                                $extensoes_video = ['mp4', 'mov', 'avi', 'webm'];
                                $tipo = in_array($extensao, $extensoes_video) ? 'video' : 'imagem';

                                $medias[] = [
                                    'media' => $pub['media'],
                                    'tipo' => $tipo,
                                    'ordem' => 1
                                ];
                            }
                            ?>

                            <div class="perfil-post flex flex-col justify-between" id="post_<?= $pub['idpublicacao'] ?>">
                                <div>
                                    <div class="perfil-post-header">
                                        <img src="<?= $pub['ft_perfil'] ? 'data:image/jpeg;base64,' . base64_encode($pub['ft_perfil']) : 'default.png'; ?>"
                                            alt="Foto de Perfil" class="perfil-post-avatar">
                                        <span class="perfil-post-user"><?= htmlspecialchars($pub['user']); ?></span>
                                        <span class="perfil-post-time"><?= date("d/m/Y H:i", strtotime($pub['data'])); ?></span>

                                        <?php if ($perfil_utilizador == $utilizador && !$viewingSaved): ?>
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

                                        <?php if ($viewingSaved): ?>
                                            <button class="save-post-btn"
                                                onclick="removerDosGuardados(<?= $pub['idpublicacao'] ?>)">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#FFA500"
                                                    viewBox="0 0 16 16">
                                                    <path
                                                        d="M2 2v13.5a.5.5 0 0 0 .74.439L8 13.069l5.26 2.87A.5.5 0 0 0 14 15.5V2a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z" />
                                                </svg>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <div class="perfil-post-content">
                                        <?= nl2br(htmlspecialchars(strval($pub['descricao']))); ?>
                                    </div>

                                    <?php if (!empty($medias)): ?>
                                        <div class="perfil-post-media-container">
                                            <?php
                                            $total_medias = count($medias);
                                            $grid_class = 'single';

                                            if ($total_medias == 2) {
                                                $grid_class = 'double';
                                            } elseif ($total_medias == 3) {
                                                $grid_class = 'triple';
                                            } elseif ($total_medias >= 4) {
                                                $grid_class = 'multiple';
                                            }
                                            ?>

                                            <div class="media-grid <?= $grid_class ?>" data-post-id="<?= $pub['idpublicacao'] ?>"
                                                onclick="abrirModalImagem(<?= $pub['idpublicacao'] ?>, 0)">
                                                <?php
                                                $medias_to_show = ($total_medias > 4) ? array_slice($medias, 0, 4) : $medias;
                                                foreach ($medias_to_show as $index => $media):
                                                    ?>
                                                    <div
                                                        class="media-item <?= ($grid_class == 'triple' && $index == 0) ? 'first-triple' : '' ?>">
                                                        <?php if ($media['tipo'] == 'video'): ?>
                                                            <video muted>
                                                                <source src="../main/publicacoes/<?= $media['media'] ?>" type="video/mp4">
                                                                Seu navegador não suporta o elemento de vídeo.
                                                            </video>
                                                            <div class="media-type-indicator">Vídeo</div>
                                                        <?php else: ?>
                                                            <img src="../main/publicacoes/<?= $media['media'] ?>"
                                                                alt="Imagem da publicação">
                                                            <div class="media-type-indicator">Imagem</div>
                                                        <?php endif; ?>

                                                        <?php if ($total_medias > 4 && $index == 3): ?>
                                                            <div class="media-overlay">
                                                                +<?= $total_medias - 4 ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div>
                                    <div class="perfil-post-actions">
                                        <div class="perfil-post-action" onclick="toggleLike(<?= $pub['idpublicacao'] ?>, this)">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                                <path
                                                    d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41 0.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                                            </svg>
                                            <span class="like-count"><?= count($like) ?></span>
                                        </div>
                                        <div class="perfil-post-action"
                                            onclick="abrirModalVerPublicacao(<?= $pub['idpublicacao'] ?>)">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                                <path
                                                    d="M20 2H4a2 2 0 0 0-2 2v15.17L5.17 16H20a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z" />
                                            </svg>
                                            <span><?= count($comentarios) ?></span>
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
                            Nenhuma publicação <?= $viewingSaved ? 'guardada' : '' ?> encontrada.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal de visualização de imagem -->
    <div id="imageModal" class="image-modal">
        <button class="image-modal-close" onclick="fecharModalImagem()">×</button>
        <button class="image-nav prev" id="prevBtn" onclick="navegarImagem(-1)">‹</button>
        <button class="image-nav next" id="nextBtn" onclick="navegarImagem(1)">›</button>
        <div class="image-counter" id="imageCounter">1 / 1</div>
        <div class="image-modal-content">
            <img id="modalImage" src="" alt="Imagem ampliada" style="display: none;">
            <video id="modalVideo" controls style="display: none;">
                <source src="" type="video/mp4">
            </video>
        </div>
    </div>

    <!-- Modal de visualização de publicação -->
    <div id="modalVerPublicacao" class="modal">
        <div class="modal-content modal-publicacao" style="width: 700px; max-height: 90vh;">
            <div class="modal-header">
                <h2>Publicação</h2>
                <button class="close" onclick="fecharPublicacao()">&times;</button>
            </div>

            <div class="modal-body" id="conteudoPublicacao" style="overflow-y: auto; max-height: calc(90vh - 150px);">
                <!-- Cabeçalho da publicação no modal -->
                <div class="modal-post-header">
                    <a href="" id="modalPerfilLink" class="flex items-center">
                        <img id="modalFtPerfil" alt="Foto de Perfil" class="profile-picture"
                            style="width: 48px; height: 48px;">
                        <span id="modalUsername" class="username" style="font-weight: 600; color: #0e2b3b;"></span>
                    </a>

                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span id="modalData" class="post-time" style="color: #6b7280; font-size: 0.875rem;"></span>
                        </div>
                    </div>
                </div>

                <div class="modal-post-content">
                    <p id="modalDescricao" class="modal-post-description" style="text-align: left;"></p>

                    <div id="modalMediaContainer" class="modal-media-container" style="display: none;">
                        <div class="modal-media-viewer">
                            <div class="modal-media-current">
                                <img id="modalCurrentImage" src="" style="display: none;" alt="Imagem da publicação">
                                <video id="modalCurrentVideo" controls style="display: none;" alt="Vídeo da publicação">
                                    <source src="" type="">
                                    Seu navegador não suporta o elemento de vídeo.
                                </video>
                            </div>
                            <button class="modal-nav prev" id="modalPrevBtn" onclick="navegarModalMedia(-1)">‹</button>
                            <button class="modal-nav next" id="modalNextBtn" onclick="navegarModalMedia(1)">›</button>
                            <div class="modal-counter" id="modalMediaCounter">1 / 1</div>
                        </div>
                    </div>

                    <div id="modalSingleMediaContainer" class="flex justify-center my-4" style="display: none;">
                        <img id="modalSingleImage" src="" style="max-width: 100%; max-height: 400px; display: none;"
                            alt="Imagem da publicação" onclick="ampliarMedia(this.src, 'image')">
                        <video id="modalSingleVideo" controls style="max-width: 100%; max-height: 400px; display: none;"
                            alt="Vídeo da publicação">
                            <source src="" type="">
                            Seu navegador não suporta o elemento de vídeo.
                        </video>
                    </div>
                </div>

                <div class="mb-6">
                    <form class="flex gap-2 items-center" method="POST" action="../main/interacoes/comentar.php">
                        <input type="hidden" name="idpublicacao" id="idpublicacao_modal" value="">
                        <img src="<?= $foto_base64 ?>" alt="Sua foto" class="w-10 h-10 rounded-full object-cover">
                        <div class="flex-1 relative">
                            <input type="text" name="comentario" required
                                class="w-full px-4 py-2 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Adicione um comentário...">
                        </div>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-500 text-white rounded-full hover:bg-blue-600 transition">Publicar</button>
                    </form>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Comentários</h3>
                    <div id="comentarios" class="space-y-4">
                        <div id="comentarioTemplate" class="hidden">
                            <div class="flex gap-3">
                                <img class="comentario-ft-perfil w-10 h-10 rounded-full object-cover"
                                    alt="Foto de Perfil">
                                <div class="flex-1">
                                    <div class="bg-gray-100 rounded-lg p-3">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span
                                                class="comentario-username font-semibold text-sm text-gray-800"></span>
                                            <span class="comentario-data text-xs text-gray-500"></span>
                                            <button
                                                class="delete-comment-btn ml-auto text-red-500 hover:text-red-700 text-xs"
                                                onclick="apagarComentario(this, <?= $_SESSION['idutilizador'] ?>, '%%IDCOMENTARIO%%')"
                                                style="display: none;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12"
                                                    fill="currentColor" viewBox="0 0 16 16">
                                                    <path
                                                        d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z" />
                                                    <path fill-rule="evenodd"
                                                        d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z" />
                                                </svg>
                                            </button>
                                        </div>
                                        <p class="comentario-conteudo text-gray-800 text-sm" style="text-align:left;">
                                        </p>
                                    </div>
                                    <div class="flex gap-4 mt-1 ml-3">
                                        <button class="text-xs text-gray-500 hover:text-gray-700">Gostar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="modalSeguidores" class="modal-seguidores">
        <div class="modal-content-seguidores">
            <div class="modal-header">
                <h2>Seguidores</h2>
                <button class="close" onclick="fecharModalSeguidores('modalSeguidores')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="loading" id="loadingSeguidores">A carregar seguidores...</div>
                <ul class="user-list" id="listaSeguidores"></ul>
            </div>
        </div>
    </div>

    <div id="modalSeguindo" class="modal-seguidores">
        <div class="modal-content-seguidores">
            <div class="modal-header">
                <h2>A seguir</h2>
                <button class="close" onclick="fecharModalSeguidores('modalSeguindo')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="loading" id="loadingSeguindo">A carregar utilizadores...</div>
                <ul class="user-list" id="listaSeguindo"></ul>
            </div>
        </div>
    </div>

    <script>
        let currentPostId = null;
        let currentImageIndex = 0;
        let currentMedias = [];
        let currentModalPostId = null;
        let modalMedias = [];
        let modalCurrentIndex = 0;

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
                            // Criar URL temporária para a nova imagem
                            const newImageUrl = URL.createObjectURL(fileInput.files[0]);

                            // Atualizar foto no perfil
                            const profilePictures = document.querySelectorAll('.profile-picture, .perfil-post-avatar, .h_profile-picture');
                            profilePictures.forEach(img => {
                                img.src = newImageUrl;
                                // Forçar recarregamento
                                img.onload = function () {
                                    URL.revokeObjectURL(newImageUrl); // Liberar memória
                                };
                            });

                            // Atualizar foto na sessão (se necessário)
                            if (data.session_updated) {
                                // Forçar recarregamento da página para garantir sincronização completa
                                setTimeout(() => {
                                    window.location.reload();
                                }, 500);
                            }
                        } else if (uploadUrl === 'upload_capa.php') {
                            document.querySelector('.cover-photo').src = URL.createObjectURL(fileInput.files[0]);
                        }
                    } else {
                        alert("Erro ao atualizar a imagem: " + data.message);
                    }
                })
                .catch(error => {
                    console.error("Erro:", error);
                    alert("Ocorreu um erro durante o upload");
                });
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

        // Função para abrir modal de imagem
        async function abrirModalImagem(postId, startIndex = 0) {
            currentPostId = postId;
            currentImageIndex = startIndex;

            try {
                const response = await fetch(`../main/interacoes/get_medias_post.php?id=${postId}`);
                const data = await response.json();

                if (data.success) {
                    currentMedias = data.medias;
                    mostrarImagemAtual();
                    document.getElementById('imageModal').style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                } else {
                    console.error('Erro ao carregar mídias:', data.message);
                    alert('Erro ao carregar mídias: ' + data.message);
                }
            } catch (error) {
                console.error('Erro na requisição:', error);
                alert('Erro ao carregar mídias');
            }
        }

        // Função para mostrar imagem atual
        function mostrarImagemAtual() {
            if (!currentMedias || currentMedias.length === 0) return;

            const media = currentMedias[currentImageIndex];
            const modalImage = document.getElementById('modalImage');
            const modalVideo = document.getElementById('modalVideo');
            const counter = document.getElementById('imageCounter');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');

            // Atualizar contador
            counter.textContent = `${currentImageIndex + 1} / ${currentMedias.length}`;

            // Mostrar/ocultar botões de navegação
            prevBtn.disabled = currentImageIndex === 0;
            nextBtn.disabled = currentImageIndex === currentMedias.length - 1;
            prevBtn.style.display = currentMedias.length > 1 ? 'block' : 'none';
            nextBtn.style.display = currentMedias.length > 1 ? 'block' : 'none';
            counter.style.display = currentMedias.length > 1 ? 'block' : 'none';

            // Mostrar mídia
            if (media.tipo === 'video') {
                modalImage.style.display = 'none';
                modalVideo.style.display = 'block';
                modalVideo.querySelector('source').src = `../main/publicacoes/${media.media}`;
                modalVideo.load();
            } else {
                modalVideo.style.display = 'none';
                modalImage.style.display = 'block';
                modalImage.src = `../main/publicacoes/${media.media}`;
            }
        }

        // Função para navegar entre imagens
        function navegarImagem(direction) {
            const newIndex = currentImageIndex + direction;

            if (newIndex >= 0 && newIndex < currentMedias.length) {
                currentImageIndex = newIndex;
                mostrarImagemAtual();
            }
        }

        // Função para fechar modal de imagem
        function fecharModalImagem() {
            document.getElementById('imageModal').style.display = 'none';
            document.body.style.overflow = 'auto';

            // Pausar vídeo se estiver tocando
            const modalVideo = document.getElementById('modalVideo');
            modalVideo.pause();
            modalVideo.currentTime = 0;
        }

        async function abrirModalVerPublicacao(postId) {
            currentModalPostId = postId;

            try {
                const response = await fetch(`../main/interacoes/get_publicacao_completa.php?id=${postId}`);
                const data = await response.json();

                if (data.success) {
                    const publicacao = document.querySelector('#post_' + postId);
                    const ftPerfil = publicacao.querySelector('.perfil-post-avatar').src;

                    // Preencher dados básicos
                    document.getElementById('modalUsername').textContent = data.user;
                    document.getElementById('modalData').textContent = data.data_formatada;
                    document.getElementById("modalFtPerfil").src = ftPerfil;
                    document.getElementById('modalPerfilLink').href = `perfil.php?id=${data.idutilizador}`;
                    document.getElementById('idpublicacao_modal').value = postId;

                    // Preencher descrição
                    const modalDescricao = document.getElementById('modalDescricao');
                    modalDescricao.innerHTML = data.descricao ? nl2br(htmlspecialchars(data.descricao)) : '';
                    modalDescricao.style.display = data.descricao ? 'block' : 'none';

                    // Configurar mídias
                    const modalMediaContainer = document.getElementById('modalMediaContainer');
                    const modalSingleMediaContainer = document.getElementById('modalSingleMediaContainer');
                    const modalSingleImage = document.getElementById('modalSingleImage');
                    const modalSingleVideo = document.getElementById('modalSingleVideo');

                    // Esconder todos os containers primeiro
                    modalMediaContainer.style.display = 'none';
                    modalSingleMediaContainer.style.display = 'none';
                    modalSingleImage.style.display = 'none';
                    modalSingleVideo.style.display = 'none';

                    if (data.medias && data.medias.length > 0) {
                        modalMedias = data.medias;
                        modalCurrentIndex = 0;

                        if (data.medias.length > 1) {
                            // Mostrar container de múltiplas mídias
                            modalMediaContainer.style.display = 'block';
                            modalSingleMediaContainer.style.display = 'none';
                            mostrarModalMediaAtual();
                        } else {
                            // Mostrar mídia única no container centralizado
                            modalMediaContainer.style.display = 'none';
                            modalSingleMediaContainer.style.display = 'flex';
                            const media = data.medias[0];

                            if (media.tipo === 'video') {
                                modalSingleImage.style.display = 'none';
                                modalSingleVideo.style.display = 'block';
                                modalSingleVideo.querySelector('source').src = `../main/publicacoes/${media.media}`;
                                modalSingleVideo.querySelector('source').type = `video/${media.media.split('.').pop()}`;
                                modalSingleVideo.load();
                            } else {
                                modalSingleVideo.style.display = 'none';
                                modalSingleImage.style.display = 'block';
                                modalSingleImage.src = `../main/publicacoes/${media.media}`;
                            }
                        }
                    }

                    // Carregar comentários
                    carregarComentarios(postId);

                    // Mostrar o modal
                    document.getElementById('modalVerPublicacao').style.display = 'flex';
                    document.body.style.overflow = 'hidden';

                    // Focar no campo de comentário
                    setTimeout(() => {
                        const commentField = document.querySelector('#modalVerPublicacao input[name="comentario"]');
                        if (commentField) commentField.focus();
                    }, 300);
                } else {
                    console.error('Erro ao carregar publicação:', data.message);
                    alert('Não foi possível carregar a publicação');
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Ocorreu um erro ao carregar a publicação');
            }
        }

        // Função auxiliar para nl2br (simulação do PHP)
        function nl2br(str) {
            return str.replace(/\n/g, '<br>');
        }

        // Função auxiliar para htmlspecialchars (simulação do PHP)
        function htmlspecialchars(str) {
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
        }

        // Função para mostrar mídia atual no modal
        function mostrarModalMediaAtual() {
            if (!modalMedias || modalMedias.length === 0) return;

            const media = modalMedias[modalCurrentIndex];
            const modalCurrentImage = document.getElementById('modalCurrentImage');
            const modalCurrentVideo = document.getElementById('modalCurrentVideo');
            const modalCounter = document.getElementById('modalMediaCounter');
            const modalPrevBtn = document.getElementById('modalPrevBtn');
            const modalNextBtn = document.getElementById('modalNextBtn');

            // Atualizar contador
            modalCounter.textContent = `${modalCurrentIndex + 1} / ${modalMedias.length}`;

            // Mostrar/ocultar botões de navegação
            modalPrevBtn.disabled = modalCurrentIndex === 0;
            modalNextBtn.disabled = modalCurrentIndex === modalMedias.length - 1;
            modalPrevBtn.style.display = modalMedias.length > 1 ? 'block' : 'none';
            modalNextBtn.style.display = modalMedias.length > 1 ? 'block' : 'none';
            modalCounter.style.display = modalMedias.length > 1 ? 'block' : 'none';

            // Mostrar mídia
            if (media.tipo === 'video') {
                modalCurrentImage.style.display = 'none';
                modalCurrentVideo.style.display = 'block';
                modalCurrentVideo.querySelector('source').src = `../main/publicacoes/${media.media}`;
                modalCurrentVideo.load();
            } else {
                modalCurrentVideo.style.display = 'none';
                modalCurrentImage.style.display = 'block';
                modalCurrentImage.src = `../main/publicacoes/${media.media}`;
            }
        }

        // Função para navegar entre mídias no modal
        function navegarModalMedia(direction) {
            const newIndex = modalCurrentIndex + direction;

            if (newIndex >= 0 && newIndex < modalMedias.length) {
                modalCurrentIndex = newIndex;
                mostrarModalMediaAtual();
            }
        }

        // Função para fechar modal de publicação
        function fecharPublicacao() {
            document.getElementById('modalVerPublicacao').style.display = 'none';
            document.body.style.overflow = 'auto';
            currentModalPostId = null;
            modalMedias = [];
            modalCurrentIndex = 0;
        }

        // Função para ampliar mídia (compatibilidade)
        function ampliarMedia(src, type) {
            // Encontrar o índice da mídia atual
            const mediaIndex = modalMedias.findIndex(media => src.includes(media.media));
            if (mediaIndex !== -1) {
                currentPostId = currentModalPostId;
                currentMedias = modalMedias;
                currentImageIndex = mediaIndex;
                mostrarImagemAtual();
                document.getElementById('imageModal').style.display = 'flex';
            }
        }

        // Função para carregar comentários
        // Função para toggle like
        async function toggleLike(postId, element) {
            try {
                const formData = new FormData();
                formData.append('idpublicacao', postId);

                const response = await fetch('../main/interacoes/like.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.text();
                const likeCount = element.querySelector('.like-count');
                let currentCount = parseInt(likeCount.textContent);

                if (result === 'liked') {
                    element.classList.add('liked');
                    likeCount.textContent = currentCount + 1;
                } else if (result === 'unliked') {
                    element.classList.remove('liked');
                    likeCount.textContent = Math.max(0, currentCount - 1);
                }
            } catch (error) {
                console.error('Erro ao dar like:', error);
            }
        }

        // Função para apagar comentário
        async function apagarComentario(button, idUtilizadorLogado, idComentario) {
            // Usar confirm padrão do navegador
            const confirmacao = confirm("Tem certeza que deseja apagar este comentário?");

            if (!confirmacao) return;

            try {
                const response = await fetch('../main/interacoes/apagar_comentario.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `idcomentario=${idComentario}`
                });

                const data = await response.json();

                if (data.success) {
                    // Remove o comentário com animação
                    const commentElement = button.closest('.flex.gap-3');
                    commentElement.style.transition = 'opacity 0.3s, transform 0.3s';
                    commentElement.style.opacity = '0';
                    commentElement.style.transform = 'translateX(20px)';

                    setTimeout(() => {
                        commentElement.remove();

                        // Verifica se não há mais comentários
                        const comentariosContainer = document.getElementById('comentarios');
                        if (comentariosContainer.children.length === 1) { // Apenas o template
                            const noComments = document.createElement('p');
                            noComments.textContent = 'Nenhum comentário ainda. Seja o primeiro a comentar!';
                            noComments.style.textAlign = 'center';
                            noComments.style.color = '#666';
                            noComments.style.padding = '20px';
                            comentariosContainer.appendChild(noComments);
                        }
                    }, 300);
                } else {
                    alert('Erro ao apagar comentário: ' + (data.message || 'Erro desconhecido'));
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao apagar comentário: ' + error.message);
            }
        }

        // Atualize a função carregarComentarios para mostrar o botão de apagar quando apropriado
        async function carregarComentarios(postId) {
            try {
                const response = await fetch(`../main/interacoes/obter_comentarios.php?idpublicacao=${postId}`);
                const comentarios = await response.json();
                const comentariosContainer = document.getElementById('comentarios');
                const template = document.getElementById('comentarioTemplate');
                const idUtilizadorLogado = <?= $_SESSION['idutilizador'] ?>;
                const idAutorPublicacao = document.getElementById('modalPerfilLink').href.split('id=')[1];

                // Limpar comentários existentes (exceto o template)
                comentariosContainer.innerHTML = '';
                comentariosContainer.appendChild(template); // Manter o template

                if (comentarios && comentarios.length > 0) {
                    comentarios.forEach(comentario => {
                        const comentarioElement = template.cloneNode(true);
                        comentarioElement.id = '';
                        comentarioElement.classList.remove('hidden');

                        // Preencher dados do comentário
                        const imgElement = comentarioElement.querySelector('.comentario-ft-perfil');
                        imgElement.src = comentario.ft_perfil ?
                            (comentario.ft_perfil.startsWith('data:image') ?
                                comentario.ft_perfil :
                                'data:image/jpeg;base64,' + base64_encode(comentario.ft_perfil)) :
                            'default.png';

                        comentarioElement.querySelector('.comentario-username').textContent = comentario.user || 'Utilizador';
                        comentarioElement.querySelector('.comentario-data').textContent = formatarData(comentario.data);
                        comentarioElement.querySelector('.comentario-conteudo').textContent = comentario.conteudo;

                        // Configurar botão de apagar
                        const deleteBtn = comentarioElement.querySelector('.delete-comment-btn');
                        deleteBtn.setAttribute('onclick', `apagarComentario(this, ${idUtilizadorLogado}, ${comentario.idcomentario})`);

                        // Mostrar o botão de apagar se:
                        // 1. O usuário logado é o autor do comentário OU
                        // 2. O usuário logado é o autor da publicação
                        if (comentario.idutilizador == idUtilizadorLogado || idUtilizadorLogado == idAutorPublicacao) {
                            deleteBtn.style.display = 'block';
                        } else {
                            deleteBtn.style.display = 'none';
                        }

                        // Remover o placeholder %%IDCOMENTARIO%% do template
                        const deleteBtnHtml = deleteBtn.outerHTML.replace('%%IDCOMENTARIO%%', comentario.idcomentario);
                        deleteBtn.outerHTML = deleteBtnHtml;

                        comentariosContainer.appendChild(comentarioElement);
                    });
                } else {
                    const noComments = document.createElement('p');
                    noComments.textContent = 'Nenhum comentário ainda. Seja o primeiro a comentar!';
                    noComments.style.textAlign = 'center';
                    noComments.style.color = '#666';
                    noComments.style.padding = '20px';
                    comentariosContainer.appendChild(noComments);
                }
            } catch (error) {
                console.error('Erro ao carregar comentários:', error);
                const comentariosContainer = document.getElementById('comentarios');
                comentariosContainer.innerHTML = `
            <p style="color: red; text-align: center; padding: 20px;">
                Erro ao carregar comentários: ${error.message}
            </p>
        `;
            }
        }
        // Função auxiliar para formatar data (adicione ao seu código)
        function formatarData(dataString) {
            const data = new Date(dataString);
            return data.toLocaleString('pt-PT', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Função auxiliar para simular base64_encode (se necessário)
        function base64_encode(str) {
            return btoa(unescape(encodeURIComponent(str)));
        }
        // Função auxiliar para formatar data
        function formatarData(dataString) {
            if (!dataString) return '';
            const data = new Date(dataString);
            return data.toLocaleString('pt-PT', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
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

        function fecharModalSeguidores(modalId) {
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

        function removerDosGuardados(idPublicacao) {
            fetch('../main/interacoes/remover_guardado.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'idpublicacao=' + idPublicacao
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove a publicação da lista ou recarrega a página
                        window.location.reload();
                    } else {
                        alert('Erro ao remover dos guardados: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao conectar com o servidor');
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
                followBtn.onclick = function (e) {
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
                body: 'idpublicacao=' + idPublicacao
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

        // Navegação por teclado
        document.addEventListener('keydown', function (e) {
            const imageModal = document.getElementById('imageModal');
            const publicacaoModal = document.getElementById('modalVerPublicacao');

            if (imageModal.style.display === 'flex') {
                switch (e.key) {
                    case 'Escape':
                        fecharModalImagem();
                        break;
                    case 'ArrowLeft':
                        navegarImagem(-1);
                        break;
                    case 'ArrowRight':
                        navegarImagem(1);
                        break;
                }
            } else if (publicacaoModal.style.display === 'flex') {
                switch (e.key) {
                    case 'Escape':
                        fecharPublicacao();
                        break;
                    case 'ArrowLeft':
                        if (modalMedias.length > 1) navegarModalMedia(-1);
                        break;
                    case 'ArrowRight':
                        if (modalMedias.length > 1) navegarModalMedia(1);
                        break;
                }
            }
        });

        // Fechar modais ao clicar fora
        document.getElementById('imageModal').addEventListener('click', function (e) {
            if (e.target === this) {
                fecharModalImagem();
            }
        });

        document.getElementById('modalVerPublicacao').addEventListener('click', function (e) {
            if (e.target === this) {
                fecharPublicacao();
            }
        });

        window.onclick = function (event) {
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