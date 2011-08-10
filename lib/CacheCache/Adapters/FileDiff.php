<?php

namespace CacheCache\Adapters;

class FileDiff extends File
{
    protected $watchDir;

    public function __construct(array $options)
    {
        parent::__construct($options);
        $this->watchDir = rtrim($options['watch'], DIRECTORY_SEPARATOR);
    }

    public function exists($key)
    {
        $filename = $this->watchDir . DIRECTORY_SEPARATOR . $key;
        $cachename = $this->filename($key);
        if (!file_exists($cachename) || filemtime($cachename) < filemtime($filename)) {
            return false;
        }
        return true;
    }
}