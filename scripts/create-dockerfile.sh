#!/bin/bash

HEROKUSTACK="$1"
SCRIPT_DIR=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" &> /dev/null && pwd)

mkdir -p ${SCRIPT_DIR}/../docker/build/
cat ${SCRIPT_DIR}/../vendor/heroku/heroku-buildpack-php/support/build/_docker/${HEROKUSTACK}.Dockerfile > ${SCRIPT_DIR}/../docker/build/${HEROKUSTACK}.Dockerfile

cat <<EOF >> "${SCRIPT_DIR}/../docker/build/${HEROKUSTACK}.Dockerfile"
ENV WORKSPACE_DIR=/workspace
ENV PATH=/app/vendor/heroku/heroku-buildpack-php/support/build/_util:\$PATH
EOF