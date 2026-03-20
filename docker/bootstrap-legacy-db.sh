#!/bin/bash
set -euo pipefail

current_db_host="${DB_HOST:-database}"
current_db_port="${DB_PORT:-5432}"
current_db_name="${DB_DATABASE:-helpsub}"
current_db_user="${DB_USERNAME:-helpsub_user}"
current_db_password="${DB_PASSWORD:-helpsub_password}"
current_test_db_name="${DB_TEST_DATABASE:-helpsub_test}"

legacy_db_name="${LEGACY_DB_DATABASE:-smartsub}"
legacy_db_user="${LEGACY_DB_USERNAME:-smartsub_user}"
legacy_db_password="${LEGACY_DB_PASSWORD:-smartsub_password}"

psql_current() {
    PGPASSWORD="$current_db_password" psql \
        -v ON_ERROR_STOP=1 \
        -h "$current_db_host" \
        -p "$current_db_port" \
        -U "$current_db_user" \
        -d postgres \
        "$@"
}

psql_legacy() {
    PGPASSWORD="$legacy_db_password" psql \
        -v ON_ERROR_STOP=1 \
        -h "$current_db_host" \
        -p "$current_db_port" \
        -U "$legacy_db_user" \
        -d postgres \
        "$@"
}

current_connection_ok() {
    PGPASSWORD="$current_db_password" psql \
        -h "$current_db_host" \
        -p "$current_db_port" \
        -U "$current_db_user" \
        -d "$current_db_name" \
        -Atqc 'SELECT 1' >/dev/null 2>&1
}

legacy_connection_ok() {
    PGPASSWORD="$legacy_db_password" psql \
        -h "$current_db_host" \
        -p "$current_db_port" \
        -U "$legacy_db_user" \
        -d "$legacy_db_name" \
        -Atqc 'SELECT 1' >/dev/null 2>&1
}

legacy_database_exists() {
    PGPASSWORD="$legacy_db_password" psql \
        -h "$current_db_host" \
        -p "$current_db_port" \
        -U "$legacy_db_user" \
        -d postgres \
        -Atqc "SELECT 1 FROM pg_database WHERE datname = '$legacy_db_name'" | grep -q '^1$'
}

if current_connection_ok; then
    echo "✓ Banco HelpSub disponível com as credenciais atuais"
    exit 0
fi

if [ "$legacy_db_user" = "$current_db_user" ] && [ "$legacy_db_name" = "$current_db_name" ]; then
    echo "→ Banco atual indisponível e não há fallback legado distinto configurado"
    exit 0
fi

if ! legacy_connection_ok && ! legacy_database_exists; then
    echo "→ Nenhum banco legado detectado; seguindo sem bootstrap"
    exit 0
fi

echo "→ Detectado banco legado; preparando transição automática para HelpSub"

psql_legacy <<SQL
DO
\$\$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = '${current_db_user}') THEN
        EXECUTE format('CREATE ROLE %I LOGIN PASSWORD %L', '${current_db_user}', '${current_db_password}');
    ELSE
        EXECUTE format('ALTER ROLE %I WITH LOGIN PASSWORD %L', '${current_db_user}', '${current_db_password}');
    END IF;
END
\$\$;
SQL

target_db_exists=$(PGPASSWORD="$legacy_db_password" psql \
    -h "$current_db_host" \
    -p "$current_db_port" \
    -U "$legacy_db_user" \
    -d postgres \
    -Atqc "SELECT 1 FROM pg_database WHERE datname = '$current_db_name'")

if [ "$target_db_exists" != "1" ] && legacy_database_exists; then
    echo "→ Clonando banco legado '$legacy_db_name' para '$current_db_name'"
    PGPASSWORD="$legacy_db_password" createdb \
        -h "$current_db_host" \
        -p "$current_db_port" \
        -U "$legacy_db_user" \
        -O "$current_db_user" \
        -T "$legacy_db_name" \
        "$current_db_name"
fi

test_db_exists=$(PGPASSWORD="$legacy_db_password" psql \
    -h "$current_db_host" \
    -p "$current_db_port" \
    -U "$legacy_db_user" \
    -d postgres \
    -Atqc "SELECT 1 FROM pg_database WHERE datname = '$current_test_db_name'")

if [ "$test_db_exists" != "1" ]; then
    echo "→ Criando banco de testes '$current_test_db_name'"
    PGPASSWORD="$legacy_db_password" createdb \
        -h "$current_db_host" \
        -p "$current_db_port" \
        -U "$legacy_db_user" \
        -O "$current_db_user" \
        "$current_test_db_name"
fi

echo "→ Ajustando ownership e grants do banco '$current_db_name'"
psql_legacy <<SQL
ALTER DATABASE "$current_db_name" OWNER TO "$current_db_user";
GRANT CONNECT, TEMPORARY ON DATABASE "$current_db_name" TO "$current_db_user";
SQL

PGPASSWORD="$legacy_db_password" psql \
    -v ON_ERROR_STOP=1 \
    -h "$current_db_host" \
    -p "$current_db_port" \
    -U "$legacy_db_user" \
    -d "$current_db_name" <<SQL
ALTER SCHEMA public OWNER TO "$current_db_user";
GRANT USAGE, CREATE ON SCHEMA public TO "$current_db_user";
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO "$current_db_user";
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO "$current_db_user";
GRANT ALL PRIVILEGES ON ALL FUNCTIONS IN SCHEMA public TO "$current_db_user";
ALTER DEFAULT PRIVILEGES FOR ROLE "$legacy_db_user" IN SCHEMA public GRANT ALL ON TABLES TO "$current_db_user";
ALTER DEFAULT PRIVILEGES FOR ROLE "$legacy_db_user" IN SCHEMA public GRANT ALL ON SEQUENCES TO "$current_db_user";
ALTER DEFAULT PRIVILEGES FOR ROLE "$legacy_db_user" IN SCHEMA public GRANT ALL ON FUNCTIONS TO "$current_db_user";
SQL

echo "✓ Transição automática de banco concluída"