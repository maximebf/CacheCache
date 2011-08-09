<?php

include 'bootstrap.php';

$cache = CacheCache\CacheManager::get();

if (($foo = $cache->get('foo')) === null) {
    $foo = 'bar';
    $cache->set('foo', $foo);
}

// ----------------------

$foo = $cache->cached('foo', function() {
    return 'bar';
});

// ----------------------

$foo = $cache->call(array($obj, 'method'), $args);
$obj = $cache->wrap($obj);

// ----------------------

$results = $cache->pipeline(function($pipe) {
    $pipe->get('hello');
    $pipe->set('foo', 'bar');
})

// ----------------------

$cache->startCapture();
echo 'bar';
$cache->endCapture('foo');