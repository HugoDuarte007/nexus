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
                        <h2>Criar Publicação</h2>
                        <button class="close" onclick="fecharModal()">✖</button>
                    </div>
                    <div class="modal-body">
                        <form action="interacoes/publicar.php" method="post" id="publicacaoForm"
                            enctype="multipart/form-data">
                            <textarea id="descricao" name="descricao" placeholder="Em que está a pensar?"
                                required></textarea>

                            <!-- Botão para escolher imagem -->
                            <label for="imagemInput" title="Adicionar imagem"
                                style="cursor: pointer; display: inline-block; margin-top: 10px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="#0e2b3b"
                                    viewBox="0 0 24 24">
                                    <path d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 
                     2 0 0 0 2 2h14a2 2 0 0 0 2-2ZM8.5 
                     13.5 11 17l3.5-4.5 4.5 6H5l3.5-4.5Zm.5-3A2 
                     2 0 1 0 7 8a2 2 0 0 0 2 2Z" />
                                </svg>
                            </label>
                            <input type="file" id="imagemInput" name="imagem" accept="image/*" style="display:none"
                                onchange="preverImagem()">

                            <!-- Pré-visualização -->
                            <div id="previewContainer" style="margin-top: 10px; display: none;">
                                <img id="previewImagem" src="#" alt="Pré-visualização da imagem"
                                    style="max-width: 100%; max-height: 300px; border-radius: 10px;" />
                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="botao">Publicar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Dropdown do perfil -->
            <div class="profile-dropdown" onclick="toggleDropdown(event)">
                <div class="user-info">
                    <span><?php echo htmlspecialchars($_SESSION["user"]); ?></span>
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

        header .user-info {
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
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
        .modalPublicacao {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            width: 500px;
            max-width: 90%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border: 1px solid #0e2b3b;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .modal-header h2 {
            margin: 0;
            color: #0e2b3b;
        }

        .close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }

        .close:hover {
            color: #333;
        }

        textarea {
            width: 100%;
            height: 150px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: vertical;
            font-family: inherit;
            font-size: 14px;
        }

        .modal-footer {
            margin-top: 20px;
            text-align: right;
        }

        .botao {
            padding: 10px 20px;
            background-color: #0e2b3b;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .botao:hover {
            background-color: #1a3d4d;
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
    function preverImagem() {
            const input = document.getElementById('imagemInput');
            const preview = document.getElementById('previewImagem');
            const container = document.getElementById('previewContainer');

            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    preview.src = e.target.result;
                    container.style.display = "block";
                }

                reader.readAsDataURL(input.files[0]);
            } else {
                container.style.display = "none";
                preview.src = "#";
            }
        }
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