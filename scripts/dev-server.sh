#!/bin/bash
# Runs the local PHP dev server and restarts it automatically if it crashes.
# Usage: scripts/dev-server.sh [host] [port]
set -uo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ROUTER="$ROOT_DIR/scripts/dev-router.php"
HOST="${1:-localhost}"
PORT="${2:-8000}"
LOG_FILE="$ROOT_DIR/scripts/dev-server.log"

if ! command -v php >/dev/null 2>&1; then
  echo "PHP is required (brew install php)." >&2
  exit 1
fi

echo "Serving $ROOT_DIR at http://$HOST:$PORT (Ctrl+C to stop; auto-restarts on crash)"
echo "Logs: $LOG_FILE"

trap 'echo; echo "Stopped."; exit 0' INT TERM

while true; do
  php -S "$HOST:$PORT" -t "$ROOT_DIR" "$ROUTER" >>"$LOG_FILE" 2>&1
  exit_code=$?
  echo "$(date '+%Y-%m-%d %H:%M:%S') dev server exited (code $exit_code) - restarting in 2s..." | tee -a "$LOG_FILE"
  sleep 2
done
