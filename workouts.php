<?php
require __DIR__ . '/app/functions.php';

$user = require_login();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create_workout';

    if ($action === 'delete_workout') {
        delete_workout((int) ($_POST['workout_id'] ?? 0));
        redirect('workouts.php');
    }

    $name = trim($_POST['name'] ?? '');
    $goal = trim($_POST['goal'] ?? '');
    $level = trim($_POST['level'] ?? '');
    $exerciseLines = preg_split('/\r\n|\r|\n/', trim($_POST['exercises'] ?? ''));
    $exercises = [];

    foreach ($exerciseLines as $line) {
        $parts = array_map('trim', explode(';', $line));

        if (count($parts) === 3 && $parts[0] !== '') {
            $exercises[] = [
                'name' => $parts[0],
                'sets' => $parts[1],
                'reps' => $parts[2],
            ];
        }
    }

    if ($name === '' || $goal === '' || $level === '' || !$exercises) {
        $error = 'Preencha o nome, objetivo, nivel e ao menos um exercicio no formato correto.';
    } else {
        $workouts = read_json('workouts');
        $workouts[] = [
            'id' => next_id($workouts),
            'name' => $name,
            'goal' => $goal,
            'level' => $level,
            'exercises' => $exercises,
        ];
        write_json('workouts', $workouts);
        $success = 'Treino cadastrado com sucesso.';
    }
}

$workouts = read_json('workouts');
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Treinos - Academy Control</title>
    <script>document.documentElement.dataset.theme = localStorage.getItem('academy-theme') || 'light';</script>
    <link rel="stylesheet" href="public/style.css">
</head>
<body>
    <?php require __DIR__ . '/partials/topbar.php'; ?>

    <main class="page stack">
        <section class="panel">
            <h1>Treinos</h1>
            <p>Cadastre fichas com exercicios, series e repeticoes. Use uma linha por exercicio.</p>

            <?php if ($success): ?>
                <div class="success"><?= e($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="grid">
                    <label>
                        Nome do treino
                        <input name="name" placeholder="Treino C - Pernas" required>
                    </label>
                    <label>
                        Objetivo
                        <input name="goal" placeholder="Hipertrofia, emagrecimento..." required>
                    </label>
                    <label>
                        Nivel
                        <select name="level" required>
                            <option>Iniciante</option>
                            <option>Intermediario</option>
                            <option>Avancado</option>
                        </select>
                    </label>
                </div>
                <label>
                    Exercicios
                    <textarea name="exercises" placeholder="Agachamento livre;4;8-10&#10;Leg press;3;12&#10;Cadeira extensora;3;12" required></textarea>
                </label>
                <button type="submit">Cadastrar treino</button>
            </form>
        </section>

        <section class="grid">
            <?php foreach ($workouts as $workout): ?>
                <?php
                $completedExercises = workout_progress((int) $user['id'], (int) $workout['id']);
                $progressPercent = workout_progress_percent($workout, $completedExercises);
                $totalExercises = count($workout['exercises']);
                ?>
                <article class="card">
                    <div class="card-head">
                        <div class="meta">
                            <span class="badge"><?= e($workout['goal']) ?></span>
                            <span class="badge"><?= e($workout['level']) ?></span>
                        </div>
                        <div class="workout-ring small <?= $progressPercent === 100 ? 'complete' : '' ?>" style="--percent: <?= $progressPercent ?>;">
                            <span><?= $progressPercent ?>%</span>
                        </div>
                    </div>
                    <h2><?= e($workout['name']) ?></h2>
                    <div class="progress-summary compact">
                        <div class="progress-label">
                            <strong><?= $progressPercent ?>%</strong>
                            <span><?= count($completedExercises) ?> de <?= $totalExercises ?> exercicios feitos</span>
                        </div>
                        <div class="progress-bar" aria-label="Progresso do treino">
                            <div class="progress-fill <?= $progressPercent === 100 ? 'complete' : '' ?>" style="width: <?= $progressPercent ?>%"></div>
                        </div>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Exercicio</th>
                                <th>Series</th>
                                <th>Reps</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($workout['exercises'] as $exercise): ?>
                                <tr>
                                    <td><?= e($exercise['name']) ?></td>
                                    <td><?= e($exercise['sets']) ?></td>
                                    <td><?= e($exercise['reps']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="card-actions">
                        <a class="button secondary" href="select-workout.php">Selecionar</a>
                        <form class="inline-form" method="post" onsubmit="return confirm('Excluir este treino? Esta acao tambem remove selecoes e progresso relacionados.');">
                            <input type="hidden" name="action" value="delete_workout">
                            <input type="hidden" name="workout_id" value="<?= (int) $workout['id'] ?>">
                            <button class="button danger" type="submit">Excluir treino</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    </main>
</body>
</html>
