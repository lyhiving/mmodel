<?php
namespace lyhiving\mmodel;

use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\Drivers\Redis\Config;

class Mcache
{

    public static $cache;
    public $key;

    public function __construct($cache = '', $config = [])
    {
        if (!is_object($cache)) {
            switch ($cache) {
                case "files":
                    CacheManager::setDefaultConfig(new ConfigurationOption($config));
                    $cache = CacheManager::getInstance('files');
                    break;
                case "redis":
                    $cache = CacheManager::getInstance('redis', new Config($config));
                    break;
            }
        }
        $this->cache = $cache;
    }

    public function init($cache)
    {
        $this->cache = $cache;
        return $this;
    }

    public function ketfix()
    {
        $this->key = str_replace(':', "_", $this->key);
    }

    public function get($key)
    {
        if (!$this->cache) {
            return false;
        }

        $this->key = $key;
        $this->ketfix();
        $CachedString = $this->cache->getItem($this->key);
        return $CachedString->get();
    }

    public function set($key, $data, $time = 0)
    {
        if (!$this->cache) {
            return false;
        }

        $this->key = $key;
        $this->ketfix();
        $CachedString = $this->cache->getItem($this->key);
        $CachedString->set($data);
        if ($time) {
            $CachedString->expiresAfter($time);
        }
        return $this->cache->save($CachedString);
    }

    public function delete($key)
    {
        if (!$this->cache) {
            return false;
        }

        $this->key = $key;
        $this->ketfix();
        return $this->cache->deleteItem($this->key);
    }
}
