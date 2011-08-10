<?php

namespace CacheCache;

class Cache implements Adapter
{
    public static $separator = ':';

    protected $namespace;

    protected $adapter;

    protected $defaultExpire;

    protected $expirationVariation = 0;

    protected $stack = array();

    protected $capturing = false;

    public function __construct(Adapter $adapter, $namespace = '', $defaultExpire = null, $expirationVariation = 0)
    {
        $this->adapter = $adapter;
        $this->namespace = $namespace;
        $this->defaultExpire = $defaultExpire;
        $this->expirationVariation = $expirationVariation;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getAdapter()
    {
        return $this->adapter;
    }

    public function setDefaultExpire($expire)
    {
        $this->defaultExpire = $expire;
    }

    public function getDefaultExpire()
    {
        return $this->defaultExpire;
    }

    public function setExpirationVariation($amplitude)
    {
        $this->expirationVariation = $amplitude;
    }

    public function getExpirationVariation()
    {
        return $this->expirationVariation;
    }

    protected function computeExpire($expire)
    {
        $expire = $expire ?: $this->defaultExpire;
        return $expire + rand(0, $this->expirationVariation);
    }

    public function key($key)
    {
        $parts = array_merge(array($this->namespace), (array) $key);
        return trim(implode(self::$separator, $parts), self::$separator);
    }

    public function ns($namespace, $defaultExpire = null)
    {
        $namespace = $this->key($namespace);
        $defaultExpire = $defaultExpire ?: $this->defaultExpire;
        return new Cache($this->adapter, $namespace, $defaultExpire, $this->expirationVariation);
    }

    public function exists($key)
    {
        $key = $this->key($key);
        return $this->adapter->exists($key);
    }

    public function get($key, $default = null)
    {
        $key = $this->key($key);
        if (($value = $this->adapter->get($key)) === null) {
            return $default;
        }
        return $value;
    }

    public function getMulti(array $keys)
    {
        $keys = array_map(array($this, 'key'), $keys);
        return $this->adapter->getMulti($keys);
    }

    public function add($key, $value, $expire = null)
    {
        $key = $this->key($key);
        $expire = $this->computeExpire($expire);
        return $this->adapter->add($key, $value, $expire);
    }

    public function set($key, $value, $expire = null)
    {
        $key = $this->key($key);
        $expire = $this->computeExpire($expire);
        return $this->adapter->set($key, $value, $expire);
    }

    public function setMulti(array $items, $expire = null)
    {
        $keys = array_map(array($this, 'key'), array_keys($items));
        $items = array_combine($keys, array_values($items));
        $expire = $this->computeExpire($expire);
        return $this->adapter->setMulti($items, $expire);
    }

    public function delete($key)
    {
        return $this->adapter->delete($this->key($key));
    }

    public function flushAll()
    {
        return $this->adapter->flushAll();
    }

    public function getset($key, $value, $expire = null)
    {
        if (($v = $this->get($key)) === null) {
            $this->add($key, $value, $expire);
            return $value;
        }
        return $v;
    }

    public function cached($key, $closure, $expire = null)
    {
        if (($value = $this->get($key)) === null) {
            $value = $closure($this);
            $this->add($key, $value, $expire);
        }
        return $value;
    }

    public function load($key, $expire)
    {
        if (($value = $this->get($key)) !== null) {
            return $value;
        }
        $this->stack[] = array($key, $expire);
        return false;
    }

    public function save($value)
    {
        if (empty($this->stack)) {
            throw new CacheException("Cache::load() must be called before Cache::save()");
        }
        list($key, $expire) = array_pop($this->stack);
        return $this->add($key, $value, $expire);
    }

    public function start($key, $expire = null, $echo = true)
    {
        if (($output = $this->load($key, $expire)) === false) {
            ob_start();
            $this->capturing = true;
            return false;
        }
        if ($echo) {
            echo $output;
        }
        return $output;
    }

    public function end($echo = true)
    {
        if (!empty($this->stack)) {
            $output = ob_get_clean();
            $this->save($output);
            $this->capturing = false;
            if ($echo) {
                echo $output;
            }
            return $output;
        }
        return false;
    }

    public function isCapturing()
    {
        return $this->capturing;
    }

    public function cancel()
    {
        if (!empty($this->stack)) {
            array_pop($this->stack);
            if ($this->capturing) {
                $this->capturing = false;
                ob_end_flush();
            }
        }
    }

    public function capture($key, $closure, $expire = null, $echo = true)
    {
        if (($output = $this->start($key, $expire, $echo)) === false) {
            $closure($this);
            return $this->end($echo);
        }
        return $output;
    }

    public function capturePage($key = null, $expire = null, $exit = true)
    {
        if ($key === null) {
            $key = '';
        }

        if ($this->start($key, $expire)) {
            if ($exit) {
                exit;
            }
            return true;
        }
        register_shutdown_function(array($this, 'end'));
        return false;
    }

    public function call($callback, $args, $expire = null)
    {
        $key = md5(serialize($callback) . serialize($args));
        return $this->cached($key, function() use ($callback, $args) {
            return call_user_func_array($callback, $args);
        }, $expire);
    }

    public function wrap($object, $key = null, $expire = null)
    {
        $key = $key ?: get_class($object);
        return new Wrapper($object, $this->ns($key, $expire));
    }

    public function supportsPipelines()
    {
        return true;
    }

    public function createPipeline()
    {
        if ($this->adapter->supportsPipelines()) {
            return $this->adapter->createPipeline();
        }
        return new Pipeline($this);
    }

    public function pipeline($closure = null)
    {
        $pipe = $this->createPipeline();
        if ($closure === null) {
            return $pipe;
        }
        $closure($pipe);
        return $pipe->execute();
    }
}