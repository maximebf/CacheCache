<?php

namespace CacheCache\Profilers;

use CacheCache\Profiler,;

class FirePHP implements Profiler
{
    public function log($text)
    {
        if (!headers_sent() && $firephp = \FirePHP::getInstance()) {
            $firephp->log($text);
        }
    }
}