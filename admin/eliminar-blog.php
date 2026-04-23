<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
requireAdmin();
require_once __DIR__ . '/../db.php';

requirePost();
verifyCsrfOrFail();

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: dashboard.php?msg=' . urlencode('ID inválido para eliminar.'));
    exit;
}

$pdo = db();
$stmt = $pdo->prepare('SELECT imagen FROM blogs WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $id]);
$blog = $stmt->fetch();

if (!$blog) {
    header('Location: dashboard.php?msg=' . urlencode('No se encontró el artículo.'));
    exit;
}

$delete = $pdo->prepare('DELETE FROM blogs WHERE id = :id');
$delete->execute([':id' => $id]);

$imagenPath = (string) ($blog['imagen'] ?? '');
if ($imagenPath !== '' && str_starts_with($imagenPath, 'uploads/blog/')) {
    $file = __DIR__ . '/../' . $imagenPath;
    if (is_file($file)) {
        @unlink($file);
    }
}

header('Location: dashboard.php?msg=' . urlencode('Artículo eliminado.'));
exit;
