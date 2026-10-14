# Entrelineas · Blog académico de noticias

Laravel 12 + Blade + MariaDB + CSS propio. Identidad editorial, acceso administrativo único y pruebas de calidad. No requiere Node, compilación frontend ni Docker. Las noticias seed son ficticias y originales.

## Preparar macOS Apple Silicon

PHP 8.2 o superior compatible con el lock, Composer 2 y MariaDB. En el equipo verificado: PHP 8.5.10, Composer 2.10.3 y MariaDB 12.3.3. Si faltan herramientas, instalarlas desde Homebrew con sus instrucciones oficiales; comprobar `php --version`, `php --ini`, `composer --version` y `mariadb --version`. Se requieren pdo_mysql, pdo_sqlite, mbstring, openssl, xml y curl. No ejecutar composer update general.

```zsh
cd /Users/luisvilameza/Universidad/Pruebas_Calidad/Labs/Examen/blog-noticias
composer install
cp .env.example .env
php artisan key:generate
```

El ZIP excluye vendor: Composer lo reconstruye desde composer.lock. Si ya existe .env, conservarlo y revisar sus valores; no reemplazar una configuración local válida con `cp`.

## Crear una base nueva y exclusiva

En una sesión administrativa de MariaDB gestionada por el propietario del equipo, ejecutar:

```sql
CREATE DATABASE pa02_blog CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'pa02_blog'@'127.0.0.1' IDENTIFIED BY 'REEMPLAZAR_CON_CLAVE_LOCAL';
GRANT ALL PRIVILEGES ON pa02_blog.* TO 'pa02_blog'@'127.0.0.1';
SHOW GRANTS FOR 'pa02_blog'@'127.0.0.1';
```

No usar root como usuario de la aplicación. Si la administración local requiere `sudo mariadb`, debe realizarla el propietario en su Terminal; no el agente. No intentar eludir autenticación. Si pa02_blog ya existe con datos, detener la preparación destructiva y revisar su exclusividad. El comando seguro se niega a hacer migrate:fresh si encuentra tablas.

Completar .env sin compartir la contraseña:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pa02_blog
DB_USERNAME=pa02_blog
DB_PASSWORD=REEMPLAZAR_CON_CLAVE_LOCAL
```

Confirmar el puerto real del servidor. Este proyecto exige 3306 para su preparación académica. Guardar el secreto solo en .env; no adjuntarlo a la entrega.

## Verificar, migrar y sembrar

```zsh
php artisan optimize:clear
php artisan pa02:prepare
```

`pa02:prepare` ejecuta desde Laravel `SELECT DATABASE(), @@port, VERSION()`, exige pa02_blog/3306 y base vacía, y solo entonces llama a `migrate:fresh --seed`. Guarda evidencia SQL, migraciones y conteos en `../evidencias`. Tres migraciones crean users, categories y posts. Seeder: una cuenta, cuatro categorías, 18 noticias (16 publicadas y dos borradores).

Para conservar una base ya instalada, usar `php artisan migrate` y, cuando corresponda, `php artisan db:seed`; el seeder evita duplicar sus noticias por título. No repetir migrate:fresh sobre contenido que deba conservarse.

## Ejecutar

```zsh
php artisan serve --host=127.0.0.1 --port=8020
```

| URL | Uso |
|---|---|
| http://127.0.0.1:8020/ | Portada |
| /noticias | Buscar, filtrar y paginar |
| /categorias | Secciones del medio |
| /noticias/{slug} | Detalle de una publicada |
| /login | Iniciar sesión |
| /admin | Resumen y noticias recientes |
| /admin/noticias | Administrar noticias |
| /admin/noticias/create | Crear noticia |

Login: `admin@example.test` / `Admin123!`, solo para demostración académica local. No existe registro público, reset de contraseña ni múltiples roles. El contenido se escribe como texto plano, con una línea en blanco entre párrafos. Blade escapa las etiquetas.

## Pruebas y evidencias

```zsh
php artisan test
composer validate --strict
php vendor/bin/pint --test
php artisan route:list
php artisan migrate:status
zsh scripts/verificar-local.sh
python3 scripts/contraste.py
```

PHPUnit usa SQLite :memory: forzado y una guarda anterior a las migraciones. Cualquier otra base se bloquea. No necesita acceso a MariaDB. El script HTTP requiere servidor activo; registra smoke y cinco muestras por URL, usa cookies temporales y las elimina al terminar. Sus tiempos son locales, no de producción.

Se verificaron teclado, foco, skip link, labels, errores, zoom 100/200 % y anchos 320/768/1200. Se aplicaron y verificaron buenas prácticas de accesibilidad; no es una certificación WCAG. La aplicación no depende de contenido auditivo para transmitir información o completar tareas.

La documentación académica está en [../documentacion](../documentacion/PLAN_PRUEBAS.md) y el informe en [../INFORME_PA02.md](../INFORME_PA02.md). Antes de enviar, completar el nombre en INTEGRANTES.txt y en la portada del informe. Los archivos de métricas y el ZIP pueden regenerarse con `python3 ../documentacion/scripts/metricas.py` y `python3 ../documentacion/scripts/empacar.py`.

## Explicación para la defensa

- Rutas públicas consultan published; el detalle aplica 404 a draft.
- Middleware auth protege todos los verbos de /admin; Auth valida y regenera sesión.
- PostRequest concentra validaciones y mensajes en español.
- El modelo Post genera slug con sufijos y fecha de primera publicación.
- Category hasMany Post; Post belongsTo Category; listas usan with y paginate.
- Blade y CSS semánticos dan foco, skip link, labels y errores asociados.
- PHPUnit verifica reglas repetibles; observación del navegador verifica apariencia y uso efectivo.

La documentación conserva los fallos iniciales y sus correcciones, junto con límites y datos reales.
