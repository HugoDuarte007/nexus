<?php session_start();
include 'partials/paises.php';

?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="imagens/favicon.ico" type="image/png">
    <title>Nexus | Sign Up</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        /* Estilo para os erros */
        .notificacao {
            display: none;
            color: rgb(218, 165, 32);
            font-size: 13px;
            margin-top: 5px;
        }

        .campo-erro {
            border: 1px solid rgb(255, 0, 0);
        }

        .signup-button {
            padding: 10px 20px;
            font-size: 1rem;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            background-color: white;
            color: black;
            transition: 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            width: 100%;
        }

        .signup-button:hover {
            background-color: black;
            color: white;
        }

        h2 img {
            width: 40px;
            height: auto;
            margin-right: 10px;

        }
    </style>
</head>

<body>
    <div class="container" style="justify-content: center;">
        <img src="imagens/fixo_portatil_telemovel.png" alt="platforms" class="platforms">
        <div class="form-container">
            <h2><a href="index.php"><img src="imagens/logo4.png"></a><br>
                Criar Conta</h2><br>
            <?php
            if (isset($_SESSION["erro"])) {
                echo "<p style='color:red;'>" . $_SESSION["erro"] . "</p>";
                unset($_SESSION["erro"]);
            }
            if (isset($_SESSION["sucesso"])) {
                echo "<p style='color:green;'>" . $_SESSION["sucesso"] . "</p>";
                unset($_SESSION["sucesso"]);
            }
            ?>
            <form id="signupForm" action="inserir.php" method="post">
                <div class="form-group">
                    <div class="column">
                        <input type="text" name="nome" id="nome" class="input" placeholder="Nome*">
                        <div class="notificacao" id="erro-nome">O nome não pode conter números.</div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="column">
                        <input type="text" name="email" id="email" class="input" placeholder="Email*">
                        <div class="notificacao" id="erro-email">O email não é válido.</div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="column">
                        <input type="text" name="user" class="input" placeholder="Nome de Utilizador*">
                    </div>
                </div>
                <div class="form-group">
                    <div class="column">
                        <input type="text" name="telemovel" id="telemovel" class="input" maxlength="9"
                            placeholder="Telemóvel*">
                        <div class="notificacao" id="erro-telemovel">O telemóvel não pode conter letras.</div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="column">
                        <input type="date" name="data_nascimento" id="data_nascimento" class="input">
                        <div class="notificacao" id="erro-idade">Deve ter pelo menos 16 anos e inserir uma data válida.
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="column">
                        <select name="pais" id="pais" class="input">
                            <option value="" disabled selected>Escolhe o país*</option>
                            <?php foreach ($paises as $pais): ?>
                                <option value="<?= htmlspecialchars($pais) ?>"><?= htmlspecialchars($pais) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="notificacao" id="erro-pais">Por favor, escolhe um país.</div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="column">
                        <div style="position: relative;">
                            <input type="password" name="password" id="password" class="input"
                                placeholder="Palavra-Passe*">
                            <button type="button" id="togglePassword"
                                style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #555; font-size: 12px; cursor: pointer;">
                                Mostrar
                            </button>
                        </div>
                        <div class="notificacao" id="erro-password">
                            A palavra-passe deve conter pelo menos 8 caracteres, incluindo maiúscula, minúscula, número
                            e caractere especial.
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="column">
                        <input type="checkbox" id="checkbox" name="checkbox" value="1">
                        <label for="checkbox">Aceito os <a href="termos.html" target="_blank">termos e
                                condições</a>*</label>
                        <div class="notificacao" id="erro-checkbox">É necessário aceitar os termos e condições.</div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="column">
                        <button id="signup-button" type="submit" class="signup-button"
                            name="botaoInserir">Registar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById("togglePassword").addEventListener("click", function () {
            const passwordInput = document.getElementById("password");
            const isPassword = passwordInput.type === "password";

            passwordInput.type = isPassword ? "text" : "password";
            this.textContent = isPassword ? "Ocultar" : "Mostrar";
        });


        document.addEventListener("DOMContentLoaded", function () {
            const form = document.getElementById("signupForm");
            const inputs = {
                nome: {
                    element: document.getElementById("nome"),
                    regex: /^[^\d]+$/,
                    error: document.getElementById("erro-nome"),
                },
                email: {
                    element: document.getElementById("email"),
                    regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                    error: document.getElementById("erro-email")
                },
                telemovel: {
                    element: document.getElementById("telemovel"),
                    regex: /^\d+$/, // Permite apenas dígitos numéricos
                    error: document.getElementById("erro-telemovel"),
                },

                idade: {
                    element: document.getElementById("data_nascimento"),
                    validate: (valor) => {
                        const birthDate = new Date(valor);
                        const today = new Date();
                        const idade = today.getFullYear() - birthDate.getFullYear();
                        const mes = today.getMonth() - birthDate.getMonth();
                        return (
                            idade > 16 ||
                            (idade === 16 && (mes > 0 || (mes === 0 && today.getDate() >= birthDate.getDate())))
                        );
                    },
                    error: document.getElementById("erro-idade"),
                },
                password: {
                    element: document.getElementById("password"),
                    regex: /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>]).{8,}$/,
                    error: document.getElementById("erro-password"),
                },
                checkbox: {
                    element: document.getElementById("checkbox"),
                    validate: (valor) => valor === true,
                    error: document.getElementById("erro-checkbox"),
                },
            };

            Object.keys(inputs).forEach((key) => {
                const input = inputs[key];
                const element = input.element;

                element.addEventListener("input", function () {
                    validarCampo(key);
                });

                element.addEventListener("change", function () {
                    validarCampo(key);
                });
            });

            function validarCampo(key) {
                const input = inputs[key];
                const valor = input.element.type === "checkbox"
                    ? input.element.checked
                    : input.element.value;

                const valido = input.regex
                    ? input.regex.test(valor)
                    : input.validate(valor);

                if (valido) {
                    input.error.style.display = "none";
                    input.element.classList.remove("campo-erro");
                } else {
                    input.error.style.display = "block";
                    input.element.classList.add("campo-erro");
                }
            }

            form.addEventListener("submit", function (e) {
                let formValido = true;
                Object.keys(inputs).forEach((key) => {
                    const input = inputs[key];
                    validarCampo(key);
                    if (input.error.style.display === "block") {
                        formValido = false;
                    }
                });

                if (!formValido) {
                    e.preventDefault();
                }
            });
        });
    </script>
    <footer>
        © 2025 Nexus. Todos os direitos reservados.
    </footer>
</body>

</html>