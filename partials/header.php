<header>
    <nav class="navbar">
        <!-- Logo -->
        <a href="../main/main.php" style="color:white;text-decoration: none;">
            <h1 class="logo">Nexus</h1>
        </a>

        <!-- Barra de pesquisa -->
        <div class="search-container">
            <input type="text" id="searchInput" class="search-bar" placeholder="Pesquisar utilizadores..."
                onkeyup="searchUsers()">
        </div>

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
                <a href="../configuracoes/config.php">Definições</a>
                <a href="../logout.php">Terminar sessão</a>
            </div>
        </div>
    </nav>

    <!-- Estilo CSS -->
    <style>
        .navbar {
    background: #0e2b3b;
    color: white;
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
    </style>
</header>

<!-- Script JavaScript -->
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
