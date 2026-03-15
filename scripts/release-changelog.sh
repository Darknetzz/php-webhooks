#!/usr/bin/env bash
# Update CHANGELOG.md: replace "## [Unreleased]" with "## [X.Y.Z] - date" and add new "## [Unreleased]".
# Usage: scripts/release-changelog.sh <version> [date]
#   version: semver (e.g. 1.0.0)
#   date: YYYY-MM-DD (default: today UTC)

set -e

VERSION="${1:?Usage: $0 <version> [date]}"
DATE="${2:-$(date -u +%Y-%m-%d)}"

# Validate semver (allow X.Y.Z)
if ! [[ "$VERSION" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
  echo "Error: version must be semver (e.g. 1.0.0)" >&2
  exit 1
fi

REPO_ROOT="$(git rev-parse --show-toplevel 2>/dev/null)" || REPO_ROOT="."
CHANGELOG="$REPO_ROOT/CHANGELOG.md"

if [[ ! -f "$CHANGELOG" ]]; then
  echo "Error: CHANGELOG.md not found at $CHANGELOG" >&2
  exit 1
fi

# Replace first "## [Unreleased]" block: the heading and everything until the next "## " or EOF
# becomes "## [X.Y.Z] - date" + that content + "## [Unreleased]\n\n"
awk -v version="$VERSION" -v date="$DATE" '
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
  exit 1
fi

mv "$CHANGELOG.tmp" "$CHANGELOG"
echo "Updated CHANGELOG.md: ## [Unreleased] -> ## [$VERSION] - $DATE"
