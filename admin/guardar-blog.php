<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
requireAdmin();
require_once __DIR__ . '/../db.php';

requirePost();
verifyCsrfOrFail();

$titulo = trim((string) ($_POST['titulo'] ?? ''));
$autor = trim((string) ($_POST['autor'] ?? ''));
$resumen = trim((string) ($_POST['resumen'] ?? ''));
$contenido = (string) ($_POST['contenido'] ?? '');
$estado = strtoupper(trim((string) ($_POST['estado'] ?? 'B')));

if (!in_array($estado, ['P', 'B'], true)) {
    $estado = 'B';
}

if ($titulo === '' || $autor === '' || trim(strip_tags($contenido)) === '') {
    header('Location: dashboard.php?msg=' . urlencode('Título, autor y contenido son obligatorios.'));
    exit;
}

$contenido = sanitizeRichHtml($contenido);

$imagenPath = null;

if (isset($_FILES['imagen']) && is_array($_FILES['imagen']) && ($_FILES['imagen']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    if ((int) $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
        header('Location: dashboard.php?msg=' . urlencode('Error al subir la imagen.'));
        exit;
    }

    $tmp = (string) $_FILES['imagen']['tmp_name'];
    $original = (string) $_FILES['imagen']['name'];
    $size = (int) $_FILES['imagen']['size'];

    if ($size > 5 * 1024 * 1024) {
        header('Location: dashboard.php?msg=' . urlencode('La imagen supera 5MB.'));
        exit;
    }

    $ext = strtolower((string) pathinfo($original, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    if (!in_array($ext, $allowed, true)) {
        header('Location: dashboard.php?msg=' . urlencode('Formato de imagen no permitido.'));
        exit;
    }

    $uploadDir = __DIR__ . '/../uploads/blog';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
        header('Location: dashboard.php?msg=' . urlencode('No se pudo crear la carpeta de imágenes.'));
        exit;
    }

    $filename = uniqid('blog_', true) . '.' . $ext;
    $target = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($tmp, $target)) {
        header('Location: dashboard.php?msg=' . urlencode('No se pudo guardar la imagen.'));
        exit;
    }

    $imagenPath = 'uploads/blog/' . $filename;
}

$pdo = db();
$stmt = $pdo->prepare('INSERT INTO blogs (titulo, autor, resumen, contenido, imagen, estado) VALUES (:titulo, :autor, :resumen, :contenido, :imagen, :estado)');
$stmt->execute([
    ':titulo' => $titulo,
    ':autor' => $autor,
    ':resumen' => $resumen,
    ':contenido' => $contenido,
    ':imagen' => $imagenPath,
    ':estado' => $estado,
]);

header('Location: dashboard.php?msg=' . urlencode('Blog guardado correctamente.'));
exit;
