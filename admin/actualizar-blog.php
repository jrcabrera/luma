<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
requireAdmin();
require_once __DIR__ . '/../db.php';

requirePost();
verifyCsrfOrFail();

$id = (int) ($_POST['id'] ?? 0);
$titulo = trim((string) ($_POST['titulo'] ?? ''));
$autor = trim((string) ($_POST['autor'] ?? ''));
$resumen = trim((string) ($_POST['resumen'] ?? ''));
$contenido = (string) ($_POST['contenido'] ?? '');
$estado = strtoupper(trim((string) ($_POST['estado'] ?? 'B')));

if ($id <= 0) {
    header('Location: dashboard.php?msg=' . urlencode('ID inválido.'));
    exit;
}

if (!in_array($estado, ['P', 'B'], true)) {
    $estado = 'B';
}

if ($titulo === '' || $autor === '' || trim(strip_tags($contenido)) === '') {
    header('Location: editar-blog.php?id=' . $id . '&msg=' . urlencode('Título, autor y contenido son obligatorios.'));
    exit;
}

$contenido = sanitizeRichHtml($contenido);

$pdo = db();
$stmt = $pdo->prepare('SELECT imagen FROM blogs WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $id]);
$current = $stmt->fetch();

if (!$current) {
    header('Location: dashboard.php?msg=' . urlencode('No se encontró el artículo.'));
    exit;
}

$imagenPath = (string) ($current['imagen'] ?? '');

if (isset($_FILES['imagen']) && is_array($_FILES['imagen']) && ($_FILES['imagen']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    if ((int) $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
        header('Location: editar-blog.php?id=' . $id . '&msg=' . urlencode('Error al subir la imagen.'));
        exit;
    }

    $tmp = (string) $_FILES['imagen']['tmp_name'];
    $original = (string) $_FILES['imagen']['name'];
    $size = (int) $_FILES['imagen']['size'];

    if ($size > 5 * 1024 * 1024) {
        header('Location: editar-blog.php?id=' . $id . '&msg=' . urlencode('La imagen supera 5MB.'));
        exit;
    }

    $ext = strtolower((string) pathinfo($original, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    if (!in_array($ext, $allowed, true)) {
        header('Location: editar-blog.php?id=' . $id . '&msg=' . urlencode('Formato de imagen no permitido.'));
        exit;
    }

    $uploadDir = __DIR__ . '/../uploads/blog';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
        header('Location: editar-blog.php?id=' . $id . '&msg=' . urlencode('No se pudo crear la carpeta de imágenes.'));
        exit;
    }

    $filename = uniqid('blog_', true) . '.' . $ext;
    $target = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($tmp, $target)) {
        header('Location: editar-blog.php?id=' . $id . '&msg=' . urlencode('No se pudo guardar la imagen.'));
        exit;
    }

    if ($imagenPath !== '' && str_starts_with($imagenPath, 'uploads/blog/')) {
        $oldPath = __DIR__ . '/../' . $imagenPath;
        if (is_file($oldPath)) {
            @unlink($oldPath);
        }
    }

    $imagenPath = 'uploads/blog/' . $filename;
}

$update = $pdo->prepare('UPDATE blogs SET titulo = :titulo, autor = :autor, resumen = :resumen, contenido = :contenido, imagen = :imagen, estado = :estado WHERE id = :id');
$update->execute([
    ':titulo' => $titulo,
    ':autor' => $autor,
    ':resumen' => $resumen,
    ':contenido' => $contenido,
    ':imagen' => $imagenPath !== '' ? $imagenPath : null,
    ':estado' => $estado,
    ':id' => $id,
]);

header('Location: dashboard.php?msg=' . urlencode('Artículo actualizado correctamente.'));
exit;
