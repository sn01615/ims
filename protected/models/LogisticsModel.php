<?php

/**
 * @desc 物流信息
 * @author YangLong
 * @date 2015-10-31
 */
class LogisticsModel extends BaseModel
{
    
    /**
     * @desc 覆盖父方法,返回当前类的(单)实例
     * @param string $className 需要实例化的类名
     * @author YangLong
     * @date 2015-10-31
     * @return LogisticsModel
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
    
    /**
     * @desc 调用API获取包裹信息
     * @param array $pkglst
     * @param int $error CURL错误信息
     * @author YangLong
     * @date 2015-11-03
     * @return string
     */
    private function getPackages($pkglst, &$error)
    {
        $url = Yii::app()->params['CK1ApiUrl'];
        $url .= '/get-packages';
        $url .= '?pkglst=' . urlencode(json_encode($pkglst));
        return getByCurl::get($url, $error);
    }
    
    /**
     * @desc 调用API获取账单信息
     * @param array $pkglst
     * @param int $error CURL错误信息
     * @author YangLong
     * @date 2015-11-03
     * @return string
     */
    private function getBills($pkglst, &$error)
    {
        $url = Yii::app()->params['CK1ApiUrl'];
        $url .= '/list-business-bills';
        $url .= '?pkglst=' . urlencode(json_encode($pkglst));
        return getByCurl::get($url, $error);
    }
    
    /**
     * @desc 调用API获取账单信息
     * @param array $pkglst
     * @param int $error CURL错误信息
     * @author YangLong
     * @date 2015-11-03
     * @return string
     */
    private function getTrackings($pkglst, &$error)
    {
        $url = Yii::app()->params['CK1ApiUrl'];
        $url .= '/list-tracking-infos';
        $url .= '?pkglst=' . urlencode(json_encode($pkglst));
        return getByCurl::get($url, $error);
    }
    
    /**
     * @desc 获取和解析包裹信息
     * @author YangLong
     * @date 2015-11-04
     * @return null
     */
    public function updatePackages()
    {
        DaemonLockTool::lock(__METHOD__);
        
        $startTime = time();
        
        label1:
        
        if (time() - $startTime > 588) {
            return null;
        }
        
        // get data
        $columns = array(
            'ck1_packages_id',
            'ProcessOrTrackNo'
        );
        $conditions = 'last_get_time<=:last_get_time - 600*run_count and error_code!=1';
        $params = array(
            ':last_get_time' => time() - 600
        );
        $joinArray = array();
        $tableAlias = 'pk';
        $order = 'run_count,last_get_time,ck1_packages_id desc';
        $limit = 10;
        $offset = null;
        $option = '';
        $groups = '';
        $packages = Ck1PackagesDAO::getInstance()->iselect($columns, $conditions, $params, true, $joinArray, $tableAlias, $order, $limit, $offset, $option, $groups);
        
        if (empty($packages)) {
            return null;
        }
        
        // 拼接数据
        $pkglst = array();
        $_packages_ids = array();
        foreach ($packages as $value) {
            $pkglst[] = $value['ProcessOrTrackNo'];
            $_packages_ids[] = $value['ck1_packages_id'];
        }
        
        // lock
        $_packages_ids = implode(',', $_packages_ids);
        $columns = array(
            'last_get_time' => time()
        );
        $conditions = Ck1PackagesDAO::getInstance()->getPk() . ' in (' . $_packages_ids . ')';
        $params = array();
        Ck1PackagesDAO::getInstance()->iupdate($columns, $conditions, $params);
        $field = 'run_count';
        Ck1PackagesDAO::getInstance()->increase($field, $conditions);
        
        $trycount = 0;
        
        label2:
        
        // call api
        $result = $this->getPackages($pkglst, $error);
        
        $result = json_decode($result, true);
        
        if (empty($result)) {
            $trycount ++;
            if ($trycount < 10 || $error > 0) {
                sleep($trycount);
                goto label2;
            }
            return null;
        }
        
        if ($result['meta']['Succeeded'] == true && isset($result['body']) && is_array($result['body'])) {
            foreach ($result['body'] as $packageInfo) {
                // ShippingMethod
                $ck1_shipping_method_id = null;
                if (is_array($packageInfo['ShippingMethod']) && isset($packageInfo['ShippingMethod']['ServiceCode'])) {
                    $columns = array(
                        'ServiceCode' => $packageInfo['ShippingMethod']['ServiceCode'],
                        'ServiceName' => $packageInfo['ShippingMethod']['ServiceName']
                    );
                    $conditions = 'ServiceCode=:ServiceCode and ServiceName=:ServiceName';
                    $params = array(
                        ':ServiceCode' => $packageInfo['ShippingMethod']['ServiceCode'],
                        ':ServiceName' => $packageInfo['ShippingMethod']['ServiceName']
                    );
                    
                    if (empty($columns['ServiceCode']) || empty($columns['ServiceName'])) {
                        // 发送邮件通知
                        ob_start();
                        echo json_encode($result);
                        $text = ob_get_clean();
                        $subject = "Ck1ShippingMethod 数据异常";
                        $to = Yii::app()->params['logmails'];
                        SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
                    }
                    
                    if (empty($columns['ServiceName'])) {
                        $columns['ServiceName'] = '';
                        $params[':ServiceName'] = '';
                    }
                    
                    $ck1_shipping_method_id = Ck1ShippingMethodDAO::getInstance()->ireplaceinto($columns, $conditions, $params, true);
                }
                
                // ToAddress
                $ck1_ship_to_address_id = null;
                if (is_array($packageInfo['ToAddress'])) {
                    $columns = array(
                        'Name' => $packageInfo['ToAddress']['Name'],
                        'Address1' => $packageInfo['ToAddress']['Address1'],
                        'Address2' => $packageInfo['ToAddress']['Address2'],
                        'City' => $packageInfo['ToAddress']['City'],
                        'Province' => $packageInfo['ToAddress']['Province'],
                        'Country' => $packageInfo['ToAddress']['Country'],
                        'CountryCode' => $packageInfo['ToAddress']['CountryCode'],
                        'PostCode' => $packageInfo['ToAddress']['PostCode'],
                        'Phone' => $packageInfo['ToAddress']['Phone'],
                        'Email' => $packageInfo['ToAddress']['Email']
                    );
                    $_hash = md5(serialize($columns));
                    $columns['hash'] = $_hash;
                    $conditions = 'hash=:hash';
                    $params = array(
                        ':hash' => $_hash
                    );
                    $ck1_ship_to_address_id = Ck1ShipToAddressDAO::getInstance()->ireplaceinto($columns, $conditions, $params, true);
                }
                
                // PackageInfo
                $ck1_package_info_id = null;
                if (is_array($packageInfo['PackageInfo'])) {
                    // Packing
                    $ck1_packing_id = null;
                    if (is_array($packageInfo['PackageInfo']['Packing'])) {
                        $columns = array(
                            'Length' => $packageInfo['PackageInfo']['Packing']['Length'],
                            'Width' => $packageInfo['PackageInfo']['Packing']['Width'],
                            'Height' => $packageInfo['PackageInfo']['Packing']['Height']
                        );
                        $conditions = 'Length=:Length and Width=:Width and Height=:Height';
                        $params = array(
                            ':Length' => $packageInfo['PackageInfo']['Packing']['Length'],
                            ':Width' => $packageInfo['PackageInfo']['Packing']['Width'],
                            ':Height' => $packageInfo['PackageInfo']['Packing']['Height']
                        );
                        $ck1_packing_id = Ck1PackingDAO::getInstance()->ireplaceinto($columns, $conditions, $params, true);
                        
                        // 安全起见
                        if (is_array($ck1_packing_id)) {
                            iMongo::getInstance()->setCollection('ck1_packing')->insert(array(
                                'ck1_packing_id' => $ck1_packing_id
                            ));
                            
                            $ck1_packing_id = array_shift($ck1_packing_id);
                            $ck1_packing_id = array_shift($ck1_packing_id);
                        }
                    }
                    
                    // master#2
                    $columns = array(
                        'ck1_packing_id' => $ck1_packing_id,
                        'WeightInit' => $packageInfo['PackageInfo']['WeightInit'],
                        'WeightForCharge' => $packageInfo['PackageInfo']['WeightForCharge'],
                        'DeclareName' => $packageInfo['PackageInfo']['DeclareName'],
                        'DeclareValue' => $packageInfo['PackageInfo']['DeclareValue'],
                        'Custom' => $packageInfo['PackageInfo']['Custom'],
                        'Remark' => $packageInfo['PackageInfo']['Remark']
                    );
                    $_hash = md5(serialize($columns));
                    $columns['hash'] = $_hash;
                    $conditions = 'hash=:hash';
                    $params = array(
                        ':hash' => $_hash
                    );
                    $ck1_package_info_id = Ck1PackageInfoDAO::getInstance()->ireplaceinto($columns, $conditions, $params, true);
                }
                
                // TrackingInfo
                $ck1_tracking_info_id = null;
                if (is_array($packageInfo['TrackingInfo'])) {
                    $columns = array(
                        'Carrier' => $packageInfo['TrackingInfo']['Carrier'],
                        'TrackingNumber' => $packageInfo['TrackingInfo']['TrackingNumber']
                    );
                    $conditions = 'Carrier=:Carrier and TrackingNumber=:TrackingNumber';
                    $params = array(
                        ':Carrier' => $packageInfo['TrackingInfo']['Carrier'],
                        ':TrackingNumber' => $packageInfo['TrackingInfo']['TrackingNumber']
                    );
                    
                    foreach ($columns as &$value) {
                        if (empty($value)) {
                            $value = '';
                        }
                    }
                    unset($value);
                    $ck1_tracking_info_id = Ck1TrackingInfoDAO::getInstance()->ireplaceinto($columns, $conditions, $params, true);
                }
                
                // StatusInfo
                $ck1_status_info_id = null;
                if (is_array($packageInfo['StatusInfo'])) {
                    // UnDeliveryReason
                    $ck1_undelivery_reason_id = 0;
                    if (is_array($packageInfo['StatusInfo']['UnDeliveryReason'])) {
                        $columns = array(
                            'ReasonCode' => $packageInfo['StatusInfo']['UnDeliveryReason']['ReasonCode'],
                            'ReasonMessage' => $packageInfo['StatusInfo']['UnDeliveryReason']['ReasonMessage']
                        );
                        $conditions = 'ReasonCode=:ReasonCode and ReasonMessage=:ReasonMessage';
                        $params = array(
                            ':ReasonCode' => $packageInfo['StatusInfo']['UnDeliveryReason']['ReasonCode'],
                            ':ReasonMessage' => $packageInfo['StatusInfo']['UnDeliveryReason']['ReasonMessage']
                        );
                        $ck1_undelivery_reason_id = Ck1UndeliveryReasonDAO::getInstance()->ireplaceinto($columns, $conditions, $params, true);
                    }
                    
                    // master#3
                    $columns = array(
                        'OriginalStatus' => $packageInfo['StatusInfo']['OriginalStatus'],
                        'ProcessStatus' => $packageInfo['StatusInfo']['ProcessStatus'],
                        'ck1_undelivery_reason_id' => $ck1_undelivery_reason_id
                    );
                    $conditions = 'OriginalStatus=:OriginalStatus and ProcessStatus=:ProcessStatus and ck1_undelivery_reason_id=:ck1_undelivery_reason_id';
                    $params = array(
                        ':OriginalStatus' => $packageInfo['StatusInfo']['OriginalStatus'],
                        ':ProcessStatus' => $packageInfo['StatusInfo']['ProcessStatus'],
                        ':ck1_undelivery_reason_id' => $ck1_undelivery_reason_id
                    );
                    $ck1_status_info_id = Ck1StatusInfoDAO::getInstance()->ireplaceinto($columns, $conditions, $params, true);
                }
                
                // master
                $columns = array(
                    'ProcessNo' => $packageInfo['ProcessNo'],
                    'RefNo' => $packageInfo['RefNo'],
                    'PackageType' => $packageInfo['PackageType'],
                    'IsTracking' => boolConvert::toStr01($packageInfo['IsTracking']),
                    'PackageType' => $packageInfo['PackageType'],
                    'ck1_shipping_method_id' => $ck1_shipping_method_id,
                    'ck1_ship_to_address_id' => $ck1_ship_to_address_id,
                    'ck1_package_info_id' => $ck1_package_info_id,
                    'ck1_tracking_info_id' => $ck1_tracking_info_id,
                    'ck1_status_info_id' => $ck1_status_info_id
                );
                if ($packageInfo['ErrorMessage'] === '查无记录') {
                    $columns['error_code'] = 2;
                } elseif ($packageInfo['Success'] === true) {
                    $columns['error_code'] = 1;
                } else {
                    $columns['error_code'] = 3;
                }
                foreach ($columns as $key => $value) {
                    if ($value === null) {
                        unset($columns[$key]);
                    }
                    if (isset($columns[$key]) && is_array($columns[$key])) {
                        // 调试发邮件 TODO
                        $subject = "调试";
                        ob_start();
                        echo "\n-----------------------------------------------------------\n";
                        var_dump($columns);
                        echo "\n-----------------------------------------------------------\n";
                        $text = ob_get_clean();
                        $to = Yii::app()->params['logmails'];
                        SendMail::sendSync(Yii::app()->params['server_desc'] . ':' . $subject, $text, $to);
                        
                        $columns[$key] = array_shift($columns[$key]);
                        $columns[$key] = array_shift($columns[$key]);
                    }
                }
                $conditions = 'ProcessOrTrackNo=:ProcessOrTrackNo';
                $params = array(
                    ':ProcessOrTrackNo' => $packageInfo['ProcessOrTrackNo']
                );
                
                Ck1PackagesDAO::getInstance()->iupdate($columns, $conditions, $params);
            }
        }
        
        // 100ms
        usleep(100000);
        goto label1;
    }
    
    /**
     * @desc 获取和解析物流轨迹信息
     * @author YangLong
     * @date 2015-11-03
     * @return null
     */
    public function updateTracking()
    {
        DaemonLockTool::lock(__METHOD__);
        
        $startTime = time();
        
        label1:
        
        if (time() - $startTime > 588) {
            return null;
        }
        
        // get data
        $columns = array(
            'ck1_trackings_id',
            'ProcessOrTrackNo'
        );
        $conditions = "tracking_status not in ('Delivered') and last_get_time<=:last_get_time - 600*run_count";
        $params = array(
            ':last_get_time' => time() - 600
        );
        $joinArray = array();
        $tableAlias = 'tk';
        $order = 'run_count,last_get_time,ck1_trackings_id desc';
        $limit = 10;
        $offset = null;
        $option = '';
        $groups = '';
        $trackings = Ck1TrackingsDAO::getInstance()->iselect($columns, $conditions, $params, true, $joinArray, $tableAlias, $order, $limit, $offset, $option, $groups);
        
        if (empty($trackings)) {
            return null;
        }
        
        // 拼接数据
        $pkglst = array();
        $_trackings_ids = array();
        $trackingsNoId = array();
        foreach ($trackings as $value) {
            $pkglst[] = $value['ProcessOrTrackNo'];
            $_trackings_ids[] = $value['ck1_trackings_id'];
            $trackingsNoId[$value['ProcessOrTrackNo']] = $value['ck1_trackings_id'];
        }
        
        // lock
        $_trackings_ids = implode(',', $_trackings_ids);
        $columns = array(
            'last_get_time' => time()
        );
        $conditions = Ck1TrackingsDAO::getInstance()->getPk() . ' in (' . $_trackings_ids . ')';
        $params = array();
        Ck1TrackingsDAO::getInstance()->iupdate($columns, $conditions, $params);
        $field = 'run_count';
        Ck1TrackingsDAO::getInstance()->increase($field, $conditions);
        
        $trycount = 0;
        
        label2:
        
        // call api
        $result = $this->getTrackings($pkglst, $error);
        
        $result = json_decode($result, true);
        
        if (empty($result)) {
            $trycount ++;
            if ($trycount < 10 || $error > 0) {
                sleep($trycount);
                goto label2;
            }
            return null;
        }
        
        if ($result['meta']['Succeeded'] == true && isset($result['body']) && is_array($result['body'])) {
            foreach ($result['body'] as $trackingInfo) {
                $_status = '';
                foreach ($trackingInfo['Details'] as $key => $trackingDetails) {
                    if (! isset($trackingsNoId[$trackingInfo['ProcessOrTrackNo']])) {
                        continue;
                    }
                    
                    if ($key === 0) {
                        $_status = $trackingDetails['Status'];
                    }
                    
                    $columns = array(
                        'ck1_trackings_id' => $trackingsNoId[$trackingInfo['ProcessOrTrackNo']],
                        'Description' => $trackingDetails['Description'],
                        'LocalTime' => $trackingDetails['LocalTime'],
                        'LocalTimeTimestamp' => $trackingDetails['LocalTimeTimestamp'],
                        'Location' => $trackingDetails['Location'],
                        'Status' => $trackingDetails['Status'],
                        'TrackingDetailType' => $trackingDetails['TrackingDetailType'],
                        'TrackingId' => $trackingDetails['TrackingId']
                    );
                    $conditions = 'TrackingId=:TrackingId and ck1_trackings_id=:ck1_trackings_id';
                    $params = array(
                        ':TrackingId' => $trackingDetails['TrackingId'],
                        ':ck1_trackings_id' => $trackingsNoId[$trackingInfo['ProcessOrTrackNo']]
                    );
                    foreach ($columns as $k => $v) {
                        if ($v === false || $v === null) {
                            unset($columns[$k]);
                        }
                    }
                    if ($columns['LocalTimeTimestamp'] < 0) {
                        unset($columns['LocalTimeTimestamp']);
                    }
                    Ck1TrackingDetailsDAO::getInstance()->ireplaceinto($columns, $conditions, $params);
                }
                
                $columns = array(
                    'HasTrackingInfo' => boolConvert::toStr01($trackingInfo['HasTrackingInfo']),
                    'ProcessNo' => $trackingInfo['ProcessNo'],
                    'tracking_status' => $_status
                );
                $conditions = 'ProcessOrTrackNo=:ProcessOrTrackNo';
                $params = array(
                    ':ProcessOrTrackNo' => $trackingInfo['ProcessOrTrackNo']
                );
                Ck1TrackingsDAO::getInstance()->iupdate($columns, $conditions, $params);
            }
        }
        
        // 100ms
        usleep(100000);
        goto label1;
    }
    
    /**
     * @desc 读取tracking信息
     * @param string $trackingNumber
     * @author YangLong
     * @date 2015-11-03
     * @return mixed
     */
    public function getTrackingInfo($trackingNumber)
    {
        if (empty($trackingNumber)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'TrackNo cannot empty.');
        }
        
        $columns = array(
            'ck1_trackings_id',
            'HasTrackingInfo',
            'ProcessNo',
            'ProcessOrTrackNo'
        );
        $conditions = 'ProcessOrTrackNo=:ProcessOrTrackNo';
        $params = array(
            ':ProcessOrTrackNo' => $trackingNumber
        );
        $result = Ck1TrackingsDAO::getInstance()->iselect($columns, $conditions, $params, false);
        
        if (! empty($result)) {
            $columns = array(
                'Description',
                'LocalTime',
                'LocalTimeTimestamp',
                'Location',
                'Status',
                'TrackingDetailType',
                'TrackingId'
            );
            $conditions = 'ck1_trackings_id=:ck1_trackings_id';
            $params = array(
                ':ck1_trackings_id' => $result['ck1_trackings_id']
            );
            $result['trackingDetails'] = Ck1TrackingDetailsDAO::getInstance()->iselect($columns, $conditions, $params, true, array(), '', 'LocalTimeTimestamp desc');
            
            $columns = array(
                'sta.Name',
                'sta.Address1',
                'sta.Address2',
                'sta.City',
                'sta.Province',
                'sta.Country',
                'sta.CountryCode',
                'sta.PostCode',
                'sta.Phone',
                'sta.Email',
                'pks.ProcessOrTrackNo',
                'sm.ServiceCode',
                'sm.ServiceName',
                'ti.Carrier',
                'ti.TrackingNumber',
                'pi.WeightForCharge'
            );
            $conditions = 'ProcessOrTrackNo=:ProcessOrTrackNo';
            $params = array(
                ':ProcessOrTrackNo' => $result['ProcessOrTrackNo']
            );
            $joinArray = array(
                array(
                    Ck1ShipToAddressDAO::getInstance()->getTableName() . ' sta',
                    'sta.ck1_ship_to_address_id=pks.ck1_ship_to_address_id',
                    'left' => true
                ),
                array(
                    Ck1ShippingMethodDAO::getInstance()->getTableName() . ' sm',
                    'sm.ck1_shipping_method_id=pks.ck1_shipping_method_id',
                    'left' => true
                ),
                array(
                    Ck1TrackingInfoDAO::getInstance()->getTableName() . ' ti',
                    'ti.ck1_tracking_info_id=pks.ck1_tracking_info_id',
                    'left' => true
                ),
                array(
                    Ck1PackageInfoDAO::getInstance()->getTableName() . ' pi',
                    'pi.ck1_package_info_id=pks.ck1_package_info_id',
                    'left' => true
                )
            );
            $result['packageDetails'] = Ck1PackagesDAO::getInstance()->iselect($columns, $conditions, $params, false, $joinArray, 'pks');
            
            return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
        } else {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, $result);
        }
    }
    
    /**
     * @desc 读取tracking信息(实时)
     * @param string $trackingNumber
     * @author YangLong
     * @date 2015-11-03
     * @return mixed
     */
    public function getTrackingInfo2($trackingNumber)
    {
        if (empty($trackingNumber)) {
            return $this->handleApiFormat(EnumOther::ACK_FAILURE, '', 'TrackNo cannot empty.');
        }
        
        $pkglst = array(
            $trackingNumber
        );
        
        $result = array();
        
        $runcount = 0;
        label1:
        // call api
        $result['trackingDetails'] = $this->getTrackings($pkglst, $error);
        $result['trackingDetails'] = json_decode($result['trackingDetails'], true);
        if (empty($result['trackingDetails'])) {
            sleep($runcount);
            $runcount ++;
            if ($runcount < 10) {
                goto label1;
            }
        }
        
        $runcount = 0;
        label2:
        // call api
        $result['packageDetails'] = $this->getPackages($pkglst, $error);
        $result['packageDetails'] = json_decode($result['packageDetails'], true);
        if (empty($result['packageDetails'])) {
            sleep($runcount);
            $runcount ++;
            if ($runcount < 10) {
                goto label2;
            }
        }
        
        $result['trackingDetails'] = $result['trackingDetails']['body'];
        $result['packageDetails'] = $result['packageDetails']['body'];
        
        return $this->handleApiFormat(EnumOther::ACK_SUCCESS, $result);
    }
    
}
