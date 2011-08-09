<?php

namespace CacheCache;

abstract class AbstractCacheAdapter implements CacheAdapter
{
    public function exists($key)
    {
        return $this->get($key) !== null;
    }

    public function getMulti($keys)
    {
        $results = array();
        foreach ($keys as $key) {
            $results[] = $this->get($key);
        }
        return $results;
    }

    public function setMutli($items, $expire)
    {
        foreach ($items as $key => $value) {
            $this->set($key, $value, $expire);
        }
    }
}