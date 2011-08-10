<?php

namespace CacheCache;

class Wrapper
{
    protected $adapter;

    protected $object;

    public function __construct($object, Adapter $adapter)
    {
        $this->object = $object;
        $this->adapter = $adapter;
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
        $key = md5($method . serialize($args) . serialize(get_object_vars($this->object)));
        if (($value = $this->adapter->get($key)) === null) {
            $value = call_user_func_array(array($this->object, $method), $args);
            $this->adapter->add($key, $value);
        }
        return $value;
    }
}