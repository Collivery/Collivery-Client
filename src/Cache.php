<?php

namespace Mds\Collivery;

class Cache
{
    private string $cacheDir;
    private array $cache;

    public function __construct(?string $cacheDir = null)
    {
        $this->cacheDir = $cacheDir ?: 'cache/mds_collivery/';
    }

    public function has($name): bool
    {
        $cache = $this->load($name);
        if (is_array($cache) && ($cache['valid'] - 30) > time()) {
            return true;
        }

        return false;
    }

    public function get($name)
    {
        $cache = $this->load($name);
        if (is_array($cache) && $cache['valid'] > time()) {
            return $cache['value'];
        }

        return null;
    }

    public function put($name, $value, $time = 1440): bool
    {
        $cache = ['value' => $value, 'valid' => time() + ($time * 60)];
        if (file_put_contents($this->cacheDir.$name, json_encode($cache))) {
            $this->cache[$name] = $cache;

            return true;
        }

        return false;
    }

    public function forget($name): bool
    {
        $cache = ['value' => '', 'valid' => 0];
        if (file_put_contents($this->cacheDir.$name, json_encode($cache))) {
            $this->cache[$name] = $cache;

            return true;
        }

        return false;
    }

    protected function create_dir($dirArray)
    {
        if (!is_array($dirArray)) {
            $dirArray = explode('/', $this->cacheDir);
        }

        array_pop($dirArray);
        $dir = implode('/', $dirArray);

        if ($dir != '' && !is_dir($dir)) {
            $this->create_dir($dirArray);
            mkdir($dir);
        }
    }

    protected function load($name)
    {
        if (!isset($this->cache[$name])) {
            if (file_exists($this->cacheDir.$name) && $content = file_get_contents($this->cacheDir.$name)) {
                $this->cache[$name] = json_decode($content, true);

                return $this->cache[$name];
            }
            $this->create_dir($this->cacheDir);
        } else {
            return $this->cache[$name];
        }
    }
}
