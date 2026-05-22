<?php
declare(strict_types=1);

const DATA_DIR = __DIR__ . '/../data';

require_once __DIR__ . '/config.php';

date_default_timezone_set(APP_TIMEZONE);

function start_app_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        $sessionDir = DATA_DIR . '/sessions';

        if (!is_dir($sessionDir)) {
            mkdir($sessionDir, 0777, true);
        }

        session_save_path($sessionDir);
        session_start();
    }
}

function data_file(string $name): string
{
    return DATA_DIR . '/' . $name . '.json';
}

function read_json(string $name): array
{
    $file = data_file($name);

    if (!file_exists($file)) {
        return [];
    }

    $content = file_get_contents($file);
    $data = json_decode($content ?: '[]', true);

    return is_array($data) ? $data : [];
}

function write_json(string $name, array $data): void
{
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0777, true);
    }

    file_put_contents(
        data_file($name),
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        DB_HOST,
        DB_NAME,
        DB_CHARSET
    );

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return $pdo;
}

function migrate_database(): void
{
    db()->exec(
        "CREATE TABLE IF NOT EXISTS academy_users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            email VARCHAR(160) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $statement = db()->prepare('SELECT id FROM academy_users WHERE email = ? LIMIT 1');
    $statement->execute(['aluno@academia.com']);

    if (!$statement->fetch()) {
        create_user('Aluno Demo', 'aluno@academia.com', '123456');
    }
}

function create_user(string $name, string $email, string $password): bool
{
    $statement = db()->prepare(
        'INSERT INTO academy_users (name, email, password) VALUES (?, ?, ?)'
    );

    return $statement->execute([
        $name,
        strtolower($email),
        password_hash($password, PASSWORD_DEFAULT),
    ]);
}

function find_user_by_email(string $email): ?array
{
    $statement = db()->prepare('SELECT * FROM academy_users WHERE email = ? LIMIT 1');
    $statement->execute([strtolower($email)]);
    $user = $statement->fetch();

    return $user ?: null;
}

function find_user_by_id(int $id): ?array
{
    $statement = db()->prepare('SELECT * FROM academy_users WHERE id = ? LIMIT 1');
    $statement->execute([$id]);
    $user = $statement->fetch();

    return $user ?: null;
}

function verify_user_login(string $email, string $password): ?array
{
    $user = find_user_by_email($email);

    if (!$user || !password_verify($password, $user['password'])) {
        return null;
    }

    return $user;
}

function seed_data(): void
{
    if (!file_exists(data_file('workouts'))) {
        write_json('workouts', [
            [
                'id' => 1,
                'name' => 'Treino A - Peito e Triceps',
                'goal' => 'Hipertrofia',
                'level' => 'Intermediario',
                'exercises' => [
                    ['name' => 'Supino reto', 'sets' => '4', 'reps' => '8-10'],
                    ['name' => 'Supino inclinado', 'sets' => '3', 'reps' => '10-12'],
                    ['name' => 'Triceps corda', 'sets' => '3', 'reps' => '12'],
                ],
            ],
            [
                'id' => 2,
                'name' => 'Treino B - Costas e Biceps',
                'goal' => 'Forca e controle',
                'level' => 'Iniciante',
                'exercises' => [
                    ['name' => 'Puxada frontal', 'sets' => '4', 'reps' => '10'],
                    ['name' => 'Remada baixa', 'sets' => '3', 'reps' => '12'],
                    ['name' => 'Rosca direta', 'sets' => '3', 'reps' => '10-12'],
                ],
            ],
        ]);
    }

    if (!file_exists(data_file('assignments'))) {
        write_json('assignments', []);
    }

    if (!file_exists(data_file('exercise_progress'))) {
        write_json('exercise_progress', []);
    }

    if (!file_exists(data_file('daily_progress'))) {
        write_json('daily_progress', []);
    }

    if (!file_exists(data_file('weight_history'))) {
        write_json('weight_history', [
            ['user_id' => 1, 'date' => '2022-07-23', 'weight' => 56.5],
            ['user_id' => 1, 'date' => '2025-02-19', 'weight' => 60.0],
            ['user_id' => 1, 'date' => '2025-05-19', 'weight' => 75.0],
            ['user_id' => 1, 'date' => '2025-07-23', 'weight' => 75.0],
            ['user_id' => 1, 'date' => '2026-01-26', 'weight' => 62.0],
        ]);
    }

    if (!file_exists(data_file('running_sessions'))) {
        write_json('running_sessions', []);
    }
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function current_user(): ?array
{
    start_app_session();

    if (empty($_SESSION['user_id'])) {
        return null;
    }

    return find_user_by_id((int) $_SESSION['user_id']);
}

function require_login(): array
{
    $user = current_user();

    if (!$user) {
        redirect('login.php');
    }

    return $user;
}

function next_id(array $items): int
{
    $ids = array_map(static fn (array $item): int => (int) ($item['id'] ?? 0), $items);

    return $ids ? max($ids) + 1 : 1;
}

function find_by_id(array $items, int $id): ?array
{
    foreach ($items as $item) {
        if ((int) ($item['id'] ?? 0) === $id) {
            return $item;
        }
    }

    return null;
}

function delete_workout(int $workoutId): void
{
    $workouts = array_values(array_filter(
        read_json('workouts'),
        static fn (array $workout): bool => (int) ($workout['id'] ?? 0) !== $workoutId
    ));
    write_json('workouts', $workouts);

    $assignments = array_values(array_filter(
        read_json('assignments'),
        static fn (array $assignment): bool => (int) ($assignment['workout_id'] ?? 0) !== $workoutId
    ));
    write_json('assignments', $assignments);

    $exerciseProgress = read_json('exercise_progress');
    foreach (array_keys($exerciseProgress) as $key) {
        $parts = explode(':', (string) $key);
        if ((int) ($parts[1] ?? 0) === $workoutId) {
            unset($exerciseProgress[$key]);
        }
    }
    write_json('exercise_progress', $exerciseProgress);

    $dailyProgress = array_filter(
        read_json('daily_progress'),
        static fn (array $item): bool => (int) ($item['workout_id'] ?? 0) !== $workoutId
    );
    write_json('daily_progress', $dailyProgress);
}

function selected_workout_for_user(int $userId): ?array
{
    $assignments = read_json('assignments');
    $workouts = read_json('workouts');

    foreach (array_reverse($assignments) as $assignment) {
        if ((int) $assignment['user_id'] === $userId) {
            return find_by_id($workouts, (int) $assignment['workout_id']);
        }
    }

    return null;
}

function progress_key(int $userId, int $workoutId): string
{
    return $userId . ':' . $workoutId;
}

function workout_progress(int $userId, int $workoutId): array
{
    $progress = read_json('exercise_progress');
    $key = progress_key($userId, $workoutId);

    return $progress[$key] ?? [];
}

function save_workout_progress(int $userId, int $workoutId, array $completedExercises): void
{
    $progress = read_json('exercise_progress');
    $key = progress_key($userId, $workoutId);
    $completedExercises = array_values(array_unique(array_map('intval', $completedExercises)));
    sort($completedExercises);

    $progress[$key] = $completedExercises;

    write_json('exercise_progress', $progress);
}

function toggle_exercise_progress(int $userId, int $workoutId, int $exerciseIndex): void
{
    $completedExercises = workout_progress($userId, $workoutId);

    if (in_array($exerciseIndex, $completedExercises, true)) {
        $completedExercises = array_values(array_diff($completedExercises, [$exerciseIndex]));
    } else {
        $completedExercises[] = $exerciseIndex;
    }

    save_workout_progress($userId, $workoutId, $completedExercises);
}

function reset_workout_progress(int $userId, int $workoutId): void
{
    save_workout_progress($userId, $workoutId, []);
}

function workout_progress_percent(array $workout, array $completedExercises): int
{
    $totalExercises = count($workout['exercises'] ?? []);

    if ($totalExercises === 0) {
        return 0;
    }

    return (int) round((count($completedExercises) / $totalExercises) * 100);
}

function today_date(): string
{
    return date('Y-m-d');
}

function daily_progress_key(int $userId, int $workoutId, string $date): string
{
    return $userId . ':' . $workoutId . ':' . $date;
}

function save_daily_workout_progress(int $userId, array $workout, array $completedExercises, ?string $date = null): void
{
    $date = $date ?: today_date();
    $workoutId = (int) $workout['id'];
    $totalExercises = count($workout['exercises'] ?? []);
    $completedCount = count($completedExercises);
    $percent = workout_progress_percent($workout, $completedExercises);
    $history = read_json('daily_progress');

    $history[daily_progress_key($userId, $workoutId, $date)] = [
        'user_id' => $userId,
        'workout_id' => $workoutId,
        'workout_name' => $workout['name'],
        'date' => $date,
        'completed' => $completedCount,
        'completed_exercises' => array_values(array_map('intval', $completedExercises)),
        'total' => $totalExercises,
        'percent' => $percent,
        'status' => $percent === 100 ? 'Completo' : 'Parcial',
        'updated_at' => date('Y-m-d H:i:s'),
    ];

    write_json('daily_progress', $history);
}

function daily_workout_history(int $userId, int $limit = 7): array
{
    $history = array_values(array_filter(
        read_json('daily_progress'),
        static fn (array $item): bool => (int) ($item['user_id'] ?? 0) === $userId
    ));

    usort(
        $history,
        static fn (array $a, array $b): int => strcmp($b['date'] ?? '', $a['date'] ?? '')
    );

    return array_slice($history, 0, $limit);
}

function daily_workout_history_by_date(int $userId): array
{
    $items = daily_workout_history($userId, 365);
    $byDate = [];

    foreach ($items as $item) {
        $byDate[$item['date']] = $item;
    }

    return $byDate;
}

function daily_workout_record(int $userId, string $date): ?array
{
    foreach (daily_workout_history($userId, 365) as $item) {
        if (($item['date'] ?? '') === $date) {
            return $item;
        }
    }

    return null;
}

function week_start_date(?string $date = null): string
{
    $timestamp = $date ? strtotime($date) : time();

    if (!$timestamp) {
        $timestamp = time();
    }

    return date('Y-m-d', strtotime('monday this week', $timestamp));
}

function week_business_days(string $weekStart): array
{
    $days = [];

    for ($index = 0; $index < 5; $index++) {
        $days[] = date('Y-m-d', strtotime('+' . $index . ' days', strtotime($weekStart)));
    }

    return $days;
}

function month_name_pt(int $month): string
{
    $months = [
        1 => 'janeiro',
        2 => 'fevereiro',
        3 => 'marco',
        4 => 'abril',
        5 => 'maio',
        6 => 'junho',
        7 => 'julho',
        8 => 'agosto',
        9 => 'setembro',
        10 => 'outubro',
        11 => 'novembro',
        12 => 'dezembro',
    ];

    return $months[$month] ?? '';
}

function weekday_name_pt(string $date): string
{
    $names = [
        1 => 'segunda-feira',
        2 => 'terca-feira',
        3 => 'quarta-feira',
        4 => 'quinta-feira',
        5 => 'sexta-feira',
    ];

    return $names[(int) date('N', strtotime($date))] ?? '';
}

function week_range_label(string $weekStart): string
{
    $start = strtotime($weekStart);
    $end = strtotime('+4 days', $start);
    $startDay = date('j', $start);
    $endDay = date('j', $end);
    $month = month_name_pt((int) date('n', $end));
    $year = date('Y', $end);

    return $startDay . ' - ' . $endDay . ' de ' . $month . ' de ' . $year;
}

function month_start_date(?string $date = null): string
{
    $timestamp = $date ? strtotime($date) : time();

    if (!$timestamp) {
        $timestamp = time();
    }

    return date('Y-m-01', $timestamp);
}

function month_days(string $monthStart): array
{
    $days = [];
    $totalDays = (int) date('t', strtotime($monthStart));

    for ($day = 1; $day <= $totalDays; $day++) {
        $days[] = date('Y-m-d', strtotime('+' . ($day - 1) . ' days', strtotime($monthStart)));
    }

    return $days;
}

function month_calendar_days(string $monthStart): array
{
    $firstDay = strtotime($monthStart);
    $startOffset = (int) date('w', $firstDay);
    $gridStart = strtotime('-' . $startOffset . ' days', $firstDay);
    $days = [];

    for ($index = 0; $index < 42; $index++) {
        $date = date('Y-m-d', strtotime('+' . $index . ' days', $gridStart));
        $days[] = [
            'date' => $date,
            'current_month' => date('Y-m', strtotime($date)) === date('Y-m', $firstDay),
        ];
    }

    return $days;
}

function month_label(string $monthStart): string
{
    $timestamp = strtotime($monthStart);

    return month_name_pt((int) date('n', $timestamp)) . ' de ' . date('Y', $timestamp);
}

function weight_history(int $userId): array
{
    $items = array_values(array_filter(
        read_json('weight_history'),
        static fn (array $item): bool => (int) ($item['user_id'] ?? 0) === $userId
    ));

    usort(
        $items,
        static fn (array $a, array $b): int => strcmp($a['date'] ?? '', $b['date'] ?? '')
    );

    return $items;
}

function save_weight_entry(int $userId, string $date, float $weight): void
{
    $items = read_json('weight_history');
    $date = date('Y-m-d', strtotime($date) ?: time());
    $updated = false;

    foreach ($items as &$item) {
        if ((int) ($item['user_id'] ?? 0) === $userId && ($item['date'] ?? '') === $date) {
            $item['weight'] = $weight;
            $updated = true;
            break;
        }
    }

    unset($item);

    if (!$updated) {
        $items[] = [
            'user_id' => $userId,
            'date' => $date,
            'weight' => $weight,
        ];
    }

    write_json('weight_history', $items);
}

function save_running_session(int $userId, string $date, float $km, int $seconds): void
{
    $items = read_json('running_sessions');
    $items[] = [
        'id' => next_id($items),
        'user_id' => $userId,
        'date' => date('Y-m-d', strtotime($date) ?: time()),
        'km' => $km,
        'seconds' => $seconds,
        'created_at' => date('Y-m-d H:i:s'),
    ];

    write_json('running_sessions', $items);
}

function running_history(int $userId, int $limit = 10): array
{
    $items = array_values(array_filter(
        read_json('running_sessions'),
        static fn (array $item): bool => (int) ($item['user_id'] ?? 0) === $userId
    ));

    usort(
        $items,
        static fn (array $a, array $b): int => strcmp($b['date'] ?? '', $a['date'] ?? '')
    );

    return array_slice($items, 0, $limit);
}

function format_duration(int $seconds): string
{
    $hours = intdiv($seconds, 3600);
    $minutes = intdiv($seconds % 3600, 60);
    $remainingSeconds = $seconds % 60;

    return sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds);
}

function format_weight(float $weight): string
{
    $formatted = number_format($weight, 1, ',', '.');

    return str_ends_with($formatted, ',0') ? substr($formatted, 0, -2) : $formatted;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

migrate_database();
seed_data();
