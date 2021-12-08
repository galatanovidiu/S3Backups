<?php

namespace Galatanovidiu\S3Backups;

class ScreenLog
{

    protected static $messages = [];

    public static function log($message)
    {
        static $instance = null;
        if($instance == null){
            $instance = new static();
        }
        self::$messages[] = $message;
        print_r($message);
    }

    public static function getMessages()
    {
        return self::$messages;
    }
}
