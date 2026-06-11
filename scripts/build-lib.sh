#!/bin/bash

set -euo pipefail

SCRIPT_DIR=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" &> /dev/null && pwd)

HEROKUSTACK=$1 # e.g. "heroku-24"
LIBNAME=$2 # e.g. "liblzf"
LIBVERSION=$3 # e.g. "3.6"
WITH_DEPLOY=${4:-"false"} # e.g. "true" or "false"

if [ "$LIBVERSION" == "" ]; then
    echo "No version was provided for $LIBNAME, skipping build."
    exit 0
fi

echo "--- Building $LIBNAME $LIBVERSION ---"

# Create the library build script
cat <<EOF > "${SCRIPT_DIR}/../${LIBNAME}-${LIBVERSION}"
#!/bin/bash
# Build Path: /app/.heroku/php/
WORKSPACE_DIR=/workspace/libraries
source \$(dirname \$0)/libraries/$LIBNAME
EOF

# Set the build command based on whether we want to deploy or not
COMMAND="bob build"

if [[ -f ".env" ]]; then
    ENV_FILE=".env"
else
    ENV_FILE="$SCRIPT_DIR/../vendor/heroku/heroku-buildpack-php/support/build/docker/env.default"
fi
OVERWRITE_FLAG=""
if [ "$WITH_DEPLOY" = "true" ]; then
    COMMAND="deploy.sh"
    OVERWRITE_FLAG="--overwrite"
fi

set -x

docker run --rm \
-v ${SCRIPT_DIR}/../:/workspace \
-w /workspace \
--env-file="$ENV_FILE" \
$HEROKUSTACK \
${COMMAND} ${OVERWRITE_FLAG} ${LIBNAME}-${LIBVERSION}

set +x
