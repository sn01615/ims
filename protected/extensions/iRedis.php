<?php

/**
 * Redis tool
 * Date: 2016-04-02 00:42:50
 * @author YangLong
 */
class iRedis
{

    /** @var iRedis */
    private static $_instance;

    /** @return iRedis */
    public static function getInstance()
    {
        if (! (self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        
    }
}
