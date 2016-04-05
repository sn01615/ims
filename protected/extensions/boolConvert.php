<?php

/**
 * @desc bool值转换
 * @author YangLong
 * @date 2012-04-01
 */
class boolConvert
{

    /**
     * @desc 将值转换为0/1
     * @param mixed $val
     * @author YangLong
     * @date 2015-04-01
     * @return number
     */
    public static function toInt01($val)
    {
        $val = strtolower($val);
        if (empty($val) || $val == 'false' || $val == 'no') {
            return 0;
        } else {
            return 1;
        }
    }

    /**
     * @desc 将值转换为0/1
     * @param mixed $val
     * @author YangLong
     * @date 2015-09-19
     * @return number
     */
    public static function toStr01($val)
    {
        $val = strtolower($val);
        if (empty($val) || $val == 'false' || $val == 'no') {
            return '0';
        } else {
            return '1';
        }
    }
}