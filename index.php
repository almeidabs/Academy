<?php
require __DIR__ . '/app/functions.php';

$user = require_login();
$workout = selected_workout_for_user((int) $user['id']);
$workouts = read_json('workouts');
$weightHistory = weight_history((int) $user['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_weight') {
    $weight = (float) str_replace(',', '.', $_POST['weight'] ?? '0');
    $date = $_POST['weight_date'] ?? today_date();

    if ($weight > 0) {
        save_weight_entry((int) $user['id'], $date, $weight);
    }

    redirect('index.php');
}

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

    redirect('index.php');
}

$completedExercises = $workout ? workout_progress((int) $user['id'], (int) $workout['id']) : [];
$progressPercent = $workout ? workout_progress_percent($workout, $completedExercises) : 0;
$calendarDate = $_GET['date'] ?? null;
$monthStart = month_start_date($calendarDate);
$calendarDays = month_calendar_days($monthStart);
$previousDate = date('Y-m-d', strtotime('-1 month', strtotime($monthStart)));
$nextDate = date('Y-m-d', strtotime('+1 month', strtotime($monthStart)));
$dailyHistoryByDate = daily_workout_history_by_date((int) $user['id']);
$today = today_date();
$latestWeight = $weightHistory ? (float) end($weightHistory)['weight'] : 0.0;
$firstWeight = $weightHistory ? (float) $weightHistory[0]['weight'] : 0.0;
$previousWeight = count($weightHistory) > 1 ? (float) $weightHistory[count($weightHistory) - 2]['weight'] : $latestWeight;
$weightDelta = $latestWeight - $previousWeight;
$totalWeightDelta = $latestWeight - $firstWeight;
$weights = array_map(static fn (array $item): float => (float) $item['weight'], $weightHistory);
$minWeight = $weights ? min($weights) : 0;
$maxWeight = $weights ? max($weights) : 1;
$rangeWeight = max(1, $maxWeight - $minWeight);
$chartPoints = [];

foreach ($weightHistory as $index => $item) {
    $x = count($weightHistory) > 1 ? 12 + (($index / (count($weightHistory) - 1)) * 276) : 150;
    $y = 124 - ((((float) $item['weight'] - $minWeight) / $rangeWeight) * 78);
    $chartPoints[] = [
        'x' => $x,
        'y' => $y,
        'date' => date('d/m/Y', strtotime($item['date'])),
        'weight' => (float) $item['weight'],
    ];
}

$polylinePoints = implode(' ', array_map(
    static fn (array $point): string => number_format($point['x'], 1, '.', '') . ',' . number_format($point['y'], 1, '.', ''),
    $chartPoints
));
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Academy Control</title>
    <script>document.documentElement.dataset.theme = localStorage.getItem('academy-theme') || 'light';</script>
    <link rel="stylesheet" href="public/style.css">
</head>
<body>
    <?php require __DIR__ . '/partials/topbar.php'; ?>

    <main class="page stack">
        <section class="schedule-panel">
            <div class="schedule-head">
                <span>Cronograma de treino</span>
                <a href="workouts.php">Mais</a>
            </div>

            <div class="schedule-month-bar">
                <a class="schedule-arrow" href="index.php?date=<?= e($previousDate) ?>" aria-label="Mes anterior">&lsaquo;</a>
                <h2><?= e(ucfirst(month_label($monthStart))) ?></h2>
                <a class="schedule-arrow" href="index.php?date=<?= e($nextDate) ?>" aria-label="Proximo mes">&rsaquo;</a>
            </div>

            <div class="schedule-weekdays">
                <span>Dom</span>
                <span>Seg</span>
                <span>Ter</span>
                <span>Qua</span>
                <span>Qui</span>
                <span>Sex</span>
                <span>Sab</span>
            </div>

            <div class="schedule-calendar">
                <?php foreach ($calendarDays as $calendarDay): ?>
                    <?php
                    $day = $calendarDay['date'];
                    $record = $dailyHistoryByDate[$day] ?? null;
                    $percent = $record ? (int) $record['percent'] : 0;
                    $isToday = $day === $today;
                    ?>
                    <div class="schedule-day <?= $calendarDay['current_month'] ? '' : 'muted' ?> <?= $isToday ? 'today' : '' ?>">
                        <div class="schedule-date"><?= e(date('j', strtotime($day))) ?></div>
                        <?php if ($record): ?>
                            <div class="schedule-mark" title="<?= e($record['workout_name']) ?>">
                                <span></span><span></span><span></span>
                            </div>
                            <div class="schedule-percent <?= $percent === 100 ? 'complete' : '' ?>"><?= $percent ?>%</div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="hero">
            <div class="panel weight-panel" data-weight-panel>
                <div class="weight-head">
                    <span>Meu peso</span>
                    <button type="button" class="weight-more" data-weight-toggle>Mais</button>
                </div>
                <div class="weight-stats">
                    <span>Peso atual: <strong><?= e(format_weight($latestWeight)) ?> kg</strong></span>
                    <strong><?= e(($weightDelta >= 0 ? '+' : '') . format_weight($weightDelta)) ?> kg</strong>
                    <strong><?= e(($totalWeightDelta >= 0 ? '+' : '') . format_weight($totalWeightDelta)) ?> kg <?= $totalWeightDelta <= 0 ? '↓' : '↑' ?></strong>
                </div>

                <div class="weight-chart">
                    <svg viewBox="0 0 300 154" role="img" aria-label="Grafico de evolucao do peso">
                        <g class="chart-grid">
                            <line x1="0" y1="22" x2="300" y2="22"></line>
                            <line x1="0" y1="48" x2="300" y2="48"></line>
                            <line x1="0" y1="74" x2="300" y2="74"></line>
                            <line x1="0" y1="100" x2="300" y2="100"></line>
                            <line x1="0" y1="126" x2="300" y2="126"></line>
                        </g>
                        <?php if ($chartPoints): ?>
                            <polygon class="chart-area" points="12,134 <?= e($polylinePoints) ?> 288,134"></polygon>
                            <polyline class="chart-line" points="<?= e($polylinePoints) ?>"></polyline>
                            <?php foreach ($chartPoints as $point): ?>
                                <circle class="chart-point" cx="<?= e((string) $point['x']) ?>" cy="<?= e((string) $point['y']) ?>" r="4"></circle>
                                <text class="chart-bubble" x="<?= e((string) $point['x']) ?>" y="<?= e((string) ($point['y'] - 16)) ?>"><?= e(format_weight($point['weight'])) ?></text>
                                <text class="chart-date" x="<?= e((string) $point['x']) ?>" y="150"><?= e($point['date']) ?></text>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </svg>
                </div>

                <form class="weight-form" method="post" data-weight-form>
                    <input type="hidden" name="action" value="save_weight">
                    <label>
                        Data
                        <input type="date" name="weight_date" value="<?= e(today_date()) ?>" required>
                    </label>
                    <label>
                        Peso
                        <input type="number" name="weight" step="0.1" min="1" value="<?= e((string) $latestWeight) ?>" required>
                    </label>
                    <button type="submit">Salvar peso</button>
                </form>
            </div>

            <div class="panel">
                <h2>Proximo treino</h2>
                <?php if ($workout): ?>
                    <div class="workout-card-list">
                        <?php foreach ($workouts as $index => $item): ?>
                            <?php
                            $itemCompleted = workout_progress((int) $user['id'], (int) $item['id']);
                            $itemPercent = workout_progress_percent($item, $itemCompleted);
                            $isSelectedWorkout = (int) $item['id'] === (int) $workout['id'];
                            ?>
                            <article class="workout-next-card <?= $isSelectedWorkout ? 'active' : '' ?> <?= $itemPercent === 100 ? 'complete' : '' ?>">
                                <div class="workout-ring" style="--percent: <?= $itemPercent ?>;">
                                    <span><?= $itemPercent ?>%</span>
                                </div>
                                <div class="workout-next-content">
                                    <span><?= (int) $index + 1 ?> dia de treino</span>
                                    <strong><?= e($item['name']) ?></strong>
                                </div>
                                <span class="workout-dot"></span>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>Nenhum treino selecionado ainda.</p>
                    <a class="button warning" href="select-workout.php">Escolher agora</a>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <script src="public/weight-panel.js"></script>
</body>
</html>
