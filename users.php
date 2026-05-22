<?php
require __DIR__ . '/app/functions.php';

require_login();

$users = db()
    ->query('SELECT id, name, email, created_at FROM academy_users ORDER BY name')
    ->fetchAll();
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Usuarios - Academy Control</title>
    <script>document.documentElement.dataset.theme = localStorage.getItem('academy-theme') || 'light';</script>
    <link rel="stylesheet" href="public/style.css">
</head>
<body>
    <?php require __DIR__ . '/partials/topbar.php'; ?>

    <main class="page stack">
        <section class="panel">
            <h1>Usuarios</h1>
            <p>Usuarios cadastrados no banco MySQL.</p>
            <a class="button" href="register.php">Criar usuario</a>
        </section>

        <section class="panel">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Criado em</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= (int) $user['id'] ?></td>
                            <td><?= e($user['name']) ?></td>
                            <td><?= e($user['email']) ?></td>
                            <td><?= e($user['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
