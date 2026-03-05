#!/bin/bash

SCRIPT_DIR=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" &> /dev/null && pwd)

HEROKUSTACK="$1" # e.g. "heroku-24"

docker build --pull --tag "$HEROKUSTACK" --file "docker/build/${HEROKUSTACK}.Dockerfile" ${SCRIPT_DIR}/..