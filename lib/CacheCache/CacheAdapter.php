<?php

namespace CacheCache;

interface CacheAdapter
{
    function exists($key);
    function get($key);
    function getMulti($keys);
    function set($key, $value, $expire);
    function setMutli($items, $expire);
}