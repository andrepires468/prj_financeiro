#!/usr/bin/env bash
set -euo pipefail

# Diretório raiz do projeto (um nível acima de scripts/)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

ENV_FILE="$PROJECT_DIR/apps/financeiro_api/.env"
BACKUP_DIR="$PROJECT_DIR/backups"
KEEP_DAYS=30

# Lê variáveis do .env
if [[ -f "$ENV_FILE" ]]; then
  export $(grep -E '^(DB_DATABASE|DB_USERNAME|DB_PASSWORD)=' "$ENV_FILE" | xargs)
fi

DB_DATABASE="${DB_DATABASE:-home}"
DB_USERNAME="${DB_USERNAME:-root}"
DB_PASSWORD="${DB_PASSWORD:-1234}"
CONTAINER="financeiro_mysql"

mkdir -p "$BACKUP_DIR"

TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/backup_${TIMESTAMP}.sql.gz"

echo "Iniciando backup do banco '$DB_DATABASE' no container '$CONTAINER'..."

docker exec "$CONTAINER" mysqldump \
  -u"$DB_USERNAME" \
  -p"$DB_PASSWORD" \
  --single-transaction \
  --routines \
  --triggers \
  "$DB_DATABASE" | gzip > "$BACKUP_FILE"

echo "Backup salvo em: $BACKUP_FILE"

# Remove backups mais antigos que KEEP_DAYS dias
REMOVED=$(find "$BACKUP_DIR" -name "*.sql.gz" -mtime +"$KEEP_DAYS" -print -delete | wc -l)
if [[ "$REMOVED" -gt 0 ]]; then
  echo "Removidos $REMOVED backup(s) com mais de $KEEP_DAYS dias."
fi

echo "Concluído."
