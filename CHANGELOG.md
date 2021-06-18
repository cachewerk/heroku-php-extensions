# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.2] - 2021-06-18
- Updated Relay to v0.1.1

## [1.2.1] - 2021-05-26
- Added Relay configuration file
- Enabled msgpack for PhpRedis
- Fixed `require` blocks for Relay and PhpRedis

## [1.2.0] - 2021-05-26
- Added [Relay](https://relaycache.com) v0.1.0
- Added [MessagePack](https://github.com/msgpack/msgpack-php) v2.1.2
- Updated Swoole to v4.6.7
- Updated PhpRedis to v5.3.4
- Updated igbinary to v3.2.2

## [1.1.1] - 2021-05-05
- Updated Swoole to v4.6.4
- Fixed demo app output for Swoole

## [1.1.0] - 2021-03-26
- Added [Swoole](https://github.com/swoole/swoole-src) v4.6.4
- Overhauled demo app
- Only build on push to `main` branch
- Make repos after successful build workflow
- Sync repos when a release is published
- Run builds for pull requests

## [1.0.0] - 2021-03-10
### Added
- Initial release

[Unreleased]: https://github.com/cachewerk/heroku-php-extensions/compare/v1.2.1...HEAD
[1.2.0]: https://github.com/cachewerk/heroku-php-extensions/compare/v1.2.0...v1.2.1
[1.2.0]: https://github.com/cachewerk/heroku-php-extensions/compare/v1.1.1...v1.2.0
[1.1.1]: https://github.com/cachewerk/heroku-php-extensions/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/cachewerk/heroku-php-extensions/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/cachewerk/heroku-php-extensions/releases/tag/v1.0.0
