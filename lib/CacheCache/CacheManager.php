<?php

namespace CacheCache;

class CacheManager
{
    const DEFAULT = 'default';

    private static $caches = array();

    public static function setup($adapters)
    {
        if (is_string($adapters)) {
            $adapters = array(self::DEFAULT => array('adapter' => $adapters));
        }

        foreach ($adapters as $key => $options) {
            if (is_string($options) || $options instanceof CacheAdapter) {
                $options = array('adapter' => $options);
            }

            $adapter = $options['adapter'];
            $ns = isset($options['namespace']) ? $options['namespace'] : '';
            $expire = isset($options['expire']) ? $options['expire'] : 0;

            if (is_string($adapter)) {
                $opts = isset($options['options']) ? $options['options'] : array();
                $adapter = new $adapter($opts);
            }

            self::create($key, $adapter, $ns, $expire);
        }
    }

    public static function set($name, CacheNamespace $cache)
    {
        self::$caches[$name] = $cache;
    }

    public static function get($name = null)
    {
        $name = $name ?: self::DEFAULT;
        if (!isset(self::$caches[$name])) {
            throw new CacheException("Cache '$name' not found");
        }
        return self::$caches[$name];
    }

    public static function create($name, CacheAdapter $cache, $namespace = '', $defaultExpire = 0)
    {
        self::$caches[$name] = new CacheNamespace($cache, $namespace, $defaultExpire);
        return self::$caches[$name];
    }

    public static function ns($namespace, $defaultExpire = null)
    {
        return self::get()->ns($namespace, $defaultExpire);
    }
}