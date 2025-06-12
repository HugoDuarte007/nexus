<!DOCTYPE html>
    <html lang='pt'>
    <head>

        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Verificação Nexus</title>
    </head>
    <body style='font-family: Arial, sans-serif; background-color: #ffffff; color: #ffffff; padding: 20px; text-align: center;'>
        <div style='max-width: 600px; margin: auto; background-color: #0e2b3b; padding: 20px; border-radius: 10px;'>
            <img src='https://cdn.pixabay.com/photo/2025/02/05/09/29/internet-9383803_1280.png' alt='Nexus Logo' style='width: 80px; margin-bottom: 10px;'>
            <h2 style='color: #ffffff;'>Bem-vindo à Nexus!</h2>
            <p>Olá <strong>{$nome}</strong>,</p>
            <p>Parece que estás a tentar criar uma conta na Nexus. Aqui está o código de verificação que precisas para continuar:</p>
            <div style='background-color: #ffffff; padding: 10px; border-radius: 5px; display: inline-block; margin: 10px 0;'>
                <h1 style   ='color: #0e2b3b; margin: 0;'>{$codigo}</h1>
            </div>
            <p>Se não foste tu que fizeste este pedido, ignora este e-mail.</p>
            <p style='color: #555;'>Atenciosamente, <br> Equipa Nexus</p>
        </div>
    </body>
    </html>