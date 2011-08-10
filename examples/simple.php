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

$foo = $cache->cached('foo', function() {
    return 'bar';
});
var_dump($foo);
var_dump($cache->get('foo'));

$cache->delete('foo');

// ----------------------

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

// ----------------------

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

// ----------------------

$ns = $cache->ns('foobar');
$ns->set('foo', 'bar');
var_dump($ns->get('foo'));
$ns->delete('foo');

// ----------------------

function sayhello($name)
{
    sleep(1);
    return "hello $name\n";
}

echo $cache->call('sayhello', array('peter'));
echo $cache->call('sayhello', array('paul'));
echo $cache->call('sayhello', array('peter'));

// ----------------------

class HelloWorld
{
    public function hello()
    {
        sleep(1);
        return "hello world\n";
    }
}

$obj = $cache->wrap(new HelloWorld());
echo $obj->hello();
echo $obj->hello();