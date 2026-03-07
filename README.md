# Heroku PHP Extensions

Pre-built PHP extensions for Heroku that are not included or fully supported by the official [PHP buildpack](https://github.com/heroku/heroku-buildpack-php).

- [Relay](https://relaycache.com)
- [PhpRedis](https://pecl.php.net/package/redis) (with _igbinary_, _lzf_, _lz4_ and _zstd_ support)
- [Swoole](https://pecl.php.net/package/swoole)
- [OpenSwoole](https://pecl.php.net/package/openswoole)
- [MessagePack](https://pecl.php.net/package/msgpack)
- [igbinary](https://pecl.php.net/package/igbinary)

The supported PHP versions are `8.1` to `8.5` on the `heroku-22` and `heroku-24` stacks.

Checkout the [demo app](https://php-extensions.herokuapp.com), or [browse the S3 bucket](https://s3.us-east-1.amazonaws.com/heroku-php-extensions/index.html).

## Usage

Add the platform repository to your Heroku app:

```bash
heroku config:set HEROKU_PHP_PLATFORM_REPOSITORIES="https://relay.so/heroku/"
# heroku config:set HEROKU_PHP_PLATFORM_REPOSITORIES="https://relay.so/heroku/arm64"
```

If you prefer using the AWS S3 repositories, add the corresponding repository to your Heroku app:

```bash
# heroku-22
heroku config:set HEROKU_PHP_PLATFORM_REPOSITORIES="https://heroku-php-extensions.s3.amazonaws.com/dist-heroku-22-stable/"

# heroku-24 (amd64)
heroku config:set HEROKU_PHP_PLATFORM_REPOSITORIES="https://heroku-php-extensions.s3.amazonaws.com/dist-heroku-24-amd64-stable/"

# heroku-24 (arm64)
heroku config:set HEROKU_PHP_PLATFORM_REPOSITORIES="https://heroku-php-extensions.s3.amazonaws.com/dist-heroku-24-arm64-stable/"
```

Next, add any of the extensions to `composer.json` as you usually would:

```bash
composer require "ext-relay:*"
composer require "ext-redis:*"
composer require "ext-swoole:*"
composer require "ext-openswoole:*"
composer require "ext-msgpack:*"
composer require "ext-igbinary:*"
```

## Troubleshooting

See [TROUBLESHOOTING.md](TROUBLESHOOTING.md).

## Contributing

Pull requests for additional Heroku stacks, PHP versions, additional extension versions and new extension are welcome.

## Development

Before continuing, read and understand the [official build instructions](https://github.com/heroku/heroku-buildpack-php/blob/main/support/build/README.md).

### Set up

```bash
# ./scripts/setup-local.sh

# Install Composer dependencies
composer install

# Copy Python requirements
cp vendor/heroku/heroku-buildpack-php/requirements.txt .

# Create environment file
cp .env.example .env
```

Be sure to set all variables in your newly created `.env` file.

### Dockerfile

Create a custom Dockerfile for `heroku-24`.

```
./scripts/create-dockerfile.sh heroku-24
```

### Build

```bash
# Docker build
./scripts/build-dockerfile.sh heroku-24

# Build libraries
./scripts/build-lib.sh heroku-24 liblzf 3.6
./scripts/build-lib.sh heroku-24 lz4 1.9.3
./scripts/build-lib.sh heroku-24 zstd 1.4.9

# Build igbinary
./scripts/build-extension.sh heroku-24 8.4 20240924 igbinary 3.2.16 "php-8.4.*" "dist-heroku-24-amd64-stable/"

# Build msgpack
./scripts/build-extension.sh heroku-24 8.4 20240924 msgpack 2.2.0 "php-8.4.*" "dist-heroku-24-amd64-stable/"

# Build phpredis (has extra dependencies, libraries need to be "deployed" already)
./scripts/build-extension.sh heroku-24 8.4 20240924 redis 6.3.0 "php-8.4.*,libraries/liblzf-*,libraries/lz4-*,libraries/zstd-*,extensions/no-debug-non-zts-20240924/igbinary-*,extensions/no-debug-non-zts-20240924/msgpack-*" "dist-heroku-24-amd64-stable/"

# Build relay (has extra dependencies, libraries need to be "deployed" already)
./scripts/build-extension.sh heroku-24 8.4 20240924 relay 0.20.0 "php-8.4.*,libraries/liblzf-*,libraries/lz4-*,libraries/zstd-*,extensions/no-debug-non-zts-20240924/igbinary-*,extensions/no-debug-non-zts-20240924/msgpack-*" "dist-heroku-24-amd64-stable/"

# Build swoole
./scripts/build-extension.sh heroku-24 8.4 20240924 swoole 6.1.7 "php-8.4.*" "dist-heroku-24-amd64-stable/"

# Build openswoole
./scripts/build-extension.sh heroku-24 8.4 20240924 openswoole 25.2.0 "php-8.4.*" "dist-heroku-24-amd64-stable/"
```
### Versions

Versions can be added and upgraded in [build.yml](./.github/workflows/build.yml) and will automatically be built and deployed by GitHub actions.
