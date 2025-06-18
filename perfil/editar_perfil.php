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
    <title>Nexus | Editar Perfil</title>
    <style>
        body {
            background-color: #ffffff;
            color: #333333;
            font-family: 'Arial', sans-serif;
        }

        .editar-container {
            width: 100%;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
            background-color: #ffffff;
        }

        .editar-form {
            width: 100%;
            max-width: 800px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 40px;
            margin-top: 30px;
            border: 1px solid #e1e8ed;
        }

        .editar-form h2 {
            text-align: center;
            color: #1a365d;
            margin-bottom: 30px;
            font-size: 2rem;
            font-weight: 700;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c5282;
            font-size: 0.95rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid #cbd5e0;
            background-color: #ffffff;
            color: #4a5568;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #3182ce;
            outline: none;
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.2);
        }

        .submit-btn {
            grid-column: span 2;
            background-color: #3182ce;
            color: white;
            border: none;
            padding: 14px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 20px;
        }

        .submit-btn:hover {
            background-color: #2b6cb0;
            transform: translateY(-2px);
        }

        .feedback-message {
            padding: 8px 12px;
            margin-top: 6px;
            border-radius: 6px;
            font-size: 0.85rem;
            display: none;
        }

        .error {
            background-color: rgba(245, 101, 101, 0.1);
            color: #e53e3e;
            border-left: 3px solid #e53e3e;
            display: block;
        }

        .success {
            background-color: rgba(72, 187, 120, 0.1);
            color: #38a169;
            border-left: 3px solid #38a169;
            display: block;
        }

        .campo-erro {
            border-color: #e53e3e !important;
        }

        .campo-valido {
            border-color: #38a169 !important;
        }

        .status-message {
            grid-column: span 2;
            padding: 15px;
            text-align: center;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            margin-top: 20px;
        }

        .status-success {
            background-color: rgba(72, 187, 120, 0.2);
            color: #2f855a;
            border: 1px solid #38a169;
        }

        .status-error {
            background-color: rgba(245, 101, 101, 0.2);
            color: #c53030;
            border: 1px solid #e53e3e;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .submit-btn {
                grid-column: span 1;
            }
            
            .status-message {
                grid-column: span 1;
            }
        }
    </style>
</head>

<body>
    <?php require '../partials/header.php'; ?>

    <div class="editar-container">
        <form method="POST" class="editar-form" id="formEditar">
            <h2>Editar Perfil</h2>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="nome">Nome Completo</label>
                    <input type="text" name="nome" id="nome" value="<?php echo htmlspecialchars($row['nome']); ?>" required>
                    <div class="feedback-message" id="feedback-nome">O nome não pode conter números</div>
                </div>

                <div class="form-group">
                    <label for="user">Username</label>
                    <input type="text" name="user" id="user" value="<?php echo htmlspecialchars($row['user']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($row['email']); ?>" required>
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
                            <option value="<?= htmlspecialchars($pais) ?>" 
                                <?= ($row['pais'] == $pais) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($pais) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="feedback-message" id="feedback-pais">Por favor, selecione um país</div>
                </div>

                <button type="submit" class="submit-btn">Guardar Alterações</button>

                <?php if (!empty($msg)): ?>
                    <div class="status-message <?php echo strpos($msg, 'sucesso') !== false ? 'status-success' : 'status-error'; ?>">
                        <?php echo $msg; ?>
                    </div>
                <?php endif; ?>
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

            document.getElementById('formEditar').addEventListener('submit', function(e) {
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
    </script>
</body>

</html>