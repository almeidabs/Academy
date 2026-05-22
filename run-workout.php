<?php
require __DIR__ . '/app/functions.php';

$user = require_login();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $km = (float) str_replace(',', '.', $_POST['km'] ?? '0');
    $seconds = (int) ($_POST['seconds'] ?? 0);
    $date = $_POST['date'] ?? today_date();

    if ($km <= 0 || $seconds <= 0) {
        $error = 'Informe os KM percorridos e use o cronometro antes de salvar.';
    } else {
        save_running_session((int) $user['id'], $date, $km, $seconds);
        redirect('run-workout.php');
    }
}

$history = running_history((int) $user['id']);
$totalKm = array_sum(array_map(static fn (array $item): float => (float) $item['km'], $history));
$totalSeconds = array_sum(array_map(static fn (array $item): int => (int) $item['seconds'], $history));
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Treino Corrida - Academy Control</title>
    <script>document.documentElement.dataset.theme = localStorage.getItem('academy-theme') || 'light';</script>
    <link rel="stylesheet" href="public/style.css">
</head>
<body>
    <?php require __DIR__ . '/partials/topbar.php'; ?>

    <main class="page stack">
        <section class="run-layout">
            <div class="panel run-panel">
                <h1>Treino Corrida</h1>
                <p>Cronometre o treino, informe a distancia e salve o registro.</p>

                <?php if ($error): ?>
                    <div class="alert"><?= e($error) ?></div>
                <?php endif; ?>

                <div class="run-timer" data-run-display>00:00:00</div>

                <div class="run-actions">
                    <button type="button" data-run-start>Iniciar</button>
                    <button type="button" class="button secondary" data-run-pause>Pausar</button>
                    <button type="button" class="button secondary" data-run-reset>Zerar</button>
                </div>

                <form method="post" class="run-form">
                    <input type="hidden" name="seconds" value="0" data-run-seconds>
                    <label>
                        Data
                        <input type="date" name="date" value="<?= e(today_date()) ?>" required>
                    </label>
                    <label>
                        KM feito
                        <input type="number" name="km" step="0.01" min="0.01" placeholder="Ex: 5.25" required>
                    </label>
                    <button type="submit">Salvar corrida</button>
                </form>
            </div>

            <div class="panel run-summary">
                <h2>Resumo</h2>
                <div class="run-stat-grid">
                    <div>
                        <span>Total KM</span>
                        <strong><?= e(number_format($totalKm, 2, ',', '.')) ?></strong>
                    </div>
                    <div>
                        <span>Tempo total</span>
                        <strong><?= e(format_duration((int) $totalSeconds)) ?></strong>
                    </div>
                    <div>
                        <span>Registros</span>
                        <strong><?= count($history) ?></strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="panel">
            <h2>Historico de corrida</h2>
            <?php if ($history): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>KM</th>
                            <th>Tempo</th>
                            <th>Ritmo medio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $run): ?>
                            <?php
                            $km = (float) $run['km'];
                            $seconds = (int) $run['seconds'];
                            $pace = $km > 0 ? (int) round($seconds / $km) : 0;
                            ?>
                            <tr>
                                <td><?= e(date('d/m/Y', strtotime($run['date']))) ?></td>
                                <td><?= e(number_format($km, 2, ',', '.')) ?> km</td>
                                <td><?= e(format_duration($seconds)) ?></td>
                                <td><?= e(format_duration($pace)) ?> / km</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Nenhuma corrida salva ainda.</p>
            <?php endif; ?>
        </section>
    </main>

    <script src="public/run-timer.js"></script>
</body>
</html>
