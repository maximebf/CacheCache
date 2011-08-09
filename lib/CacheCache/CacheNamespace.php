<?php

namespace CacheCache;

class CacheNamespace
{
    public static $separator = ':';

    protected $namespace;

    protected $adapter;

    protected $defaultExpire = 0;

    public function __construct(CacheAdapter $adapter, $namespace = '', $defaultExpire = 0)
    {
        $this->namespace = $namespace;
        $this->adapter = $adapter;
        $this->defaultExpire = $defaultExpire;
    }

    public function key($key)
    {
        return implode(self::$separator, array($this->namespace, $key));
    }

    public function ns($namespace, $defaultExpire = null)
    {
        $namespace = $this->key($namespace);
        $defaultExpire = $defaultExpire ?: $this->defaultExpire;
        return new CacheNamespace($this->adapter, $namespace, $defaultExpire);
    }

    public function exists($key)
    {
        return $this->adapter->exists($this->key($key));
    }

    public function get($key, $default = null)
    {
        if (($value = $this->adapter->get($this->key($key))) === null) {
            return $default;
        }
        return $value;
    }

    public function getMulti($keys)
    {
        return $this->adapter->getMulti(array_map(array($this, 'key'), $keys));
    }

    public function set($key, $value, $expire = null)
    {
        $expire = $expire ?: $this->defaultExpire;
        $this->adapter->set($this->key($key), $value, $expire);
    }

    public function setMulti($items, $expire = null)
    {
        $values = array_values();
        $keys = array_map(array($this, 'key'), array_keys($items));
        $items = array_combine($keys, $values);
        $expire = $expire ?: $this->defaultExpire;
        return $this->adapter->setMulti($items, $expire);
    }

    public function getset($key, $value, $expire = null)
    {
        if (($v = $this->get($key)) === null) {
            $this->set($key, $value, $expire);
            return $value;
        }
        return $v;
    }

    public function cached($key, $closure, $expire = null)
    {
        if (($value = $this->get($key)) === null) {
            $value = $closure($this);
            $this->set($key, $value, $expire);
        }
        return $value;
    }

    public function call($callback, $args, $expire = null)
    {
        $key = md5(serialize($callback));
        return $this->cached($key, function() use ($callback $args) {
            return call_user_func_array($callback, $args);
        }, $expire)
    }

    public function wrap($object, $key = null, $expire = null)
    {
        $key = $key ?: get_class($object);
        return new CacheWrapper($object, $this->ns($key, $expire));
    }

    public function pipeline($closure = null)
    {
        $pipe = new CachePipeline($this);
        if ($closure === null) {
            return $pipe;
        }
        $closure($pipe);
        return $pipe->execute();
    }

    public function startCapture()
    {
        ob_start();
    }

    public function endCapture($key, $expire = null)
    {
        $output = ob_get_clean();
        $this->set($key, $output, $expire);
    }

    public function capture($key, $closure, $expire = null)
    {
        $this->startCapture();
        $closure($this);
        $this->endCapture($key, $expire);
    }

    public function capturePage($key, $expire = null)
    {
        $this->startCapture();
        register_shutdown_function(array($this, 'endCapture'), $key, $expire);
    }
}