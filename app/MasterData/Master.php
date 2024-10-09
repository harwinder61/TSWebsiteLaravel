<?php

namespace App\MasterData;

class Master
{
    public static $type = 'single_array';
    public static $data = [];

    public static function getData()
    {
        return static::$data;
        
    }

    public static function getValues()
    {
        return array_column(static::$data, 'value');
    }

    public static function getKeys()
    {
        return array_keys(static::$data);
    }
}