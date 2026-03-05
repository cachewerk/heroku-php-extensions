#!/bin/bash

set -euo pipefail

SCRIPT_DIR=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" &> /dev/null && pwd)

HEROKUSTACK="$1" # e.g. "heroku-22"
PHP_VERSION="$2" # e.g. "8.4"
PHP_APIVERSION="$3" # e.g. "20240924"
EXTENSION_NAME="$4" # e.g. "igbinary"
EXTENSION_VERSION="$5" # e.g. "3.2.15"
EXTENSION_DEPS="$6" # e.g. "php-$PHP_VERSION.*"
UPSTREAM_S3_PREFIX="$7" # upstream (lang-php) S3 prefix, e.g. "dist-heroku-24-amd64-stable/"
WITH_DEPLOY=${8:-"false"} # e.g. "true" or "false"

if [ "$EXTENSION_VERSION" == "" ]; then
    echo "No version was provided for $EXTENSION_NAME, skipping build."
    exit 0
fi

echo "--- Building $EXTENSION_NAME $EXTENSION_VERSION ---"

EXTENSION_FOLDER="$SCRIPT_DIR/../extensions/no-debug-non-zts-${PHP_APIVERSION}/"
EXTENSION_FILE="$EXTENSION_FOLDER${EXTENSION_NAME}-${EXTENSION_VERSION}"
# Create the extension build script
mkdir -p $EXTENSION_FOLDER
cat <<EOF > "$EXTENSION_FILE"
#!/bin/bash
# Build Path: /app/.heroku/php/
# Build Deps: $EXTENSION_DEPS
source \$(dirname \$0)/../$EXTENSION_NAME
EOF

# Set the build command based on whether we want to deploy or not
COMMAND="bob build"
OVERWRITE_FLAG=""
ENV_FILE=".env"
if [ "$WITH_DEPLOY" = "true" ]; then
    COMMAND="deploy.sh"
    ENV_FILE="$SCRIPT_DIR/../vendor/heroku/heroku-buildpack-php/support/build/_docker/env.default"
    OVERWRITE_FLAG="--overwrite"
fi

set -x

docker run --rm \
-v ${SCRIPT_DIR}/../:/workspace \
-w /workspace \
--env UPSTREAM_S3_BUCKET=lang-php \
--env UPSTREAM_S3_PREFIX=${UPSTREAM_S3_PREFIX} \
--env-file="$ENV_FILE" \
${HEROKUSTACK} ${COMMAND} \
${OVERWRITE_FLAG} extensions/no-debug-non-zts-${PHP_APIVERSION}/${EXTENSION_NAME}-${EXTENSION_VERSION}

set +x
