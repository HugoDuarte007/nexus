<header>
    <nav class="navbar">
        <!-- Logo -->
        <a href="../main/main.php" class="flex flex-1 justify-start" style="color:white;text-decoration: none;">
            <h1 class="logo">Nexus</h1>
        </a>

        <!-- Barra de pesquisa -->
        <div class="flex-1">
            <input type="text" id="searchInput" class="search-bar w-full" placeholder="Pesquisar utilizadores..."
                onkeyup="searchDropdown(this)">

            <div id="searchList" class="hidden bg-white absolute border border-black gap-2 rounded-xl "
                style="z-index:1000;">
                <?php
                $sql = "SELECT * FROM utilizador;";
                $result = mysqli_query($con, $sql);
                $foto_perfil = $_SESSION['ft_perfil'] ?? null;
                $foto_base64 = $foto_perfil ? "data:image/jpeg;base64," . base64_encode($foto_perfil) : "default.png";
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
            <!-- Botão de mensagens -->
            <button class="styled-button message-button" title="Mensagens"
                onclick="window.location.href='../mensagens/mensagens.php'">
                <svg xmlns="http://www.w3.org/2000/svg" height="28" viewBox="0 0 24 24" width="28" fill="white"
                    class="rotated-icon">
                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" />
                </svg>
            </button>

            <!-- Botão de publicar -->
            <button class="styled-button" title="Publicar" style="font-size:33px;" onclick="abrirModal()">+</button>

            <div id="modalPublicacao" class="modalPublicacao">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Criar Nova Publicação</h2>
                        <button class="close" onclick="fecharModal()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="#0e2b3b">
                                <path
                                    d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
                            </svg>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="interacoes/publicar.php" method="post" id="publicacaoForm"
                            enctype="multipart/form-data">
                            <textarea id="descricao" name="descricao" placeholder="Em que está a pensar?"
                                required></textarea>

                            <div class="media-upload">
                                <!-- Área de arrastar e soltar -->
                                <div class="drop-area" id="dropArea">
                                    <div class="drop-content">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="#0e2b3b"
                                            viewBox="0 0 24 24">
                                            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z" />
                                        </svg>
                                        <p>Arraste e solte fotos ou vídeos aqui</p>
                                        <p class="subtext">Ou clique para selecionar arquivos</p>
                                    </div>
                                    <input type="file" id="imagemInput" name="imagem" accept="image/*"
                                        style="opacity: 0; position: absolute; width: 100%; height: 100%; top: 0; left: 0; cursor: pointer;">
                                </div>

                                <!-- Preview da imagem -->
                                <div id="previewContainer" class="hidden">
                                    <div class="preview-header">
                                        <span>Pré-visualização</span>
                                        <button type="button" onclick="removerImagem()">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                viewBox="0 0 24 24" fill="#0e2b3b">
                                                <path
                                                    d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
                                            </svg>
                                        </button>
                                    </div>
                                    <img id="previewImagem" src="#" alt="Pré-visualização da imagem"
                                        class="preview-image" />
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="botao-publicar">
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

            <!-- Dropdown do perfil -->
            <div class="profile-dropdown" onclick="toggleDropdown(event)">
                <div class="user-info">
                    <p><?php echo htmlspecialchars($_SESSION["user"]); ?></p>
                    <img src="<?php echo $foto_base64; ?>" alt="Foto de Perfil" class="profile-picture">
                </div>
                <div id="dropdownMenu" class="dropdown-content">
                    <a href="../perfil/perfil.php">Ver perfil</a>
                    <a href="../perfil/editar_perfil.php">Definições</a>
                    <a href="../logout.php">Terminar sessão</a>
                </div>
            </div>
        </div>
    </nav>

    <style>
        header .styled-button {
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

        header .styled-button:hover {
            background-color: rgba(0, 0, 0, 0.2);
            color: white;
        }

        header .styled-button .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        header .navbar {
            background: #0e2b3b;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        }

        header .profile-dropdown {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }

        header .user-info {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px;
            border-radius: 8px;
            transition: background-color 0.2s;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;

        }

        header .user-info:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        header .profile-picture {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }

        header .dropdown-content {
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

        header .dropdown-content a {
            color: white;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            transition: background-color 0.2s;
        }

        header .dropdown-content a:hover {
            background-color: #555;
        }

        header .logo {
            font-size: 24px;
            font-weight: bold;
        }

        header .search-bar {
            background-color: white;
            color: #333;
            padding: 10px;
            border-radius: 20px;
            border: none;
            outline: none;
        }



        header .user-info span {
            font-size: 16px;
            font-weight: bold;
        }

        header .profile-picture {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
        }

        /* Estilos do Modal */
        /* Estilos do Modal */
        header .modalPublicacao {
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

        header .modal-content {
            background-color: white;
            border-radius: 12px;
            width: 500px;
            max-width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            border: none;
        }

        header .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid #eaeaea;
        }

        header .modal-header h2 {
            margin: 0;
            color: #0e2b3b;
            font-size: 1.2rem;
            font-weight: 600;
        }

        header .close {
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            transition: background-color 0.2s;
        }

        header .close:hover {
            background-color: #f5f5f5;
        }

        header .modal-body {
            padding: 20px;
        }

        header .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #eee;
        }

        header .user-info span {
            font-weight: 500;
            color: black;
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

        header .media-upload {
            margin-bottom: 20px;
        }

        header .drop-area {
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 15px;
        }

        header .drop-area:hover {
            border-color: #0e2b3b;
            background-color: #f9f9f9;
        }

        header .drop-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            color: #666;
        }

        header .drop-content p {
            margin: 0;
        }

        header .drop-content .subtext {
            font-size: 0.9rem;
            color: #999;
        }

        header #previewContainer {
            display: none;
            flex-direction: column;
            border: 1px solid #eee;
            border-radius: 8px;
            overflow: hidden;
        }

        header .preview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            background-color: #f9f9f9;
            border-bottom: 1px solid #eee;
        }

        header .preview-header span {
            font-size: 0.9rem;
            color: #666;
        }

        header .preview-header button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            transition: background-color 0.2s;
        }

        header .preview-header button:hover {
            background-color: #f0f0f0;
        }

        header .preview-image {
            max-width: 100%;
            max-height: 300px;
            object-fit: contain;
            display: block;
        }

        header .modal-footer {
            padding: 15px 0 5px;
            text-align: right;
        }

        header .botao-publicar {
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

        header .botao-publicar:hover {
            background-color: #1a3d4d;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        /* Responsividade */
        @media (max-width: 600px) {
            .modal-content {
                width: 90%;
                max-height: 80vh;
            }

            .drop-area {
                padding: 20px;
            }
        }

        header .drop-area.highlight {
            border-color: #0e2b3b;
            background-color: #f0f5f9;
        }

        /* Garantir que o input file não seja visível */
        header #imagemInput {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            border: 0;
        }
    </style>
</header>

<!-- DROP DOWN SERACH BAR -->
<script>
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
    function abrirModal() {
        document.getElementById("modalPublicacao").style.display = "flex";
        document.body.style.overflow = "hidden";
    }

    function fecharModal() {
        document.getElementById("modalPublicacao").style.display = "none";
        document.body.style.overflow = "auto";
    }

    // Função para pré-visualizar imagem
    function preverImagem() {
        const input = document.getElementById('imagemInput');
        const preview = document.getElementById('previewImagem');
        const container = document.getElementById('previewContainer');
        const dropArea = document.getElementById('dropArea');

        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function (e) {
                preview.src = e.target.result;
                container.style.display = "flex";
                dropArea.style.display = "none";
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    // Função para remover imagem selecionada
    function removerImagem() {
        const input = document.getElementById('imagemInput');
        const preview = document.getElementById('previewImagem');
        const container = document.getElementById('previewContainer');
        const dropArea = document.getElementById('dropArea');

        input.value = '';
        preview.src = '#';
        container.style.display = "none";
        dropArea.style.display = "block";
    }

    // Implementação de drag and drop
    document.addEventListener('DOMContentLoaded', function () {
        const dropArea = document.getElementById('dropArea');
        const input = document.getElementById('imagemInput');

        if (dropArea && input) {
            // Click na área de drop
            dropArea.addEventListener('click', function () {
                input.click();
            });

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
                const files = dt.files;

                if (files.length) {
                    input.files = files;
                    preverImagem();
                }
            }
        }

        // Verificar se o input de imagem existe
        const imagemInput = document.getElementById('imagemInput');
        if (imagemInput) {
            imagemInput.addEventListener('change', preverImagem);
        }
    });

    // Fechar modal ao pressionar ESC
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            fecharModal();
        }
    });
</script>

<!-- DROPDOWN PERFIL -->
<script>
    function toggleDropdown(event) {
        event.stopPropagation(); // Impede que o clique feche imediatamente
        const menu = document.getElementById("dropdownMenu");
        const isOpen = menu.style.display === "block";
        menu.style.display = isOpen ? "none" : "block";
    }

    // Fechar dropdown ao clicar fora
    window.addEventListener("click", function () {
        const menu = document.getElementById("dropdownMenu");
        if (menu) {
            menu.style.display = "none";
        }
    });
</script>