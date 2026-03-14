#!/usr/bin/env bash
# Build and push Docker image to Docker Hub (and optionally ghcr.io).
# Uses DOCKERHUB_TOKEN from .env if set; otherwise uses existing docker login.

set -e

repo_root="$(git rev-parse --show-toplevel 2>/dev/null)" || repo_root="."
[ -f "$repo_root/.env" ] && set -a && . "$repo_root/.env" && set +a

IMAGE="${DOCKER_IMAGE:-darknetz/php-webhooks}"
DOCKERHUB_USER="${DOCKERHUB_USERNAME:-darknetz}"
GHCR_IMAGE="${GHCR_IMAGE:-}"   # set to ghcr.io/owner/repo to also push there

if [ -n "$DOCKERHUB_TOKEN" ]; then
  echo "Logging in to Docker Hub ..."
  echo "$DOCKERHUB_TOKEN" | docker login -u "$DOCKERHUB_USER" --password-stdin
fi

# Version and repo URL for image (footer link when no .git in container)
GIT_COMMIT="$(git rev-parse --short HEAD 2>/dev/null)" || GIT_COMMIT=unknown
GIT_TAG="$(git describe --tags --exact-match 2>/dev/null)" || true
GIT_REPO_URL=""
remote="$(git config --get remote.origin.url 2>/dev/null)" || true
if [ -n "$remote" ]; then
  if [[ "$remote" =~ ^git@([^:]+):(.+?)\.git$ ]]; then
    GIT_REPO_URL="https://${BASH_REMATCH[1]}/${BASH_REMATCH[2]}"
  elif [[ "$remote" =~ ^https?://. ]]; then
    GIT_REPO_URL="${remote%.git}"
  fi
fi

echo "Building $IMAGE ..."
docker build -t "$IMAGE:latest" \
  --build-arg "GIT_COMMIT=$GIT_COMMIT" \
  --build-arg "GIT_TAG=$GIT_TAG" \
  --build-arg "GIT_REPO_URL=$GIT_REPO_URL" \
  .

# Optional: tag image with git tag if we're on a tagged commit
if [ -n "$GIT_TAG" ]; then
  docker tag "$IMAGE:latest" "$IMAGE:$GIT_TAG"
  to_push="$IMAGE:latest $IMAGE:$GIT_TAG"
else
  to_push="$IMAGE:latest"
fi

echo "Pushing $IMAGE ..."
for t in $to_push; do
  docker push "$t"
done

if [ -n "$GHCR_IMAGE" ]; then
  echo "Pushing $GHCR_IMAGE ..."
  docker tag "$IMAGE:latest" "$GHCR_IMAGE:latest"
  docker push "$GHCR_IMAGE:latest"
fi

echo "Done."
