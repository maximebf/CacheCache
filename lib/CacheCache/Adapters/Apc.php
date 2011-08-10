<?php

namespace CacheCache\Adapters;

class Apc extends AbstractAdapter
{
    public function exists($key)
    {
        return apc_exists($key);
    }

    public function get($key)
    {
        if (($value = apc_fetch($key)) === false) {
            return null;
        }
        return $value;
    }

    public function add($key, $value, $expire = null)
    {
        $expire = $expire ?: 0;
        return apc_add($key, $value, $expire);
    }

    public function set($key, $value, $expire = null)
    {
        $expire = $expire ?: 0;
        return apc_store($key, $value, $expire);
    }

    public function delete($key)
    {
        return apc_delete($key);
    }

    public function flushAll()
    {

    }
}