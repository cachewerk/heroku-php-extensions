<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$redisURL = parse_url(getenv('REDIS_URL'));

$redis = new Redis;
$redis->connect($redisURL['host'], $redisURL['port'], 1.0, null, 250, 1.0);
$redis->auth($redisURL['pass']);

function to_json($value) {
    return json_encode(
        $value,
        JSON_THROW_ON_ERROR | JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE
    );
}

echo '<pre>';

echo 'php: ' . phpversion() . PHP_EOL;
echo 'platform repo: ' . getenv('HEROKU_PHP_PLATFORM_REPOSITORIES') . PHP_EOL;

echo PHP_EOL;

echo 'igbinary extension loaded: ' . (int) extension_loaded('igbinary') . PHP_EOL;
echo 'phpredis extension loaded: ' . (int) extension_loaded('redis') . PHP_EOL;

echo PHP_EOL;

echo 'phpredis igbinary support: ' . (int) defined('Redis::SERIALIZER_IGBINARY') . PHP_EOL;

if (defined('Redis::SERIALIZER_IGBINARY')) {
    $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_IGBINARY);
    $redis->set('test:igbinary', 'cafe babe');
    echo 'igbinary test: ' . to_json($redis->rawCommand('GET', 'test:igbinary')) . PHP_EOL;
    $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);
}

echo PHP_EOL;

echo 'phpredis lzf support: ' . (int) defined('Redis::COMPRESSION_LZF') . PHP_EOL;

if (defined('Redis::COMPRESSION_LZF')) {
    $redis->setOption(Redis::OPT_COMPRESSION, Redis::COMPRESSION_LZF);
    $redis->set('test:lzf', 'dead beef');
    echo 'test lzf: ' . $redis->get('test:lzf') . PHP_EOL;
    echo 'test raw lzf: ' . to_json($redis->rawCommand('GET', 'test:lzf')) . PHP_EOL;
    $redis->setOption(Redis::OPT_COMPRESSION, Redis::COMPRESSION_NONE);
}

echo PHP_EOL;

echo 'phpredis lz4 support: ' . (int) defined('Redis::COMPRESSION_LZ4') . PHP_EOL;

if (defined('Redis::COMPRESSION_LZ4')) {
    $redis->setOption(Redis::OPT_COMPRESSION, Redis::COMPRESSION_LZ4);
    $redis->set('test:lz4', 'decaf bad');
    echo 'test lz4: ' . $redis->get('test:lz4') . PHP_EOL;
    echo 'test raw lz4: ' . to_json($redis->rawCommand('GET', 'test:lz4')) . PHP_EOL;
    $redis->setOption(Redis::OPT_COMPRESSION, Redis::COMPRESSION_NONE);
}

echo PHP_EOL;

echo 'phpredis zstd support: ' . (int) defined('Redis::COMPRESSION_ZSTD') . PHP_EOL;

if (defined('Redis::COMPRESSION_ZSTD')) {
    $redis->setOption(Redis::OPT_COMPRESSION, Redis::COMPRESSION_ZSTD);
    $redis->set('test:zstd', 'face feed');
    echo 'test zstd: ' . $redis->get('test:zstd') . PHP_EOL;
    echo 'test raw zstd: ' . to_json($redis->rawCommand('GET', 'test:zstd')) . PHP_EOL;
    $redis->setOption(Redis::OPT_COMPRESSION, Redis::COMPRESSION_NONE);
}

echo PHP_EOL;

echo '...done!' . PHP_EOL;

echo '</pre>';
