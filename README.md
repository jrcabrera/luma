# luma

## Admin del blog

El acceso a `admin/login.php` ahora valida usuarios contra la tabla `admin_users` de la base de datos.
Ademas, las acciones del panel usan proteccion CSRF, cierre de sesion por POST y endurecimiento basico de sesion.

### Crear el primer administrador

Opcion 1: inicializacion automatica

Define estas variables de entorno antes de abrir el sitio:

- `LUMA_ADMIN_USER`
- `LUMA_ADMIN_PASS`
- `LUMA_ADMIN_NAME` (opcional)

Si la tabla `admin_users` esta vacia, el sistema creara el primer usuario automaticamente.

Opcion 2: insercion manual en MySQL

Ejecuta primero la aplicacion una vez para que se cree la tabla `admin_users` y luego inserta un usuario con un hash generado por PHP:

```php
<?php
echo password_hash('TU_CLAVE_SEGURA', PASSWORD_DEFAULT);
```

Despues inserta el resultado en MySQL:

```sql
INSERT INTO admin_users (usuario, nombre, clave_hash, activo)
VALUES ('admin@tu-dominio.com', 'Administrador', 'PEGA_AQUI_EL_HASH', 1);
```

## URLs sin extension

El proyecto incluye un archivo `.htaccess` para servir rutas limpias sin `.html` ni `.php`.

Ejemplos:

- `/contact` en lugar de `/contact.html`
- `/blog` en lugar de `/blog.php`
- `/admin/login` en lugar de `/admin/login.php`

Notas:

- En Apache debe estar activo `mod_rewrite`.
- El virtual host o la carpeta debe permitir `AllowOverride All` para que `.htaccess` funcione.
- Cuando existe el mismo nombre en `.php` y `.html`, la ruta limpia prioriza `.php`.

