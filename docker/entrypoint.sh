#!/usr/bin/env bash

set -euo pipefail

chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

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
