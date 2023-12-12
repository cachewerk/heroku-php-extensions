# Heroku PHP Extensions

Pre-built PHP extensions for Heroku that are not included or fully supported by the official [PHP buildpack](https://github.com/heroku/heroku-buildpack-php).

- [Relay](https://relaycache.com)
- [PhpRedis](https://pecl.php.net/package/redis) (with _igbinary_, _lzf_, _lz4_ and _zstd_ support)
- [Swoole](https://pecl.php.net/package/swoole)
- [OpenSwoole](https://pecl.php.net/package/openswoole)
- [MessagePack](https://pecl.php.net/package/msgpack)
- [igbinary](https://pecl.php.net/package/igbinary)

The supported PHP versions are `7.3` to `8.2` on the `heroku-20` and `heroku-22` stacks.

Checkout the [demo app](https://php-extensions.herokuapp.com), or [browse the S3 bucket](https://s3.us-east-1.amazonaws.com/heroku-php-extensions/index.html).

## Usage

Add the platform repository to your Heroku app:

```bash
heroku config:set HEROKU_PHP_PLATFORM_REPOSITORIES="https://relay.so/heroku/"
```

If you prefer using the AWS S3 repositories, add the corresponding repository to your Heroku app:

```bash
# heroku-20
heroku config:set HEROKU_PHP_PLATFORM_REPOSITORIES="https://heroku-php-extensions.s3.amazonaws.com/dist-heroku-20-stable/"

# heroku-22
heroku config:set HEROKU_PHP_PLATFORM_REPOSITORIES="https://heroku-php-extensions.s3.amazonaws.com/dist-heroku-22-stable/"
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
# Install Composer dependencies
composer install

# Copy Python requirements
cp vendor/heroku/heroku-buildpack-php/requirements.txt .

# Create environment file
cp .env.example .env
```

Be sure to set all variables in your newly created `.env` file.

### Dockerfile

Create a custom Dockerfile for `heroku-22`.

```
cat vendor/heroku/heroku-buildpack-php/support/build/_docker/heroku-22.Dockerfile > docker/build/heroku-22.Dockerfile
cat docker/heroku-22.Dockerfile >> docker/build/heroku-22.Dockerfile
```

### Build

```bash
# Docker build
docker build --pull --tag heroku-22 --file docker/build/heroku-22.Dockerfile .

# Build libraries
docker run --rm -ti --env-file=.env heroku-22 bob build --overwrite libraries/liblzf-3.6
docker run --rm -ti --env-file=.env heroku-22 bob build --overwrite libraries/lz4-1.9.3
docker run --rm -ti --env-file=.env heroku-22 bob build --overwrite libraries/zstd-1.4.9

# Build igbinary
docker run --rm -ti --env-file=.env heroku-22 bob build extensions/no-debug-non-zts-20200930/igbinary-3.2.15

# Build msgpack
docker run --rm -ti --env-file=.env heroku-22 bob build extensions/no-debug-non-zts-20200930/msgpack-2.2.0

# Build phpredis
docker run --rm -ti --env-file=.env heroku-22 bob build extensions/no-debug-non-zts-20200930/redis-6.0.2

# Build relay
docker run --rm -ti --env-file=.env heroku-22 bob build extensions/no-debug-non-zts-20200930/relay-0.6.8

# Build swoole
docker run --rm -ti --env-file=.env heroku-22 bob build extensions/no-debug-non-zts-20200930/swoole-4.8.13

# Build openswoole
docker run --rm -ti --env-file=.env heroku-22 bob build extensions/no-debug-non-zts-20200930/openswoole-4.12.1
```
