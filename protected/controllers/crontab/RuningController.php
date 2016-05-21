<?php

/**
 * @desc 信息下载、解释队列处理
 * @author YangLong
 * @date 2015-03-02
 */
class RuningController extends Controller
{

    /**
     * @desc 默认方法
     * @author YangLong
     * @date 2015-02-13
     */
    public function actionIndex()
    {
        $key = 'eBaySessionCount';
        echo iMemcache::getInstance()->get($key);
    }

    /**
     * @desc jobs 001
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs001()
    {
        // CASE处理
        $this->executeCaseUpload();
    }

    /**
     * @desc jobs 002
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs002()
    {
        // OPEN CASE更新
        $this->generateCaseUpdateQueue();
    }

    /**
     * @desc jobs 003
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs003()
    {
        $this->executeReturnDownQueue(); // 运行return执行队列
    }

    /**
     * @desc jobs 004
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs004()
    {
        // 下载
        $this->generateCaseDownQueue();
    }

    /**
     * @desc jobs 005
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs005()
    {
        // 解析return
        $this->parseReturns();
    }

    /**
     * @desc jobs 006
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs006()
    {
        // 下载队列生成
        $this->generateMsgDownQueue();
    }

    /**
     * @desc jobs 007
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs007()
    {
        // return 处理
        $this->executeReturnUpload();
    }

    /**
     * @desc jobs 008
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs008()
    {
        // Feedback
        $this->generateFeedbackUpdateQueue();
    }

    /**
     * @desc jobs 009
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs009()
    {
        // Feedback
        $this->executeFeedbackUpdateQueue();
    }

    /**
     * @desc jobs 010
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs010()
    {
        // 解析
        $this->parseFeedback();
    }

    /**
     * @desc jobs 011
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs011()
    {
        // 状态更新
        $this->generateCaseUpdateStatusQueue();
    }

    /**
     * @desc jobs 012
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs012()
    {
        // 状态更新
        $this->executeCaseUpdateStatusQueue();
    }

    /**
     * @desc jobs 013
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs013()
    {
        // 解析Case状态更新数据
        $this->parseCasesStatus();
    }

    /**
     * @desc jobs 014
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs014()
    {
        // 升级最新消息的一些状态信息（无队列）(收件箱)
        $this->updateMsgByList();
    }

    /**
     * @desc jobs 015
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs015()
    {
        // 生成订单下载队列
        $this->generateEbayOrdersDownQueue();
    }

    /**
     * @desc jobs 016
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs016()
    {
        $this->executeReplyQueue(); // 运行回复队列
    }

    /**
     * @desc jobs 017
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs017()
    {
        // 下载return
        $this->generateReturnDownQueue();
    }

    /**
     * @desc jobs 018
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs018()
    {
        // v1解析
        $this->parseMessagesV1();
    }

    /**
     * @desc jobs 019
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs019()
    {
        // Case解析
        $this->parseCases();
    }

    /**
     * @desc jobs 020
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs020()
    {
        // 更新return
        $this->executeReturnUpdateQueue();
    }

    /**
     * @desc jobs 021
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs021()
    {
        // excute orders download queue
        $this->executeEbayOrdersDownQueue();
    }

    /**
     * @desc jobs 022
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs022()
    {
        // 解析
        $this->parseEbayOrders();
    }

    /**
     * @desc jobs 023
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs023()
    {
        // 下载队列运行
        $this->executeMsgDownQueueXml();
    }

    /**
     * @desc jobs 024
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs024()
    {
        // OPEN CASE更新
        $this->executeCaseUpdateQueue();
    }

    /**
     * @desc jobs 025
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs025()
    {
        // 下载
        $this->executeCaseDownQueue();
    }

    /**
     * @desc jobs 026
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs026()
    {
        // 生成ebay listing 下载队列
        $this->generateEbayListingQueue();
    }

    /**
     * @desc jobs 027
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs027()
    {
        // 下载ebay listing
        $this->executeEbayListingQueue();
    }

    /**
     * @desc jobs 028
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs028()
    {
        // 解析ebay listing
        $this->parseEbayListing();
    }

    /**
     * @desc jobs 029
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs029()
    {
        // disputes下载队列生成
        $this->generateDisputesDownQueue();
    }

    /**
     * @desc jobs 030
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs030()
    {
        // return更新队列
        $this->generateReturnUpdateQueue();
    }

    /**
     * @desc jobs 031
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs031()
    {
        // 用户信息更新
        $this->getUserInfo();
    }

    /**
     * @desc jobs 032
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs032()
    {
        $this->executeQueue();
    }

    /**
     * @desc jobs 033
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs033()
    {
        $this->parseDisputes();
    }

    /**
     * @desc jobs 034
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs034()
    {
        $this->executeFeedbackUpload();
    }

    /**
     * @desc jobs 035
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs035()
    {
        $this->uploadImage();
    }

    /**
     * @desc jobs 036
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs036()
    {
        $this->sendSyncRun();
    }

    /**
     * @desc jobs 037
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs037()
    {
        $this->updateTracking();
    }

    /**
     * @desc jobs 038
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs038()
    {
        $this->updatePackages();
    }

    /**
     * @desc jobs 039
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs039()
    {}

    /**
     * @desc jobs 040
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs040()
    {}

    /**
     * @desc jobs 041
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs041()
    {}

    /**
     * @desc jobs 042
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs042()
    {}

    /**
     * @desc jobs 043
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs043()
    {}

    /**
     * @desc jobs 044
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs044()
    {}

    /**
     * @desc jobs 045
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs045()
    {}

    /**
     * @desc jobs 046
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs046()
    {}

    /**
     * @desc jobs 047
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs047()
    {}

    /**
     * @desc jobs 048
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs048()
    {}

    /**
     * @desc jobs 049
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs049()
    {}

    /**
     * @desc jobs 050
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs050()
    {}

    /**
     * @desc jobs 051
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs051()
    {}

    /**
     * @desc jobs 052
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs052()
    {}

    /**
     * @desc jobs 053
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs053()
    {}

    /**
     * @desc jobs 054
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs054()
    {}

    /**
     * @desc jobs 055
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs055()
    {}

    /**
     * @desc jobs 056
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs056()
    {}

    /**
     * @desc jobs 057
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs057()
    {}

    /**
     * @desc jobs 058
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs058()
    {}

    /**
     * @desc jobs 059
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs059()
    {}

    /**
     * @desc jobs 060
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs060()
    {
        $this->sendMailLog();
    }

    /**
     * @desc jobs 061
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs061()
    {}

    /**
     * @desc jobs 062
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs062()
    {}

    /**
     * @desc jobs 063
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs063()
    {}

    /**
     * @desc jobs 064
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs064()
    {}

    /**
     * @desc jobs 065
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs065()
    {}

    /**
     * @desc jobs 066
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs066()
    {}

    /**
     * @desc jobs 067
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs067()
    {}

    /**
     * @desc jobs 068
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs068()
    {}

    /**
     * @desc jobs 069
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs069()
    {}

    /**
     * @desc jobs 070
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs070()
    {
        $this->clearMongoDB();
    }

    /**
     * @desc jobs 071
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs071()
    {}

    /**
     * @desc jobs 072
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs072()
    {}

    /**
     * @desc jobs 073
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs073()
    {}

    /**
     * @desc jobs 074
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs074()
    {}

    /**
     * @desc jobs 075
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs075()
    {}

    /**
     * @desc jobs 076
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs076()
    {
        $this->geteBayDetails();
    }

    /**
     * @desc jobs 077
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs077()
    {
        $this->sendTongJi();
    }

    /**
     * @desc jobs 078
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs078()
    {}

    /**
     * @desc jobs 079
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs079()
    {}

    /**
     * @desc jobs 080
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs080()
    {}

    /**
     * @desc jobs 081
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs081()
    {}

    /**
     * @desc jobs 082
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs082()
    {}

    /**
     * @desc jobs 083
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs083()
    {}

    /**
     * @desc jobs 084
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs084()
    {}

    /**
     * @desc jobs 085
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs085()
    {}

    /**
     * @desc jobs 086
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs086()
    {}

    /**
     * @desc jobs 087
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs087()
    {}

    /**
     * @desc jobs 088
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs088()
    {}

    /**
     * @desc jobs 089
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs089()
    {}

    /**
     * @desc jobs 090
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs090()
    {}

    /**
     * @desc jobs 091
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs091()
    {}

    /**
     * @desc jobs 092
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs092()
    {}

    /**
     * @desc jobs 093
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs093()
    {}

    /**
     * @desc jobs 094
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs094()
    {}

    /**
     * @desc jobs 095
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs095()
    {}

    /**
     * @desc jobs 096
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs096()
    {}

    /**
     * @desc jobs 097
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs097()
    {}

    /**
     * @desc jobs 098
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs098()
    {}

    /**
     * @desc jobs 099
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs099()
    {}

    /**
     * @desc jobs 100
     * @author YangLong
     * @date 2015-07-01
     */
    public function actionImsJobs100()
    {}

    /**
     * @desc 生成Case状态更新队列
     * @author YangLong
     * @date 2015-05-26
     */
    private function generateCaseUpdateStatusQueue()
    {
        CaseUpdateModel::model()->generateCaseUpdateStatusQueue();
    }

    /**
     * @desc 运行Case状态更新队列
     * @author YangLong
     * @date 2015-05-26
     */
    private function executeCaseUpdateStatusQueue()
    {
        CaseUpdateModel::model()->executeCaseUpdateStatusQueue();
    }

    /**
     * @desc 生成Open Case更新(down)队列
     * @author YangLong
     * @date 2015-04-20
     */
    private function generateCaseUpdateQueue()
    {
        CaseUpdateModel::model()->generateCaseUpdateQueue();
    }

    /**
     * @desc 运行Open Case更新(down)队列
     * @author YangLong
     * @date 2015-04-20
     */
    private function executeCaseUpdateQueue()
    {
        CaseUpdateModel::model()->executeCaseUpdateQueue();
    }

    /**
     * @desc 运行Case处理队列
     * @author YangLong
     * @date 2015-04-16
     */
    private function executeCaseUpload()
    {
        CaseUploadModel::model()->executeCaseUpload();
    }

    /**
     * @desc 生成下载队列
     * @author YangLong
     * @date 2015-02-13
     */
    private function generateMsgDownQueue()
    {
        MsgQueueModel::model()->generateMsgDownQueue();
    }

    /**
     * @desc 生成CASE下载队列
     * @author YangLong
     * @date 2015-03-27
     */
    private function generateCaseDownQueue()
    {
        CaseDownModel::model()->generateCaseDownQueue();
    }

    /**
     * @desc 运行CASE下载队列
     * @author YangLong
     * @date 2015-03-27
     */
    private function executeCaseDownQueue()
    {
        CaseDownModel::model()->executeCaseDownQueue();
    }

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
     * @desc 获取Case状态原始XML数据
     * @author YangLong
     * @date 2015-05-26
     */
    public function actionGetCasesStatus()
    {
        $this->imsEcsCheck();
        
        $result = CaseUpdateModel::model()->getCasesStatus();
        $this->renderJson($result);
    }

    /**
     * @desc 删除Case状态原始XML数据
     * @author YangLong
     * @date 2015-05-26
     */
    public function actionDeleteCasesStatus()
    {
        $this->imsEcsCheck();
        
        $ids = CInputFilter::getnorepeatInts('ids');
        $result = CaseUpdateModel::model()->deleteCasesStatus($ids);
        $this->renderJson($result);
    }

    private function parseCasesStatus()
    {
        // 配置
        // $homeUrl = Yii::app()->params['ecs_api_url'];
        $homeUrl = Yii::app()->params['home_url'];
        
        // 获取
        $key = imsTokenTool::getInstance()->getToken();
        $url = "{$homeUrl}?r=crontab/Runing/GetCasesStatus&key={$key}";
        $result = getByCurl::get($url);
        
        // 解析
        $resultArr = CaseUpdateModel::model()->parseCasesStatus($result);
        
        // 删除
        if (! empty($resultArr)) {
            $strId = implode(',', $resultArr);
            $key = imsTokenTool::getInstance()->getToken();
            $url = "{$homeUrl}?r=crontab/Runing/DeleteCasesStatus&ids={$strId}&key={$key}";
            $result = getByCurl::get($url);
        }
    }

    /**
     * @desc IMS数据解析接口
     * @author YangLong
     * @date 2015-03-30
     */
    private function parseCases()
    {
        CaseModel::model()->parseCases();
    }

    /**
     * @desc 生成下载feedback 队列
     * @author liaojianwen
     * @date 2015-05-19
     */
    private function generateFeedbackUpdateQueue()
    {
        FeedbackUpdateModel::model()->generateFeedbackUpdateQueue(); // 生成队列
    }

    /**
     * @desc 执行下载feedback 队列
     * @author liaojianwen
     * @date 2015-05-19
     */
    private function executeFeedbackUpdateQueue()
    {
        $runcount = 0;
        label:
        FeedbackUpdateModel::model()->executeFeedbackUpdateQueue(); // 执行队列
        if ($runcount < 10) {
            $runcount ++;
            goto label;
        }
    }

    /**
     * @desc 获取和锁定feedback原始下载数据(XML)
     * @author liaojianwen
     * @date 2015-05-19
     */
    public function actionGetFeedback()
    {
        $this->imsEcsCheck();
        
        $result = FeedbackDownModel::model()->getFeedbackDownData(EnumOther::FEEDBACK_PARSE_SIZE);
        if ($result !== false) {
            $result = array(
                'Ack' => 'Success',
                'body' => $result
            );
        } else {
            $result = array(
                'Ack' => 'Failure',
                'body' => ''
            );
        }
        $this->renderJson($result);
    }

    /**
     * @desc 消息解析V1
     * @author YangLong
     * @date 2015-07-02
     * @018
     */
    private function parseMessagesV1()
    {
        // v1解析
        MsgDownModel::model()->parseMessagesV1();
    }

    /**
     * @desc 删除已经解析了的feedback数据
     * @author liaojianwen
     * @date 2015-05-19
     */
    public function actionDeleteFeedback()
    {
        $this->imsEcsCheck();
        
        $ids = CInputFilter::getnorepeatInts('ids');
        $result = FeedbackDownModel::model()->deleteFeedbackDownData($ids);
        if ($result !== false) {
            $result = array(
                'Ack' => 'Success',
                'body' => $result
            );
        } else {
            $result = array(
                'Ack' => 'Failure',
                'body' => ''
            );
        }
        $this->renderJson($result);
    }

    /**
     * @desc feedback数据解析接口
     * @author liaojianwen
     * @date 2015-05-19
     */
    private function parseFeedback()
    {
        $homeUrl = Yii::app()->params['home_url'];
        $key = imsTokenTool::getInstance()->getToken();
        $data = getByCurl::get("{$homeUrl}?r=crontab/Runing/GetFeedback&key={$key}");
        $data = json_decode($data, true);
        $result = FeedbackDownModel::model()->parseFeedback($data);
        if ($result !== false && is_array($result)) {
            $result = implode(',', $result);
            getByCurl::get("{$homeUrl}?r=crontab/Runing/DeleteFeedback&key={$key}&ids=" . $result);
        }
    }

    /**
     * @desc 升级最新最多200条消息的一些状态信息（无队列）(收件箱)
     * @author YangLong
     * @date 2015-06-02
     */
    private function updateMsgByList()
    {
        MsgDealModel::model()->updateMsgByList();
    }

    /**
     * @desc 新的队列运行机制
     * @author YangLong
     * @date 2015-06-11
     * @023
     */
    private function executeMsgDownQueueXml()
    {
        MsgDownModel::model()->executeMsgDownQueueXml();
    }

    /**
     * @desc 获取用户信息
     * @author YangLong
     * @date 2015-06-12
     */
    private function getUserInfo()
    {
        MsgDownModel::model()->getUserInfo();
    }

    /**
     * @desc 生成订单下载队列
     * @author YangLong
     * @date 2015-06-13
     */
    private function generateEbayOrdersDownQueue()
    {
        EbayOtherInfoModel::model()->generateEbayOrdersDownQueue();
    }

    /**
     * @desc 运行订单下载队列
     * @author YangLong
     * @date 2015-06-14
     */
    private function executeEbayOrdersDownQueue()
    {
        EbayOtherInfoModel::model()->executeEbayOrdersDownQueue();
    }

    /**
     * @desc 解析订单下载的数据
     * @author YangLong
     * @date 2015-06-14
     * @022
     */
    private function parseEbayOrders()
    {
        EbayOtherInfoModel::model()->parseEbayOrders();
    }

    /**
     * @desc 生成return下载队列
     * @author liaojianwen
     * @date 2015-06-18
     */
    private function generateReturnDownQueue()
    {
        ReturnDownModel::model()->generateReturnDownQueue();
    }

    /**
     * @desc 执行return下载队列
     * @author liaojianwen
     * @date 2015-06-18
     */
    private function executeReturnDownQueue()
    {
        ReturnDownModel::model()->executeReturnDownQueue();
    }

    /**
     * @desc 获取和锁定Return原始下载数据(XML)
     * @author liaojianwen
     * @date 2015-06-16
     */
    public function actionGetReturns()
    {
        $this->imsEcsCheck();
        
        $result = ReturnDownModel::model()->getDownloadData(EnumOther::RETURN_PARSE_SIZE);
        if ($result !== false) {
            $result = array(
                'Ack' => 'Success',
                'body' => $result
            );
        } else {
            $result = array(
                'Ack' => 'Failure',
                'body' => ''
            );
        }
        $this->renderJson($result);
    }

    /**
     * @desc return数据解析接口
     * @author liaojianwen
     * @date 2015-06-16
     */
    private function parseReturns()
    {
        DaemonLockTool::lock(__METHOD__);
        
        $startTime = time();
        
        label1:
        if (time() - $startTime > 590) {
            return false;
        }
        
        $homeUrl = Yii::app()->params['home_url'];
        $key = imsTokenTool::getInstance()->getToken();
        $returns = getByCurl::get("{$homeUrl}?r=crontab/Runing/GetReturns&key={$key}");
        $returns = json_decode($returns, true);
        $result = ReturnModel::model()->parseReturns($returns);
        if ($result !== false && is_array($result) && ! empty($result)) {
            $result = implode(',', $result);
            getByCurl::get("{$homeUrl}?r=crontab/Runing/DeleteReturns&key={$key}&ids=" . $result);
            goto label1;
        }
        
        sleep(15);
        goto label1;
    }

    /**
     * @desc 删除returns状态原始XML数据
     * @author liaojianwen
     * @date 2015-06-16
     */
    public function actionDeleteReturns()
    {
        $this->imsEcsCheck();
        
        $ids = CInputFilter::getnorepeatInts('ids');
        $result = ReturnDownModel::model()->deleteReturnDownData($ids);
        if ($result !== false) {
            $result = array(
                'Ack' => 'Success',
                'body' => $result
            );
        } else {
            $result = array(
                'Ack' => 'Failure',
                'body' => $result
            );
        }
        $this->renderJson($result);
    }

    /**
     * @desc 执行return处理
     * @author liaojianwen
     * @date 2015-07-02
     */
    private function executeReturnUpload()
    {
        ReturnUploadModel::model()->executeReturnUpload();
    }

    /**
     * @desc return 更新队列生成
     * @author liaojianwen
     * @date 2015-07-02
     */
    private function generateReturnUpdateQueue()
    {
        ReturnUpdateModel::model()->generateReturnUpdateQueue();
    }

    /**
     * @desc return 更新队列执行
     * @author liaojianwen
     * @date 2015-07-02
     */
    private function executeReturnUpdateQueue()
    {
        ReturnUpdateModel::model()->executeReturnUpdateQueue();
    }

    /**
     * @desc 运行回复队列
     * @author lvjianfei
     * @date 2015-04-27
     */
    private function executeReplyQueue()
    {
        ImsjobsModel::model()->executeReplyQueue();
    }

    /**
     * @desc 获取并更新物流服务枚举信息
     * @author YangLong
     * @date 2015-07-23
     */
    private function geteBayDetails()
    {
        EbayOtherInfoModel::model()->geteBayDetails();
    }

    /**
     * @desc 生成ebay listing 队列
     * @author liaojianwen
     * @date 2015-07-27
     */
    private function generateEbayListingQueue()
    {
        EbayListingModel::model()->generateEbayListingQueue();
    }

    /**
     * @desc 下载listing 原始数据
     * @author liaojianwen
     * @date 2015-07-27
     */
    private function executeEbayListingQueue()
    {
        EbayListingModel::model()->executeEbayListingQueue();
    }

    /**
     * @desc 获取和锁定Listing原始下载数据(XML)
     * @author liaojianwen
     * @date 2015-07-28
     */
    public function actionGetEbayListing()
    {
        $this->imsEcsCheck();
        
        $result = EbayListingDownModel::model()->getListingDownData(EnumOther::LISTING_PARSE_SIZE);
        if ($result !== false) {
            $result = array(
                'Ack' => 'Success',
                'body' => $result
            );
        } else {
            $result = array(
                'Ack' => 'Failure',
                'body' => ''
            );
        }
        $this->renderJson($result);
    }

    /**
     * @desc解析listing 信息
     * @author liaojianwen
     * @date 2015-07-28
     */
    private function parseEbayListing()
    {
        $homeUrl = Yii::app()->params['home_url'];
        $key = imsTokenTool::getInstance()->getToken();
        $listingData = getByCurl::get("{$homeUrl}?r=crontab/Runing/GetEbayListing&key={$key}");
        $listingData = json_decode($listingData, true);
        $result = EbayListingModel::model()->parseEbayListing($listingData);
        if ($result !== false && is_array($result)) {
            $result = implode(',', $result);
            getByCurl::get("{$homeUrl}?r=crontab/Runing/DeleteEbayListing&key={$key}&ids=" . $result);
        }
    }

    /**
     * @desc 删除returns状态原始XML数据
     * @author liaojianwen
     * @date 2015-07-28
     */
    public function actionDeleteEbayListing()
    {
        $this->imsEcsCheck();
        
        $ids = CInputFilter::getnorepeatInts('ids');
        $result = EbayListingDownModel::model()->deleteListingDownData($ids);
        if ($result !== false) {
            $result = array(
                'Ack' => 'Success',
                'body' => $result
            );
        } else {
            $result = array(
                'Ack' => 'Failure',
                'body' => $result
            );
        }
        $this->renderJson($result);
    }

    /**
     * @desc 生成Disputes下载队列
     * @author YangLong
     * @date 2015-08-11
     */
    private function generateDisputesDownQueue()
    {
        DisputesModel::model()->generateDisputesDownQueue();
    }

    /**
     * @desc 守护队列
     * @author YangLong
     * @date 2015-08-13
     */
    private function executeQueue()
    {
        QueueTracerModel::daemon();
    }

    /**
     * @desc 获取和锁定Dispute原始下载数据(XML)
     * @author liaojianwen
     * @date 2015-06-16
     */
    public function actionGetDisputes()
    {
        $this->imsEcsCheck();
        
        $result = DisputesModel::model()->getDownloadDisputes(EnumOther::DISPUTES_PARSE_SIZE);
        if ($result !== false) {
            $result = array(
                'Ack' => 'Success',
                'body' => $result
            );
        } else {
            $result = array(
                'Ack' => 'Failure',
                'body' => ''
            );
        }
        $this->renderJson($result);
    }

    /**
     * @desc return数据解析接口
     * @author liaojianwen
     * @date 2015-06-16
     */
    private function parseDisputes()
    {
        set_time_limit(120);
        $i = 0;
        while (true) {
            
            $homeUrl = Yii::app()->params['home_url'];
            $key = imsTokenTool::getInstance()->getToken();
            $disputes = getByCurl::get("{$homeUrl}?r=crontab/Runing/GetDisputes&key={$key}");
            $disputes = json_decode($disputes, true);
            $result = DisputesModel::model()->parseDisputes($disputes);
            if ($result !== false && is_array($result) && ! empty($result)) {
                $result = implode(',', $result);
                getByCurl::get("{$homeUrl}?r=crontab/Runing/DeleteDisputes&key={$key}&ids=" . $result);
            }
            
            $i ++;
            if ($i > 10000 || $disputes['Ack'] != 'Success') {
                break;
            }
            sleep(1);
        }
    }

    /**
     * @desc 删除returns状态原始XML数据
     * @author liaojianwen
     * @date 2015-06-16
     */
    public function actionDeleteDisputes()
    {
        $this->imsEcsCheck();
        
        $ids = CInputFilter::getnorepeatInts('ids');
        $result = DisputesModel::model()->deleteDisputesDownData($ids);
        if ($result !== false) {
            $result = array(
                'Ack' => 'Success',
                'body' => $result
            );
        } else {
            $result = array(
                'Ack' => 'Failure',
                'body' => $result
            );
        }
        $this->renderJson($result);
    }

    /**
     * @desc 清除mongodb历史日志
     * @author YangLong
     * @date 2015-08-24
     */
    private function clearMongoDB()
    {
        // recount msglabel count.
        $columns = array(
            'msg_label_id'
        );
        $conditions = 'true';
        $labelIds = MsgLabelDAO::getInstance()->iselect($columns, $conditions, array());
        foreach ($labelIds as $labelId) {
            $text = 'SELECT
    COUNT(*)
FROM
    msg_label_ref AS mlr
        JOIN
    msg AS m ON mlr.msg_id = m.msg_id
WHERE
    msg_label_id = :msg_label_id
        AND m.aggregate_hide = 0';
            $params = array(
                ':msg_label_id' => $labelId['msg_label_id']
            );
            $count = MsgLabelRefDAO::getInstance()->setTextQuery($text, $params, 'queryScalar');
            
            $columns = array(
                'msg_count' => $count
            );
            $conditions = 'msg_label_id=:msg_label_id';
            $params = array(
                ':msg_label_id' => $labelId['msg_label_id']
            );
            MsgLabelDAO::getInstance()->iupdate($columns, $conditions, $params);
        }
        
        // mongdb clear.
        
        $clearArray = array(
            'getUserInfoNoSuccess' => array(
                'holdtime' => 3600
            ),
            'msgListInsert' => array(
                'holdtime' => 600
            ),
            'pCaseDetail' => array(
                'holdtime' => 600
            ),
            'pCaseDetailInsert' => array(
                'holdtime' => 600
            ),
            'v1MsgDownXml' => array(
                'holdtime' => 3600
            ),
            'v1details' => array(
                'holdtime' => 3600
            ),
            'ebayOrdersDQDelLog' => array(
                'holdtime' => 3600
            ),
            'updateMsgByListXML' => array(
                'holdtime' => 3600
            ),
            'eBayGetOrders' => array(
                'holdtime' => 3600
            ),
            'getUserCases_1302' => array(
                'holdtime' => 3600
            ),
            'getUserInfoNoSW' => array(
                'holdtime' => 3600
            ),
            'eBayGetUserF' => array(
                'holdtime' => 3600 * 24
            ),
            'makeMsgQ' => array(
                'holdtime' => 3600 * 24 * 3
            ),
            'getMyMessagesBadXML' => array(
                'holdtime' => 3600 * 24 * 15
            )
        );
        foreach ($clearArray as $key => $value) {
            $criteria = array(
                'time' => array(
                    '$lt' => time() - $value['holdtime']
                )
            );
            $tryCount = 0;
            label1:
            try {
                $result = iMongo::getInstance()->getCollection($key)->remove($criteria, array());
            } catch (Exception $e) {
                $tryCount ++;
                if ($tryCount < 10) {
                    goto label1;
                }
            }
        }
    }

    /**
     * @desc 发送统计邮件
     * @author YangLong
     * @date 2015-09-12
     */
    private function sendMailLog()
    {
        // MonitorModel::model()->jobsTatusMail();
    }

    /**
     * @desc 处理feedback回复队列
     * @author liaojianwen
     * @date 2015-09-02
     */
    private function executeFeedbackUpload()
    {
        FeedbackUploadQueueModel::model()->executeFeedbackUpload();
    }

    /**
     * @desc 预上传图片
     * @author YangLong
     * @date 2015-09-20
     */
    private function uploadImage()
    {
        MsgDownModel::model()->executeUploadImageQueue();
    }

    /**
     * @desc 异步发送邮件
     * @author YangLong
     * @date 2015-09-25
     */
    private function sendSyncRun()
    {
        SendMail::sendSyncRun();
    }

    /**
     * @desc 获取和解析物流轨迹信息
     * @author YangLong
     * @date 2015-11-03
     */
    private function updateTracking()
    {
        LogisticsModel::model()->updateTracking();
    }

    /**
     * @desc 获取和解析包裹信息
     * @author YangLong
     * @date 2015-11-04
     */
    private function updatePackages()
    {
        LogisticsModel::model()->updatePackages();
    }

    /**
     * @desc 发送统计信息
     * @author YangLong
     * @date 2015-09-28
     */
    private function sendTongJi()
    {
        EbayOtherInfoModel::model()->sendTongJi();
    }
}
