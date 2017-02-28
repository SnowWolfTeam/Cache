<?php
namespace PhpCache\Exception;
class RedisException extends \Exception
{
    const REDIS_EXTENSION_NOT_EXIST = 0x1000;

    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
    }

    public function __toString()
    {
        return __CLASS__ . ":[{Line:$this->line}]: {$this->message}\n";
    }
}