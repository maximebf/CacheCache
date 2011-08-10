<?php

namespace CacheCache;

class CacheManager
{
    const _DEFAULT = 'default';

    private static $caches = array();

    public static $defaultProfiler;

    public static function setup($adapters, $defaultProfiler = null)
    {
        if (is_string($adapters)) {
            $adapters = array(self::_DEFAULT => array('adapter' => $adapters));
        }

        if ($defaultProfiler !== null && is_string($defaultProfiler)) {
            $defaultProfiler = new $defaultProfiler();
        }
        self::$defaultProfiler = $defaultProfiler;

        foreach ($adapters as $key => $options) {
            if (is_string($options) || $options instanceof Adapter) {
                $options = array('adapter' => $options);
            }

            $adapter = $options['adapter'];
            $ns = isset($options['namespace']) ? $options['namespace'] : '';
            $expire = isset($options['expire']) ? $options['expire'] : null;
            $expirationVariation = isset($options['variation']) ? $options['variation'] : 0;
            $profiler = isset($options['profiler']) ? $options['profiler'] : null;

            if (is_string($adapter)) {
                $opts = isset($options['options']) ? $options['options'] : array();
                $adapter = new $adapter($opts);
            }

            if ($profiler !== null && is_string($profiler)) {
                $profiler = new $profiler();
            }

            self::create($key, $adapter, $ns, $expire, $expirationVariation, $profiler);
        }
    }

    public static function set($name, Cache $cache)
    {
        self::$caches[$name] = $cache;
    }

    public static function get($name = null)
    {
        $name = $name ?: self::_DEFAULT;
        if (!isset(self::$caches[$name])) {
            throw new CacheException("Cache '$name' not found");
        }
        return self::$caches[$name];
    }

    public static function create($name, Adapter $cache, $namespace = '', $defaultExpire = null, $expirationVariation = 0, Profiler $profiler = null)
    {
        $profiler = $profiler ?: self::$defaultProfiler;

        $cache = new Cache($cache, $namespace, $defaultExpire, $expirationVariation);
        if ($profiler !== null) {
            $cache = new ProfiledCache($cache, $profiler);
        }

        self::$caches[$name] = $cache;
        return self::$caches[$name];
    }

    public static function ns($namespace, $defaultExpire = null)
    {
        return self::get()->ns($namespace, $defaultExpire);
    }
}