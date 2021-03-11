# Heroku PHP Extensions

Pre-built PHP extensions for Heroku that are not included or fully supported by the official [PHP buildpack](https://github.com/heroku/heroku-buildpack-php).

- [igbinary](https://pecl.php.net/package/igbinary)
- [PhpRedis](https://pecl.php.net/package/redis) (with _lzf, lz4, zstd and igbinary_ support)
- [Relay](https://relaycache.com) _(coming soon)_

The supported PHP versions are `7.3`, `7.4` and `8.0` on the `heroku-18` and `heroku-20` stacks.

Checkout the [demo app](https://php-extensions.herokuapp.com), or [browse the S3 bucket](https://s3.us-east-1.amazonaws.com/heroku-php-extensions/index.html).

## Usage

First, find out your app’s stack by running `heroku info`, then add the corresponding repository to your application:

```bash
# heroku-18
heroku config:set HEROKU_PHP_PLATFORM_REPOSITORIES="https://heroku-php-extensions.s3.amazonaws.com/dist-heroku-18-stable/"

# heroku-20
heroku config:set HEROKU_PHP_PLATFORM_REPOSITORIES="https://heroku-php-extensions.s3.amazonaws.com/dist-heroku-20-stable/"
```

Next, add any of the extensions to `composer.json` as you usually would:

```bash
composer require "ext-igbinary:*"
composer require "ext-redis:*"
# composer require "ext-relay:*"
```

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

Create a custom Dockerfile for `heroku-20`.

```
cat vendor/heroku/heroku-buildpack-php/support/build/_docker/heroku-20.Dockerfile > docker/build/heroku-20.Dockerfile
cat docker/heroku-20.Dockerfile >> docker/build/heroku-20.Dockerfile
```

### Build

```bash
# Docker build
docker build --pull --tag heroku-20 --file docker/build/heroku-20.Dockerfile .

# Build libraries
docker run --rm -ti --env-file=.env heroku-20 bob build --overwrite libraries/liblzf-3.6
docker run --rm -ti --env-file=.env heroku-20 bob build --overwrite libraries/lz4-1.9.3
docker run --rm -ti --env-file=.env heroku-20 bob build --overwrite libraries/zstd-1.4.9

# Build igbinary
docker run --rm -ti --env-file=.env heroku-20 bob build extensions/no-debug-non-zts-20200930/igbinary-3.2.1

# Build phpredis
docker run --rm -ti --env-file=.env heroku-20 bob build extensions/no-debug-non-zts-20200930/redis-5.3.3
```
