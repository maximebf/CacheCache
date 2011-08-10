<?php

namespace CacheCache\Adapters;

class Memory extends AbstractAdapter
{
    protected $cache = array();

    public function get($key)
    {
        if (!array_key_exists($key, $this->cache)) {
            return null;
        }
        return $this->cache[$key];
    }

    public function set($key, $value, $expire = null)
    {
        return $this->cache[$key] = $value;
    }

    public function delete($key)
    {
        unset($this->cache[$key]);
    }

    public function flushAll()
    {
        $this->cache = array();
    }
}