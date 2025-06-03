<header>
    <nav class="navbar">
        <a href="../main/main.php" style="color:white;text-decoration: none;">
            <h1 class="logo">Nexus</h1>
        </a>
        <div class="search-container">
            <input type="text" id="searchInput" class="search-bar" placeholder="Pesquisar utilizadores..."
                onkeyup="searchUsers()">
        </div>
        <button class="styled-button message-button" title="Mensagens"
            onclick="window.location.href='../mensagens/mensagens.php'">
            <svg xmlns="http://www.w3.org/2000/svg" height="28" viewBox="0 0 24 24" width="28" fill="white"
                class="rotated-icon">
                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" />
            </svg>
        </button>
        <button class="styled-button" title="Publicar" style="font-size:33px;" onclick="abrirModal()">+</button>
        <button class="styled-button" title="Perfil" onclick="window.location.href='../perfil/perfil.php'">
            <div class="user-info">
                <span><?php echo htmlspecialchars($utilizador); ?></span>
                <img src="<?php echo $foto_base64; ?>" alt="Foto de Perfil" class="profile-picture">
            </div>
        </button>
    </nav>
</header>