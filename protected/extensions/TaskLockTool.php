<?php

/**
 * @desc 任务锁(永远不要忘记释放锁)
 * @author YangLong
 * @date 2015-6-26
 */
class TaskLockTool
{

    const MONGODB = 'getmypid';

    /**
     * @desc 加锁
     * @param string $tag
     * @author YangLong
     * @date 2015-6-26
     * @return boolean|null
     */
    public static function lock($tag)
    {
        $lock = iMongo::getInstance()->setCollection(self::MONGODB)->findOne(array(
            'locktag' => $tag
        ), array(
            'pid',
            'time'
        ));
        
        if ($lock !== null && $lock['pid'] != 0 && $lock['time'] > time() - 3600) {
            die('task lock!');
            return false;
        } else {
            if (empty($lock)) {
                iMongo::getInstance()->setCollection(self::MONGODB)->insert(array(
                    'locktag' => $tag,
                    'pid' => getmypid(),
                    'time' => time()
                ));
            } else {
                iMongo::getInstance()->setCollection(self::MONGODB)->update(array(
                    'locktag' => $tag
                ), array(
                    '$set' => array(
                        'pid' => getmypid(),
                        'time' => time()
                    )
                ));
            }
            // History
            iMongo::getInstance()->setCollection(self::MONGODB . 'History')->insert(array(
                'locktag' => $tag,
                'pid' => getmypid(),
                'time' => time()
            ));
        }
    }

    /**
     * @desc 释放锁
     * @param string $tag
     * @author YangLong
     * @date 2015-6-26
     * @return null
     */
    public static function unlock($tag)
    {
        iMongo::getInstance()->setCollection(self::MONGODB)->update(array(
            'locktag' => $tag
        ), array(
            '$set' => array(
                'pid' => 0,
                'time' => time()
            )
        ));
    }
}