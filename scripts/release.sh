#!/usr/bin/env bash
# Release helper: update CHANGELOG, optionally create and push tag.
#
# Non-interactive (for CI / hooks): scripts/release.sh <version> [date]
#   Updates CHANGELOG only. Use in GitHub Actions or scripts.
#
# Interactive: scripts/release.sh
#   Shows last release, asks for next version, shows unreleased changelog,
#   prompts for confirmation, then updates CHANGELOG, commits, tags, and pushes.
#
# Version: semver (e.g. 1.0.0). Date: YYYY-MM-DD (default: today UTC).

set -e

REPO_ROOT="$(git rev-parse --show-toplevel 2>/dev/null)" || REPO_ROOT="."
CHANGELOG="$REPO_ROOT/CHANGELOG.md"

# --- Shared: semver check ---
validate_semver() {
  local v="$1"
  if ! [[ "$v" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    echo "Error: version must be semver (e.g. 1.0.0), got: $v" >&2
    return 1
  fi
  return 0
}

# --- Shared: update CHANGELOG (Unreleased -> version) ---
update_changelog() {
  local version="$1"
  local date="$2"
  if [[ ! -f "$CHANGELOG" ]]; then
    echo "Error: CHANGELOG.md not found at $CHANGELOG" >&2
    return 1
  fi
  awk -v version="$version" -v date="$date" '
    BEGIN { done = 0; in_unreleased = 0; unreleased_content = "" }
    /^## \[Unreleased\]/ && !done {
      in_unreleased = 1
      unreleased_content = ""
      next
    }
    in_unreleased {
      if (/^## /) {
        in_unreleased = 0
        done = 1
        printf "## [%s] - %s\n\n", version, date
        if (unreleased_content != "") printf "%s", unreleased_content
        printf "## [Unreleased]\n\n"
        print
        next
      }
      unreleased_content = unreleased_content $0 "\n"
      next
    }
    { print }
    END {
      if (in_unreleased) {
        printf "## [%s] - %s\n\n", version, date
        if (unreleased_content != "") printf "%s", unreleased_content
        printf "## [Unreleased]\n\n"
      }
    }
  ' "$CHANGELOG" > "$CHANGELOG.tmp"
  if ! grep -q '^## \[' "$CHANGELOG.tmp"; then
    echo "Error: CHANGELOG.md had no ## [Unreleased] or structure changed" >&2
    rm -f "$CHANGELOG.tmp"
    return 1
  fi
  mv "$CHANGELOG.tmp" "$CHANGELOG"
  echo "Updated CHANGELOG.md: ## [Unreleased] -> ## [$version] - $date"
}

# --- Interactive: get last release version from CHANGELOG ---
get_last_version_from_changelog() {
  awk '
    /^## \[Unreleased\]/ { next }
    /^## \[([0-9]+\.[0-9]+\.[0-9]+)\]/ { print substr($2, 2, length($2)-2); exit }
  ' "$CHANGELOG"
}

# --- Interactive: get unreleased changelog body (for summary) ---
get_unreleased_content() {
  awk '
    /^## \[Unreleased\]/ { in_unreleased = 1; next }
    in_unreleased {
      if (/^## /) { exit }
      print
    }
  ' "$CHANGELOG"
}

# --- Non-interactive entry: version (and optional date) passed as args ---
if [[ -n "${1:-}" ]]; then
  VERSION="$1"
  DATE="${2:-$(date -u +%Y-%m-%d)}"
  validate_semver "$VERSION" || exit 1
  update_changelog "$VERSION" "$DATE"
  exit 0
fi

# --- Interactive: require TTY so we don't block CI ---
if [[ ! -t 0 ]] || [[ ! -t 1 ]]; then
  echo "Error: interactive mode requires a TTY. Use: $0 <version> [date]" >&2
  exit 1
fi

if [[ ! -f "$CHANGELOG" ]]; then
  echo "Error: CHANGELOG.md not found at $CHANGELOG" >&2
  exit 1
fi

LAST="$(get_last_version_from_changelog)"
if [[ -z "$LAST" ]]; then
  # Fallback to latest git tag
  LAST_TAG="$(git tag -l 'v*' --sort=-v:refname 2>/dev/null | head -1)"
  if [[ -n "$LAST_TAG" ]]; then
    LAST="${LAST_TAG#v}"
  else
    LAST="(none)"
  fi
fi

echo "Last release: ${LAST}"
echo ""

read -r -p "Next version (e.g. 1.0.1): " VERSION
VERSION="${VERSION:-}"
if [[ -z "$VERSION" ]]; then
  echo "Aborted (no version given)." >&2
  exit 1
fi
validate_semver "$VERSION" || exit 1

if git rev-parse "v$VERSION" &>/dev/null; then
  echo "Error: tag v$VERSION already exists." >&2
  exit 1
fi

UNRELEASED="$(get_unreleased_content)"
echo ""
echo "--- Unreleased changelog ---"
if [[ -n "$UNRELEASED" ]]; then
  echo "$UNRELEASED"
else
  echo "(no entries under ## [Unreleased])"
fi
echo "---"
echo ""

read -r -p "Create release v$VERSION, update CHANGELOG, commit, tag and push? [y/N] " CONFIRM
if [[ ! "$CONFIRM" =~ ^[yY] ]]; then
  echo "Aborted."
  exit 0
fi

DATE="$(date -u +%Y-%m-%d)"
update_changelog "$VERSION" "$DATE"

cd "$REPO_ROOT"
git add CHANGELOG.md
git diff --staged --quiet && { echo "No changelog diff (already up to date?)." >&2; exit 1; }
git commit -m "Release v$VERSION"
git tag "v$VERSION"
echo "Committed and tagged v$VERSION. Pushing branch and tag..."
git push origin HEAD
git push origin "v$VERSION"
echo "Done. Release v$VERSION created and pushed."