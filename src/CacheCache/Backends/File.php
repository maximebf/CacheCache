<?php
/*
 * This file is part of the CacheCache package.
 *
 * (c) 2012 Maxime Bouroumeau-Fuseau
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CacheCache\Backends;

class File extends AbstractBackend
{
    /** @var string */
    protected $dir;

    /** @var bool */
    protected $idAsFilename = false;

    /** @var bool */
    protected $subDirs = false;

    /** @var string */
    protected $fileExtension;

    /** @var array */
    protected $filenameCache = array();

    /**
     * Constructor
     *
     * Options:
     *  - dir: the directory where to store files (default: /tmp)
     *  - sub_dirs: whether to use sub directories for namespaces (default: false)
     *  - id_as_filename: whether to use the id as the filename (default: false)
     *  - file_extension: a file extension to be added to the filename, with the leading dot (default: none)
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $dir = isset($options['dir']) ? $options['dir'] : '/tmp';
        $this->dir = rtrim($dir, DIRECTORY_SEPARATOR);
        $this->subDirs = isset($options['sub_dirs']) && $options['sub_dirs'];
        $this->idAsFilename = isset($options['id_as_filename']) && $options['id_as_filename'];
        $this->fileExtension = isset($options['file_extension']) ? $options['file_extension'] : '';
    }

    public function exists($id)
    {
        return file_exists($this->filename($id));
    }

    public function get($id)
    {
        if ($this->exists($id)) {
            $filename = $this->filename($id);
            list($value, $expire) = $this->decode(file_get_contents($filename));
            if ($expire !== null && $expire < time()) {
                $this->delete($id);
                return null;
            }
            return $value;
        }
        return null;
    }

    public function set($id, $value, $ttl = null)
    {
        $filename = $this->filename($id);
        $dirname = dirname($filename);
        if (!file_exists($dirname)) {
            mkdir($dirname, 0777, true);
        }
        file_put_contents($filename, $this->encode($value, $ttl));
    }

    public function delete($id)
    {
        if ($this->exists($id)) {
            unlink($this->filename($id));
        }
        return true;
    }

    public function flushAll()
    {
        foreach (new \DirectoryIterator($this->dir) as $file) {
            if (substr($file->getFilename(), 0, 1) === '.' || $file->isDir()) {
                continue;
            }
            unlink($file->getPathname());
        }
        return true;
    }

    /**
     * Generates the filename associated to an $id
     *
     * @param string $id
     * @return string
     */
    public function filename($id)
    {
        if (isset($this->filenameCache[$id])) {
            return $this->filenameCache[$id];
        }

        $dir = $this->dir;
        $filename = $id;

        if ($this->subDirs) {
            $parts = explode(CacheCache\Cache::$namespaceSeparator, $id);
            $filename = array_pop($parts);
            $dir .= DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts);
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
        }

        if (!$this->idAsFilename) {
            $filename = md5($filename);
        }

        if (!empty($this->fileExtension)) {
            $filename .= $this->fileExtension;
        }

        $filename = $dir . DIRECTORY_SEPARATOR . $filename;
        $this->filenameCache[$id] = $filename;
        return $filename;
    }

    /**
     * Encodes some data to a string so it can be written to disk
     *
     * @param mixed $data
     * @param int $ttl
     * @return string
     */
    public function encode($data, $ttl)
    {
        $expire = null;
        if ($ttl !== null) {
            $expire = time() + $ttl;
        }
        return serialize(array($data, $expire));
    }

    /**
     * Decodes a string encoded by {@see encode()}
     *
     * Must returns a tuple (data, expire). Expire
     * can be null to signal no expiration.
     *
     * @param string $data
     * @return array (data, expire)
     */
    public function decode($data)
    {
        return unserialize($data);
    }
}
