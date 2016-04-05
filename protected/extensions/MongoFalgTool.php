<?php

/**
 * @desc falg tool
 * @author YangLong
 * @date 2015-08-12
 */
class MongoFalgTool
{

    /**
     * @desc 设置falg
     * @param string $falg
     * @param mixed $value
     * @author YangLong
     * @date 2015-08-12
     */
    public static function setfalg($falg, $value)
    {
        if (self::getfalg($falg) === false) {
            iMongo::getInstance()->setCollection(__CLASS__)->insert(array(
                'falg' => $falg,
                'value' => $value
            ));
        } else {
            iMongo::getInstance()->setCollection(__CLASS__)->update(array(
                'falg' => $falg
            ), array(
                '$set' => array(
                    'value' => $value
                )
            ));
        }
    }

    /**
     * @desc 获取falg
     * @param string $falg
     * @author YangLong
     * @date 2015-08-12
     * @return mixed|boolean
     */
    public static function getfalg($falg)
    {
        $result = iMongo::getInstance()->setCollection(__CLASS__)->findOne(array(
            'falg' => $falg
        ), array(
            'value'
        ));
        if (! empty($result)) {
            return $result['value'];
        } else {
            return false;
        }
    }
}