<?php

namespace CacheCache;

class FallbackCache extends Cache
{
    protected $adapters = array();

    public function __construct($adapters, $namespace = '', $defaultExpire = 0, $expirationVariation = 0)
    {
        if ($adapters instanceof Adapter) {
            $adapters = array($adapters);
        }

        foreach ($adapters as $adapter) {
            if (!($adapter instanceof Adapter)) {
                throw new CacheException("Adapters must be instance of CacheCache\Adapter in CacheCache\FallbackCache");
            }
        }

        $this->adapters = $adapters;
        $this->namespace = $namespace;
        $this->defaultExpire = $defaultExpire;
        $this->expirationVariation = $expirationVariation;
    }

    public function exists($key)
    {
        $key = $this->key($key);
        foreach ($this->adapters as $adapter) {
            if ($adapter->exists($key)) {
                return true;
            }
        }
        return false;
    }

    public function get($key, $default = null)
    {
        $key = $this->key($key);
        foreach ($this->adapters as $adapter) {
            if (($value = $adapter->get($key)) !== null) {
                return $value;
            }
        }
        return $default;
    }

    public function getMulti(array $keys)
    {
        $keys = array_map(array($this, 'key'), $keys);
        foreach ($this->adapters as $adapter) {
            if (($value = $adapter->getMulti($keys)) !== null) {
                return $value;
            }
        }
        return null;
    }

    public function createPipeline()
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->supportsPipelines()) {
                return $adapter->createPipeline();
            }
        }
        return new Pipeline($this);
    }
}