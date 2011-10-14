<?php

include 'bootstrap.php';
$cache = CacheCache\CacheManager::get();

if (!$cache->start('foo')) {
    echo "bar\n";
    $cache->end();
}

if (!$cache->start('foo')) {
    echo "foobar\n";
    $cache->end();
}

var_dump($cache->get('foo'));
$cache->delete('foo');