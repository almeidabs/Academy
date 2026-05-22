<?php
require __DIR__ . '/app/functions.php';

start_app_session();

$loggedUser = current_user();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirmation = $_POST['password_confirmation'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $error = 'Preencha todos os campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Informe um e-mail valido.';
    } elseif (strlen($password) < 6) {
        $error = 'A senha precisa ter pelo menos 6 caracteres.';
    } elseif ($password !== $passwordConfirmation) {
        $error = 'A confirmacao de senha nao confere.';
    } elseif (find_user_by_email($email)) {
        $error = 'Ja existe um usuario com este e-mail.';
    } else {
        create_user($name, $email, $password);

        if ($loggedUser) {
            redirect('users.php');
        }

        $user = verify_user_login($email, $password);

        if ($user) {
            $_SESSION['user_id'] = (int) $user['id'];
            redirect('index.php');
        }

        $error = 'Nao foi possivel entrar com o usuario criado.';
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Criar usuario - Academy Control</title>
    <script>document.documentElement.dataset.theme = localStorage.getItem('academy-theme') || 'light';</script>
    <link rel="stylesheet" href="public/style.css">
</head>
<body class="login-page">
    <main class="login panel">
        <h1>Criar usuario</h1>
        <p>Cadastre seu acesso para selecionar e acompanhar seus treinos.</p>

        <?php if ($error): ?>
            <div class="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <label>
                Nome
                <input name="name" value="<?= e($_POST['name'] ?? '') ?>" required>
            </label>
            <label>
                E-mail
                <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required>
            </label>
            <label>
                Senha
                <span class="password-field">
                    <input type="password" name="password" required>
                    <button type="button" class="password-toggle" data-password-toggle>Mostrar</button>
                </span>
            </label>
            <label>
                Confirmar senha
                <span class="password-field">
                    <input type="password" name="password_confirmation" required>
                    <button type="button" class="password-toggle" data-password-toggle>Mostrar</button>
                </span>
            </label>
            <button type="submit">Criar usuario</button>
        </form>
        <p>Ja tem usuario? <a href="login.php">Entrar</a></p>
    </main>
    <script src="public/password-toggle.js"></script>
    <script src="public/theme-toggle.js"></script>
</body>
</html>
