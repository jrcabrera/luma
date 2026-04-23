<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';

if (!empty($_SESSION['admin_logged'])) {
    header('Location: dashboard.php');
    exit;
}

$pdo = db();
$error = '';
$setupRequired = !hasAdminUsers($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isLoginRateLimited()) {
        $error = 'Demasiados intentos. Espera unos minutos antes de volver a intentar.';
    } else {
        $usuario = trim((string) ($_POST['usuario'] ?? ''));
        $clave = (string) ($_POST['clave'] ?? '');

        $admin = findAdminUser($pdo, $usuario);

        if ($admin && (int) $admin['activo'] === 1 && password_verify($clave, (string) $admin['clave_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_logged'] = true;
            $_SESSION['admin_user'] = (string) $admin['usuario'];
            $_SESSION['admin_user_id'] = (int) $admin['id'];
            clearLoginFailures();
            csrfToken();
            header('Location: dashboard.php');
            exit;
        }

        recordLoginFailure();
        $error = 'Usuario o clave incorrectos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrador Blog - LUMA</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; }
        .card { max-width: 420px; margin: 90px auto; background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 8px 24px rgba(0,0,0,.08); }
        h1 { margin-top: 0; font-size: 22px; }
        label { display: block; margin-top: 12px; font-weight: 600; }
        input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; margin-top: 6px; box-sizing: border-box; }
        button { margin-top: 16px; width: 100%; border: 0; padding: 11px; border-radius: 6px; background: #8b1e43; color: #fff; font-weight: 700; cursor: pointer; }
        .error { color: #b00020; margin-top: 10px; }
        .hint { color: #666; font-size: 13px; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Administrador Blog LUMA</h1>
        <form method="post">
            <label for="usuario">Usuario</label>
            <input type="text" id="usuario" name="usuario" autocomplete="username" required>

            <label for="clave">Clave</label>
            <input type="password" id="clave" name="clave" autocomplete="current-password" required>

            <button type="submit">Ingresar</button>
            <?php if ($error !== ''): ?>
                <p class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
            <?php if ($setupRequired): ?>
                <p class="hint">No existe un administrador activo en la base de datos. Crea un registro en <strong>admin_users</strong> o define las variables de entorno <strong>LUMA_ADMIN_USER</strong> y <strong>LUMA_ADMIN_PASS</strong> para inicializar el primero.</p>
            <?php endif; ?>
            <p class="hint">El acceso se bloquea temporalmente despues de varios intentos fallidos.</p>
        </form>
    </div>
</body>
</html>
