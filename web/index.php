<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/header.phtml';

require __DIR__ . '/card-general.phtml';
require __DIR__ . '/card-redis.phtml';
require __DIR__ . '/card-relay.phtml';
require __DIR__ . '/card-igbinary.phtml';
require __DIR__ . '/card-msgpack.phtml';
require __DIR__ . '/card-swoole.phtml';
require __DIR__ . '/card-openswoole.phtml';

require __DIR__ . '/footer.phtml';
