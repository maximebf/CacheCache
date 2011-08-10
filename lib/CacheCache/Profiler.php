<?php

namespace CacheCache;

interface Profiler
{
    function log($text);
    
    function logOperation($operation, $key = null, $time = null, $data = null);
}