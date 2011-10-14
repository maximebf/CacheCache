<?php

include 'bootstrap.php';
$cache = CacheCache\CacheManager::get();

$r = $cache->pipeline(function($pipe) {
    $pipe->set('foo', 'bar');
    $pipe->set('bar', 'foo');
    $pipe->get('foo');
    $pipe->set('foo', 'foobar');
    $pipe->get('foo');
});
var_dump($r);

$cache->delete('foo');
$cache->delete('bar');