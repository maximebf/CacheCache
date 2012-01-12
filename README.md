# CacheCache

Caching framework for PHP 5.3+

Features:

-   Easy retreival and storing of key, value pairs using the many available methods
-   Cache function calls
-   Available object wrapper to cache calls to methods
-   Pipelines ala Predis (see below)
-   Namespaces
-   TTL variations to avoid all caches to expire at the same time
-   Multiple backends support (apc, file, memcache(d), memory, redis, session)
-   Profiling
-   Very well documented

CacheCache features are exposed through a Cache object which itself uses backends to store the data.
Multiple instances of Cache objects can be managed using the CacheManager.

Full documentation in the [docs folder](https://github.com/maximebf/CacheCache/tree/master/docs)

Examples:

    $cache = new CacheCache\Cache(new CacheCache\Backends\Memory());

    if (($foo = $cache->get('foo')) === null) {
        $foo = 'bar';
        $cache->set('foo', $foo);
    }

    if (!$cache->start('foo')) {
        echo "bar\n";
        $cache->end();
    }

    $cache->call('sleep', array(2));
    $cache->call('sleep', array(2)); // won't sleep!

    $r = $cache->pipeline(function($pipe) {
        $pipe->set('foo', 'bar');
        $pipe->set('bar', 'foo');
        $pipe->get('foo');
        $pipe->set('foo', 'foobar');
        $pipe->get('foo');
    });

More examples in [examples/](https://github.com/maximebf/CacheCache/tree/master/examples)
