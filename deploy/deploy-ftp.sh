#!/usr/bin/env bash
# deploy/deploy-ftp.sh — publica src/ via FTP (fallback; primário é deploy.sh com SSH).
# MANUAL: você roda quando quiser. NÃO é on-push.
#
# Uso: (mesmos flags do deploy.sh: --dry-run, --yes, --no-delete)
# Config em deploy/.deploy.env (gitignored): FTP_HOST, FTP_USER, FTP_PASS, FTP_PATH, FTP_PORT, FTP_SSL.
# IMPORTANTE: FTP_SSL=yes (FTP puro dá timeout).
set -euo pipefail

DIR=$(cd "$(dirname "$0")" && pwd)
ENV="$DIR/.deploy.env"
SOURCE="$DIR/../src"

DRY=no; ASSUME_YES=no; DELETE=yes
for arg in "$@"; do
  case "$arg" in
    --dry-run)   DRY=yes ;;
    --yes|-y)    ASSUME_YES=yes ;;
    --no-delete) DELETE=no ;;
    *) echo "Argumento desconhecido: $arg"; exit 2 ;;
  esac
done

command -v lftp >/dev/null 2>&1 || { echo "Erro: lftp não instalado (macOS: brew install lftp)"; exit 1; }
[ -f "$ENV" ] || { echo "Erro: crie deploy/.deploy.env a partir de deploy/.deploy.env.example"; exit 1; }

while IFS='=' read -r key val; do
  [[ "$key" =~ ^[A-Z_]+$ ]] && export "$key=$val"
done < <(grep -v '^[[:space:]]*#' "$ENV" | grep '=')

: "${FTP_HOST:?FTP_HOST obrigatório (ex.: ftp.kataoka.com.br)}"
: "${FTP_USER:?FTP_USER obrigatório}"
: "${FTP_PASS:?FTP_PASS obrigatório}"
: "${FTP_PATH:?FTP_PATH obrigatório (ex.: joguinhodamila — pasta do app relativa ao home FTP)}"
FTP_PORT="${FTP_PORT:-21}"
FTP_SSL="${FTP_SSL:-yes}"

if [ "$FTP_SSL" = yes ]; then
  SSL_CFG="set ftp:ssl-allow yes; set ftp:ssl-force yes; set ftp:ssl-protect-data yes; set ssl:verify-certificate no;"
  SSL_LABEL="TLS explícito"
else
  SSL_CFG="set ftp:ssl-allow no;"
  SSL_LABEL="FTP puro (sem TLS)"
fi

echo "=================================================================="
echo "  ORIGEM: $SOURCE"
echo "  ALVO:   ftp://$FTP_HOST:$FTP_PORT/$FTP_PATH   ($SSL_LABEL, passivo)"
echo "  MODO:   $([ "$DRY" = yes ] && echo 'DRY-RUN — nada é alterado' || echo 'DEPLOY REAL')  |  DELETE=$DELETE"
echo "=================================================================="
echo

if [ "$DRY" = no ] && [ "$ASSUME_YES" = no ]; then
  read -r -p "Deploy real nesse alvo? digite 'sim' pra continuar: " ans
  [ "$ans" = "sim" ] || { echo "Cancelado."; exit 0; }
fi

MIRROR_OPTS="--reverse --verbose=1"
[ "$DELETE" = yes ] && MIRROR_OPTS="$MIRROR_OPTS --delete"
[ "$DRY" = yes ]    && MIRROR_OPTS="$MIRROR_OPTS --dry-run"

lftp -c "
$SSL_CFG
set ftp:passive-mode yes
set net:timeout 20
set net:max-retries 3
set net:reconnect-interval-base 5
open -p $FTP_PORT -u '$FTP_USER','$FTP_PASS' '$FTP_HOST'
cd $FTP_PATH
mirror $MIRROR_OPTS \
  --exclude .DS_Store \
  --exclude .git/ \
  --exclude 'api/data/*.sqlite*' \
  --exclude '.well-known/' \
  '$SOURCE' .
chmod 755 api/data
bye
"

echo "✓ $([ "$DRY" = yes ] && echo 'Dry-run concluído — nada foi alterado' || echo 'Deploy concluído')"
echo
echo "  Admin:  https://joguinhodamila.kataoka.com.br/?mila=<admin-token>"
echo "  Client: https://joguinhodamila.kataoka.com.br/"
