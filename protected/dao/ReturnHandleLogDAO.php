<?php

/**
 * @desc Return处理记录表
 * @author liaojianwen
 * @date 2015-06-30
 */
class ReturnHandleLogDAO extends BaseDAO
{

    /**
     * @desc 对象实例重用
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-06-30
     * @return ReturnHandleLogDAO
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::createInstance($className);
    }

    /**
     * @desc 构造方法
     * @author liaojianwen
     * @date 2015-06-30
     */
    public function __construct()
    {
        $this->dbConnection = Yii::app()->db;
        $this->dbCommand = $this->dbConnection->createCommand();
        $this->tableName = 'return_handle_log';
        $this->primaryKey = 'return_handle_log_id';
        $this->created = 'create_time';
        $this->shop = 'shop';
    }

    /**
     * @desc 查找处理人、处理方式
     * @param $returnid
     * @author liaojianwen
     * @date 2015-07-24
     * @return unknown
     */
    public function getReturnOperator($returnid)
    {
        $selects = 'return_id,handle_type,responseText,handle_user';
        $condition = 'return_id = :return_id';
        $param = array(
            ':return_id' => $returnid
        );
        $result = $this->dbCommand->reset()
            ->select($selects)
            ->from($this->tableName)
            ->where($condition, $param)
            ->queryAll();
        foreach ($result as &$value) {
            $value['note_md5'] = md5(trim($value['responseText']));
            switch ($value['handle_type']) {
                case 'APPROVE_REQUEST':
                    $value['handle_type_md5'] = md5(trim('SELLER_APPROVE_REQUEST'));
                    break;
                case 'APPROVE_RMA':
                    $value['hanle_type_md5'] = md5(trim('SELLER_PROVIDE_RMA'));
                    break;
                case 'issueReturnRefund':
                    $value['handle_type_md5'] = md5(trim('SELLER_ISSUE_REFUND')); // @todo
                    break;
                case 'issueReturnPartRefund':
                    $value['handle_type_md5'] = md5(trim('SELLER_OFFER_PARTIAL_REFUND'));
                    break;
                case 'sendReturnMsg':
                    $value['handle_type_md5'] = md5(trim('SELLER_SEND_MESSAGE'));
                    break;
                case 'returnAskHelp':
                    $value['handle_type_md5'] = md5(trim('SELLER_ESCALATE')); // @todo
                    break;
            }
        }
        return $result;
    }
}