<?php

/**
 * @desc 文件锁
 * @author YangLong
 * @date 2015-07-05
 */
class DaemonLockTool
{

    private static $fp;

    /**
     * @desc 加锁
     * @param string $tag            
     * @author YangLong
     * @date 2015-07-05
     * @return null
     */
    public static function lock($tag)
    {
        $filename = BASE_PATH . '/protected/runtime/lockfiles/' . md5($tag);
        // file_exists($filename) || touch($filename);
        file_put_contents($filename, time());
        
        self::$fp = fopen($filename, "r+");
        
        if (! flock(self::$fp, LOCK_EX | LOCK_NB)) {
            die(0);
        }
    }
    
    /**
     * @desc 释放锁
     * @param string $tag            
     * @author YangLong
     * @date 2015-07-05
     * @return null
     */
    public static function unlock($tag)
    {
        if (self::$fp !== null) {
            flock(self::$fp, LOCK_UN);
        }
    }
}