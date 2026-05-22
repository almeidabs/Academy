CREATE TABLE IF NOT EXISTS academy_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO academy_users (name, email, password)
SELECT 'Aluno Demo', 'aluno@academia.com', '$2y$10$.Y/kRPKrz6Kl7igxLOVQ/.6jImM5ExI0vnaJwNHLPbOtOYbR4ZwcS'
WHERE NOT EXISTS (
    SELECT 1 FROM academy_users WHERE email = 'aluno@academia.com'
);
