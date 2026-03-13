#!/usr/bin/env bash
# Build and push Docker image to Docker Hub (and optionally ghcr.io).
# Run manually or from a git pre-push hook. Requires: docker login first.

set -e

IMAGE="${DOCKER_IMAGE:-darknetz/php-webhooks}"
GHCR_IMAGE="${GHCR_IMAGE:-}"   # set to ghcr.io/owner/repo to also push there

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
