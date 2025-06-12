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

            <div id="searchList" class="hidden bg-white absolute border border-black gap-2 rounded-xl z-100">
                <?php
                $sql = "SELECT * FROM utilizador;";
                $result = mysqli_query($con, $sql);
                ?>


                <?php while ($user = mysqli_fetch_assoc($result)): ?>

                    <a href="../perfil/perfil.php?id=<?= $user['idutilizador'] ?>" id="<?= $user['idutilizador'] ?>" name="<?= $user['user'] ?>" class="hidden flex items-center gap-2 rounded-xl hover:bg-gray-100" style="padding: 12px;">
                        <img class="bg-gray-100 w-8 h-8 rounded-full" src="<?= $user['ft_perfil'] ? 'data:image/jpeg;base64,' . base64_encode($user['ft_perfil']) : 'default.png'; ?>" alt="">
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

            <!-- Dropdown do perfil -->
            <div class="profile-dropdown" onclick="toggleDropdown(event)">
                <div class="user-info">
                    <span><?php echo htmlspecialchars($utilizador); ?></span>
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

    <!-- Estilo CSS -->
    <style>
        
.styled-button {
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

.styled-button:hover {
    background-color: rgba(0, 0, 0, 0.2);
    color: white;
}

.styled-button .user-info {
    display: flex;
    align-items: center;
    gap: 10px;
}
        .navbar {
            background: #0e2b3b;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        }

        .profile-dropdown {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px;
            border-radius: 8px;
            transition: background-color 0.2s;
        }

        .user-info:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .profile-picture {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }

        .dropdown-content {
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

        .dropdown-content a {
            color: white;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            transition: background-color 0.2s;
        }

        .dropdown-content a:hover {
            background-color: #555;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
        }

        .search-bar {
            background-color: white;
            color: #333;
            padding: 10px;
            border-radius: 20px;
            border: none;
            outline: none;
        }

        .user-info {
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .user-info span {
            font-size: 16px;
            font-weight: bold;
        }

        .profile-picture {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
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
    window.addEventListener("click", function() {
        const menu = document.getElementById("dropdownMenu");
        if (menu) {
            menu.style.display = "none";
        }
    });
</script>