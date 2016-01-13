<?php
/**
 * @desc return_detail处理类
 * @author liaojianwen
 * @date 2015-06-19
 */
class ReturnDetailModel extends BaseModel
{
    
    /**
     * @desc 覆盖父方法返回ReturnDetailModel对象
     * @param string $className 需要实例化的类名
     * @author liaojianwen
     * @date 2015-06-19
     * @return ReturnDetailModel
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
    
    /**
     * @desc 获取return 明细
     * @author liaojianwen
     * @date 2015-06-19
     */
    public function getReturnDetail($returnId)
    {
         if (empty($returnId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'information is no exists');
        }
        // 获取客户所有店铺
        $shopParam = array();
        $shopParam['seller_id'] = Yii::app()->session['userInfo']['seller_id'];
        $shopParam['is_delete'] = boolConvert::toInt01(false);
        $shopParam['status'] = 1;
        $shopArr = ShopDAO::getInstance()->findAllByAttributes($shopParam, array(
            'shop_id'
        ));
        if (empty($shopArr)) {
            $result = $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '用户还未注册店铺');
            return $result;
        }
        foreach ($shopArr as $value) {
            $shopidArr[] = $value['shop_id'];
        }
        $shopidArr = implode(',', $shopidArr);
        $result['list'] = ReturnDetailDAO::getInstance()->getReturnDetail($returnId, $shopidArr);
        if (empty($result)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '查询数据库失败');
        }
        
        if (isset(Yii::app()->session['switchInfo']['accountId']) && ! empty(Yii::app()->session['switchInfo']['accountId'])) {
            $shopidArr = Yii::app()->session['switchInfo']['accountId'];
        }
        return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result, '');
    
    }
    
    /**
     * @desc 获取上下页
     * @param  int $returnid
     * @param  int $creationDate
     * @param  string $status
     * @param string $itemId
     * @param string $cust
     * @author liaojianwen
     * @date 2015-06-23
     * @return array
     */
    public function getReturnPreNextID($returnid,$creationDate,$status,$itemId,$cust)
    {
        if (empty($returnid)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'information is no exists');
        }
        // 获取客户所有店铺
        $shopParam = array();
        $shopParam['seller_id'] = Yii::app()->session['userInfo']['seller_id'];
        $shopParam['is_delete'] = boolConvert::toInt01(false);
        $shopParam['status'] = 1;
        // 获取切换店铺信息
        $siteId = isset(Yii::app()->session['switchInfo']['siteId']) ? Yii::app()->session['switchInfo']['siteId'] : - 1;
        $accountId = isset(Yii::app()->session['switchInfo']['accountId']) ? Yii::app()->session['switchInfo']['accountId'] : 0;
        if (is_numeric($siteId) && $siteId > - 1) {
            $shopParam['site_id'] = $siteId;
        }
        $shopArr = ShopDAO::getInstance()->findAllByAttributes($shopParam, array(
            'shop_id'
        ));
        if (empty($shopArr)) {
            $result = $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '用户还未注册店铺');
            return $result;
        }
        foreach ($shopArr as $value) {
            $shopidArr[] = $value['shop_id'];
        }
        $shopId = implode(',', $shopidArr);
        if (is_numeric($accountId) && $accountId > 0) {
            $shopId = $accountId;
        }
        $paramArr['shop_id'] = $shopId; 
        $result = ReturnDAO::getInstance()->getPreNextID($creationDate,$returnid,$paramArr,$status,$itemId,$cust);
        
        return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result, '');
        
    }
    
    
    
    /**
     * @desc 添加return备注
     * @param integer $returnId return的id
     * @param string $text备注text
     * @param int $sellerId serller_id
     * @author liaojianwen
     * @date 2015-06-23
     * @return Ambigous <multitype:, boolean, multitype:string array string >
     */
    public function addItemNote($text,$returnId,$sellerId)
    {
        if ($returnId <= 0) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '`returnId` can not empty.');
        }
          $tokeninfo = ReturnDetailDAO::getInstance()->getReturnInfo($returnId,$sellerId);
            $cust ='';
        if ($tokeninfo !== false) {
            $cust = $tokeninfo['S_buyerLoginName'];
            $itemnote = ItemNoteDAO::getInstance();
            $username = isset(Yii::app()->session['userInfo']['username']) ? Yii::app()->session['userInfo']['username'] : 0;
            if ($username !== 0) {
                $param['author_name'] = $username;
            } else {
                return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '用户未登陆');
            }
            $param['text'] = $text;
            $param['create_time'] = time();
            $param['item_id'] = $tokeninfo['D_iD_itemId'];
            $param['cust'] = $cust;
            $result = $itemnote->insert($param);
            if ($result === false) {
                return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
            } else {
                return $this->handleApiFormat(EnumOther::ACK_SUCCESS, '');
            }
       }
    }
    
    /**
     * @desc 获取return历史信息
     * @param int $returnid
     * @author liaojianwen
     * @date 2015-06-24
     */
    public function getReturnHistory($returnid)
    {
        if (empty($returnid)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '信息不存在');
        }
        //获取客户所有店铺
        $shopParam = array();
        $shopParam['seller_id'] = Yii::app()->session['userInfo']['seller_id'];
        $shopParam['is_delete'] = boolConvert::toInt01(false);
        $shopParam['status'] 	= 1;
        $shopArr = ShopDAO::getInstance()->findAllByAttributes($shopParam, array('shop_id'));
    	if (empty($shopArr)) {
            $res= $this->handleApiFormat(EnumOther::ACK_FAILURE,'','用户还未注册店铺');
            return $res;
        }
        foreach ($shopArr as $value) {
            $shopidArr[] = $value['shop_id'];
        }
        $shopidArr = implode(',', $shopidArr);
        $result['list'] = ReturnHistoryDAO::getInstance()->getReturnHistory($returnid,$shopidArr);
        foreach($result['list'] as &$value){
              if(empty($value['note'])){
                  $value['note'] ='';
              }
              $value['note_md5'] = md5(trim($value['note']));
              $value['activityDetial_description_md5'] = md5(trim($value['activity']));
        }
        if ($result['list'] == false) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '信息不存在');
        }
        return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result, '');
    
    }
    
    /**
     * @desc 通过case id获取requestid
     * @param unknown_type $caseid
     * @author liaojianwen
     * @date 2015-06-30
     */
    public function getReturnRequest($caseid)
    {
        if(empty($caseid)){
            return $this->handleApiFormat(EnumOther::ACK_FAILURE,'','#caseid is empty#');
        }
        //获取客户所有的店铺
        $shopParam = array();
        $shopParam['seller_id'] = Yii::app()->session['userInfo']['seller_id'];
        $shopParam['is_delete'] = boolConvert::toInt01(false);
        $shopParam['status']=1;
        $shopArr = ShopDAO::getInstance()->findAllByAttributes($shopParam,array('shop_id'));
        if(empty($shopArr)){
            $res= $this->handleApiFormat(EnumOther::ACK_FAILURE,'','用户还未注册店铺');
            return $res;
        }
        foreach ($shopArr as $value) {
            $shopidArr[] = $value['shop_id'];
        }
        $shopidArr = implode(',', $shopidArr);
        $caseId_id = CaseDAO::getInstance()->getCaseId($caseid,$shopidArr);
        if($caseId_id){
            $result['returnId'] = ReturnHistoryDAO::getInstance()->getReturnRequest($caseId_id,$shopidArr);
        }
        if ($result['returnId'] == false) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '信息不存在');
        }
        return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result, '');
    }
    
    /**
     * @desc 获取退货地址
     * @param  int $returnid
     * @author liaojianwen
     * @date 2015-06-30
     */
    public function getReturnAddr($returnid)
    {
        if(empty($returnid)){
            return $this->handleApiFormat(EnumOther::ACK_FAILURE,'','#returnid is empty#');
        }
        //获取客户所有的店铺
        $shopParam = array();
        $shopParam['seller_id'] = Yii::app()->session['userInfo']['seller_id'];
        $shopParam['is_delete'] = boolConvert::toInt01(false);
        $shopParam['status']=1;
        $shopArr = ShopDAO::getInstance()->findAllByAttributes($shopParam,array('shop_id'));
        if(empty($shopArr)){
            $res= $this->handleApiFormat(EnumOther::ACK_FAILURE,'','用户还未注册店铺');
            return $res;
        }
        foreach ($shopArr as $value) {
            $shopidArr[] = $value['shop_id'];
        }
        $shopidArr = implode(',', $shopidArr);
        $returnId_id = ReturnDetailDAO::getInstance()->getReturnID($returnid,$shopidArr);
        if(!empty($returnId_id)){
//            $result = ReturnHistoryDAO::getInstance()->getReturnAddr($returnId_id);
              $result = ReturnDetailDAO::getInstance()->getReturnAddr($returnId_id);
            if(!empty($result)){
                return $this->handleApiFormat(EnumOther::ACK_SUCCESS,$result,'');
            } else {
                return $this->handleApiFormat(EnumOther::ACK_FAILURE,'','#ReturnAddr is not exists#');
            }
        } else {
             return $this->handleApiFormat(EnumOther::ACK_FAILURE,'','#returnId_id is not exists#');
        }
    }
    
   /**
    * @desc  accept the return
    * @param string $returnid
    * @param string $RMA
    * @param string $returnAddr
    * @param string $sellerId
    * @author liaojianwen
    * @date 2015-06-30
    */ 
   public function approveReturn($returnid,$RMA,$sellerId)
   {
        if (empty($returnid) || empty($sellerId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '数据不能为空');
        }
         $token = ReturnDAO::getInstance()->lawfulReturnID($returnid, $sellerId);
         if ($token === false) {
            return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'returnid Error.');
         } else {
             ReturnHistoryDAO::getInstance()->begintransaction();
             try{
                $param['return_id'] = $returnid;
                $param['creationDate'] = time();
                $param['author'] = 'SELLER';
                $param['create_time'] =time();
                $param['activity'] ='SELLER_PROVIDE_RMA';
                $param['rma'] = $RMA;
                $result = ReturnHistoryDAO::getInstance()->insert($param);
                
                $param['activity']='SELLER_APPROVE_REQUEST';
                unset($param['rma']);
                $result4 = ReturnHistoryDAO::getInstance()->insert($param);
                
                $returnId_id = $token['returnId_id'];
                $siteid = $token['site_id'];
                $decision = 'APPROVE';
                $columns_appr = array(
                    'upload_type' => 'APPROVE_REQUEST',
                    'upload_data' =>serialize(compact('returnId_id','decision','siteid')),
                    'token' =>$token['token'],
                    'create_time' =>time()
                
                );
                $result_appr = ReturnUploadQueueDAO::getInstance()->iinsert($columns_appr);
                if($result_appr !== false){
                    $decision = 'PROVIDE_RMA';
                    $addr = ReturnDetailModel::model()->getReturnAddr($returnid);
                    $returnAddr = $addr['Body'];
                    $columns_RMA = array(
                        'upload_type' => 'PROVIDE_RMA',
                        'upload_data' => serialize(compact('returnId_id', 'decision','RMA', 'returnAddr', 'siteid')),
                        'token' => $token['token'],
                        'create_time' => time()
                    );
                    $result_rma = ReturnUploadQueueDAO::getInstance()->iinsert($columns_RMA,true);
                }
                $return = array('returnRma'=>$result_rma);
                //以下部分是插入我的操作日志表的代码
        		$username = isset(Yii::app()->session['userInfo']['username']) ? Yii::app()->session['userInfo']['username'] : 0;
        		if ($username !== 0) {
//        		    $paramArr['handle_user'] = $username;
        		} else {
         		    return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '用户未登陆');
        		}
        		$paramArr['handle_user'] = $username;
        		$paramArr['return_id'] = $returnid;
        		$paramArr['create_time'] = time();
        		$paramArr['handle_type'] = 'APPROVE_REQUEST';
        		$result2 = ReturnHandleLogDAO::getInstance()->insert($paramArr);
        		
        		if($result_appr !== false){
            		$paramRMA['handle_user'] = $username;
            		$paramRMA['return_id'] = $returnid;
            		$paramRMA['create_time'] = time();
            		$paramRMA['handle_type'] ='PROVIDE_RMA';
            		$paramRMA['returnAddr'] = serialize($returnAddr);
            		$paramRMA['RMA'] =$RMA;
            		$result3 = ReturnHandleLogDAO::getInstance()->insert($paramRMA);
        		}
                 if ($result === false || $result_appr === false || $result_rma === false || $result2 === false || $result3 ===false) {
                    ReturnHistoryDAO::getInstance()->rollback();
                    return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
                } else {
                    ReturnHistoryDAO::getInstance()->commit();
                    return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $return, '');
                }
             } catch (Exception $e) {
                ReturnHistoryDAO::getInstance()->rollback();
                return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
            }
                
         }
   }
    
    /**
     * @desc   return 退全款
     * @param int $returnid
     * @param string  $sellerId
     * @author liaojianwen
     * @date 2015-07-01
     */
    public function issueReturnRefund($returnid, $sellerId)
    {
        if (empty($returnid) || empty($sellerId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '数据不能为空');
        }
        $token = ReturnDAO::getInstance()->lawfulReturnID($returnid, $sellerId);
        
        if ($token === false) {
            return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'returnid Error.');
        } else {
            ReturnHistoryDAO::getInstance()->begintransaction();
            try {
                $param['return_id'] = $returnid;
                $param['creationDate'] = time();
                $param['activity'] = 'SELLER_ISSUE_REFUND';
                $param['author'] = 'SELLER';
                $param['create_time'] = time();
                $result = ReturnHistoryDAO::getInstance()->insert($param);
                
                $returnId_id = $token['returnId_id'];
                $siteid = $token['site_id'];
                $itemRefundDetail = EstimatedRefundDAO::getInstance()->getItemizedRefundDetail($returnid, $sellerId);
                $columns = array(
                    'upload_type' => __FUNCTION__,
                    'upload_data' => serialize(compact('returnId_id', 'itemRefundDetail', 'siteid')),
                    'token' => $token['token'],
                    'create_time' => time()
                );
                
                $result1 = ReturnUploadQueueDAO::getInstance()->iinsert($columns, true);
                $return = array(
                    'returnRefund' => $result1
                );
                // 以下部分是插入我的操作日志表的代码
                $username = isset(Yii::app()->session['userInfo']['username']) ? Yii::app()->session['userInfo']['username'] : 0;
                if ($username !== 0) {
                    $paramArr['handle_user'] = $username;
                } else {
                    return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '用户未登陆');
                }
                $paramArr['return_id'] = $returnid;
                $paramArr['itemizedRefundDetail'] = serialize(compact('itemRefundDetail'));
                $paramArr['create_time'] = time();
                $paramArr['handle_type'] = __FUNCTION__;
                $result2 = ReturnHandleLogDAO::getInstance()->iinsert($paramArr);
                
                if ($result === false || $result1 === false || $result2 === false) {
                    ReturnHistoryDAO::getInstance()->rollback();
                    return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', '写入数据库失败,已回滚');
                } else {
                    ReturnHistoryDAO::getInstance()->commit();
                    return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $return, '');
                }
            } catch (Exception $e) {
                ReturnHistoryDAO::getInstance()->rollback();
                return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
            }
        }
    }
    
   /**
    * @desc return 部分退款
    * @param string $returnid
    * @param string $amount
    * @param string $text
    * @param $sellerId
    * @author liaojianwen
    * @date 2015-07-01
    */
   public function issueReturnPartRefund($returnid,$amount,$currencyId,$text,$sellerId)
   {
        if (empty($returnid) ||empty($amount) || empty($currencyId) || empty($sellerId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '数据不能为空');
        }
         $token = ReturnDAO::getInstance()->lawfulReturnID($returnid, $sellerId);
         
         if ($token === false) {
            return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'returnid Error.');
         } else {
             ReturnHistoryDAO::getInstance()->begintransaction();
             try{
                $param['return_id'] = $returnid;
                $param['creationDate'] = time();
                $param['activity']='SELLER_OFFER_PARTIAL_REFUND';
                $param['note'] = $text;
                $param['partialRefundAmount'] = (double)$amount;
                $param['author'] = 'SELLER';
                $param['create_time'] =time();
                $result = ReturnHistoryDAO::getInstance()->insert($param);
                
                $returnId_id = $token['returnId_id'];
                $siteid = $token['site_id'];
                $columns = array(
                    'upload_type' => __FUNCTION__,
                    'upload_data' =>serialize(compact('returnId_id', 'amount', 'currencyId', 'text', 'siteid')),
                    'token' =>$token['token'],
                    'create_time' =>time()
                
                );
                $result1 = ReturnUploadQueueDAO::getInstance()->iinsert($columns,true);
                $return = array('returnPartRefund'=>$result1);
                //以下部分是插入我的操作日志表的代码
        		$username = isset(Yii::app()->session['userInfo']['username']) ? Yii::app()->session['userInfo']['username'] : 0;
        		if ($username !== 0) {
        		    $paramArr['handle_user'] = $username;
        		} else {
         		    return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '用户未登陆');
        		}
        		$paramArr['return_id'] = $returnid;
        		$paramArr['create_time'] = time();
        		$paramArr['amount'] = $amount;
        		$paramArr['currencyId'] = $currencyId;
        		$paramArr['responseText'] =$text;
        		$paramArr['handle_type'] = __FUNCTION__;
        		$result2 = ReturnHandleLogDAO::getInstance()->insert($paramArr);
                if ($result === false || $result1 === false || $result2 === false) {
                    ReturnHistoryDAO::getInstance()->rollback();
                    return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
                } else {
                    ReturnHistoryDAO::getInstance()->commit();
                    return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $return,'');
                }
              } catch (Exception $e) {
                   ReturnHistoryDAO::getInstance()->rollback();
                   return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
             }
         }
   }
   
   /**
    * @desc return 发送信息
    * @param string $returnid
    * @param string $text
    * @param string $sellerId
    * @author liaojianwen
    * @date 2015-07-01
    */
   public function sendReturnMsg($returnid,$text,$sellerId)
   {
        if (empty($returnid) ||empty($text) || empty($sellerId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '数据不能为空');
        }
         $token = ReturnDAO::getInstance()->lawfulReturnID($returnid, $sellerId);
         
         if ($token === false) {
            return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'returnid Error.');
         } else {
             ReturnHistoryDAO::getInstance()->begintransaction();
             try{
                $param['return_id'] = $returnid;
                $param['creationDate'] = time();
                $param['activity']='SELLER_SEND_MESSAGE';
                $param['note'] = $text;
                $param['author'] = 'SELLER';
                $param['create_time'] =time();
                $result = ReturnHistoryDAO::getInstance()->insert($param);
                
                $returnId_id = $token['returnId_id'];
                $siteid = $token['site_id'];
                $columns = array(
                    'upload_type' => __FUNCTION__,
                    'upload_data' =>serialize(compact('returnId_id', 'text', 'siteid')),
                    'token' =>$token['token'],
                    'create_time' =>time()
                
                );
                $result1 = ReturnUploadQueueDAO::getInstance()->iinsert($columns,true);
                $return = array('returnMsg'=>$result1);
                //以下部分是插入我的操作日志表的代码
        		$username = isset(Yii::app()->session['userInfo']['username']) ? Yii::app()->session['userInfo']['username'] : 0;
        		if ($username !== 0) {
        		    $paramArr['handle_user'] = $username;
        		} else {
         		    return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '用户未登陆');
        		}
        		$paramArr['return_id'] = $returnid;
        		$paramArr['create_time'] = time();
        		$paramArr['responseText'] =$text;
        		$paramArr['handle_type'] = __FUNCTION__;
        		$result2 = ReturnHandleLogDAO::getInstance()->insert($paramArr);
                if ($result === false || $result1 === false || $result2 === false) {
                    ReturnHistoryDAO::getInstance()->rollback();
                    return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
                } else {
                    ReturnHistoryDAO::getInstance()->commit();
                    return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $return, '');
                }
              } catch (Exception $e) {
                   ReturnHistoryDAO::getInstance()->rollback();
                   return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
             }
         }
   
   }
   
  /**
   * @desc 申请ebay 介入
   * @param string $returnid
   * @param string $text
   * @param string $reason
   * @param string $sellerId
   * @author liaojianwen
   * @date 2015-07-01
   */
   public function returnAskHelp($returnid,$text,$reason,$sellerId)
   {
        if (empty($returnid) ||empty($reason) || empty($sellerId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '数据不能为空');
        }
         $token = ReturnDAO::getInstance()->lawfulReturnID($returnid, $sellerId);
         
         if ($token === false) {
            return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'returnid Error.');
         } else {
             ReturnHistoryDAO::getInstance()->begintransaction();
             try{
                $param['return_id'] = $returnid;
                $param['creationDate'] = time();
                $param['activity']='SELLER_ESCALATE';
                $param['note'] = $text;
                $param['author'] = 'SELLER';
                $param['create_time'] =time();
                $result = ReturnHistoryDAO::getInstance()->insert($param);
                
                $returnId_id = $token['returnId_id'];
                $siteid = $token['site_id'];
                $columns = array(
                    'upload_type' => __FUNCTION__,
                    'upload_data' =>serialize(compact('returnId_id', 'reason', 'text', 'siteid')),
                    'token' =>$token['token'],
                    'create_time' =>time()
                
                );
                $result1 = ReturnUploadQueueDAO::getInstance()->iinsert($columns,true);
                $return = array('returnEbayHelp'=>$result1);
                //以下部分是插入我的操作日志表的代码
        		$username = isset(Yii::app()->session['userInfo']['username']) ? Yii::app()->session['userInfo']['username'] : 0;
        		if ($username !== 0) {
        		    $paramArr['handle_user'] = $username;
        		} else {
         		    return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '用户未登陆');
        		}
        		$paramArr['return_id'] = $returnid;
        		$paramArr['create_time'] = time();
        		$paramArr['responseText'] =$text;
        		$paramArr['reason'] =$reason;
        		$paramArr['handle_type'] = __FUNCTION__;
        		$result2 = ReturnHandleLogDAO::getInstance()->insert($paramArr);
                if ($result === false || $result1 === false || $result2 === false) {
                    ReturnHistoryDAO::getInstance()->rollback();
                    return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
                } else {
                    ReturnHistoryDAO::getInstance()->commit();
                    return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $return, '');
                }
             } catch (Exception $e) {
                   ReturnHistoryDAO::getInstance()->rollback();
                   return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
             }
         }
   }
   
   /**
    * @desc 拒绝returns
    * @param string $returnid 
    * @param string $text comments
    * @param string $sellerId 
    * @author liaojianwen
    * @date 2015-08-10
    * @return Ambigous <multitype:, boolean, multitype:string array string >
    */
   public function declineReturns($returnid,$text,$sellerId)
   {
       if(empty($returnid) || empty($sellerId)){
           return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '数据不能为空');
       }
       $token = ReturnDAO::getInstance()->lawfulReturnID($returnid, $sellerId);
        
       if ($token === false) {
           return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'returnid Error.');
       } else {
           ReturnHistoryDAO::getInstance()->begintransaction();
           try{
               $param['return_id'] = $returnid;
               $param['creationDate'] = time();
               $param['activity']='SELLER_DECLINE_REQUEST';
               $param['note'] = $text;
               $param['author'] = 'SELLER';
               $param['create_time'] =time();
               $result = ReturnHistoryDAO::getInstance()->insert($param);
               
               $returnId_id = $token['returnId_id'];
               $siteid = $token['site_id'];
               $columns = array(
                   'upload_type' => __FUNCTION__,
                   'upload_data' =>serialize(compact('returnId_id', 'text', 'siteid')),
                   'token' =>$token['token'],
                   'create_time' =>time()
               
               );
               $result1 = ReturnUploadQueueDAO::getInstance()->iinsert($columns,true);
               $return = array('returnDecline'=>$result1);
               //以下部分是插入我的操作日志表的代码
               $username = isset(Yii::app()->session['userInfo']['username']) ? Yii::app()->session['userInfo']['username'] : 0;
               if ($username !== 0) {
                   $paramArr['handle_user'] = $username;
               } else {
                   return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '用户未登陆');
               }
               $paramArr['return_id'] = $returnid;
               $paramArr['create_time'] = time();
               $paramArr['responseText'] =$text;
               $paramArr['handle_type'] = __FUNCTION__;
               $result2 = ReturnHandleLogDAO::getInstance()->insert($paramArr);
               if ($result === false || $result1 === false || $result2 === false) {
                   ReturnHistoryDAO::getInstance()->rollback();
                   return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
               } else {
                   ReturnHistoryDAO::getInstance()->commit();
                   return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $return, '');
               }
            } catch (Exception $e) {
                   ReturnHistoryDAO::getInstance()->rollback();
                   return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', '写入数据库失败');
            }
       }
       
       
   }
   
   /**
    * @desc 获取下一步操作
    * @param string $returnid
    * @param string $sellerId
    */
   public function getSellerOptions($returnid,$sellerId)
   {
        if (empty($returnid) || empty($sellerId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '数据不能为空');
        }
        $result = SellerOptionDAO::getInstance()->getSellerOptions($returnid,$sellerId);
        if(!empty($result)){
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result,'');
        }else{
             return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'option is not exists');
        }
   
   }
   
   /**
    * @desc 获取request 的历史
    * @param $returnId_id
    * @param $sellerId
    * @author liaojianwen
    * @date 2015-07-07
    */
   public function getReturn2Case($returnId_id,$sellerId)
   {
       if (empty($returnId_id) || empty($sellerId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '数据不能为空');
        }
       $result =  ReturnHistoryDAO::getInstance()->getReturn2Case($returnId_id,$sellerId);
        if(!empty($result)){
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result,'');
        }else{
             return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'option is not exists');
        }
   }
   
    /**
     * @desc 通过orderLineItemId 获取productName
     * @param $orderLineItemId
     * @param $sellerId
     * @author liaojianwen
     * @date 2015-07-10
     */
   public function getProductName($orderLineItemId,$sellerId)
   {
        if (empty($orderLineItemId) || empty($sellerId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '数据不能为空');
        }
       $result = EbayOrderTransactionDAO::getInstance()->getProductName($orderLineItemId,$sellerId);
       if(!empty($result)){
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result,'');
        }else{
             return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'productName is not exists');
        }
   }
   
   /**
    * @desc 获取订单退款信息
    * @param $returnid
    * @param $sellerId
    * @author liaojianwen
    * @date 2015-07-17
    */
    public function getOrdersRefund($returnid,$sellerId)
      {
           if (empty($returnid) || empty($sellerId)) {
              return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '数据不能为空');
          }
          
//      $result = EbayOrdersDAO::getInstance()->getOrdersRefund($orderId_id,$sellerId);
        $result = ReturnMoneyMovementDAO::getInstance()->getMoneyMovement($returnid,$sellerId);
        if(!empty($result)){
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result,'');
        }else{
             return $this->handleApiForMat(EnumOther::ACK_FAILURE, '', 'order is not refund');
        }
          
     } 
     
     /**
     * @desc 查找处理人、处理方式
     * @param $returnid
     * @param $sellerId
     * @author liaojianwen
     * @date 2015-07-24
     */
    public function getReturnOperator($returnid,$sellerId)
    {
        if (empty($returnid) || empty($sellerId)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', '');
        }
        
        $result = ReturnHandleLogDAO::getInstance()->getReturnOperator($returnid);
        if(empty($result)){
            return $this->handleApiForMat(EnumOther::ACK_FAILURE,'','operation is not exists');
        }
        return  $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result, '');   
    }
     
   
  
}