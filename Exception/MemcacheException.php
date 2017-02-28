<?php
namespace PhpCache\Exception;
class MemcacheException extends \Exception
{
    const MEMCACHE_EXTENSION_NOT_INSTALL = 0x1000;

    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
    }

    public function __toString()
    {
        return __CLASS__ . ":[{Line:$this->line}]: {$this->message}\n";
    }
}