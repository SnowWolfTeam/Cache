<?php
namespace PhpCache\Exception;
class FileException extends \Exception
{
    const MKDIR_ERROR = 0x1000;
    const PARAMS_NOT_ARRAY = 0x1001;
    const CACHE_NAME_ERROR = 0x1002;
    const CACHE_TIME_OUT = 0x1003;
    const FILE_OPEN_FAILED = 0x1004;
    const FILE_READ_FAILED = 0x1005;
    const FILE_WRITE_FAILED = 0x1006;
    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
    }

    public function __toString()
    {
        return __CLASS__ . ":[{Line:$this->line}]: {$this->message}\n";
    }
}