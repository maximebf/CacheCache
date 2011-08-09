<?php

namespace CacheCache;

class CacheWrapper
{
    protected $cache;

    protected $object;

    public function __construct($object, CacheNamespace $cache)
    {
        $this->object = $object;
        $this->cache = $cache;
    }

    public function __set($name, $value)
    {
        if (method_exists($this->object, '__set')) {
            return $this->__call('__set', func_get_args());
        }
        $this->object->$name = $value;
    }

    public function __get($name)
    {
        if (method_exists($this->object, '__get')) {
            return $this->__call('__get', func_get_args());
        }
        return $this->object->$name;
    }

    public function __isset($name)
    {
        if (method_exists($this->object, '__isset')) {
            return $this->__call('__isset', func_get_args());
        }
        return isset($this->object->$name);
    }

    public function __unset($name)
    {
        if (method_exists($this->object, '__unset')) {
            return $this->__call('__unset', func_get_args());
        }
        unset($this->object->$name);
    }

    public function __toString()
    {
        return $this->__call('__toString', func_get_args());
    }

    public function __invoke()
    {
        return $this->__call('__invoke', func_get_args());
    }

    public function __call($method, $args)
    {
        $key = md5($method . serialize($args));
        if (($value = $this->cache->get($key)) === null) {
            $value = call_user_func_array(array($this->object, $method), $args);
            $this->cache->set($key, $value, $this->expire);
        }
        return $value;
    }
}