<?php

/**
 * @desc 自动标签
 * @author YangLong
 * @date 2015-11-07
 */
class AutoLabelModel extends BaseModel
{
    
    /**
     * @desc 覆盖父方法,返回当前类的(单)实例
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-11-07
     * @return AutoLabelModel
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
    
    /**
     * @desc 获取消息的自动标签
     * @param int $msgid
     * @param int $error CURL错误信息
     * @author YangLong
     * @date 2015-11-07
     * @return mixed
     */
    private function getMsgAutoLabel($msgid, &$error)
    {
        $url = Yii::app()->params['TopicApiUrl'];
        $url .= '?msg_id=' . $msgid;
        return getByCurl::get($url, $error);
    }
    
    /**
     * @desc 定时获取消息的自动标签
     * @author YangLong
     * @date 2015-11-07
     * @return mixed
     */
    public function updateMsgAutoLabel()
    {
        DaemonLockTool::lock(__METHOD__);
        
        $startTime = time();
        
        label1:
        
        if (time() - $startTime > 588) {
            return null;
        }
        
        // get data
        $columns = array(
            'msg_id',
            'msg_text_resolve_id'
        );
        $conditions = 'label_parse=\'0\'';
        $params = array();
        $joinArray = array();
        $tableAlias = '';
        $order = 'msg_text_resolve_id desc';
        $limit = 10;
        $offset = null;
        $option = '';
        $groups = '';
        $msgids = MsgTextResolveDAO::getInstance()->iselect($columns, $conditions, $params, true, $joinArray, $tableAlias, $order, $limit, $offset, $option, $groups);
        
        foreach ($msgids as $value) {
            $result = $this->getMsgAutoLabel($value['msg_id'], $error);
            $result = json_decode($result, true);
            if (is_array($result) && $result['Ack'] == true && is_array($result['Body'])) {
                foreach ($result['Body'] as $val) {
                    $columns = array(
                        'auto_label_name' => $val['Topic']
                    );
                    $conditions = 'auto_label_name=:auto_label_name';
                    $params = array(
                        ':auto_label_name' => $val['Topic']
                    );
                    $msg_auto_label_id = MsgAutoLabelDAO::getInstance()->ireplaceinto($columns, $conditions, $params, true);
                    
                    $columns = array(
                        'msg_id' => $value['msg_id'],
                        'msg_auto_label_id' => $msg_auto_label_id,
                        'Probability' => $val['$val']
                    );
                    $conditions = 'msg_id=:msg_id and msg_auto_label_id=:msg_auto_label_id';
                    $params = array(
                        ':msg_id' => $value['msg_id'],
                        ':msg_auto_label_id' => $msg_auto_label_id
                    );
                    MsgAutoLabelRefDAO::getInstance()->ireplaceinto($columns, $conditions, $params);
                }
            }
        }
    }
    
}
