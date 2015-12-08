<?php

/**
 * @desc Case处理记录表
 * @author lvjianfei
 * @date 2015-04-20
 */
class CaseHandleLogDAO extends BaseDAO
{
    
    
    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author lvjianfei
     * @date 2015-04-20
     * @return CaseHandleLogDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author lvjianfei
     * @date 2015-04-20
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'case_handle_log';
        $this->primaryKey = 'case_log_id';
        $this->created = 'create_time';
        $this->shop = 'shop';
    }
    
    /**
     * @desc 获取我的处理记录
     * @param caseid case的id
     * @return array
     * @author lvjianfei
     * @date 2015-04-20
     */
    public function getCaseHandleLog($caseid)
    {
        $selects = 'case_id,handle_type,carrier,trackingNum,amount,reason,shipdate,create_time,handle_user';
        $condition = 'case_id = :case_id';
        $params = array(
            ':case_id' => $caseid
        );
        $result = $this->dbCommand->reset()
            ->select($selects)
            ->from($this->tableName)
            ->where($condition, $params)
            ->order('case_log_id desc')
            ->queryAll();
        return $result;
    }
    
    /**
     * @desc 查找处理人、处理方式
     * @param $caseid
     * @author liaojianwen
     * @date 2015-07-15
     */
    public function getCaseOperator($caseid)
    {
        $selects = 'case_id,handle_type,responseText,handle_user';
        $condition = 'case_id = :case_id';
        $param = array(
            ':case_id'=>$caseid
        );
        $result = $this->dbCommand->reset()
                    ->select($selects)
                    ->from($this->tableName)
                    ->where($condition,$param)
                    ->queryAll();
       foreach($result as &$value){
           $value['note_md5'] = md5(trim($value['responseText']));
           switch($value['handle_type']){
               case 'addResponse':
                   $value['handle_type_md5'] = md5(trim('Seller offered another solution.'));
                   break;
               case 'addTrackingInfo':
                   $value['hanle_type_md5'] = md5(trim('Seller provided tracking information for shipment.'));
                   break;
               case 'addShippingInfo' :
                   $value['handle_type_md5'] = md5(trim('Seller provided shipping information.'));//@todo
                   break;
               case 'fullRefund' :
                    $value['handle_type_md5'] = md5(trim('Seller issued full refund to buyer.'));
                   break;
               case 'partialRefund' :
                    $value['handle_type_md5'] = md5(trim('Seller issued partial refund to buyer.'));
                   break;
               case 'ebayHelp':
                    $value['handle_type_md5'] = md5(trim(''));//@todo
                   break;
               case 'returnItemRefund' :
                    $value['handle_type_md5'] = md5(trim(''));////@todo
                   break;
               case 'provideReturnInfo' :
                   $value['handle_type_md5'] = md5(trim(''));////@todo
                   break;
           }
       }
       return $result;
                    
    
    
    }
    
}