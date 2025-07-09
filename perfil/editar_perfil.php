<?php
session_start();
require "../ligabd.php";


if (!isset($_SESSION["user"])) {
    header("Location: ../login.php");
    exit();
}

$iduser = $_SESSION["idutilizador"];
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = mysqli_real_escape_string($con, $_POST["nome"]);
    $user = mysqli_real_escape_string($con, $_POST["user"]);
    $email = mysqli_real_escape_string($con, $_POST["email"]);
    $telemovel = mysqli_real_escape_string($con, $_POST["telemovel"]);
    $data_nascimento = mysqli_real_escape_string($con, $_POST["data_nascimento"]);
    $pais = mysqli_real_escape_string($con, $_POST["pais"]);

    $query = "UPDATE utilizador SET 
              nome='$nome', 
              user='$user', 
              email='$email',
              telemovel='$telemovel',
              data_nascimento='$data_nascimento',
              pais='$pais'
              WHERE idutilizador = '$iduser'";

    if (mysqli_query($con, $query)) {
        $msg = "Perfil atualizado com sucesso!";
    } else {
        $msg = "Erro ao atualizar perfil: " . mysqli_error($con);
    }
}

$query = "SELECT nome, user, email, telemovel, data_nascimento, pais FROM utilizador WHERE idutilizador = '$iduser'";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);

require "../partials/paises.php";
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../imagens/favicon.ico" type="image/png">
    <link rel="stylesheet" type="text/css" href="../main/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <title>Nexus | Editar Perfil</title>
    <style>
        :root {
            --primary: #0e2b3b;
            --primary-hover: #1a3d4d;
            --secondary: #4a5568;
            --light: #f8fafc;
            --dark: #1a202c;
            --success: #38a169;
            --error: #e53e3e;
            --border: #e2e8f0;
            --radius: 12px;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        body {
            background-color: var(--light);
            color: var(--dark);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        .editar-container {
            width: 100%;
            min-height: calc(100vh - 80px);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem 1rem;
            margin-top: 80px;
        }

        .editar-header {
            width: 100%;
            max-width: 800px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .editar-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary);
            margin: 0;
            position: relative;
        }

        .editar-title::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 60px;
            height: 4px;
            background-color: var(--primary);
            border-radius: 2px;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background-color: var(--error);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .logout-btn:hover {
            background-color: #c53030;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .editar-form {
            width: 100%;
            max-width: 800px;
            background-color: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 2.5rem;
            border: 1px solid var(--border);
            animation: fadeIn 0.5s ease;
        }

        .editar-form h2 {
            text-align: center;
            color: var(--primary);
            margin-bottom: 2rem;
            font-size: 1.5rem;
            font-weight: 700;
            position: relative;
            padding-bottom: 10px;
        }

        .editar-form h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background-color: var(--primary);
            border-radius: 3px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
            position: relative;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--primary);
            font-size: 0.95rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid var(--border);
            background-color: white;
            color: var(--secondary);
            font-size: 1rem;
            transition: all 0.3s;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(14, 43, 59, 0.2);
        }

        .submit-btn {
            grid-column: span 2;
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.875rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .submit-btn:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(14, 43, 59, 0.2);
        }

        .feedback-message {
            padding: 0.5rem 0.75rem;
            margin-top: 0.5rem;
            border-radius: 6px;
            font-size: 0.85rem;
            display: none;
        }

        .error {
            background-color: rgba(229, 62, 62, 0.1);
            color: var(--error);
            border-left: 3px solid var(--error);
            display: block;
        }

        .success {
            background-color: rgba(56, 161, 105, 0.1);
            color: var(--success);
            border-left: 3px solid var(--success);
            display: block;
        }

        .campo-erro {
            border-color: var(--error) !important;
        }

        .campo-valido {
            border-color: var(--success) !important;
        }

        .status-message {
            grid-column: span 2;
            padding: 1rem;
            text-align: center;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            margin-top: 1rem;
            animation: slideDown 0.3s ease;
        }

        .status-success {
            background-color: rgba(56, 161, 105, 0.2);
            color: #2f855a;
            border: 1px solid var(--success);
        }

        .status-error {
            background-color: rgba(229, 62, 62, 0.2);
            color: #c53030;
            border: 1px solid var(--error);
        }

        .form-footer {
            grid-column: span 2;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
        }

        .cancel-btn {
            background-color: white;
            color: var(--primary);
            border: 1px solid var(--primary);
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .cancel-btn:hover {
            background-color: rgba(14, 43, 59, 0.05);
            border-color: var(--primary-hover);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .submit-btn,
            .status-message,
            .form-footer {
                grid-column: span 1;
            }

            .form-group.full-width {
                grid-column: span 1;
            }

            .editar-container {
                padding: 1rem;
                margin-top: 70px;
            }

            .editar-form {
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <?php require '../partials/header.php'; ?>

    <div class="editar-container">
        <div class="editar-header">
            <h1 class="editar-title">Editar Perfil</h1>
            <button class="logout-btn" onclick="logout()">
                <i class="fas fa-sign-out-alt"></i>
                Terminar Sessão
            </button>
        </div>

        <form method="POST" class="editar-form" id="formEditar">
            <h2>Informações Pessoais</h2>

            <div class="form-grid">
                <div class="form-group">
                    <label for="nome">Nome Completo</label>
                    <input type="text" name="nome" id="nome" value="<?php echo htmlspecialchars($row['nome']); ?>"
                        required>
                    <div class="feedback-message" id="feedback-nome">O nome não pode conter números</div>
                </div>

                <div class="form-group">
                    <label for="user">Username</label>
                    <input type="text" name="user" id="user" value="<?php echo htmlspecialchars($row['user']); ?>"
                        required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($row['email']); ?>"
                        required>
                    <div class="feedback-message" id="feedback-email">Por favor, insira um email válido</div>
                </div>

                <div class="form-group">
                    <label for="telemovel">Telemóvel</label>
                    <input type="tel" name="telemovel" id="telemovel" maxlength="9"
                        value="<?php echo htmlspecialchars($row['telemovel']); ?>" required>
                    <div class="feedback-message" id="feedback-telemovel">Apenas números são permitidos</div>
                </div>

                <div class="form-group">
                    <label for="data_nascimento">Data de Nascimento</label>
                    <input type="date" name="data_nascimento" id="data_nascimento"
                        value="<?php echo htmlspecialchars($row['data_nascimento']); ?>" required>
                    <div class="feedback-message" id="feedback-idade">Você deve ter pelo menos 16 anos</div>
                </div>

                <div class="form-group">
                    <label for="pais">País</label>
                    <select name="pais" id="pais" required>
                        <option value="">Selecione um país</option>
                        <?php foreach ($paises as $pais): ?>
                            <option value="<?= htmlspecialchars($pais) ?>" <?= ($row['pais'] == $pais) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($pais) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="feedback-message" id="feedback-pais">Por favor, selecione um país</div>
                </div>

                <?php if (!empty($msg)): ?>
                    <div
                        class="status-message <?php echo strpos($msg, 'sucesso') !== false ? 'status-success' : 'status-error'; ?>">
                        <?php echo $msg; ?>
                    </div>
                <?php endif; ?>

                <div class="form-footer">
                    <a href="../perfil/perfil.php" class="cancel-btn">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-save"></i> Guardar Alterações
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const inputs = {
                nome: {
                    element: document.getElementById("nome"),
                    feedback: document.getElementById("feedback-nome"),
                    validate: (value) => /^[^\d]+$/.test(value)
                },
                email: {
                    element: document.getElementById("email"),
                    feedback: document.getElementById("feedback-email"),
                    validate: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
                },
                telemovel: {
                    element: document.getElementById("telemovel"),
                    feedback: document.getElementById("feedback-telemovel"),
                    validate: (value) => /^\d+$/.test(value)
                },
                idade: {
                    element: document.getElementById("data_nascimento"),
                    feedback: document.getElementById("feedback-idade"),
                    validate: (value) => {
                        if (!value) return false;
                        const birthDate = new Date(value);
                        const today = new Date();
                        const age = today.getFullYear() - birthDate.getFullYear();
                        const monthDiff = today.getMonth() - birthDate.getMonth();
                        return (age > 16) || (age === 16 && monthDiff >= 0);
                    }
                },
                pais: {
                    element: document.getElementById("pais"),
                    feedback: document.getElementById("feedback-pais"),
                    validate: (value) => value !== ""
                }
            };

            Object.keys(inputs).forEach(key => {
                const { element, feedback, validate } = inputs[key];

                element.addEventListener('input', () => validateField(key));
                element.addEventListener('change', () => validateField(key));
                element.addEventListener('blur', () => validateField(key));
            });

            function validateField(key) {
                const { element, feedback, validate } = inputs[key];
                const value = element.value;
                const isValid = validate(value);

                if (value.trim() === "") {
                    feedback.style.display = 'none';
                    element.classList.remove('campo-valido', 'campo-erro');
                    return;
                }

                if (isValid) {
                    element.classList.add('campo-valido');
                    element.classList.remove('campo-erro');
                    feedback.style.display = 'none';
                } else {
                    element.classList.add('campo-erro');
                    element.classList.remove('campo-valido');
                    feedback.style.display = 'block';
                    feedback.className = 'feedback-message error';
                }
            }

            document.getElementById('formEditar').addEventListener('submit', function (e) {
                let isValid = true;

                Object.keys(inputs).forEach(key => {
                    const { element, feedback, validate } = inputs[key];
                    validateField(key);

                    if (!validate(element.value)) {
                        isValid = false;
                        feedback.style.display = 'block';
                        feedback.className = 'feedback-message error';
                        element.classList.add('campo-erro');
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    const firstError = document.querySelector('.campo-erro');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });

            // Validar campos iniciais
            Object.keys(inputs).forEach(key => validateField(key));
        });

        function logout() {
            window.location.href = '../logout.php';
        }
    </script>
</body>

</html>