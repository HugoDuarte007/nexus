<?php
session_start();
require "../ligabd.php";

if (!isset($_SESSION["user"])) {
    header("Location: ../login.php");
    exit();
}

$utilizador = htmlspecialchars($_SESSION["user"]);

// Buscar informações do utilizador logado
$query = "SELECT idutilizador, ft_perfil FROM utilizador WHERE user = '$utilizador'";
$result = mysqli_query($con, $query);
$user_data = mysqli_fetch_assoc($result);
$id_utilizador = $user_data['idutilizador'];
$foto_perfil = $user_data['ft_perfil'] ?? null;
$foto_base64 = $foto_perfil ? "data:image/jpeg;base64," . base64_encode($foto_perfil) : "default.png";

// Buscar pessoas que o utilizador segue
$query_seguidores = "SELECT u.idutilizador, u.user, u.ft_perfil 
                     FROM seguidor s 
                     JOIN utilizador u ON s.id_seguido = u.idutilizador 
                     WHERE s.id_seguidor = $id_utilizador";
$seguidores = mysqli_query($con, $query_seguidores);

// Buscar conversas recentes com contagem de mensagens não lidas
$query_conversas = "SELECT DISTINCT u.idutilizador, u.user, u.ft_perfil, 
                           MAX(m.dataenvio) as ultima_msg,
                           COUNT(CASE WHEN ld.iddestinatario = $id_utilizador AND ld.lida = 0 THEN 1 END) as nao_lidas
                    FROM mensagem m
                    JOIN listadestinatarios ld ON m.idmensagem = ld.idmensagem
                    JOIN utilizador u ON 
                        (m.idremetente = u.idutilizador OR ld.iddestinatario = u.idutilizador)
                    WHERE (m.idremetente = $id_utilizador OR ld.iddestinatario = $id_utilizador)
                    AND u.idutilizador != $id_utilizador
                    GROUP BY u.idutilizador
                    ORDER BY ultima_msg DESC";
$conversas = mysqli_query($con, $query_conversas);

// Verificar se há um destinatário selecionado
$destinatario = isset($_GET['destinatario']) ? (int) $_GET['destinatario'] : null;

// Buscar mensagens com o destinatário selecionado
if ($destinatario) {
    // Marcar mensagens como lidas quando abrir a conversa
    $query_marcar_lida = "UPDATE listadestinatarios ld
                          JOIN mensagem m ON ld.idmensagem = m.idmensagem
                          SET ld.lida = 1
                          WHERE ld.iddestinatario = $id_utilizador AND m.idremetente = $destinatario AND ld.lida = 0";
    mysqli_query($con, $query_marcar_lida);

    $query_mensagens = "SELECT m.*, u.user as remetente_nome, u.ft_perfil as remetente_foto, u.idutilizador as remetente_id
                        FROM mensagem m
                        JOIN utilizador u ON m.idremetente = u.idutilizador
                        JOIN listadestinatarios ld ON m.idmensagem = ld.idmensagem
                        WHERE (m.idremetente = $id_utilizador AND ld.iddestinatario = $destinatario)
                        OR (m.idremetente = $destinatario AND ld.iddestinatario = $id_utilizador)
                        ORDER BY m.dataenvio ASC";
    $mensagens = mysqli_query($con, $query_mensagens);

    // Buscar informações do destinatário
    $query_destinatario = "SELECT user, ft_perfil FROM utilizador WHERE idutilizador = $destinatario";
    $result_dest = mysqli_query($con, $query_destinatario);
    $destinatario_data = mysqli_fetch_assoc($result_dest);
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensagens | Nexus</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="../imagens/favicon.ico" type="image/png">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body>

    <?php require '../partials/header.php'; ?>

    <div class="messages-container">
        <!-- Sidebar com lista de conversas -->
        <div class="messages-sidebar">
            <div class="messages-header">
                <h2>Mensagens</h2>
                <div class="search-bar">
                    <input type="text" placeholder="Pesquisar pessoas...">
                </div>
            </div>

            <div class="conversas-list">
                <?php if (mysqli_num_rows($conversas) > 0): ?>
                    <?php while ($conversa = mysqli_fetch_assoc($conversas)): ?>
                        <a href="mensagens.php?destinatario=<?= $conversa['idutilizador'] ?>"
                            class="conversa-item <?= ($destinatario == $conversa['idutilizador']) ? 'active' : '' ?> <?= ($conversa['nao_lidas'] > 0) ? 'nao-lida' : '' ?>"
                            data-user-id="<?= $conversa['idutilizador'] ?>">
                            <img src="<?= $conversa['ft_perfil'] ? 'data:image/jpeg;base64,' . base64_encode($conversa['ft_perfil']) : '../imagens/default.png' ?>"
                                alt="Foto de perfil" class="conversa-avatar">
                            <div class="conversa-info">
                                <span class="conversa-nome"><?= htmlspecialchars($conversa['user']) ?></span>
                                <span class="conversa-ultima"><?= date("d/m H:i", strtotime($conversa['ultima_msg'])) ?></span>
                            </div>
                            <?php if ($conversa['nao_lidas'] > 0): ?>
                                <div class="badge-nao-lida"><?= $conversa['nao_lidas'] ?></div>
                            <?php endif; ?>
                        </a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="no-conversas">Nenhuma conversa encontrada. Comece a seguir pessoas para enviar mensagens.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Área de conversa -->
        <div class="messages-content">
            <?php if ($destinatario): ?>
                <div class="conversa-header">
                    <a href="../perfil/perfil.php?id=<?= $destinatario ?>" style="text-decoration: none; color: inherit;">
                        <img src="<?= $destinatario_data['ft_perfil'] ? 'data:image/jpeg;base64,' . base64_encode($destinatario_data['ft_perfil']) : '../imagens/default.png' ?>"
                            alt="Foto de perfil" class="destinatario-avatar">
                    </a>
                    <span class="destinatario-nome"><?= htmlspecialchars($destinatario_data['user']) ?></span>
                    <div class="conversa-actions">
                        <button class="action-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                                <path fill="#0e2b3b"
                                    d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="mensagens-list" id="mensagensContainer">
                    <?php
                    $mensagens_array = [];
                    while ($msg = mysqli_fetch_assoc($mensagens)) {
                        $mensagens_array[] = $msg;
                    }

                    $mensagens_array = array_reverse($mensagens_array);
                    ?>
                    <?php if (count($mensagens_array) > 0): ?>
                        <?php foreach ($mensagens_array as $msg): ?>
                            <div class="mensagem <?= ($msg['idremetente'] == $id_utilizador) ? 'enviada' : 'recebida' ?>">
                                <?php if ($msg['idremetente'] != $id_utilizador): ?>
                                    <a href="../perfil/perfil.php?id=<?= $msg['remetente_id'] ?>" style="text-decoration: none;">
                                        <img src="<?= $msg['remetente_foto'] ? 'data:image/jpeg;base64,' . base64_encode($msg['remetente_foto']) : '../imagens/default.png' ?>"
                                            alt="Foto de perfil" class="mensagem-avatar">
                                    </a>
                                <?php endif; ?>
                                <div class="mensagem-conteudo">
                                    <p><?= nl2br(htmlspecialchars($msg['mensagem'])) ?></p>
                                    <span class="mensagem-hora"><?= date("H:i", strtotime($msg['dataenvio'])) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-messages">
                            <p>Nenhuma mensagem ainda. Envie a primeira mensagem!</p>
                        </div>
                    <?php endif; ?>
                </div>

                <form class="mensagem-form" id="form" action="enviar_mensagem.php" method="POST" id="formMensagem">
                    <input type="hidden" name="destinatario" value="<?= $destinatario ?>">
                    <div class="input-container">
                        <textarea name="mensagem" id="form_mensagem" placeholder="Escreve uma mensagem..." autofocus
                            required id="inputMensagem"></textarea>
                        <button type="submit" class="send-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                                <path fill="#0e2b3b" d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" />
                            </svg>
                        </button>
                    </div>
                    <script>
                        var form = document.querySelector("#form");
                        var form_mensagem = document.querySelector("#form_mensagem");

                        form_mensagem.addEventListener('keydown', (e) => {
                            if (e.key === 'Enter' && !e.shiftKey) {
                                e.preventDefault();
                                form.submit();
                            }
                        });
                    </script>
                </form>
            <?php else: ?>
                <div class="no-conversa-selected">
                    <div class="empty-state">
                        <div class="icon-container">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="64" height="64">
                                <path fill="#0e2b3b"
                                    d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z" />
                            </svg>
                        </div>
                        <h3>Seleciona uma conversa</h3>
                        <p>Escolhe uma conversa da lista para começar a enviar mensagens</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
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

        // Funções para o dropdown do perfil
        function toggleDropdown(event) {
            event.stopPropagation();
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

        // Rolagem automática para a última mensagem
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('mensagensContainer');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }

            // Focar no campo de texto ao carregar
            const inputMensagem = document.getElementById('inputMensagem');
            if (inputMensagem) {
                inputMensagem.focus();
            }

            // Atualizar notificações periodicamente
            setInterval(atualizarNotificacoes, 5000); // A cada 5 segundos
        });

        // Envio de mensagem com AJAX
        document.getElementById('formMensagem')?.addEventListener('submit', function (e) {
            e.preventDefault();

            const form = this;
            const formData = new FormData(form);
            const mensagensContainer = document.getElementById('mensagensContainer');
            const inputMensagem = document.getElementById('inputMensagem');
            const mensagemTexto = inputMensagem.value.trim();

            if (!mensagemTexto) return;

            fetch(form.action, {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Adiciona a nova mensagem ao chat
                        const novaMensagem = document.createElement('div');
                        novaMensagem.className = 'mensagem enviada';
                        novaMensagem.innerHTML = `
                        <div class="mensagem-conteudo">
                            <p>${data.mensagem}</p>
                            <span class="mensagem-hora">${data.hora}</span>
                        </div>
                    `;
                        mensagensContainer.appendChild(novaMensagem);

                        // Remove a mensagem de "nenhuma mensagem"
                        const noMessages = document.querySelector('.no-messages');
                        if (noMessages) {
                            noMessages.remove();
                        }

                        // Limpa o campo de texto
                        inputMensagem.value = '';

                        // Rolagem para a nova mensagem
                        mensagensContainer.scrollTop = mensagensContainer.scrollHeight;
                    } else {
                        alert('Erro ao enviar mensagem: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro na comunicação com o servidor');
                });
        });

        // Função para atualizar notificações
        function atualizarNotificacoes() {
            fetch('get_mensagens_nao_lidas.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Atualizar badge no header
                        const headerBadge = document.getElementById('mensagens-badge');
                        if (headerBadge) {
                            if (data.total_nao_lidas > 0) {
                                headerBadge.textContent = data.total_nao_lidas;
                                headerBadge.style.display = 'block';
                            } else {
                                headerBadge.style.display = 'none';
                            }
                        }

                        // Atualizar badges nas conversas
                        document.querySelectorAll('.conversa-item').forEach(item => {
                            const userId = item.getAttribute('data-user-id');
                            const badge = item.querySelector('.badge-nao-lida');

                            if (data.conversas_nao_lidas[userId]) {
                                if (badge) {
                                    badge.textContent = data.conversas_nao_lidas[userId];
                                } else {
                                    const newBadge = document.createElement('div');
                                    newBadge.className = 'badge-nao-lida';
                                    newBadge.textContent = data.conversas_nao_lidas[userId];
                                    item.appendChild(newBadge);
                                }
                                item.classList.add('nao-lida');
                            } else {
                                if (badge) {
                                    badge.remove();
                                }
                                item.classList.remove('nao-lida');
                            }
                        });
                    }
                })
                .catch(error => console.error('Erro ao atualizar notificações:', error));
        }
    </script>
</body>

</html>