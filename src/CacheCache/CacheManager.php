<?php
/*
 * This file is part of the CacheCache package.
 *
 * (c) 2012 Maxime Bouroumeau-Fuseau
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CacheCache;

use Monolog\Logger;

/**
 * Manages multiple instances of Cache objects
 */
class CacheManager
{
    const _DEFAULT = 'default';

    /** @var Logger */
    public static $logger;

    /** @var int */
    public static $logLevel;

    /** @var array */
    public static $defaults = array(
        'backend' => null,
        'backend_args' => null,
        'namespace' => '',
        'ttl' => null,
        'variation' => 0
    );

    /** @var array */
    private static $caches = array();

    /**
     * Setups the cache manager.
     *
     * If $caches is the class name of a backend, a {@see Backend} instance,
     * a {@see Cache} instance will be created under the default name.
     *
     * $caches can also be an array to define multiple cache instances an once.
     * Keys will be used as cache names and values must be compatible with the
     * {@see factory()} method $options argument.
     * 
     * <code>
     *      CacheManager::setup(array(
     *          'default' => 'CacheCache\Backend\File'
     *      ));
     * </code>
     *
     * If $logger is not null, all Backend instances will be wrapped in a 
     * {@see LoggingBackend} object.
     *
     * @see factory()
     * @param array $caches
     * @param Logger $logger
     * @param int $logLevel
     */
    public static function setup($caches, Logger $logger = null, $logLevel = null)
    {
        if (!is_array($caches)) {
            $caches = array(self::_DEFAULT => array('backend' => $caches));
        }

        self::$logger = $logger;
        self::$logLevel = $logLevel;

        foreach ($caches as $name => $options) {
            self::$caches[$name] = self::factory($options);
        }
    }

    /**
     * Creates a {@see Cache} object
     *
     * $options can either be the class name of a backend, a {@see Backend}
     * instance or an array.
     *
     * Possible array values:
     *  - backend: backend class name or {@see Backend} instance
     *  - backend_args: an array of constructor arguments for the backend
     *  - namespace
     *  - ttl
     *  - variation
     *
     * Default values for these options can be defined in the $defaults static
     * property.
     *
     * @param array $options
     * @return Cache
     */
    public static function factory($options)
    {
        if (is_string($options) || $options instanceof Backend) {
            $options = array('backend' => $options);
        } else if (!is_array($options)) {
            throw new CacheException("Options argument in CacheManager::factory() must be an array");
        }

        $options = array_merge(self::$defaults, $options);
        if (!isset($options['backend'])) {
            throw new CacheException("No backend specified in options array for CacheManager::factory()");
        }

        $backend = $options['backend'];
        if (is_string($backend)) {
            if (isset($options['backend_args'])) {
                $backendClass = new \ReflectionClass($backend);
                $backend = $backendClass->newInstanceArgs($options['backend_args']);
            } else {
                $backend = new $backend();
            }
        }

        if (self::$logger !== null) {
            $backend = new LoggingBackend($backend, self::$logger, self::$logLevel);
        }
            
        $cache = new Cache($backend, $options['namespace'], $options['ttl'], $options['variation']);
        return $cache;
    }

    /**
     * Makes a {@see Cache} instance available through $name
     *
     * @param string $name
     * @param Cache $cache
     */
    public static function set($name, Cache $cache)
    {
        self::$caches[$name] = $cache;
    }

    /**
     * Returns the {@see Cache} instance under $name
     *
     * @param string $name If null will used the instance named CacheManager::_DEFAULT
     * @return Cache
     */
    public static function get($name = null)
    {
        $name = $name ?: self::_DEFAULT;
        if (!isset(self::$caches[$name])) {
            throw new CacheException("Cache '$name' not found");
        }
        return self::$caches[$name];
    }

    /**
     * Shorcut to self::get()->ns()
     *
     * @see Cache::ns()
     * @param string $namespace
     * @param int $defaultTTL
     * @return Cache
     */
    public static function ns($namespace, $defaultTTL = null)
    {
        return self::get()->ns($namespace, $defaultTTL);
    }
}
