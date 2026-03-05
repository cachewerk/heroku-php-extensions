#!/bin/bash

SCRIPT_DIR=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" &> /dev/null && pwd)

# Install Composer dependencies
composer install --working-dir=${SCRIPT_DIR}/.. --prefer-dist --no-progress --no-suggest --ignore-platform-reqs

# Copy Python requirements
cp ${SCRIPT_DIR}/../vendor/heroku/heroku-buildpack-php/requirements.txt .

# Create environment file
cp ${SCRIPT_DIR}/../.env.example ${SCRIPT_DIR}/../.env
