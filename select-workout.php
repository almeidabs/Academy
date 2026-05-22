<?php
require __DIR__ . '/app/functions.php';

$user = require_login();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $workoutId = (int) ($_POST['workout_id'] ?? 0);
    $workout = find_by_id(read_json('workouts'), $workoutId);

    if (!$workout) {
        $error = 'Selecione um treino valido.';
    } else {
        $assignments = read_json('assignments');
        $assignments[] = [
            'id' => next_id($assignments),
            'user_id' => (int) $user['id'],
            'workout_id' => $workoutId,
            'selected_at' => date('Y-m-d H:i:s'),
        ];
        write_json('assignments', $assignments);
        redirect('current-workout.php');
    }
}

$workouts = read_json('workouts');
$currentWorkout = selected_workout_for_user((int) $user['id']);
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Selecionar treino - Academy Control</title>
    <script>document.documentElement.dataset.theme = localStorage.getItem('academy-theme') || 'light';</script>
    <link rel="stylesheet" href="public/style.css">
</head>
<body>
    <?php require __DIR__ . '/partials/topbar.php'; ?>

    <main class="page stack">
        <section class="panel">
            <h1>Selecionar treino</h1>
            <p>Escolha qual ficha ficara ativa para o usuario <?= e($user['name']) ?>.</p>

            <?php if ($success): ?>
                <div class="success"><?= e($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post">
                <label>
                    Treino
                    <select name="workout_id" required>
                        <?php foreach ($workouts as $workout): ?>
                            <option value="<?= (int) $workout['id'] ?>" <?= $currentWorkout && (int) $currentWorkout['id'] === (int) $workout['id'] ? 'selected' : '' ?>>
                                <?= e($workout['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button type="submit">Salvar selecao</button>
            </form>
        </section>

        <?php if ($currentWorkout): ?>
            <section class="panel">
                <h2>Selecionado agora</h2>
                <div class="meta">
                    <span class="badge"><?= e($currentWorkout['goal']) ?></span>
                    <span class="badge"><?= e($currentWorkout['level']) ?></span>
                </div>
                <h3><?= e($currentWorkout['name']) ?></h3>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
