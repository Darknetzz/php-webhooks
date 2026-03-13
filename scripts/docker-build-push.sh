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

echo "Building $IMAGE ..."
docker build -t "$IMAGE:latest" .

# Optional: tag with git version
if tag=$(git describe --tags --exact-match 2>/dev/null); then
  docker tag "$IMAGE:latest" "$IMAGE:$tag"
  to_push="$IMAGE:latest $IMAGE:$tag"
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
