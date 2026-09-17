#!/usr/bin/env bash

set -euo pipefail

chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

# Los volúmenes nombrados de storage/bootstrap-cache persisten entre reinicios.
# Si alguna vista compilada quedó con otro propietario (p. ej. por un `docker exec`
# ejecutado como root), php-fpm (www-data) no puede actualizar su mtime con
# touch() y Blade falla con "Utime failed: Operation not permitted". Se limpia
# la caché de vistas en cada arranque para garantizar que se regenere como www-data.
rm -rf storage/framework/views/*.php

attempt=1
max_attempts=30

until php artisan db:show --no-interaction >/dev/null 2>&1; do
    if [ "$attempt" -ge "$max_attempts" ]; then
        echo "No fue posible conectar con MySQL después de ${max_attempts} intentos." >&2
        exit 1
    fi

    echo "Esperando la conexión con MySQL (${attempt}/${max_attempts})..."
    attempt=$((attempt + 1))
    sleep 2
done

php artisan migrate --force --no-interaction
php artisan storage:link --no-interaction >/dev/null 2>&1 || true

exec "$@"
