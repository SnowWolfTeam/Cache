<?php
namespace PhpCache\RedisCache;

use PhpCache\Exception\RedisException;

class RedisCache
{
    /**
     * 根据需要使用扩展或类库，返回实例
     * @param $type
     * @return null|\Predis\Client|\Redis
     * @throws RedisException
     */
    public static function instance($type)
    {
        if ($type === 1) {
            if (!extension_loaded('redis'))
                throw new RedisException('redis扩展没有安装', RedisException::REDIS_EXTENSION_NOT_EXIST);
            else
                $redis = new \Redis();
            return $redis;
        } elseif ($type === 2) {
            $client = NULL;
            if (is_string($connectParams))
                $client = new \Predis\Client($connectParams);
            else
                $client = new \Predis\Client([
                    'scheme' => $connectParams['scheme'],
                    'host' => $connectParams['host'],
                    'port' => $connectParams['port'],
                ]);
            return $client;
        }
    }
}