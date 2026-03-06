#!/bin/bash
set -e

RELAY_INI=`find $(php-config --ini-dir) -name "*-relay.ini"`

sed -i 's/^;\? \?relay.maxmemory =.*/relay.maxmemory = 100M/' $RELAY_INI
sed -i 's/^;\? \?relay.eviction_policy =.*/relay.eviction_policy = lru/' $RELAY_INI