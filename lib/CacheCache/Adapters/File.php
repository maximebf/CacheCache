<?php

namespace CacheCache\Adapters;

class File extends AbstractAdapter
{
    protected $dir;

    public function __construct(array $options)
    {
        $dir = isset($options['dir']) ? $options['dir'] : '/tmp';
        $this->dir = rtrim($dir, DIRECTORY_SEPARATOR);
    }

    public function exists($key)
    {
        return file_exists($this->filename($key));
    }

    public function get($key)
    {
        if ($this->exists($key)) {
            $filename = $this->filename($key);
            return unserialize(file_get_contents($filename));
        }
        return null;
    }

    public function set($key, $value, $expire = null)
    {
        $filename = $this->filename($key);
        $dirname = dirname($filename);
        if (!file_exists($dirname)) {
            mkdir($dirname, 0777, true);
        }
        file_put_contents($filename, serialize($value));
    }

    public function delete($key)
    {
        return unlink($this->filename($key));
    }

    public function flushAll()
    {
        foreach (new \DirectoryIterator($this->dir) as $file) {
            if (substr($file->getFilename(), 0, 1) === '.' || $file->isDir()) {
                continue;
            }
            unlink($file->getPathname());
        }
    }

    public function filename($key)
    {
        $filename = md5($key);
        return $this->dir . DIRECTORY_SEPARATOR . $filename:
    }
}