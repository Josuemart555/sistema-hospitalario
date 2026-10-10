---
name: laravel-expert
description: Convenciones de Laravel para sistema-hospitalario (Laravel 13, PHP 8.3, app monolítica con Blade + Vue + Bootstrap, Yajra DataTables, spatie/permission, medialibrary y Fortify). Úsala SIEMPRE que crees o modifiques código PHP/Blade/Vue/migraciones/tests de este repo — controladores, Form Requests, Services, DataTables, Models, rutas, permisos, vistas y pruebas.
---

# Laravel en sistema-hospitalario

Monolito Laravel `^13.17`, PHP `^8.3`, sin `Modules/`. Todo vive en `app/`. Frontend renderizado con **Blade + Bootstrap** y componentes **Vue** puntuales (`resources/js/components`). Autenticación con **Fortify** (2FA, passkeys, invitación por correo). Antes de crear algo, busca el equivalente existente (p. ej. `app/Http/Controllers/Admin/UserController.php`) e imítalo.

Paquetes clave: `spatie/laravel-permission` (roles/permisos), `spatie/laravel-medialibrary` (avatar), `yajra/laravel-datatables` (listados), `laravel/fortify`, `laravel/pint`, PHPUnit 12.

## Mapa del proyecto

| Necesidad | Dónde / cómo | Ejemplo real |
|---|---|---|
| Pantallas CRUD | Controlador `resource` en `app/Http/Controllers/Admin/` que devuelve `View`/`RedirectResponse` | `UserController`, `RoleController` |
| Validar input | Form Request en `app/Http/Requests/` (`Admin/` para administración) | `Admin/StoreUserRequest`, `UpdateProfileRequest` |
| Listados | Clase DataTable en `app/DataTables/` extendiendo `AdministrationDataTable`; el controlador hace `$dataTable->render('admin.x.index')` | `RolesDataTable` |
| Lógica reutilizable | Clase en `app/Services/` (inyectada por método/constructor) | `MenuService`, `ProgressiveLoginThrottle` |
| Menú dinámico | Modelo `Option` (árbol + permiso) vía `MenuService`; tras cambiar opciones/roles/usuarios llama `$menu->flush()` | `UserController::store` |
| Autorización | Permisos Spatie con formato `recurso.accion` aplicados como middleware `can:` en la ruta | `usuarios.administrar`, `dashboard.ver` |
| Notificaciones | `app/Notifications/` | `UserInvitationNotification` |
| Vistas | `resources/views/<area>/<recurso>/{index,form,columns/action}.blade.php`, layouts en `layouts/`, parciales en `partials/`, componentes en `components/` | `admin/users/form.blade.php` |
| Textos | Español; `lang/es/*.php` para textos del framework. Los mensajes flash del admin están en español dentro del controlador | `->with('success', 'Usuario creado...')` |

## Rutas

En `routes/web.php`, dentro de `Route::middleware(['auth','verified'])`. Las de administración van bajo `prefix('administracion')->name('admin.')`, URLs en español (`usuarios`, `roles`, `permisos`, `opciones`) con `->parameters([...])` para el binding en singular y `->names('x')`. Cada recurso lleva `->middleware('can:<recurso>.administrar')`. Al crear un módulo nuevo: ruta + permiso + registro en `Option` (menú) + seeder/migración del permiso.

## Controladores

Delgados: reciben un Form Request, usan `DB::transaction` para escrituras múltiples (usuario + roles + permisos + opciones + media), delegan lo reutilizable a un Service, y devuelven `View`, `RedirectResponse` o `JsonResponse` (DataTables). Tipa siempre el retorno y mantén los docblocks `/** Display a listing... */` estándar del resource. Redirige con `->with('success', ...)` (se muestra en `partials/messages.blade.php`).

## Autorización y seguridad

- Se resuelve con **permisos Spatie + middleware `can:`**, no con Policies (no existe `app/Policies`). No crees Policies salvo que se pida.
- Nombres de permiso: `recurso.accion` en minúsculas.
- Datos de salud/personales: nunca loguees ni expongas datos de pacientes en errores, URLs o respuestas innecesarias. Valida y autoriza en servidor aunque el botón esté oculto en la vista.
- Blade con `{{ }}` (escapado); `{!! !!}` solo con HTML generado por nosotros (columnas DataTable con `rawColumns`).
- Mantén `SecurityHeaders` middleware y el throttle de login (`ProgressiveLoginThrottle`) intactos.
- Contraseñas con `Hash::make`; los usuarios nuevos se crean `status = 'invited'` con contraseña aleatoria y token de reset.

## Eloquent y base de datos

- Eager loading (`with`, `withCount`) en listados; en DataTables selecciona solo las columnas necesarias.
- `$fillable` explícito, nunca `$guarded = []`. Modelos propios: `User`, `Role` (extiende el de Spatie), `Option`.
- Migraciones nuevas con fecha actual, claves foráneas explícitas e índices en columnas de filtro.
- Cachea con `Cache::remember` usando clave versionada (patrón `menu.version`) e invalida con un método `flush()`.
- Correos/reportes/integraciones lentas → Jobs (`QUEUE_CONNECTION` ya tiene tabla `jobs`).

## Frontend (Blade + Vue)

Usa Bootstrap y los parciales/componentes existentes (`empty-state`, `DualListbox`, `OptionsTree`, `PasswordStrength`) antes de crear otros. Vue solo para widgets interactivos registrados en `resources/js/app.js`; el resto es Blade. Tablas: Yajra DataTables con acciones en `columns/action.blade.php`.

## Entorno y pruebas

- El proyecto corre en **Docker** (`docker-compose.yml`; MySQL en el host `mysql-global`). Ejecuta artisan/composer/tests dentro del contenedor o con el flujo ya usado en el repo, y verifica antes de asumir.
- **Prohibido probar contra el MySQL de desarrollo.** `phpunit.xml` fuerza SQLite `:memory:` (con `<env>` y `<server>`); no lo debilites. Nunca corras seeders, `migrate:fresh` ni comandos que escriban en el MySQL local sin que el usuario lo pida.
- Tests **PHPUnit** (no Pest) en `tests/Feature` (HTTP end-to-end, `RefreshDatabase`, `actingAs` con usuario con permiso) y `tests/Unit`. Patrón existente: un archivo por área (`UsersDataTableTest`, `AdministrationAuthorizationTest`, `MenuServiceTest`...). Usa `Mail::fake()`, `Notification::fake()`, `Queue::fake()`, `Http::fake()`.
- Todo cambio de autorización o de CRUD admin lleva test de: acceso permitido, acceso denegado (403) y validación.

## Estilo

- Imita el estilo del archivo que editas. Archivos existentes no usan `declare(strict_types=1)`; en archivos **nuevos** puedes añadirlo.
- Tipos de argumentos y retorno explícitos, `readonly`/constructor promotion cuando aplique, colecciones en vez de `foreach` imperativos cuando mejore la lectura.
- `vendor/bin/pint` **solo sobre los archivos que tú creaste o modificaste** (`pint --dirty` o rutas concretas), nunca sobre todo el repo.
- Hay guía extendida en `.agents/skills/laravel-best-practices/rules/*.md`; consúltala por tema (eloquent, security, validation, caching...) cuando necesites detalle.

## Antes de dar por terminada una tarea

- [ ] ¿Controlador delgado, validación en Form Request, retorno tipado?
- [ ] ¿Ruta con `can:<recurso>.accion` y permiso creado/asignado (y `MenuService::flush()` si cambió el menú)?
- [ ] ¿Listados con DataTable y sin N+1?
- [ ] ¿Escrituras múltiples en `DB::transaction`?
- [ ] ¿Textos de interfaz en español y vistas reutilizando layout/parciales?
- [ ] ¿Tests PHPUnit añadidos y ejecutados con SQLite en memoria (nunca el MySQL de desarrollo)?
- [ ] ¿Pint solo en archivos propios? ¿Sin Policies/Repositories/API Resources innecesarios?
- [ ] ¿Sin datos sensibles de pacientes en logs ni respuestas?
