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

$query = "SELECT * FROM utilizador WHERE idutilizador = '$idperfil'";
$result = mysqli_query($con, $query);
if ($result) {
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $perfil_foto_perfil = $row['ft_perfil'] ?? null;
    $perfil_foto_capa = $row['ft_capa'] ?? null;
    $perfil_nome = $row['nome'] ?? "Nome não disponível";
    $perfil_utilizador = $row['user'] ?? "Username não disponível";
    setlocale(LC_TIME, 'pt_PT.UTF-8', 'Portuguese_Portugal', 'Portuguese');

    $perfil_data = isset($row['data_registo']) ? strftime("dia %e de %B de %Y", strtotime($row['data_registo'])) : "um dia.";
} else {
    $perfil_foto_perfil = null;
    $perfil_foto_capa = null;
    $perfil_nome = "Erro ao carregar nome";
}

$perfil_foto_base64 = $perfil_foto_perfil ? "data:image/jpeg;base64," . base64_encode($perfil_foto_perfil) : "default.png";
$perfil_foto_capa_base64 = $perfil_foto_capa ? "data:image/jpeg;base64," . base64_encode($perfil_foto_capa) : "capa_default.jpg";



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

$foto_base64 = $foto_perfil ? "data:image/jpeg;base64," . base64_encode($foto_perfil) : "default.png";
$foto_capa_base64 = $foto_capa ? "data:image/jpeg;base64," . base64_encode($foto_capa) : "capa_default.jpg";
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../imagens/favicon.ico" type="image/png">
    <link rel="stylesheet" type="text/css" href="../main/style.css">
    <title>Nexus | Perfil</title>
    <style>
        .cover-wrapper {
            position: relative;
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            height: 45vh;
            overflow: hidden;
            cursor: pointer;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
        }

        .cover-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: 0.3s ease;
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

        .cover-wrapper:hover .cover-overlay {
            opacity: 1;
        }

        .profile-container {
            text-align: center;
            padding: 100px 20px 20px;
            position: relative;
            max-width: 1000px;
            margin: 0 auto;
        }

        .profile-picture-wrapper {
            position: absolute;
            top: -75px;
            left: 50%;
            transform: translateX(-50%);
            cursor: pointer;
        }

        .profile-picture-large {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #0e2b3b;
            transition: 0.3s ease;
            background-color: #fff;
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

        .profile-picture-wrapper:hover .profile-picture-overlay {
            opacity: 1;
        }

        .profile-picture-wrapper:hover .profile-picture-large {
            filter: brightness(0.7);
        }

        .camera-icon {
            width: 40px;
            height: 40px;
            background: url('camara.png') no-repeat center;
            background-size: contain;
        }

        #fileInput,
        #fileInputCapa {
            display: none;
        }

        .definicoes {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background-color: transparent;
            background-image: url("definicoes.png");
            background-size: 100%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .definicoes:hover {
            transform: rotate(60deg);
        }

        .profile-info h2 {
            margin: 20px 0 5px;
        }

        .profile-info p {
            margin: 5px 0;
            word-wrap: break-word;
        }

        /* Responsivo para ecrãs pequenos */
        @media (max-width: 600px) {
            .profile-container {
                padding-top: 120px;
            }

            .profile-picture-large {
                width: 120px;
                height: 120px;
            }

            .definicoes {
                top: 10px;
                right: 10px;
                width: 40px;
                height: 40px;
            }

            .camera-icon {
                width: 30px;
                height: 30px;
            }
        }

        .definicoes {
            position: fixed;
            top: 105px;
            right: 15px;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background-color: transparent;
            background-image: url("definicoes.png");
            background-size: 100%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .definicoes:hover {
            transform: rotate(60deg);
        }
    </style>
</head>

<body>

    <?php require '../partials/header.php'; ?>

    <div class="cover-wrapper" onclick="document.getElementById('fileInputCapa').click();">
        <img src="<?php echo $perfil_foto_capa_base64; ?>" alt="Foto de Capa" class="cover-photo">
        <?php if ($perfil_utilizador == $utilizador): ?>
            <div class="cover-overlay">
                <div class="camera-icon"></div>
            </div>
            <input type="file" id="fileInputCapa" accept="image/*"
                onchange="uploadImage('fileInputCapa', 'upload_capa.php')">
        <?php endif; ?>
    </div>


    <div class="profile-container">
        <?php if ($perfil_utilizador == $utilizador): ?>
            <button class="definicoes" onclick="window.location.href='editar_perfil.php'"></button>
        <?php endif; ?>

        <div class="profile-picture-wrapper" onclick="document.getElementById('fileInput').click();">
            <img src="<?php echo $perfil_foto_base64; ?>" alt="Foto de Perfil" class="profile-picture-large">

            <?php if ($perfil_utilizador == $utilizador): ?>
                <div class="profile-picture-overlay">
                    <div class="camera-icon"></div>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($perfil_utilizador == $utilizador): ?>
            <input type="file" id="fileInput" accept="image/*" onchange="uploadImage('fileInput', 'upload_foto.php')">
        <?php endif; ?>

        <div class="profile-info">
            <h2><?php echo htmlspecialchars($perfil_nome); ?></h2>
            <i>
                <p>@<?php echo htmlspecialchars($perfil_utilizador); ?></p>
            </i>
            <p>Utilizador da NEXUS desde <?php echo $perfil_data ?></p>
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
                        // Atualizar apenas a imagem na página sem recarregar
                        if (uploadUrl === 'upload_foto.php') {
                            document.querySelector('.profile-picture-large').src = URL.createObjectURL(fileInput.files[0]);
                        } else if (uploadUrl === 'upload_capa.php') {
                            document.querySelector('.cover-photo').src = URL.createObjectURL(fileInput.files[0]);
                        }
                    } else {
                        alert("Erro ao atualizar a imagem: " + data.message);
                    }
                })
                .catch(error => console.error("Erro:", error));
        }
    </script>
</body>

</html>