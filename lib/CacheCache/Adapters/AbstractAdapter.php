<?php

namespace CacheCache\Adapters;

use CacheCache\Adapter;

abstract class AbstractAdapter implements Adapter
{
    public function exists($key)
    {
        return $this->get($key) !== null;
    }

    public function getMulti(array $keys)
    {
        $results = array();
        foreach ($keys as $key) {
            $results[] = $this->get($key);
        }
        return $results;
    }

    public function add($key, $value, $expire = null)
    {
        $this->set($key, $value, $expire);
    }

    public function setMulti(array $items, $expire = null)
    {
        foreach ($items as $key => $value) {
            $this->set($key, $value, $expire);
        }
    }

    public function supportsPipelines()
    {
        return false;
    }

    public function createPipeline()
    {
        return null;
    }
}