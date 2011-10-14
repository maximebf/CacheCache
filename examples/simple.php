<?php

include 'bootstrap.php';
$cache = CacheCache\CacheManager::get();

var_dump($cache->exists('foo'));

if (($foo = $cache->get('foo')) === null) {
    $foo = 'bar';
    $cache->set('foo', $foo);
}
var_dump($foo);
var_dump($cache->get('foo'));

$cache->delete('foo');
var_dump($cache->exists('foo'));
var_dump($cache->get('foo'));

// ----------------------

$foo = $cache->getset('foo', function() {
    return 'bar';
});
var_dump($foo);
var_dump($cache->get('foo'));

$cache->delete('foo');

// ----------------------

$ns = $cache->ns('foobar');
$ns->set('foo', 'bar');
var_dump($ns->get('foo'));
$ns->delete('foo');
