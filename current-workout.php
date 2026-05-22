<?php
require __DIR__ . '/app/functions.php';

$user = require_login();
$workout = selected_workout_for_user((int) $user['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $workout) {
    $action = $_POST['action'] ?? '';
    $workoutId = (int) $workout['id'];

    if ($action === 'toggle_exercise') {
        toggle_exercise_progress((int) $user['id'], $workoutId, (int) ($_POST['exercise_index'] ?? -1));
    }

    if ($action === 'reset_progress') {
        reset_workout_progress((int) $user['id'], $workoutId);
    }

    $updatedCompletedExercises = workout_progress((int) $user['id'], $workoutId);
    save_daily_workout_progress((int) $user['id'], $workout, $updatedCompletedExercises);

    redirect('current-workout.php');
}

$completedExercises = $workout ? workout_progress((int) $user['id'], (int) $workout['id']) : [];
$progressPercent = $workout ? workout_progress_percent($workout, $completedExercises) : 0;
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Treino atual - Academy Control</title>
    <script>document.documentElement.dataset.theme = localStorage.getItem('academy-theme') || 'light';</script>
    <link rel="stylesheet" href="public/style.css">
</head>
<body>
    <?php require __DIR__ . '/partials/topbar.php'; ?>

    <main class="page stack">
        <?php if ($workout): ?>
            <section class="panel selected-workout-panel">
                <div class="selected-workout-head">
                    <div>
                        <h1>Selecionado agora</h1>
                        <div class="meta">
                            <span class="badge"><?= e($workout['goal']) ?></span>
                            <span class="badge"><?= e($workout['level']) ?></span>
                        </div>
                        <h2><?= e($workout['name']) ?></h2>
                    </div>
                    <div class="workout-ring <?= $progressPercent === 100 ? 'complete' : '' ?>" style="--percent: <?= $progressPercent ?>;">
                        <span><?= $progressPercent ?>%</span>
                    </div>
                </div>

                <div class="progress-summary">
                    <div class="progress-label">
                        <strong><?= $progressPercent ?>%</strong>
                        <span><?= count($completedExercises) ?> de <?= count($workout['exercises']) ?> exercicios feitos</span>
                    </div>
                    <div class="progress-bar" aria-label="Progresso do treino">
                        <div class="progress-fill <?= $progressPercent === 100 ? 'complete' : '' ?>" style="width: <?= $progressPercent ?>%"></div>
                    </div>
                </div>
            </section>

            <section class="panel">
                <h2>Marcacao do treino</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Exercicio</th>
                            <th>Series</th>
                            <th>Reps</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($workout['exercises'] as $index => $exercise): ?>
                            <?php $isDone = in_array($index, $completedExercises, true); ?>
                            <tr class="<?= $isDone ? 'exercise-done' : '' ?>">
                                <td><?= e($exercise['name']) ?></td>
                                <td><?= e($exercise['sets']) ?></td>
                                <td><?= e($exercise['reps']) ?></td>
                                <td>
                                    <form class="inline-form" method="post">
                                        <input type="hidden" name="action" value="toggle_exercise">
                                        <input type="hidden" name="exercise_index" value="<?= (int) $index ?>">
                                        <button class="button <?= $isDone ? 'complete' : 'secondary' ?>" type="submit">
                                            <?= $isDone ? 'Feito' : 'Marcar' ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($completedExercises): ?>
                    <form class="inline-form progress-reset" method="post">
                        <input type="hidden" name="action" value="reset_progress">
                        <button class="button secondary" type="submit">Reiniciar progresso</button>
                    </form>
                <?php endif; ?>
            </section>
        <?php else: ?>
            <section class="panel">
                <h1>Nenhum treino selecionado</h1>
                <p>Selecione um treino para acompanhar a marcacao dos exercicios.</p>
                <a class="button" href="select-workout.php">Selecionar treino</a>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
