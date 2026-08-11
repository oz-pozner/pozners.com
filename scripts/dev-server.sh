#!/bin/bash
# Runs the local PHP dev server and restarts it automatically if it crashes.
# Listens on all network interfaces by default so you can open it from a
# phone/tablet on the same Wi-Fi for mobile testing, not just this machine.
# Usage: scripts/dev-server.sh [host] [port]
set -uo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ROUTER="$ROOT_DIR/scripts/dev-router.php"
HOST="${1:-0.0.0.0}"
PORT="${2:-8000}"
LOG_FILE="$ROOT_DIR/scripts/dev-server.log"

if ! command -v php >/dev/null 2>&1; then
  echo "PHP is required (brew install php)." >&2
  exit 1
fi

echo "Serving $ROOT_DIR"
echo "  Local:   http://localhost:$PORT"
if [ "$HOST" = "0.0.0.0" ]; then
  LAN_IP="$(ipconfig getifaddr en0 2>/dev/null || ipconfig getifaddr en1 2>/dev/null || hostname -I 2>/dev/null | awk '{print $1}')"
  if [ -n "$LAN_IP" ]; then
    echo "  Network: http://$LAN_IP:$PORT  (for phones/tablets on the same Wi-Fi)"
  fi
fi
echo "(Ctrl+C to stop; auto-restarts on crash)"
echo "Logs: $LOG_FILE"

trap 'echo; echo "Stopped."; exit 0' INT TERM

while true; do
  php -S "$HOST:$PORT" -t "$ROOT_DIR" "$ROUTER" >>"$LOG_FILE" 2>&1
  exit_code=$?
  echo "$(date '+%Y-%m-%d %H:%M:%S') dev server exited (code $exit_code) - restarting in 2s..." | tee -a "$LOG_FILE"
  sleep 2
done
