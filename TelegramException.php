<?php
class TelegramException extends Exception
{
    public function __construct($message, $code = 0)
    {
        parent::__construct($message, $code);
        $path = 'TelegramException.log';
        $status = file_put_contents($path, date('Y-m-d H:i:s', time()) .' '. self::__toString() . "\n", FILE_APPEND);
    }
}
