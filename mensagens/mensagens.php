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
                        <button class="action-btn delete-conversation" title="Apagar conversa" id="deleteConversationBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"
                                fill="#ff4757">
                                <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z" />
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
                <!-- Modal de confirmação -->
                <div id="confirmDeleteModal" class="modal"
                    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
                    <div class="modal-content"
                        style="background: white; padding: 20px; border-radius: 8px; max-width: 400px; width: 90%;">
                        <h3 style="margin-top: 0; color: #0e2b3b;">Apagar conversa</h3>
                        <p style="margin-bottom: 20px;">Tem certeza que deseja apagar esta conversa permanentemente?</p>
                        <div style="display: flex; justify-content: flex-end; gap: 10px;">
                            <button id="cancelDelete"
                                style="padding: 8px 16px; border-radius: 4px; border: none; background: #f0f0f0; cursor: pointer;">Cancelar</button>
                            <button id="confirmDelete"
                                style="padding: 8px 16px; border-radius: 4px; border: none; background: #ff4757; color: white; cursor: pointer;">Apagar</button>
                        </div>
                    </div>
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
    <!-- Modal de confirmação -->
    <div id="confirmDeleteModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h3>Apagar conversa</h3>
            <p>Tem certeza que deseja apagar esta conversa permanentemente?</p>
            <div class="modal-actions">
                <button id="cancelDelete" class="modal-btn cancel">Cancelar</button>
                <button id="confirmDelete" class="modal-btn confirm">Apagar</button>
            </div>
        </div>
    </div>

    <script>
        // Funções para a barra de pesquisa
        const searchList = document.querySelector('#searchList');
        const usersList = Array.from(searchList?.children || []);

        function searchDropdown(inputEl) {
            var search = inputEl.value;

            if (searchList) {
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
        }

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

            // Configurar o modal de apagar conversa
            const deleteBtn = document.getElementById('deleteConversationBtn');
            const modal = document.getElementById('confirmDeleteModal');
            const cancelBtn = document.getElementById('cancelDelete');
            const confirmBtn = document.getElementById('confirmDelete');

            if (deleteBtn && modal) {
                deleteBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    modal.style.display = 'flex';
                });

                cancelBtn.addEventListener('click', function () {
                    modal.style.display = 'none';
                });

                confirmBtn.addEventListener('click', function () {
                    apagarConversa();
                    modal.style.display = 'none';
                });

                // Fechar modal ao clicar fora
                modal.addEventListener('click', function (e) {
                    if (e.target === modal) {
                        modal.style.display = 'none';
                    }
                });
            }
        });

        function apagarConversa() {
            const destinatario = <?= $destinatario ? $destinatario : 'null' ?>;

            if (!destinatario) {
                alert('Nenhum destinatário selecionado');
                return;
            }

            fetch('apagar_conversa.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'destinatario=' + destinatario
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'mensagens.php';
                    } else {
                        alert('Erro ao apagar conversa: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro na comunicação com o servidor');
                });
        }

        // Atualizar notificações periodicamente
        setInterval(atualizarNotificacoes, 5000);

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