#!/usr/bin/env bash
set -euo pipefail

# Diretório raiz do projeto (um nível acima de scripts/)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

ROOT_ENV_FILE="$PROJECT_DIR/.env"
API_ENV_FILE="$PROJECT_DIR/apps/financeiro_api/.env"
BACKUP_DIR="$PROJECT_DIR/backups"
KEEP_DAYS=30

# Carrega um .env preservando caracteres especiais (!, #, espaços, etc.)
# Só exporta as variáveis que o script realmente usa.
load_env_vars() {
  local env_file="$1"
  [[ -f "$env_file" ]] || return 0
  local line var val
  while IFS= read -r line || [[ -n "$line" ]]; do
    # Remove CR (\r) de finais de linha Windows (CRLF)
    line="${line%$'\r'}"
    [[ "$line" =~ ^[[:space:]]*# ]] && continue
    [[ -z "${line//[[:space:]]/}" ]] && continue
    if [[ "$line" =~ ^(DB_DATABASE|DB_USERNAME|DB_PASSWORD|MEGA_EMAIL|MEGA_PASSWORD|MEGA_REMOTE_DIR|MEGA_KEEP_DAYS)= ]]; then
      var="${line%%=*}"
      val="${line#*=}"
      # Apara CR residual e espaços/tabs nas bordas
      val="${val%$'\r'}"
      val="${val#"${val%%[![:space:]]*}"}"
      val="${val%"${val##*[![:space:]]}"}"
      # Remove aspas envolventes, se houver
      if [[ "$val" =~ ^\".*\"$ ]]; then
        val="${val:1:${#val}-2}"
      elif [[ "$val" =~ ^\'.*\'$ ]]; then
        val="${val:1:${#val}-2}"
      fi
      export "$var=$val"
    fi
  done < "$env_file"
}

# Carrega o .env da raiz primeiro; valores do .env da API têm prioridade
# para DB_* (banco é config da API). MEGA_* geralmente ficam na raiz.
load_env_vars "$ROOT_ENV_FILE"
load_env_vars "$API_ENV_FILE"

DB_DATABASE="${DB_DATABASE:-home}"
DB_USERNAME="${DB_USERNAME:-root}"
DB_PASSWORD="${DB_PASSWORD:-1234}"
CONTAINER="financeiro_mysql"

# Configurações do MEGA (podem vir do .env ou ficar fixas aqui)
MEGA_REMOTE_DIR="${MEGA_REMOTE_DIR:-/Backups/Financeiro}"
MEGA_KEEP_DAYS="${MEGA_KEEP_DAYS:-$KEEP_DAYS}"

mkdir -p "$BACKUP_DIR"

TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/backup_${TIMESTAMP}.sql.gz"
ENV_BACKUP_FILE="$BACKUP_DIR/backup_${TIMESTAMP}_env.tar.gz"

echo "Iniciando backup do banco '$DB_DATABASE' no container '$CONTAINER'..."

docker exec "$CONTAINER" mysqldump \
  -u"$DB_USERNAME" \
  -p"$DB_PASSWORD" \
  --single-transaction \
  --routines \
  --triggers \
  "$DB_DATABASE" | gzip > "$BACKUP_FILE"

echo "Backup salvo em: $BACKUP_FILE"

# ---------------------------------------------------------------------------
# Empacota todos os arquivos .env* do projeto preservando a estrutura de
# pastas (relativa à raiz do projeto). Ignora node_modules, vendor, .git
# e a própria pasta de backups.
# ---------------------------------------------------------------------------
echo "Empacotando arquivos .env* do projeto..."
mapfile -t env_files < <(cd "$PROJECT_DIR" && find . -name ".env*" -type f \
  -not -path "./node_modules/*" -not -path "*/node_modules/*" \
  -not -path "./vendor/*" -not -path "*/vendor/*" \
  -not -path "./.git/*" \
  -not -path "./backups/*" \
  | sed 's|^\./||' | sort)

if [[ "${#env_files[@]}" -gt 0 ]]; then
  (cd "$PROJECT_DIR" && tar -czf "$ENV_BACKUP_FILE" "${env_files[@]}")
  echo "Backup de .env* salvo em: $ENV_BACKUP_FILE (${#env_files[@]} arquivo(s))"
  for f in "${env_files[@]}"; do
    echo "  - $f"
  done
else
  echo "Nenhum arquivo .env* encontrado — pulando tarball."
  ENV_BACKUP_FILE=""
fi

# ---------------------------------------------------------------------------
# Upload para o MEGA (requer MEGAcmd instalado: https://mega.nz/cmd)
# ---------------------------------------------------------------------------
# No Windows (Git Bash/MSYS) os comandos ficam como mega-*.bat. Se o MEGAcmd
# foi instalado em C:\Users\<user>\AppData\Local\MEGAcmd e ainda não está no
# PATH, acrescentamos automaticamente aqui.
if [[ -n "${LOCALAPPDATA:-}" ]] && command -v cygpath >/dev/null 2>&1; then
  WIN_MEGACMD_DIR_MSYS="$(cygpath -u "${LOCALAPPDATA}\\MEGAcmd" 2>/dev/null || true)"
  if [[ -n "$WIN_MEGACMD_DIR_MSYS" && -d "$WIN_MEGACMD_DIR_MSYS" ]]; then
    case ":$PATH:" in
      *":$WIN_MEGACMD_DIR_MSYS:"*) ;;
      *) export PATH="$PATH:$WIN_MEGACMD_DIR_MSYS" ;;
    esac
  fi
fi

# Detecta o sufixo correto dos binários (vazio em Linux/Mac, ".bat" em Windows)
MEGA_SUFFIX=""
if command -v mega-put >/dev/null 2>&1; then
  MEGA_SUFFIX=""
elif command -v mega-put.bat >/dev/null 2>&1; then
  MEGA_SUFFIX=".bat"
  # Evita que o MSYS/Git Bash converta caminhos remotos tipo "/DEV/..." em
  # "C:/Program Files/Git/DEV/..." ao chamar os binários Windows do MEGAcmd.
  export MSYS_NO_PATHCONV=1
  export MSYS2_ARG_CONV_EXCL="*"
fi

if command -v "mega-put${MEGA_SUFFIX}" >/dev/null 2>&1; then
  echo "Enviando backup para o MEGA em '$MEGA_REMOTE_DIR'..."

  # Se MEGA_EMAIL/MEGA_PASSWORD foram fornecidos no .env, garante sessão.
  # Caso contrário, assume que 'mega-login' já foi executado manualmente.
  if [[ -n "${MEGA_EMAIL:-}" && -n "${MEGA_PASSWORD:-}" ]]; then
    if ! "mega-whoami${MEGA_SUFFIX}" 2>/dev/null | grep -q "$MEGA_EMAIL"; then
      "mega-login${MEGA_SUFFIX}" "$MEGA_EMAIL" "$MEGA_PASSWORD" >/dev/null
    fi
  fi

  # Garante que a pasta remota exista
  "mega-mkdir${MEGA_SUFFIX}" -p "$MEGA_REMOTE_DIR" >/dev/null 2>&1 || true

  # Faz upload de um arquivo local para $MEGA_REMOTE_DIR.
  # No Windows o mega-put.bat chama um binário nativo que só entende caminhos
  # no formato Windows (E:\...), não o estilo MSYS (/e/...). Converte quando
  # estamos usando o wrapper .bat.
  upload_to_mega() {
    local local_file="$1"
    local upload_path="$local_file"
    if [[ "$MEGA_SUFFIX" == ".bat" ]] && command -v cygpath >/dev/null 2>&1; then
      upload_path="$(cygpath -w "$local_file")"
    fi
    if "mega-put${MEGA_SUFFIX}" -c "$upload_path" "$MEGA_REMOTE_DIR/"; then
      echo "Upload concluído: $MEGA_REMOTE_DIR/$(basename "$local_file")"
      return 0
    fi
    return 1
  }

  UPLOAD_OK=1
  upload_to_mega "$BACKUP_FILE" || UPLOAD_OK=0
  if [[ -n "$ENV_BACKUP_FILE" ]]; then
    upload_to_mega "$ENV_BACKUP_FILE" || UPLOAD_OK=0
  fi

  if [[ "$UPLOAD_OK" -eq 1 ]]; then
    # Limpeza de backups antigos no MEGA.
    # MEGA_KEEP_DAYS=0 (ou vazio) desabilita a expiração — mantém tudo.
    # Cobre tanto backup_*.sql.gz quanto backup_*_env.tar.gz.
    if [[ "${MEGA_KEEP_DAYS:-0}" =~ ^[0-9]+$ ]] && [[ "$MEGA_KEEP_DAYS" -gt 0 ]]; then
      CUTOFF=$(date -d "$MEGA_KEEP_DAYS days ago" +%Y%m%d 2>/dev/null || date -v-"${MEGA_KEEP_DAYS}"d +%Y%m%d)
      REMOVED_REMOTE=0
      while IFS= read -r remote_file; do
        file_date=$(echo "$remote_file" | grep -oE 'backup_[0-9]{8}' | sed 's/backup_//')
        if [[ -n "$file_date" && "$file_date" -lt "$CUTOFF" ]]; then
          "mega-rm${MEGA_SUFFIX}" "$MEGA_REMOTE_DIR/$remote_file" >/dev/null 2>&1 && \
            REMOVED_REMOTE=$((REMOVED_REMOTE + 1))
        fi
      done < <("mega-ls${MEGA_SUFFIX}" "$MEGA_REMOTE_DIR" 2>/dev/null | grep -E '^backup_.*\.(sql\.gz|tar\.gz)$' || true)

      if [[ "$REMOVED_REMOTE" -gt 0 ]]; then
        echo "Removidos $REMOVED_REMOTE arquivo(s) antigo(s) no MEGA."
      fi
    else
      echo "Expiração de backups no MEGA desabilitada (MEGA_KEEP_DAYS=${MEGA_KEEP_DAYS:-0})."
    fi
  else
    echo "AVISO: falha ao enviar um ou mais arquivos para o MEGA." >&2
  fi
else
  echo "AVISO: 'mega-put' não encontrado. Pulando upload para o MEGA." >&2
  echo "Instale o MEGAcmd: https://mega.nz/cmd" >&2
fi

# Remove backups locais mais antigos que KEEP_DAYS dias
# (cobre backup_*.sql.gz e backup_*_env.tar.gz)
REMOVED=$(find "$BACKUP_DIR" -type f \
  \( -name "backup_*.sql.gz" -o -name "backup_*_env.tar.gz" \) \
  -mtime +"$KEEP_DAYS" -print -delete | wc -l)
if [[ "$REMOVED" -gt 0 ]]; then
  echo "Removidos $REMOVED arquivo(s) local(is) com mais de $KEEP_DAYS dias."
fi

echo "Concluído."
