#!/bin/bash

set -euo pipefail

SCRIPT_DIR=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" &> /dev/null && pwd)

HEROKUSTACK="$1" # e.g. "heroku-22"
PHP_VERSION="$2" # e.g. "8.4"
EXTENSION_NAME="$3" # e.g. "igbinary"
EXTENSION_VERSION="$4" # e.g. "3.2.15"
EXTENSION_DEPS="$5" # e.g. "php-$PHP_VERSION.*"
UPSTREAM_S3_PREFIX="$6" # upstream (lang-php) S3 prefix, e.g. "dist-heroku-24-amd64-stable/"
WITH_DEPLOY=${7:-"false"} # e.g. "true" or "false"

if [ "$EXTENSION_VERSION" == "" ]; then
    echo "No version was provided for $EXTENSION_NAME, skipping build."
    exit 0
fi

echo "--- Building $EXTENSION_NAME $EXTENSION_VERSION ---"

EXTENSION_FOLDER="$SCRIPT_DIR/.."
EXTENSION_FILE="$EXTENSION_FOLDER/ext-${EXTENSION_NAME}-${EXTENSION_VERSION}_php-${PHP_VERSION}"

# Create the extension build script
cat <<EOF > "$EXTENSION_FILE"
#!/bin/bash
# Build Path: /app/.heroku/php/
# Build Deps: $EXTENSION_DEPS
WORKSPACE_DIR=/workspace
source \$(dirname \$0)/extensions/$EXTENSION_NAME
EOF

# Set the build command based on whether we want to deploy or not
COMMAND="bob build"
OVERWRITE_FLAG=""
if [[ -f ".env" ]]; then
    ENV_FILE=".env"
else
    ENV_FILE="$SCRIPT_DIR/../vendor/heroku/heroku-buildpack-php/support/build/docker/env.default"
fi
if [ "$WITH_DEPLOY" = "true" ]; then
    COMMAND="deploy.sh"
    OVERWRITE_FLAG="--overwrite"
fi

set -x

docker run --rm \
-v ${SCRIPT_DIR}/../:/workspace \
-w /workspace \
--env UPSTREAM_S3_BUCKET=heroku-buildpack-php \
--env UPSTREAM_S3_PREFIX=${UPSTREAM_S3_PREFIX} \
--env-file="$ENV_FILE" \
${HEROKUSTACK} ${COMMAND} \
${OVERWRITE_FLAG} ext-${EXTENSION_NAME}-${EXTENSION_VERSION}_php-${PHP_VERSION}

set +x
