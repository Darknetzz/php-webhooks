#!/usr/bin/env bash
# Backward-compat wrapper: delegates to release.sh.
# Prefer: scripts/release.sh (interactive) or scripts/release.sh <version> [date]
exec "$(dirname "$0")/release.sh" "$@"
