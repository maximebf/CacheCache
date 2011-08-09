<?php

namespace CacheCache;

class CachePipeline
{
    protected $cache;

    protected $commands = array();

    protected $expire = null;

    public function __construct(CacheNamespace $cache)
    {
        $this->cache = $cache;
    }

    public function get($key)
    {
        $this->commands[] = array('get', $key);
    }

    public function set($key, $value)
    {
        $this->commands[] = array('set', $key, $value);
    }

    public function expire($expire)
    {
        $this->expire = $expire;
    }

    public function execute()
    {
        $groups = array();
        $results = array();
        $currentOperation = null;
        $currentGroup = array();

        foreach ($this->commands as $args) {
            $op = array_shift($args);
            if ($currentOperation !== $op) {
                $groups[] = array($currentOperation, $currentGroup);
                $currentOperation = $op;
                $currentGroup = array();
            }
            if ($currentOperation === 'get') {
                $currentGroup[] = $args[0];
            } else {
                $currentGroup[$args[0]] = $args[1];
            }
        }
        array_shift($groups);

        foreach ($groups as $group) {
            list($op, $args) = $group;
            if ($op === 'set') {
                $result = $this->cache->setMulti($args, $this->expire);
                $results = array_merge($results, array_fill(0, count($args), $result));
            } else {
                $results = array_merge($results, $this->cache->getMulti($args));
            }
        }

        return $results;
    }
}