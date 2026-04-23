<?php
declare(strict_types=1);

const DB_HOST = '137.220.34.190';
const DB_PORT = 3306;
const DB_NAME = 'luma';
const DB_USER = 'jrcabrera';
const DB_PASS = '1P0g8j9r*';

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    ensureBlogTable($pdo);
    ensureAdminUsersTable($pdo);

    return $pdo;
}

function ensureBlogTable(PDO $pdo): void
{
    $sql = "
        CREATE TABLE IF NOT EXISTS blogs (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            titulo VARCHAR(255) NOT NULL,
            autor VARCHAR(120) NULL,
            resumen TEXT NULL,
            contenido LONGTEXT NOT NULL,
            imagen VARCHAR(255) NULL,
            estado CHAR(1) NOT NULL DEFAULT 'B',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_estado (estado),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ";

    $pdo->exec($sql);

    // Migra instalaciones anteriores que no tenian el campo autor.
    $columns = $pdo->query('SHOW COLUMNS FROM blogs LIKE "autor"')->fetchAll();
    if (count($columns) === 0) {
        $pdo->exec('ALTER TABLE blogs ADD COLUMN autor VARCHAR(120) NULL AFTER titulo');
    }
}

function ensureAdminUsersTable(PDO $pdo): void
{
    $sql = "
        CREATE TABLE IF NOT EXISTS admin_users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            usuario VARCHAR(120) NOT NULL,
            nombre VARCHAR(120) NULL,
            clave_hash VARCHAR(255) NOT NULL,
            activo TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_admin_usuario (usuario),
            INDEX idx_admin_activo (activo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ";

    $pdo->exec($sql);
    seedInitialAdminFromEnv($pdo);
}

function seedInitialAdminFromEnv(PDO $pdo): void
{
    $count = (int) $pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
    if ($count > 0) {
        return;
    }

    $usuario = trim((string) (getenv('LUMA_ADMIN_USER') ?: ''));
    $clave = (string) (getenv('LUMA_ADMIN_PASS') ?: '');
    $nombre = trim((string) (getenv('LUMA_ADMIN_NAME') ?: 'Administrador LUMA'));

    if ($usuario === '' || $clave === '') {
        return;
    }

    $stmt = $pdo->prepare('INSERT INTO admin_users (usuario, nombre, clave_hash, activo) VALUES (:usuario, :nombre, :clave_hash, 1)');
    $stmt->execute([
        ':usuario' => $usuario,
        ':nombre' => $nombre,
        ':clave_hash' => password_hash($clave, PASSWORD_DEFAULT),
    ]);
}

function findAdminUser(PDO $pdo, string $usuario)
{
    $stmt = $pdo->prepare('SELECT id, usuario, nombre, clave_hash, activo FROM admin_users WHERE usuario = :usuario LIMIT 1');
    $stmt->execute([':usuario' => $usuario]);

    return $stmt->fetch();
}

function hasAdminUsers(PDO $pdo): bool
{
    return (int) $pdo->query('SELECT COUNT(*) FROM admin_users WHERE activo = 1')->fetchColumn() > 0;
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function formatFechaEs(string $date): string
{
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return $date;
    }

    $meses = [
        1 => 'enero',
        2 => 'febrero',
        3 => 'marzo',
        4 => 'abril',
        5 => 'mayo',
        6 => 'junio',
        7 => 'julio',
        8 => 'agosto',
        9 => 'septiembre',
        10 => 'octubre',
        11 => 'noviembre',
        12 => 'diciembre',
    ];

    $dia = (int) date('d', $timestamp);
    $mes = $meses[(int) date('n', $timestamp)] ?? date('m', $timestamp);
    $anio = date('Y', $timestamp);

    return $dia . ' ' . $mes . ', ' . $anio;
}

function sanitizeRichHtml(string $html): string
{
    // Elimina bloques peligrosos y mantiene etiquetas basicas de formato.
    $clean = preg_replace('/<\s*(script|style|iframe|object|embed)[^>]*>.*?<\s*\/\s*\1\s*>/is', '', $html);
    $clean = preg_replace('/on[a-z]+\s*=\s*"[^"]*"/i', '', (string) $clean);
    $clean = preg_replace('/on[a-z]+\s*=\s*\'[^\']*\'/i', '', (string) $clean);
    $clean = preg_replace('/on[a-z]+\s*=\s*[^\s>]+/i', '', (string) $clean);
    $clean = preg_replace('/javascript\s*:/i', '', (string) $clean);

    $allowed = '<p><br><strong><b><em><i><u><h1><h2><h3><h4><h5><h6><ul><ol><li><blockquote><a><img><span><div>';
    return strip_tags((string) $clean, $allowed);
}
