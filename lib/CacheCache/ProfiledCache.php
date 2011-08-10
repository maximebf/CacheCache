<?php

namespace CacheCache;

class ProfiledCache extends Cache
{
    protected $profiledNamespace;

    protected $profiler;

    protected $profileStart;

    public function __construct(Cache $ns, Profiler $profiler = null)
    {
        parent::__construct($ns->adapter, $ns->namespace, $ns->defaultExpire, $ns->expirationVariation);
        $this->profiledNamespace = $ns;
        $this->profiler = $profiler;
        $this->profiler->logOperation('create namespace', $ns->namespace);
    }

    public function getProfiledNamespace()
    {
        return $this->profiledNamespace;
    }

    public function setProfiler(Profiler $profiler)
    {
        $this->profiler = $profiler;
    }

    public function getProfiler()
    {
        return $this->profiler;
    }

    protected function startProfile()
    {
        $this->profileStart = microtime(true);
    }

    protected function stopProfile($operation, $key = null, $data = null)
    {
        if ($this->profiler !== null) {
            $time = microtime(true) - $this->profileStart;
            $this->profiler->logOperation($operation, $key, $time, $data);
        }
    }

    public function ns($namespace, $defaultExpire = null)
    {
        return new ProfiledCache(parent::ns($namespace, $defaultExpire), $this->profiler);
    }

    public function exists($key)
    {
        $this->startProfile();
        $exists = parent::exists($key);
        $this->stopProfile('exists', $this->key($key));
        return $exists;
    }

    public function get($key, $default = null)
    {
        $this->startProfile();
        $value = parent::get($key, $default);
        $this->stopProfile('get', $this->key($key));
        return $value;
    }

    public function getMulti(array $keys)
    {
        $this->startProfile();
        $results = parent::getMulti($keys);
        $this->stopProfile('getMulti', array_map(array($this, 'key'), $keys));
        return $results;
    }

    public function add($key, $value, $expire = null)
    {
        $this->startProfile();
        $success = parent::add($key, $value, $expire);
        $this->stopProfile('add', $this->key($key), array('expire' => $expire));
        return $success;
    }

    public function set($key, $value, $expire = null)
    {
        $this->startProfile();
        $success = parent::set($key, $value, $expire);
        $this->stopProfile('set', $this->key($key), array('expire' => $expire));
        return $success;
    }

    public function setMulti(array $items, $expire = null)
    {
        $this->startProfile();
        $success = parent::setMulti($items, $expire);
        $this->stopProfile('setMulti', array_map(array($this, 'key'), 
            array_keys($items)), array('expire' => $expire));
        return $success;
    }

    public function delete($key)
    {
        $this->startProfile();
        $success = parent::delete($key);
        $this->stopProfile('delete', $this->key($key));
        return $success;
    }

    public function load($key, $expire = null)
    {
        if (($value = parent::load($key, $expire)) === false) {
            $this->profiler->logOperation('start capture', $this->key($key));
            $this->startProfile();
            return false;
        }
        $this->profiler->logOperation('capture hit', $this->key($key));
        return $value;
    }

    public function save($value)
    {
        if (count($this->stack)) {
            $this->stopProfile('end capture', $this->key($this->stack[count($this->stack) - 1][0]));
        }
        return parent::save($value);
    }

    public function cancel()
    {
        if (count($this->stack)) {
            $this->stopProfile('cancel capture', $this->key($this->stack[count($this->stack) - 1][0]));
        }
        return parent::cancel();
    }

    public function createPipeline()
    {
        $this->profiler->logOperation('create pipeline');
        return parent::createPipeline();
    }
}