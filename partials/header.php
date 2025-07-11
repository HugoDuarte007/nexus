<header>
    <nav class="h_navbar">
        <!-- Logo -->
        <a href="../main/main.php" class="flex flex-1 justify-start" style="color:white;text-decoration: none;">
            <h1 class="h_logo">Nexus</h1>
        </a>
        <!-- Barra de pesquisa -->
        <div class="flex-1">
            <input type="text" id="searchInput" class="h_search-bar w-full" placeholder="Pesquisar utilizadores..."
                onkeyup="searchDropdown(this)">

            <div id="searchList" class="hidden bg-white absolute border border-black gap-2 rounded-xl "
                style="z-index:1000;">
                <?php
                $sql = "SELECT * FROM utilizador;";
                $result = mysqli_query($con, $sql);
                $foto_perfil = $_SESSION['ft_perfil'] ?? null;
                $foto_base64 = $foto_perfil ? "data:image/jpeg;base64," . base64_encode($foto_perfil) : "../imagens/default.png";
                ?>

                <?php while ($user = mysqli_fetch_assoc($result)): ?>
                    <a href="../perfil/perfil.php?id=<?= $user['idutilizador'] ?>" id="<?= $user['idutilizador'] ?>"
                        name="<?= $user['user'] ?>" class="hidden flex items-center gap-2 rounded-xl hover:bg-gray-100"
                        style="padding: 12px;">
                        <img class="bg-gray-100 w-8 h-8 rounded-full"
                            src="<?= $user['ft_perfil'] ? 'data:image/jpeg;base64,' . base64_encode($user['ft_perfil']) : 'default.png'; ?>"
                            alt="">
                        <p class="text-black"><?= $user['user'] ?></p>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="flex gap-1 items-center flex-1 justify-end">
            <!-- Botão de notificações -->
            <div class="h_notifications-dropdown" style="position: relative;">
                <button class="h_styled-button notification-button" title="Notificações"
                    onclick="toggleNotifications(event)" style="position: relative;">
                    <svg xmlns="http://www.w3.org/2000/svg" height="28" viewBox="0 0 24 24" width="28" fill="white">
                        <path
                            d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                    </svg>
                    <!-- Badge para notificações não vistas -->
                    <span id="notificacoes-badge" class="h_notification-badge" style="display: none;">0</span>
                </button>

                <!-- Dropdown de notificações -->
                <div id="notificationsDropdown" class="h_notifications-content" style="display: none;">
                    <div class="h_notifications-header">
                        <h3>Notificações</h3>
                        <button onclick="marcarTodasComoVistas()" class="h_mark-all-read">
                            Marcar todas como vistas
                        </button>
                    </div>
                    <div id="notificationsList" class="h_notifications-list">
                        <div class="h_loading">A carregar notificações...</div>
                    </div>
                </div>
            </div>

            <!-- Botão de mensagens com badge -->
            <button class="h_styled-button message-button" title="Mensagens"
                onclick="window.location.href='../mensagens/mensagens.php'" style="position: relative;">
                <svg xmlns="http://www.w3.org/2000/svg" height="28" viewBox="0 0 24 24" width="28" fill="white"
                    class="rotated-icon">
                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" />
                </svg>
                <!-- Badge para mensagens não lidas -->
                <span id="mensagens-badge" class="h_notification-badge" style="display: none;">0</span>
            </button>

            <!-- Botão de publicar -->
            <button class="h_styled-button" title="Publicar" style="font-size:33px;" onclick="abrirModal()">+</button>

            <!-- Modal de Publicação -->
            <div id="modalPublicacao" class="h_modalPublicacao">
                <div class="h_modal-content">
                    <div class="h_modal-header">
                        <h2>Criar Nova Publicação</h2>
                        <button class="h_close" onclick="fecharModal()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="#0e2b3b">
                                <path
                                    d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
                            </svg>
                        </button>
                    </div>
                    <div class="h_modal-body">
                        <form action="interacoes/publicar.php" method="post" id="publicacaoForm"
                            enctype="multipart/form-data">
                            <textarea id="descricao" name="descricao" placeholder="Em que está a pensar?"></textarea>

                            <div class="h_media-upload">
                                <!-- Área de arrastar e soltar -->
                                <div class="h_drop-area" id="dropArea">
                                    <div class="h_drop-content">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="#0e2b3b"
                                            viewBox="0 0 24 24">
                                            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z" />
                                        </svg>
                                        <p>Arraste e solte fotos ou vídeos aqui</p>
                                        <p class="h_subtext">Ou clique para selecionar arquivos</p>
                                        <p class="h_file-types">Suportados: JPG, PNG, GIF, MP4, MOV, AVI (máx. 10
                                            arquivos)</p>
                                    </div>
                                    <input type="file" id="mediaInput" name="media[]" accept="image/*,video/*" multiple>
                                </div>

                                <!-- Preview das mídias -->
                                <div id="previewContainer" class="h_preview-container">
                                    <div class="h_preview-header">
                                        <span id="previewTitle">Pré-visualização</span>
                                        <button type="button" onclick="removerTodasMedias()">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                viewBox="0 0 24 24" fill="#0e2b3b">
                                                <path
                                                    d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div id="mediaPreviewGrid" class="h_media-preview-grid">
                                        <!-- Previews serão adicionados aqui dinamicamente -->
                                    </div>
                                </div>
                            </div>

                            <div class="h_modal-footer">
                                <button type="submit" class="h_botao-publicar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                        fill="white">
                                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" />
                                    </svg>
                                    Publicar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Perfil - Comportamento diferente para admin e usuário normal -->
            <?php if ($_SESSION["id_tipos_utilizador"] == 0): ?>
                <!-- Dropdown para administradores -->
                <div class="h_profile-dropdown" onclick="toggleDropdown(event)">
                    <div class="h_user-info">
                        <p><?php echo htmlspecialchars($_SESSION["user"]); ?></p>
                        <img src="<?php echo $foto_base64; ?>" alt="Foto de Perfil" class="h_profile-picture">
                    </div>
                    <div id="dropdownMenu" class="h_dropdown-content">
                        <a href="../perfil/perfil.php">Ver perfil</a>
                        <a href="../admin/utilizadores.php">Administração</a>
                        <a href="../logout.php">Terminar sessão</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Link direto para usuários normais -->
                <a href="../perfil/perfil.php" class="h_profile-link">
                    <div class="h_user-info">
                        <p><?php echo htmlspecialchars($_SESSION["user"]); ?></p>
                        <img src="<?php echo $foto_base64; ?>" alt="Foto de Perfil" class="h_profile-picture">
                    </div>
                </a>
            <?php endif; ?>
        </div>
    </nav>

    <style>
        /* Estilos existentes mantidos... */
        .h_styled-button {
            padding: 10px 20px;
            font-size: 1rem;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            background-color: transparent;
            color: white;
            transition: 0.3s ease;
            display: flex;
            align-items: center;
            width: auto;
        }

        .h_styled-button:hover {
            background-color: rgba(0, 0, 0, 0.2);
            color: white;
        }

        .h_notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #ff4757;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
            animation: pulse 2s infinite;
            border: 2px solid #0e2b3b;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        /* Estilos do dropdown de notificações */
        .h_notifications-dropdown {
            position: relative;
            display: inline-block;
        }

        .h_notifications-content {
            position: absolute;
            right: 0;
            top: 100%;
            background-color: white;
            min-width: 350px;
            max-width: 400px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            border-radius: 8px;
            margin-top: 5px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            max-height: 500px;
            overflow-y: auto;
        }

        .h_notifications-header {
            padding: 15px 20px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .h_notifications-header h3 {
            margin: 0;
            color: var(--primary);
            font-size: 1.1rem;
            font-weight: 600;
        }

        .h_mark-all-read {
            background: none;
            border: none;
            color: var(--primary);
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            padding: 4px 8px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        .h_mark-all-read:hover {
            background-color: rgba(14, 43, 59, 0.1);
        }

        .h_notifications-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .h_notification-item {
            padding: 12px 20px;
            border-bottom: 1px solid #f3f4f6;
            cursor: pointer;
            transition: background-color 0.2s;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            text-decoration: none;
            color: inherit;
        }

        .h_notification-item:hover {
            background-color: #f8f9fa;
        }

        .h_notification-item:last-child {
            border-bottom: none;
        }

        .h_notification-item.unread {
            background-color: #f0f8ff;
            border-left: 3px solid var(--primary);
        }

        .h_notification-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
        }

        .h_notification-content {
            flex: 1;
            min-width: 0;
        }

        .h_notification-text {
            font-size: 0.9rem;
            line-height: 1.4;
            margin-bottom: 4px;
            color: #374151;
            word-break: break-word;
            /* Quebra palavras longas */
            white-space: normal;
            /* Permite quebras de linha */
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            /* Limita a 3 linhas */
            -webkit-box-orient: vertical;
        }

        .h_notification-text strong {
            color: var(--primary);
            font-weight: 600;
        }

        .h_notification-time {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .h_notification-icon {
            position: absolute;
            bottom: -2px;
            right: -2px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
        }

        .h_notification-icon.like {
            background-color: #dc3545;
        }

        .h_notification-icon.comentario {
            background-color: #28a745;
        }

        .h_notification-icon.save {
            background-color: #ffc107;
        }

        .h_notification-icon.seguir {
            background-color: #007bff;
        }

        .h_notification-icon svg {
            width: 12px;
            height: 12px;
            fill: white;
        }

        .h_loading {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-style: italic;
        }

        .h_no-notifications {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }

        .h_no-notifications svg {
            width: 48px;
            height: 48px;
            margin-bottom: 12px;
            opacity: 0.5;
        }

        .h_navbar {
            background: #0e2b3b;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        }

        .h_profile-dropdown {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }

        /* Novo estilo para link direto do perfil */
        .h_profile-link {
            text-decoration: none;
            color: inherit;
            cursor: pointer;
        }

        .h_user-info {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px;
            border-radius: 8px;
            transition: background-color 0.2s;
            color: white;
            cursor: pointer;
        }

        .h_user-info:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .h_profile-picture {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }

        .h_dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #333;
            min-width: 160px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            border-radius: 8px;
            margin-top: 5px;
            overflow: hidden;
        }

        .h_dropdown-content a {
            color: white;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            transition: background-color 0.2s;
        }

        .h_dropdown-content a:hover {
            background-color: #555;
        }

        .h_logo {
            font-size: 24px;
            font-weight: bold;
            position: relative;
            display: inline-block;
            color: #fff;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
            transition: all 0.5s ease;
            transform-origin: center;
        }

        .h_logo:hover {
            animation: nexusGlow 1.5s infinite alternate;
            text-shadow: 0 0 15px #fff, 0 0 30px #00b4d8;
        }

        .h_logo::before {
            content: attr(data-text);
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            color: #00b4d8;
            overflow: hidden;
            clip-path: polygon(0% 0%, 100% 0%, 100% 50%, 0% 50%);
            animation: nexusSplit 1.5s infinite alternate;
            opacity: 0.8;
        }

        @keyframes nexusGlow {
            0% {
                transform: scale(1);
                text-shadow: 0 0 10px #fff, 0 0 20px #00b4d8;
            }

            100% {
                transform: scale(1.1);
                text-shadow: 0 0 20px #fff, 0 0 40px #00b4d8, 0 0 60px #0096c7;
            }
        }

        @keyframes nexusSplit {
            0% {
                transform: translateY(0);
                clip-path: polygon(0% 0%, 100% 0%, 100% 50%, 0% 50%);
            }

            100% {
                transform: translateY(-5px);
                clip-path: polygon(0% 30%, 100% 30%, 100% 70%, 0% 70%);
            }
        }

        .h_search-bar {
            background-color: white;
            color: #333;
            padding: 10px;
            border-radius: 20px;
            border: none;
            outline: none;
        }

        /* Estilos do Modal */
        .h_modalPublicacao {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            justify-content: center;
            align-items: center;
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }

        .h_modal-content {
            background-color: white;
            border-radius: 12px;
            width: 500px;
            max-width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .h_modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid #eaeaea;
        }

        .h_modal-header h2 {
            margin: 0;
            color: #0e2b3b;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .h_close {
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            transition: background-color 0.2s;
        }

        .h_close:hover {
            background-color: #f5f5f5;
        }

        .h_modal-body {
            padding: 20px;
        }

        header textarea {
            width: 100%;
            height: 120px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            resize: none;
            font-family: inherit;
            font-size: 14px;
            margin-bottom: 15px;
            transition: border 0.3s;
        }

        header textarea:focus {
            outline: none;
            border-color: #0e2b3b;
        }

        .h_media-upload {
            margin-bottom: 20px;
        }

        .h_drop-area {
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 15px;
            position: relative;
        }

        .h_drop-area:hover {
            border-color: #0e2b3b;
            background-color: #f9f9f9;
        }

        .h_drop-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            color: #666;
        }

        .h_drop-content p {
            margin: 0;
        }

        .h_drop-content .h_subtext {
            font-size: 0.9rem;
            color: #999;
        }

        .h_drop-content .h_file-types {
            font-size: 0.8rem;
            color: #777;
            margin-top: 5px;
        }

        .h_preview-container {
            display: none;
            flex-direction: column;
            border: 1px solid #eee;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 15px;
        }

        .h_preview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            background-color: #f9f9f9;
            border-bottom: 1px solid #eee;
        }

        .h_preview-header span {
            font-size: 0.9rem;
            color: #666;
        }

        .h_preview-header button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            transition: background-color 0.2s;
        }

        .h_preview-header button:hover {
            background-color: #f0f0f0;
        }

        .h_media-preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 8px;
            padding: 15px;
            max-height: 300px;
            overflow-y: auto;
        }

        .h_preview-item {
            position: relative;
            aspect-ratio: 1;
            border-radius: 8px;
            overflow: hidden;
            background-color: #f0f0f0;
        }

        .h_preview-item img,
        .h_preview-item video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .h_preview-remove {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .h_modal-footer {
            padding: 15px 0 5px;
            text-align: right;
        }

        .h_botao-publicar {
            padding: 10px 20px;
            background-color: #0e2b3b;
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.3s;
        }

        .h_botao-publicar:hover {
            background-color: #1a3d4d;
        }

        .h_drop-area.highlight {
            border-color: #0e2b3b;
            background-color: #f0f5f9;
        }

        header #mediaInput {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0;
            cursor: pointer;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @media (max-width: 600px) {
            .h_modal-content {
                width: 90%;
                max-height: 80vh;
            }

            .h_drop-area {
                padding: 20px;
            }

            .h_media-preview-grid {
                grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            }
        }
    </style>
</header>

<script>
    // Array para armazenar os arquivos selecionados
    let selectedFiles = [];

    // Funções para a barra de pesquisa
    const searchList = document.querySelector('#searchList');
    const usersList = Array.from(searchList.children);

    function searchDropdown(inputEl) {
        var search = inputEl.value;
        searchList.style.width = searchList.parentElement.offsetWidth + "px";

        if (search == "") {
            searchList.classList.add('hidden');
            return;
        } else {
            searchList.classList.remove('hidden');
        }

        usersList.forEach(userEl => {
            if (userEl.name.toLowerCase().search(search.toLowerCase()) != -1) {
                userEl.classList.remove('hidden');
            } else {
                userEl.classList.add('hidden');
            }
        });
    }

    // Funções para o modal de publicação
    function abrirModal() {
        document.getElementById("modalPublicacao").style.display = "flex";
        document.body.style.overflow = "hidden";
    }

    function fecharModal() {
        document.getElementById("modalPublicacao").style.display = "none";
        document.body.style.overflow = "auto";
        document.getElementById('publicacaoForm').reset();
        document.getElementById('previewContainer').style.display = "none";
        document.getElementById('dropArea').style.display = "block";

        // Limpar arquivos selecionados
        selectedFiles = [];
        atualizarPreview();
    }

    function preverMedias() {
        const input = document.getElementById('mediaInput');
        const files = Array.from(input.files);

        if (files.length === 0) return;

        // Verificar limite de 10 arquivos
        if (selectedFiles.length + files.length > 10) {
            alert('Máximo de 10 arquivos permitidos');
            return;
        }

        // Validar cada arquivo
        for (let file of files) {
            const validImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            const validVideoTypes = ['video/mp4', 'video/mov', 'video/avi', 'video/webm'];
            const extensoes_permitidas = [...validImageTypes, ...validVideoTypes];

            if (!extensoes_permitidas.includes(file.type)) {
                alert('Por favor, selecione apenas imagens (JPEG, PNG, GIF, WEBP) ou vídeos (MP4, MOV, AVI, WEBM)');
                return;
            }

            // Verificar tamanho do arquivo
            const tamanho_maximo = validVideoTypes.includes(file.type) ? 52428800 : 5242880;
            if (file.size > tamanho_maximo) {
                const limite = validVideoTypes.includes(file.type) ? '50MB' : '5MB';
                alert(`Tamanho máximo do arquivo: ${limite}`);
                return;
            }
        }

        // Adicionar arquivos válidos
        selectedFiles = selectedFiles.concat(files);
        atualizarPreview();
    }

    function atualizarPreview() {
        const container = document.getElementById('previewContainer');
        const dropArea = document.getElementById('dropArea');
        const grid = document.getElementById('mediaPreviewGrid');
        const title = document.getElementById('previewTitle');

        if (selectedFiles.length === 0) {
            container.style.display = 'none';
            dropArea.style.display = 'block';
            return;
        }

        container.style.display = 'flex';
        dropArea.style.display = 'none';
        title.textContent = `${selectedFiles.length} arquivo(s) selecionado(s)`;

        // Limpar grid
        grid.innerHTML = '';

        // Adicionar preview para cada arquivo
        selectedFiles.forEach((file, index) => {
            const previewItem = document.createElement('div');
            previewItem.className = 'h_preview-item';

            const removeBtn = document.createElement('button');
            removeBtn.className = 'h_preview-remove';
            removeBtn.innerHTML = '×';
            removeBtn.onclick = () => removerMedia(index);

            if (file.type.startsWith('video/')) {
                const video = document.createElement('video');
                video.src = URL.createObjectURL(file);
                video.muted = true;
                previewItem.appendChild(video);
            } else {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                previewItem.appendChild(img);
            }

            previewItem.appendChild(removeBtn);
            grid.appendChild(previewItem);
        });
    }

    function removerMedia(index) {
        selectedFiles.splice(index, 1);
        atualizarPreview();
        atualizarInputFile();
    }

    function removerTodasMedias() {
        selectedFiles = [];
        atualizarPreview();
        atualizarInputFile();
    }

    function atualizarInputFile() {
        const input = document.getElementById('mediaInput');
        const dt = new DataTransfer();

        selectedFiles.forEach(file => {
            dt.items.add(file);
        });

        input.files = dt.files;
    }

    // Inicialização quando o DOM estiver pronto
    document.addEventListener('DOMContentLoaded', function () {
        // Configurar drag and drop
        const dropArea = document.getElementById('dropArea');
        const input = document.getElementById('mediaInput');

        if (dropArea && input) {
            // Evitar comportamentos padrão
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            // Efeito visual ao arrastar sobre a área
            ['dragenter', 'dragover'].forEach(eventName => {
                dropArea.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, unhighlight, false);
            });

            function highlight() {
                dropArea.classList.add('highlight');
            }

            function unhighlight() {
                dropArea.classList.remove('highlight');
            }

            // Manipular arquivos soltos
            dropArea.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = Array.from(dt.files);

                // Adicionar aos arquivos selecionados
                if (selectedFiles.length + files.length > 10) {
                    alert('Máximo de 10 arquivos permitidos');
                    return;
                }

                selectedFiles = selectedFiles.concat(files);
                atualizarPreview();
                atualizarInputFile();
            }
        }

        // Configurar o input de arquivo
        const mediaInput = document.getElementById('mediaInput');
        if (mediaInput) {
            mediaInput.addEventListener('change', preverMedias);
        }

        // Fechar modal ao pressionar ESC
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                fecharModal();
            }
        });

        // Verificar envio do formulário
        const form = document.getElementById('publicacaoForm');
        if (form) {
            form.addEventListener('submit', function (e) {
                const descricao = document.getElementById('descricao').value;

                if (!descricao && selectedFiles.length === 0) {
                    e.preventDefault();
                    alert('Por favor, adicione uma descrição ou uma mídia');
                    return;
                }
            });
        }

        // Atualizar notificações de mensagens periodicamente
        atualizarNotificacoesMensagens();
        setInterval(atualizarNotificacoesMensagens, 10000);

        // Atualizar notificações
        atualizarNotificacoes();
        setInterval(atualizarNotificacoes, 15000);
    });

    // Funções para o dropdown do perfil (apenas para administradores)
    function toggleDropdown(event) {
        event.stopPropagation();
        const menu = document.getElementById("dropdownMenu");
        if (menu) {
            const isOpen = menu.style.display === "block";
            menu.style.display = isOpen ? "none" : "block";
        }
    }

    // Fechar dropdown ao clicar fora (apenas para administradores)
    window.addEventListener("click", function () {
        const menu = document.getElementById("dropdownMenu");
        if (menu) {
            menu.style.display = "none";
        }
    });

    // Função para atualizar notificações de mensagens no header
    function atualizarNotificacoesMensagens() {
        fetch('../mensagens/get_mensagens_nao_lidas.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const badge = document.getElementById('mensagens-badge');
                    if (badge) {
                        if (data.total_nao_lidas > 0) {
                            badge.textContent = data.total_nao_lidas;
                            badge.style.display = 'flex';
                        } else {
                            badge.style.display = 'none';
                        }
                    }
                }
            })
            .catch(error => console.error('Erro ao atualizar notificações:', error));
    }

    // Funções para notificações
    function toggleNotifications(event) {
        event.stopPropagation();
        const dropdown = document.getElementById('notificationsDropdown');
        const isOpen = dropdown.style.display === 'block';

        // Fechar outros dropdowns
        const menu = document.getElementById("dropdownMenu");
        if (menu) menu.style.display = "none";

        if (isOpen) {
            dropdown.style.display = 'none';
        } else {
            dropdown.style.display = 'block';
            carregarNotificacoes();
        }
    }

    function carregarNotificacoes() {
        const lista = document.getElementById('notificationsList');
        lista.innerHTML = '<div class="h_loading">A carregar notificações...</div>';

        fetch('notificacoes/get_notificacoes.php')
            .then(response => response.json())
            .then(data => {
                console.log('Resposta das notificações:', data); // Debug
                if (data.success) {
                    renderizarNotificacoes(data.notificacoes);
                } else {
                    lista.innerHTML = '<div class="h_no-notifications">Erro ao carregar notificações</div>';
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                lista.innerHTML = '<div class="h_no-notifications">Erro ao carregar notificações</div>';
            });
    }

    function renderizarNotificacoes(notificacoes) {
        const lista = document.getElementById('notificationsList');

        if (notificacoes.length === 0) {
            lista.innerHTML = `
                <div class="h_no-notifications">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                    </svg>
                    <p>Nenhuma notificação</p>
                </div>
            `;
            return;
        }

        lista.innerHTML = notificacoes.map(notificacao => {
            const avatar = notificacao.remetente_foto
                ? `data:image/jpeg;base64,${notificacao.remetente_foto}`
                : 'default.png';

            let texto = '';
            let icone = '';
            let link = '';

            switch (notificacao.tipo) {
                case 'like':
                    texto = `<strong>${notificacao.remetente_user}</strong> gostou da sua publicação`;
                    icone = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>';
                    link = `main.php#post-${notificacao.id_publicacao}`;
                    break;
                case 'comentario':
                    texto = `<strong>${notificacao.remetente_user}</strong> comentou na sua publicação`;
                    if (notificacao.conteudo) {
                        texto += `: "${notificacao.conteudo}"`;
                    }
                    icone = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M21 6h-2v9H6v2c0 .55.45 1 1 1h11l4 4V7c0-.55-.45-1-1-1zm-4 6V3c0-.55-.45-1-1-1H3c-.55 0-1 .45-1 1v14l4-4h11c.55 0 1-.45 1-1z"/></svg>';
                    link = `main.php#post-${notificacao.id_publicacao}`;
                    break;
                case 'save':
                    texto = `<strong>${notificacao.remetente_user}</strong> guardou a sua publicação`;
                    icone = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M17 3H7c-1.1 0-2 .9-2 2v16l7-3 7 3V5c0-1.1-.9-2-2-2z"/></svg>';
                    link = `main.php#post-${notificacao.id_publicacao}`;
                    break;
                case 'seguir':
                    texto = `<strong>${notificacao.remetente_user}</strong> começou a seguir-te`;
                    icone = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M16 4c0-1.11.89-2 2-2s2 .89 2 2-.89 2-2 2-2-.89-2-2zm4 18v-6h2.5l-2.54-7.63A1.5 1.5 0 0 0 18.54 8H16c-.8 0-1.54.37-2 1l-3.72 5.6L8.5 11.5 7 13l4.5 4.5L14 15l2 7h4z"/></svg>';
                    link = `../perfil/perfil.php?id=${notificacao.id_remetente}`;
                    break;
            }

            return `
                <div class="h_notification-item ${!notificacao.vista ? 'unread' : ''}" 
                     onclick="abrirNotificacao('${link}', ${notificacao.id_notificacao})">
                    <div style="position: relative;">
                        <img src="${avatar}" alt="Avatar" class="h_notification-avatar">
                        <div class="h_notification-icon ${notificacao.tipo}">
                            ${icone}
                        </div>
                    </div>
                    <div class="h_notification-content">
                        <div class="h_notification-text">${texto}</div>
                        <div class="h_notification-time">${notificacao.tempo_formatado}</div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function marcarComoVista(idNotificacao) {
        fetch('notificacoes/marcar_vista.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id_notificacao=${idNotificacao}`
        });
    }

    function abrirNotificacao(link, idNotificacao) {
        // Marcar como vista
        marcarComoVista(idNotificacao);

        // Fechar dropdown
        document.getElementById('notificationsDropdown').style.display = 'none';

        // Navegar para o link
        if (link.includes('perfil.php')) {
            window.location.href = link;
        } else {
            // Para links do main.php, verificar se já estamos na página
            if (window.location.pathname.includes('main.php')) {
                // Se já estamos no main.php, apenas fazer scroll para o post
                const postId = link.split('#')[1];
                if (postId) {
                    const element = document.getElementById(postId);
                    if (element) {
                        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            } else {
                // Se não estamos no main.php, navegar para lá
                window.location.href = link;
            }
        }

        // Atualizar badge
        atualizarNotificacoes();
    }

    function marcarTodasComoVistas() {
        fetch('notificacoes/marcar_vista.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: ''
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualizar interface
                    document.querySelectorAll('.h_notification-item.unread').forEach(item => {
                        item.classList.remove('unread');
                    });

                    // Atualizar badge
                    const badge = document.getElementById('notificacoes-badge');
                    badge.style.display = 'none';

                    atualizarNotificacoes();
                }
            });
    }

    function atualizarNotificacoes() {
        fetch('notificacoes/get_notificacoes.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const badge = document.getElementById('notificacoes-badge');
                    if (data.nao_vistas > 0) {
                        badge.textContent = data.nao_vistas;
                        badge.style.display = 'flex';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            })
            .catch(error => console.error('Erro ao atualizar notificações:', error));
    }

    // Fechar dropdowns ao clicar fora
    document.addEventListener('click', function (event) {
        const notificationsDropdown = document.getElementById('notificationsDropdown');
        const notificationsButton = event.target.closest('.notification-button');

        if (!notificationsButton && notificationsDropdown) {
            notificationsDropdown.style.display = 'none';
        }
    });
</script>