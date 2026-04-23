<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
requireAdmin();
require_once __DIR__ . '/../db.php';

$pdo = db();
$msg = trim((string) ($_GET['msg'] ?? ''));

$stmt = $pdo->query('SELECT id, titulo, autor, resumen, imagen, estado, created_at FROM blogs ORDER BY created_at DESC');
$blogs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador Blog - LUMA</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f6f7fb; }
        .wrap { max-width: 1100px; margin: 24px auto; padding: 0 16px; }
        .top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .btn { display: inline-block; border: 0; padding: 8px 12px; border-radius: 6px; text-decoration: none; background: #8b1e43; color: #fff; cursor: pointer; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 8px 24px rgba(0,0,0,.06); padding: 18px; margin-bottom: 18px; }
        input, textarea, select { width: 100%; box-sizing: border-box; border: 1px solid #ccc; border-radius: 6px; padding: 10px; margin-top: 6px; }
        textarea { min-height: 120px; resize: vertical; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .full { grid-column: 1 / -1; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #ececec; padding: 10px 8px; text-align: left; font-size: 14px; vertical-align: top; }
        th { background: #fafafa; }
        .tag { font-weight: 700; padding: 2px 8px; border-radius: 999px; display: inline-block; }
        .tag-p { background: #d8f7e4; color: #126a35; }
        .tag-b { background: #ffe3e3; color: #7f1d1d; }
        .msg { padding: 10px; border-radius: 6px; background: #e9f6ff; color: #0b4f74; margin-bottom: 12px; }
        .mini-btn { font-size: 12px; padding: 6px 8px; border-radius: 5px; text-decoration: none; color: #fff; background: #333; margin-right: 6px; display: inline-block; }
        .mini-btn.pub { background: #126a35; }
        .mini-btn.bor { background: #8b1e43; }
        .mini-btn.view { background: #0b4f74; }
        .mini-btn.edit { background: #1d4ed8; }
        .mini-btn.del { background: #b91c1c; border: 0; cursor: pointer; }
        .inline-form { display: inline-block; margin: 0; }
        img.thumb { width: 80px; height: 50px; object-fit: cover; border-radius: 6px; }
        @media (max-width: 800px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="wrap">
    <div class="top">
        <h1>Administrador de Blog</h1>
        <form class="inline-form" method="post" action="logout.php">
            <input type="hidden" name="csrf_token" value="<?php echo h(csrfToken()); ?>">
            <button class="btn" type="submit">Cerrar sesión</button>
        </form>
    </div>

    <?php if ($msg !== ''): ?>
        <div class="msg"><?php echo h($msg); ?></div>
    <?php endif; ?>

    <div class="card">
        <h2>Nuevo artículo</h2>
        <form method="post" action="guardar-blog.php" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo h(csrfToken()); ?>">
            <div class="grid">
                <div>
                    <label>Título</label>
                    <input type="text" name="titulo" maxlength="255" required>
                </div>
                <div>
                    <label>Autor</label>
                    <input type="text" name="autor" maxlength="120" required>
                </div>
                <div>
                    <label>Estado</label>
                    <select name="estado" required>
                        <option value="B" selected>B - Borrador</option>
                        <option value="P">P - Publicado</option>
                    </select>
                </div>
                <div class="full">
                    <label>Resumen</label>
                    <textarea name="resumen" placeholder="Texto corto para la cabecera del blog"></textarea>
                </div>
                <div class="full">
                    <label>Contenido</label>
                    <textarea name="contenido" required placeholder="Contenido del blog"></textarea>
                </div>
                <div class="full">
                    <label>Foto (jpg, png, webp, gif - max 5MB)</label>
                    <input type="file" name="imagen" accept=".jpg,.jpeg,.png,.webp,.gif">
                </div>
            </div>
            <button class="btn" type="submit">Guardar</button>
        </form>
    </div>

    <div class="card">
        <h2>Artículos cargados</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Imagen</th>
                    <th>Título</th>
                    <th>Autor</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($blogs) === 0): ?>
                    <tr><td colspan="7">Aún no hay artículos.</td></tr>
                <?php else: ?>
                    <?php foreach ($blogs as $blog): ?>
                        <tr>
                            <td><?php echo (int) $blog['id']; ?></td>
                            <td>
                                <?php if (!empty($blog['imagen'])): ?>
                                    <img class="thumb" src="../<?php echo h((string) $blog['imagen']); ?>" alt="miniatura">
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?php echo h((string) $blog['titulo']); ?></td>
                            <td><?php echo h((string) ($blog['autor'] ?: 'LUMA')); ?></td>
                            <td>
                                <?php if ($blog['estado'] === 'P'): ?>
                                    <span class="tag tag-p">Publicado</span>
                                <?php else: ?>
                                    <span class="tag tag-b">Borrador</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo h(formatFechaEs((string) $blog['created_at'])); ?></td>
                            <td>
                                <form class="inline-form" action="cambiar-estado.php" method="post">
                                    <input type="hidden" name="csrf_token" value="<?php echo h(csrfToken()); ?>">
                                    <input type="hidden" name="id" value="<?php echo (int) $blog['id']; ?>">
                                    <input type="hidden" name="estado" value="P">
                                    <button class="mini-btn pub" type="submit">Publicar</button>
                                </form>
                                <form class="inline-form" action="cambiar-estado.php" method="post">
                                    <input type="hidden" name="csrf_token" value="<?php echo h(csrfToken()); ?>">
                                    <input type="hidden" name="id" value="<?php echo (int) $blog['id']; ?>">
                                    <input type="hidden" name="estado" value="B">
                                    <button class="mini-btn bor" type="submit">Borrador</button>
                                </form>
                                <a class="mini-btn edit" href="editar-blog.php?id=<?php echo (int) $blog['id']; ?>">Editar</a>
                                <a class="mini-btn view" href="../blog-detalle.php?id=<?php echo (int) $blog['id']; ?>" target="_blank">Ver</a>
                                <form class="inline-form" action="eliminar-blog.php" method="post" onsubmit="return confirm('¿Seguro que deseas eliminar este artículo?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo h(csrfToken()); ?>">
                                    <input type="hidden" name="id" value="<?php echo (int) $blog['id']; ?>">
                                    <button class="mini-btn del" type="submit">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.4/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: 'textarea[name="contenido"]',
    menubar: false,
    height: 320,
    plugins: 'lists link image table code',
    toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link image table | alignleft aligncenter alignright | code'
});
</script>
</body>
</html>
