<?php
require __DIR__ . '/app/functions.php';

start_app_session();

if (current_user()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $user = verify_user_login($email, $password);

    if ($user) {
        $_SESSION['user_id'] = (int) $user['id'];
        redirect('index.php');
    }

    $error = 'E-mail ou senha invalidos.';
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar - Academy Control</title>
    <script>document.documentElement.dataset.theme = localStorage.getItem('academy-theme') || 'light';</script>
    <link rel="stylesheet" href="public/style.css">
</head>
<body class="login-page">
    <main class="login panel">
        <h1>Academy Control</h1>
        <p>Acesse sua area para controlar e selecionar seus treinos.</p>

        <?php if ($error): ?>
            <div class="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <label>
                E-mail
                <input type="email" name="email" value="aluno@academia.com" required>
            </label>
            <label>
                Senha
                <span class="password-field">
                    <input type="password" name="password" value="123456" required>
                    <button type="button" class="password-toggle" data-password-toggle>Mostrar</button>
                </span>
            </label>
            <button type="submit">Entrar</button>
        </form>
        <p>Nao tem usuario? <a href="register.php">Criar usuario</a></p>
    </main>
    <script src="public/password-toggle.js"></script>
    <script src="public/theme-toggle.js"></script>
</body>
</html>
