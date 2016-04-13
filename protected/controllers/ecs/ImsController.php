<?php

/**
 * @desc IMS调用接口
 * @author YangLong
 * @date 2015-04-27
 */
class ImsController extends Controller
{

    /**
     * @desc 默认方法
     * @author YangLong
     * @date 2015-04-27
     */
    public function actionIndex()
    {}

    /**
     * @desc IMS和ECS之间通信的验证
     * @author YangLong
     * @date 2015-03-30
     */
    private function imsEcsCheck()
    {
        $key = CInputFilter::getString('key');
        if (! imsTokenTool::getInstance()->verifyKey($key)) {
            $result = array(
                'Ack' => 'Failure',
                'body' => 'verify error.'
            );
            $this->renderJson($result);
            Yii::app()->end(); // safe
        }
    }

    /**
     * @desc IMS获取msg下载数据
     * @author heguangquan,YangLong
     * @date 2015-04-27
     */
    public function actionGetMsgDownsData()
    {
        $this->imsEcsCheck();
        
        $taskNumber = 50; // TODO枚举化
        $result = ImsjobsModel::model()->getMsgDownsData($taskNumber);
        $this->renderJson($result);
    }

    /**
     * @desc IMS删除msg下载数据
     * @author heguangquan,YangLong
     * @date 2015-01-29
     */
    public function actionDelMsgDownsData()
    {
        $this->imsEcsCheck();
        
        $strId = CInputFilter::getString('ids');
        $result = ImsjobsModel::model()->delMsgDownsData($strId);
        $this->renderJson($result);
    }

    /**
     * @desc 接收IMS推送的回复队列数据，并入库
     * @author lvjianfei,YangLong,zhanwei
     * @date 2015-03-04
     */
    public function actionGenerateReplyMsgQueue()
    {
        $this->imsEcsCheck();
        
        $replyInfo = CInputFilter::getString('replyInfo');
        $replyInfo = json_decode(base64_decode($replyInfo), true);
        $replyInfo['isSendEmail'] = boolConvert::toInt01($replyInfo['isSendEmail']);
        $replyInfo['imgUrl'] = json_encode($replyInfo['imgUrl']);
        
        $result = ImsjobsModel::model()->generateReplyMsgQueue($replyInfo);
        $this->renderJson($result);
    }

    /**
     * @desc 生成队列
     * @author zhanwei,YangLong
     * @date 2015-04-27
     */
    public function actionGenerateDownQueue()
    {
        $msgDownQueueJson = CInputFilter::getString('MsgDownQueueJson');
        
        $authKey = CInputFilter::getString('AuthKey');
        $result = ImsjobsModel::model()->generateDownQueue($msgDownQueueJson, $authKey);
        $this->renderJson($result);
    }
}