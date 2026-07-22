#!/usr/bin/env bash
# deploy/deploy.sh — publica src/ na produção via rsync sobre SSH.
# MANUAL: você roda quando quiser. NÃO é on-push. (Fallback FTP: deploy-ftp.sh)
#
# Uso:
#   ./deploy/deploy.sh --dry-run     # mostra o que faria; NÃO altera nada
#   ./deploy/deploy.sh               # deploy real (pede confirmação do alvo)
#   ./deploy/deploy.sh --yes         # sem perguntar
#   ./deploy/deploy.sh --no-delete   # sobe arquivos mas NÃO apaga os que sobraram no servidor
#
# Config em deploy/.deploy.env (gitignored): SSH_HOST, SSH_USER, SSH_PORT, REMOTE_PATH, SSH_KEY.
set -euo pipefail

DIR=$(cd "$(dirname "$0")" && pwd)
ENV="$DIR/.deploy.env"
SOURCE="$DIR/../src/"   # barra no fim: envia o CONTEÚDO de src/ pra dentro de REMOTE_PATH

DRY=no; ASSUME_YES=no; DELETE=yes
for arg in "$@"; do
  case "$arg" in
    --dry-run)   DRY=yes ;;
    --yes|-y)    ASSUME_YES=yes ;;
    --no-delete) DELETE=no ;;
    *) echo "Argumento desconhecido: $arg"; exit 2 ;;
  esac
done

command -v rsync >/dev/null 2>&1 || { echo "Erro: rsync não encontrado"; exit 1; }
[ -f "$ENV" ] || { echo "Erro: crie deploy/.deploy.env a partir de deploy/.deploy.env.example"; exit 1; }

while IFS='=' read -r key val; do
  [[ "$key" =~ ^[A-Z_]+$ ]] && export "$key=$val"
done < <(grep -v '^[[:space:]]*#' "$ENV" | grep '=')

: "${SSH_HOST:?SSH_HOST obrigatório (ex.: kataoka.com.br)}"
: "${SSH_USER:?SSH_USER obrigatório (ex.: kataokac)}"
: "${REMOTE_PATH:?REMOTE_PATH obrigatório — pasta do app relativa ao home SSH (ex.: joguinhodamila)}"
SSH_PORT="${SSH_PORT:-65002}"

SSH_CMD="ssh -p $SSH_PORT -o StrictHostKeyChecking=accept-new -o ServerAliveInterval=15"
[ -n "${SSH_KEY:-}" ] && SSH_CMD="$SSH_CMD -i ${SSH_KEY/#\~/$HOME}"

echo "=================================================================="
echo "  ORIGEM: $SOURCE"
echo "  ALVO:   $SSH_USER@$SSH_HOST:$REMOTE_PATH   (porta $SSH_PORT)"
echo "  MODO:   $([ "$DRY" = yes ] && echo 'DRY-RUN — nada é alterado' || echo 'DEPLOY REAL')  |  DELETE=$DELETE"
echo "=================================================================="
echo

if [ "$DRY" = no ] && [ "$ASSUME_YES" = no ]; then
  read -r -p "Deploy real nesse alvo? digite 'sim' pra continuar: " ans
  [ "$ans" = "sim" ] || { echo "Cancelado."; exit 0; }
fi

RSYNC_OPTS=(-avz --checksum --ignore-times --human-readable)
[ "$DELETE" = yes ] && RSYNC_OPTS+=(--delete)
[ "$DRY" = yes ]    && RSYNC_OPTS+=(--dry-run)

rsync "${RSYNC_OPTS[@]}" \
  --exclude '.DS_Store' \
  --exclude '.git/' \
  --exclude 'api/data/*.sqlite*' \
  --exclude '.well-known/' \
  -e "$SSH_CMD" \
  "$SOURCE" "$SSH_USER@$SSH_HOST:$REMOTE_PATH/"

# Garante permissão de escrita no data/ (SQLite precisa criar/escrever o arquivo).
if [ "$DRY" = no ]; then
  ssh -p "$SSH_PORT" -o StrictHostKeyChecking=accept-new ${SSH_KEY:+-i ${SSH_KEY/#\~/$HOME}} \
    "$SSH_USER@$SSH_HOST" "chmod 755 $REMOTE_PATH/api/data"
fi

echo "✓ $([ "$DRY" = yes ] && echo 'Dry-run concluído — nada foi alterado' || echo 'Deploy concluído')"
echo
echo "  Admin:  https://joguinhodamila.kataoka.com.br/?mila=<admin-token>"
echo "  Client: https://joguinhodamila.kataoka.com.br/"
