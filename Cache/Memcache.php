<?php
namespace PhpCache\Cache;

use PhpCache\Exception\MemcacheException;
use think\cache\driver\Memcached;

class Memcache
{
    /**
     * 检查扩展是否已经安装，返回Memcached实例
     */
    public static function instance()
    {
        if (!extension_loaded('Memcached'))
            throw new MemcacheException('Memcache扩展没有安装');
        else
            return $memcache = new Memcached();
    }
}