#!/bin/bash
set -e

# Auto-create cache directory
mkdir -p .composer-cache

# Run composer with proper mounts and permissions
docker run --rm \
  -u "$(id -u):$(id -g)" \
  -v "$(pwd):/var/www/html" \
  -v "$(pwd)/.composer-cache:/tmp/.composer-cache" \
  -e COMPOSER_HOME=/tmp/.composer-cache \
  -e COMPOSER_CACHE_DIR=/tmp/.composer-cache/cache \
  -e COMPOSER_NO_INTERACTION=1 \
  -w /var/www/html \
  laravelsail/php84-composer:latest \
  composer "$@"