# Heroku PHP Extensions

Pre-built PHP extensions for Heroku.

- [igbinary](https://pecl.php.net/package/igbinary)
- [PhpRedis](https://pecl.php.net/package/redis) _(with lzf, lz4, zstd, igbinary support)_
- [Relay](https://relaycache.com) _(coming soon)_

The supported PHP versions are `7.3`, `7.4` and `8.0` on `heroku-18` and `heroku-20`.

- [S3 bucket browser](https://s3.us-east-1.amazonaws.com/heroku-php-extensions/index.html)
- [Heroku demo app](https://php-extensions.herokuapp.com)

## Usage

Find out your app’s stack by running `heroku info`, then add the repository according to the stack version of your application:

```bash
# heroku-18
heroku config:set HEROKU_PHP_PLATFORM_REPOSITORIES="https://heroku-php-extensions.s3.amazonaws.com/dist-heroku-18-stable/"

# heroku-20
heroku config:set HEROKU_PHP_PLATFORM_REPOSITORIES="https://heroku-php-extensions.s3.amazonaws.com/dist-heroku-20-stable/"
```

Then add any of the extensions to `composer.json`:

```bash
composer require ext-igbinary:*
composer require ext-redis:*
# composer require ext-relay:*
```

## Development

Before continuing, read the [the build readme](https://github.com/heroku/heroku-buildpack-php/blob/main/support/build/README.md).

### Set up

```bash
# Install Composer dependencies
composer install

# Copy python requirements
cp vendor/heroku/heroku-buildpack-php/requirements.txt .

# Create environment file
cp .env.example .env
```

You must set the all variables in the `.env` file.

### Dockerfile

```
mkdir build
cat vendor/heroku/heroku-buildpack-php/support/build/_docker/heroku-20.Dockerfile > build/heroku-20.Dockerfile
cat docker/heroku-20.Dockerfile >> build/heroku-20.Dockerfile
```

### Build

```bash
# Docker build
docker build --pull --tag heroku-20 --file build/heroku-20.Dockerfile .

# Build libraries
docker run --rm -ti --env-file=.env heroku-20 bob build --overwrite libraries/liblzf-3.6
docker run --rm -ti --env-file=.env heroku-20 bob build --overwrite libraries/lz4-1.9.3
docker run --rm -ti --env-file=.env heroku-20 bob build --overwrite libraries/zstd-1.4.9

# Build igbinary
docker run --rm -ti --env-file=.env heroku-20 bob build extensions/no-debug-non-zts-20200930/igbinary-3.2.1

# Build phpredis
docker run --rm -ti --env-file=.env heroku-20 bob build extensions/no-debug-non-zts-20200930/redis-5.3.3
```
