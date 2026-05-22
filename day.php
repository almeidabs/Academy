<?php
require __DIR__ . '/app/functions.php';

$user = require_login();
$date = $_GET['date'] ?? today_date();
$timestamp = strtotime($date);

if (!$timestamp) {
    $date = today_date();
    $timestamp = strtotime($date);
}

$record = daily_workout_record((int) $user['id'], date('Y-m-d', $timestamp));
$workout = $record ? find_by_id(read_json('workouts'), (int) $record['workout_id']) : selected_workout_for_user((int) $user['id']);
$completedExercises = $record['completed_exercises'] ?? [];
$percent = $record ? (int) $record['percent'] : 0;
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Treinos do dia - Academy Control</title>
    <script>document.documentElement.dataset.theme = localStorage.getItem('academy-theme') || 'light';</script>
    <link rel="stylesheet" href="public/style.css">
</head>
<body>
    <?php require __DIR__ . '/partials/topbar.php'; ?>

    <main class="page stack">
        <section class="panel">
            <a href="index.php" class="back-link">Voltar ao calendario</a>
            <h1><?= e(date('d/m/Y', $timestamp)) ?></h1>
            <p><?= e(weekday_name_pt(date('Y-m-d', $timestamp)) ?: 'domingo/sabado') ?></p>

            <?php if ($record): ?>
                <div class="progress-summary">
                    <div class="progress-label">
                        <strong><?= $percent ?>%</strong>
                        <span><?= (int) $record['completed'] ?> de <?= (int) $record['total'] ?> exercicios feitos</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill <?= $percent === 100 ? 'complete' : '' ?>" style="width: <?= $percent ?>%"></div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert">Nenhum treino foi registrado neste dia.</div>
            <?php endif; ?>
        </section>

        <?php if ($workout): ?>
            <section class="panel">
                <div class="meta">
                    <span class="badge"><?= e($workout['goal']) ?></span>
                    <span class="badge"><?= e($workout['level']) ?></span>
                </div>
                <h2><?= e($workout['name']) ?></h2>
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
                            <?php $isDone = $record && in_array($index, $completedExercises, true); ?>
                            <tr class="<?= $isDone ? 'exercise-done' : '' ?>">
                                <td><?= e($exercise['name']) ?></td>
                                <td><?= e($exercise['sets']) ?></td>
                                <td><?= e($exercise['reps']) ?></td>
                                <td>
                                    <span class="status-pill <?= $isDone ? 'complete' : 'partial' ?>">
                                        <?= $isDone ? 'Feito' : 'Pendente' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
