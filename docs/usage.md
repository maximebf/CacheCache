
# Usage

## Requirements and installation

-   CacheCache needs at least PHP 5.3
-   Some backends may rely on external libraries or PECL extensions
-   The lib folder needs to be added to the include paths:

        set_include_path(implode(PATH_SEPARATOR, array(
            '/path/to/cachecache/lib', 
            get_include_path()
        )));

-   An SPL autoloader must be registered, example:

        spl_autoload_register(function($className) {
            $filename = str_replace('\\', DIRECTORY_SEPARATOR, trim($className, '\\')) . '.php';
            require_once $filename;
        });

## The CacheManager

CacheManager is a static class that can be used to initialize and store multiple instances
of Cache objects.

Storing and accessing Cache objects:

    $cache = new Cache(new Backends\Memory());
    CacheManager::set('mycache', $cache);

    // ...

    $cache = CacheManager::get('mycache');

Creating Cache objects can be done using the factory() method. It takes an array as only argument
with the following possible values:

-   backend: backend class name or Backend instance
-   backend_args: an array of constructor arguments for the backend
-   namespace
-   ttl
-   variation

Example:

    $cache = CacheManager::factory(array(
        'backend' => 'CacheCache\Backends\Memcache',
        'backend_args' => array(array(
            'host' => 'localhost',
            'port' => 11211
        ))
    ));

Finally, multiple Cache objects can be created at the same time using the setup() method. It takes
as first parameter an array of key/value pairs where keys will be used as the cache name to be used
with the get() method and values are an array to be used with factory(). The second argument can
be a Profiler instance to enabled profiling.

    CacheManager::setup(array(
        'array' => 'CacheCache\Backends\Memory',
        'memcache' => array(
            'backend' => 'CacheCache\Backends\Memcache',
            'backend_args' => array(array(
                'host' => 'localhost',
                'port' => 11211
            ))
        )
    ));

    $cache = CacheManager::get('array');

## Simple usage

Availables getter/setter methods:

-   exists($id)
-   get($id, $default = null)
-   set($id, $value, $ttl = null)
-   add($id, $value, $ttl = null)
-   delete($id)

The add() method will not store the data if the key already exists whereas set() will do.
$ttl stand for Time To Live and will be the lifetime in seconds of the key/value pair.

If get() is used to retreive a non existing $id, the $default value is returned instead.

Examples:

    $cache->set('foo', 'bar');
    $cache->exists('foo');
    $cache->get('foo');
    $cache->delete('foo');

    $cache->add('foo', 'bar', 10);
    $cache->exists('foo'); // true
    sleep(11);
    $cache->exists('foo'); // false

    if (($foo = $cache->get('foo')) === null) {
        $foo = 'bar';
        $cache->set('foo', $foo);
    }

To ease the process if filling the cache on a miss, the getset() method is available:

    $foo = $cache->getset('foo', function() {
        return 'bar';
    });

In this example, the closure is called only when foo does not exist. Another way of 
doing a similar operation without the use of closures is using the load() and save() methods.

    if (!($foo = $cache->load('foo'))) {
        $foo = 'bar';
        $cache->save($foo);
    }

load() and save() calls can be nested. A currently running operation (after performing a load())
can be canceled using cancel().

    if (!($foo = $cache->load('foo'))) {
        try {
            $foo = 'bar';
            $cache->save($foo);
        } catch (Exception $e) {
            $cache->cancel();
            $foo = 'default value';
        }
    }

## Caching function calls

The call() function can be used to cache function calls. It behaves the same way as
call_user_func_array(). The cache id is generated using the function name and the serialized arguments.

    function do_heavy_stuff($arg) {
        sleep(1);
        return $arg;
    }

    echo $cache->call('do_heavy_stuff', array('foo')); // sleeps 1 sec
    echo $cache->call('do_heavy_stuff', array('bar')); // sleeps 1 sec
    echo $cache->call('do_heavy_stuff', array('foo')); // won't sleep

## Caching object methods

Object methods can be cached by wrapping an object into a special proxy class. The object can be used
as usual but all calls to its method will be cached. The cache id for each method is computed using 
the method name, the serialized arguments and the serialized public properties of the object. The wrap()
method automatically creates a namespace for all cache ids of this object which is, by default, named after the class
name.

    class MyClass {
        public function doHeavyStuff($arg) {
            sleep(1);
            return $arg;
        }
    }

    $obj = $cache->wrap(new MyClass());
    echo $obj->doHeavyStuff('foo'); // sleeps 1 sec
    echo $obj->doHeavyStuff('foo'); // won't sleep

## Capturing content

CacheCache provides multiple ways to capture echoed content. The easiest one works the same way as load() and save()
but uses start() and end().

    if (!$cache->start('foo')) {
        echo 'bar';
        $cache->end();
    }

cancel() can also be used to cancel a call to start().

The output of a function can also be captured using the capture() method which works the same way as getset().

    $foo = $cache->capture('foo', function() {
        echo 'bar';
    });

Finally, the whole content of a page can be captured using capturePage().

    $cache->capturePage();
    
By default, the cache id will be computed from the REQUEST_URI and the $_REQUEST array and the method calls exit()
if there is a hit.

## Multiple operations at once and pipelines

Multi get and set operations are available in a similar fashion as with the libmemcached pecl extension.

    $cache->setMulti(array(
        'foo' => 'bar',
        'bar' => 'foo'
    ));
    
    $r = $cache->getMulti(array('foo', 'bar'));
    // $r = array('bar', 'foo');

CacheCache also introduces the concept of Pipelines inspired by Predis. A pipeline is an object that
stack operations and executes them all at the same time. Not all backends have native support for pipelines
(only redis for the moment). A simple pipeline implementation based on setMulti() and getMulti() is provided
for the other ones.

    $r = $cache->pipeline(function($pipe) {
        $pipe->set('foo', 'bar');
        $pipe->set('bar', 'foo');
        $pipe->get('foo');
        $pipe->get('bar');
    });

## Namespaces

Namespaces allow you to better organize cache ids. A cache namespace is simply a new Cache object bound to
a specific namespace. To create a subnamespace of the current one, use the ns() method.

    $cache->set('a', 'b'); // id = a

    $foo = $cache->ns('foo');
    $foo->set('a', 'c'); // id = foo:a

## Multiple caches

Multiple backends can be chained together using the MultiCache class. When a value is retreived (get or exists)
it will first try in the first backend then in the second if it misses, then the third, etc...  
When a key/value pair is modified (insert, add or delete), the operation will be performed on all backends.

    $cache = new MultiCache(array($backend1, $backend2));

NOTE: Cache objects are themselves backends!

## Profiling

Backend usage can be profiled using the ProfiledBackend class. It uses a Profiler class which provides an output
method. Two profilers are available: Text and FirePHP.

    $profiler = new Profilers\Text();
    $backend = new ProfiledBackend(new Backends\Memory());
    $cache = new Cache($backend);

Two ease the use of Profilers, the CacheManager::setup() methods can take as second argument a profiler class 
which will be applied to all backends created through the manager.

    CacheManager::setup(array(/* ... */), new Profilers\Text());
