#!/bin/bash
# Deploys the current git HEAD (tracked files only) to IONOS over FTP.
#
# Content that's normally edited live through /admin - content/members.json
# and uploads/ - is intentionally never touched by this script. Those
# directories are excluded from the mirror so a code deploy can never
# clobber live site content; they're seeded once manually on first deploy
# (see README.md) and then left alone here.
#
# Credentials come from .env (gitignored, never committed) - see
# .env.example for the required FTP_* keys.
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

if ! command -v lftp >/dev/null 2>&1; then
  echo "lftp is required (brew install lftp)." >&2
  exit 1
fi
if ! command -v git >/dev/null 2>&1; then
  echo "git is required." >&2
  exit 1
fi
if [ ! -f .env ]; then
  echo "Missing .env - copy .env.example to .env and fill in the FTP_* values first." >&2
  exit 1
fi

# Load .env without `source`: values like the bcrypt password hash contain
# literal $ characters that `source` would try to expand as shell variables.
while IFS='=' read -r key value; do
  export "$key=$value"
done < <(command grep -E '^[A-Za-z_][A-Za-z0-9_]*=' .env)

: "${FTP_HOST:?Set FTP_HOST in .env}"
: "${FTP_USERNAME:?Set FTP_USERNAME in .env}"
: "${FTP_PASSWORD:?Set FTP_PASSWORD in .env}"
FTP_REMOTE_PATH="${FTP_REMOTE_PATH:-/}"
FTP_PORT="${FTP_PORT:-21}"
FTP_SSL="${FTP_SSL:-yes}"

if [ -n "$(git status --porcelain)" ]; then
  echo "Warning: working tree has uncommitted changes - deploying the last commit only ($(git rev-parse --short HEAD))." >&2
fi

WORKDIR="$(mktemp -d)"
trap 'rm -rf "$WORKDIR"' EXIT
git archive HEAD | tar -x -C "$WORKDIR"

echo "Deploying $(git rev-parse --short HEAD) to $FTP_HOST:$FTP_REMOTE_PATH ..."
lftp <<LFTP_EOF
set ftp:ssl-force $([ "$FTP_SSL" = "yes" ] && echo true || echo false)
set ftp:ssl-protect-data $([ "$FTP_SSL" = "yes" ] && echo true || echo false)
set net:max-retries 2
open -u "$FTP_USERNAME","$FTP_PASSWORD" -p "$FTP_PORT" "$FTP_HOST"
mirror --reverse --delete --verbose \
  --exclude-glob content/ \
  --exclude-glob uploads/ \
  --exclude-glob .env \
  --exclude-glob .git/ \
  --exclude-glob scripts/ \
  "$WORKDIR/" "$FTP_REMOTE_PATH"
bye
LFTP_EOF

echo "Deploy complete."
