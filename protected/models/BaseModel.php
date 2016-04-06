<?php

/**
 * @desc table model base class
 * @author Weixun Luo 
 * @date 2014-10-10
 */
abstract class BaseModel
{

    private static $_models = array();
    // class name => model
    
    /**
     * @desc Returns the static model of the specified table model class.
     * @author Weixun Luo
     * @param string $className 需要实例化的类名
     * @return static table model instance.
     * @date 2014-10-10
     */
    public static function model($className = __CLASS__)
    {
        if (isset(self::$_models[$className])) {
            return self::$_models[$className];
        } else {
            $model = self::$_models[$className] = new $className(null);
            return $model;
        }
    }

    /**
     * @desc API格式处理,列表格式:'Body'=>array('list'=>'','count'=>'','page'=>array('page'=>'','pageSize'=>'')), 内容格式:'Body'=>array('content'=>'')
     * @param string $ack 状态值;Success:成功;Failure:失败
     * @param array $body 数据主体(除Ack为Failure外)
     * @param string $error 错误信息
     * @author heguangquan
     * @date 2015-03-12
     * @modify YangLong 2015-07-28 增加警告
     * @return array 结果
     */
    public function handleApiFormat($ack, $body = array(), $error = '')
    {
        if ($ack != EnumOther::ACK_SUCCESS && $ack != EnumOther::ACK_FAILURE && $ack != EnumOther::ACK_WARNING) {
            return false;
        }
        $apiResult = array();
        $apiResult['Ack'] = $ack;
        if ($ack === EnumOther::ACK_FAILURE) {
            $apiResult['Error'] = $error;
        } elseif ($ack === EnumOther::ACK_WARNING) {
            $apiResult['Body'] = $body;
            $apiResult['Error'] = $error;
        } else {
            $apiResult['Body'] = $body;
        }
        $apiResult['GmtTimeStamp'] = gmdate(DATE_ISO8601);
        $apiResult['LocalTimeStamp'] = date(DATE_ISO8601);
        return $apiResult;
    }
}