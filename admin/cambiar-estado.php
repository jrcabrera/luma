<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
requireAdmin();
require_once __DIR__ . '/../db.php';

requirePost();
verifyCsrfOrFail();

$id = (int) ($_POST['id'] ?? 0);
$estado = strtoupper(trim((string) ($_POST['estado'] ?? 'B')));

if ($id <= 0 || !in_array($estado, ['P', 'B'], true)) {
    header('Location: dashboard.php?msg=' . urlencode('Parámetros inválidos.'));
    exit;
}

$pdo = db();
$stmt = $pdo->prepare('UPDATE blogs SET estado = :estado WHERE id = :id');
$stmt->execute([
    ':estado' => $estado,
    ':id' => $id,
]);

header('Location: dashboard.php?msg=' . urlencode('Estado actualizado.'));
exit;
