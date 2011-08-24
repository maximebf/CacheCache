<?php
/**
 * CacheCache
 * Copyright (c) Maxime Bouroumeau-Fuseau
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author Maxime Bouroumeau-Fuseau
 * @copyright (c) Maxime Bouroumeau-Fuseau
 * @license http://www.opensource.org/licenses/mit-license.php
 */

namespace CacheCache;

/**
 * Manages multiple instances of Cache objects
 */
class CacheManager
{
    const _DEFAULT = 'default';

    /** @var Profiler */
    public static $profiler;

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
     * If $profiler is not null, all Backend instances will be wrapped in a 
     * {@see ProfiledBackend} object.
     *
     * @see factory()
     * @param array $caches
     * @param Profiler $profiler
     */
    public static function setup($caches, Profiler $profiler = null)
    {
        if (!is_array($caches)) {
            $caches = array(self::_DEFAULT => array('backend' => $caches));
        }

        self::$profiler = $profiler;

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
            throw new CacheException("Options for '$name' in CacheManager::create() must be an array");
        }

        $options = array_merge(self::$defaults, $options);
        if (!isset($options['backend'])) {
            throw new CacheException("No backend specified for '$name' in CacheManager::create()");
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

        if (self::$profiler !== null) {
            $backend = new ProfiledBackend($backend, self::$profiler);
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