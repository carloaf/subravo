#!/bin/bash
set -e

# =============================================================================
# HelpSub — Container Entrypoint
# =============================================================================

echo "╔══════════════════════════════════════╗"
echo "║  HelpSub — Iniciando aplicacao...     ║"
echo "╚══════════════════════════════════════╝"

# ── Aguarda dependências ──────────────────────────────────────────
wait_for_service() {
    local host="$1" port="$2" name="$3" retries=30
    echo "→ Aguardando ${name} (${host}:${port})..."
    until nc -z "$host" "$port" 2>/dev/null || [ $retries -le 0 ]; do
        retries=$((retries - 1))
        sleep 1
    done
    if [ $retries -le 0 ]; then
        echo "✗ ${name} não respondeu após 30s"
        exit 1
    fi
    echo "✓ ${name} disponível"
}

wait_for_service "${DB_HOST:-database}" "${DB_PORT:-5432}" "PostgreSQL"
wait_for_service "${REDIS_HOST:-redis}" "${REDIS_PORT:-6379}" "Redis"

# ── Bootstrap de banco legado ───────────────────────────────────
if [ "${CONTAINER_ROLE:-app}" = "app" ] && [ -x /usr/local/bin/bootstrap-legacy-db.sh ]; then
    /usr/local/bin/bootstrap-legacy-db.sh
fi

# ── Permissões de storage ────────────────────────────────────────
echo "→ Ajustando permissões..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# ── Instalar dependências dev em ambiente local ──────────────────
if [ "$APP_ENV" != "production" ] && [ -f /var/www/html/composer.json ]; then
    if [ -f /usr/bin/composer ]; then
        echo "→ Instalando dependências (dev)..."
        composer install --working-dir=/var/www/html --no-interaction --quiet 2>/dev/null || true
    fi
fi

# ── Gerar APP_KEY se ausente ─────────────────────────────────────
# env_file: .env injeta variáveis no momento da CRIAÇÃO do container.
# Se APP_KEY foi gerado depois, o env var ainda está vazio.
# Solução: ler sempre do .env file e exportar para o processo.
ENV_KEY=$(grep -E '^APP_KEY=' /var/www/html/.env 2>/dev/null | cut -d= -f2-)
if [ -z "$ENV_KEY" ] || [ "$ENV_KEY" = "base64:" ]; then
    echo "→ Gerando APP_KEY..."
    php artisan key:generate --force --no-interaction
    ENV_KEY=$(grep -E '^APP_KEY=' /var/www/html/.env | cut -d= -f2-)
fi
export APP_KEY="$ENV_KEY"
echo "✓ APP_KEY configurado"

# ── Migrations ───────────────────────────────────────────────────
echo "→ Executando migrations..."
php artisan migrate --force --no-interaction

# ── Cache de configuração (produção) ─────────────────────────────
if [ "$APP_ENV" = "production" ]; then
    echo "→ Otimizando para produção..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
else
    echo "→ Ambiente de desenvolvimento — sem cache"
    php artisan config:clear 2>/dev/null || true
    php artisan route:clear 2>/dev/null || true
    php artisan view:clear 2>/dev/null || true
fi

# ── Storage link ─────────────────────────────────────────────────
php artisan storage:link --force --no-interaction 2>/dev/null || true

echo "╔══════════════════════════════════════╗"
echo "║  HelpSub — Pronto! (porta 8081)      ║"
echo "╚══════════════════════════════════════╝"

# ── Inicia Apache ────────────────────────────────────────────────
exec "$@"
