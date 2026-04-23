<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
requireAdmin();
require_once __DIR__ . '/../db.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: dashboard.php?msg=' . urlencode('ID inválido para edición.'));
    exit;
}

$pdo = db();
$stmt = $pdo->prepare('SELECT id, titulo, autor, resumen, contenido, imagen, estado FROM blogs WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $id]);
$blog = $stmt->fetch();

if (!$blog) {
    header('Location: dashboard.php?msg=' . urlencode('No se encontró el artículo.'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Blog - LUMA</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f6f7fb; }
        .wrap { max-width: 900px; margin: 24px auto; padding: 0 16px; }
        .top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .btn { display: inline-block; border: 0; padding: 9px 12px; border-radius: 6px; text-decoration: none; background: #8b1e43; color: #fff; cursor: pointer; }
        .btn.secondary { background: #4b5563; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 8px 24px rgba(0,0,0,.06); padding: 18px; }
        input, textarea, select { width: 100%; box-sizing: border-box; border: 1px solid #ccc; border-radius: 6px; padding: 10px; margin-top: 6px; }
        textarea { min-height: 140px; resize: vertical; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .full { grid-column: 1 / -1; }
        .preview { margin-top: 8px; }
        .preview img { width: 160px; border-radius: 6px; object-fit: cover; }
        @media (max-width: 800px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="wrap">
    <div class="top">
        <h1>Editar artículo #<?php echo (int) $blog['id']; ?></h1>
        <a class="btn secondary" href="dashboard.php">Volver</a>
    </div>

    <div class="card">
        <form method="post" action="actualizar-blog.php" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo h(csrfToken()); ?>">
            <input type="hidden" name="id" value="<?php echo (int) $blog['id']; ?>">
            <div class="grid">
                <div>
                    <label>Título</label>
                    <input type="text" name="titulo" maxlength="255" required value="<?php echo h((string) $blog['titulo']); ?>">
                </div>
                <div>
                    <label>Autor</label>
                    <input type="text" name="autor" maxlength="120" required value="<?php echo h((string) ($blog['autor'] ?? '')); ?>">
                </div>
                <div>
                    <label>Estado</label>
                    <select name="estado" required>
                        <option value="B" <?php echo $blog['estado'] === 'B' ? 'selected' : ''; ?>>B - Borrador</option>
                        <option value="P" <?php echo $blog['estado'] === 'P' ? 'selected' : ''; ?>>P - Publicado</option>
                    </select>
                </div>
                <div class="full">
                    <label>Resumen</label>
                    <textarea name="resumen"><?php echo h((string) $blog['resumen']); ?></textarea>
                </div>
                <div class="full">
                    <label>Contenido</label>
                    <textarea name="contenido" required><?php echo h((string) $blog['contenido']); ?></textarea>
                </div>
                <div class="full">
                    <label>Nueva foto (opcional)</label>
                    <input type="file" name="imagen" accept=".jpg,.jpeg,.png,.webp,.gif">
                    <?php if (!empty($blog['imagen'])): ?>
                        <div class="preview">
                            <p>Foto actual:</p>
                            <img src="../<?php echo h((string) $blog['imagen']); ?>" alt="foto actual">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <button class="btn" type="submit">Guardar cambios</button>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.4/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: 'textarea[name="contenido"]',
    menubar: false,
    height: 380,
    plugins: 'lists link image table code',
    toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link image table | alignleft aligncenter alignright | code'
});
</script>
</body>
</html>
