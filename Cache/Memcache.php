<?php
namespace PhpCache\Cache;

use PhpCache\Exception\MemcacheException;
use think\cache\driver\Memcached;

class Memcache
{
    /**
     * 检查扩展是否已经安装，返回Memcached实例
     * @return Memcached
     * @throws MemcacheException
     */
    public static function instance()
    {
        if (!extension_loaded('Memcached'))
            throw new MemcacheException('Memcache扩展没有安装', MemcacheException::MEMCACHE_EXTENSION_NOT_INSTALL);
        else
            return $memcache = new Memcached();
    }
}