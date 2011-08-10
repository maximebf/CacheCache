<?php

namespace CacheCache;

interface Adapter
{
    function exists($key);

    function get($key);

    function getMulti(array $keys);

    function add($key, $value, $expire = null);

    function set($key, $value, $expire = null);

    function setMulti(array $items, $expire = null);

    function delete($key);

    function flushAll();

    function supportsPipelines();

    function createPipeline();
}