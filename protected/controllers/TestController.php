<?php

class TestController extends Controller
{

    public function actionIndex()
    {
        imsTool::xmlHeader();
        // $token = '';
        // // $token = 'AgAAAA**AQAAAA**aAAAAA**iZ/+VQ**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6AEmYWoCpWGpAudj6x9nY+seQ**B8ICAA**AAMAAA**EHDPZ0eNYQWd/+V6SeRkPb1ZJd3aEeVgkedyp7eQhwlaBTN88KkuYNF8EkDXX20jEQN8jcawk2L8BKyJndIW0GyHFrywK+de2eiXVbYCbNNk2T8NTD+YaDSz3TEF8YSAiXLpzXH+LJ9srqPwvcCyiwi1cVhrQYUzol79Qfl54+pwC+QLwV/QtaVSaA/9tOHcJcvuaNApcvV/B5DfFhgWEL+FsFwmN+RBMRUp/MLpkEI0hoXEOJLNvySViNUWbBcQJ3tGpnghVAGdZRUE0iZZq+OfaUsouDHNRMucbHZOVSc+z7LOpDJ6r4fTBRxjphPbzpCIG1NvyoTsFrsbC3PdsZOY7n/hM+LbLakMcbBnW/JFOI+0XpPusiWP/NjTx80f1K7p0+zbxx0LTFms18zviNxpzYh4U85gJw+z5CN3/bejRvGdMeRCq+gB86vLwGZNFvZrgPLGHGQHuv42lio+rC6z7O8D7L1HiRPlI8ZDa+0HvTuBgH175QiGzcuVqVRAlwghVCR551iBlwcftHkWErxjjFN0ZBr0UcZG91Whq31D4WW4XEkkPrCH/7lxz4MEkUC7aXaMAKgA6AVwEqrGe6nSasG9Uw66aOB9pueaxTP6M333revnBiyYLBuB+eGCTFMGUpoZi4/0uXwsFISPG1zogkvjmcfjmE0QRBr9M8bDfUje9clVNeqBLM4rV65A34m3E8jxDItraSX8+l3EcY3B6Syi7uK3mS5FHzOd5JEpm8KLew421FF9sngf9kOt';
        // $Body = 'test';
        // $ItemID = '23423423';
        // $RecipientIDArray = array(
        // 'ssdfs'
        // );
        // $CorrelationID = rand(1111111, 9999999);
        
        // echo MsgDownModel::model()->addMemberMessagesAAQToBidder($token, $Body, $ItemID, $RecipientIDArray, $CorrelationID);
        
        $token = 'AgAAAA**AQAAAA**aAAAAA**iZ/+VQ**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6AEmYWoCpWGpAudj6x9nY+seQ**B8ICAA**AAMAAA**EHDPZ0eNYQWd/+V6SeRkPb1ZJd3aEeVgkedyp7eQhwlaBTN88KkuYNF8EkDXX20jEQN8jcawk2L8BKyJndIW0GyHFrywK+de2eiXVbYCbNNk2T8NTD+YaDSz3TEF8YSAiXLpzXH+LJ9srqPwvcCyiwi1cVhrQYUzol79Qfl54+pwC+QLwV/QtaVSaA/9tOHcJcvuaNApcvV/B5DfFhgWEL+FsFwmN+RBMRUp/MLpkEI0hoXEOJLNvySViNUWbBcQJ3tGpnghVAGdZRUE0iZZq+OfaUsouDHNRMucbHZOVSc+z7LOpDJ6r4fTBRxjphPbzpCIG1NvyoTsFrsbC3PdsZOY7n/hM+LbLakMcbBnW/JFOI+0XpPusiWP/NjTx80f1K7p0+zbxx0LTFms18zviNxpzYh4U85gJw+z5CN3/bejRvGdMeRCq+gB86vLwGZNFvZrgPLGHGQHuv42lio+rC6z7O8D7L1HiRPlI8ZDa+0HvTuBgH175QiGzcuVqVRAlwghVCR551iBlwcftHkWErxjjFN0ZBr0UcZG91Whq31D4WW4XEkkPrCH/7lxz4MEkUC7aXaMAKgA6AVwEqrGe6nSasG9Uw66aOB9pueaxTP6M333revnBiyYLBuB+eGCTFMGUpoZi4/0uXwsFISPG1zogkvjmcfjmE0QRBr9M8bDfUje9clVNeqBLM4rV65A34m3E8jxDItraSX8+l3EcY3B6Syi7uK3mS5FHzOd5JEpm8KLew421FF9sngf9kOt';
        $Subject = 'test111';
        $Body = 'test333333';
        $ItemID = '151832656239';
        $RecipientIDArray = array(
            '9cn8334'
        );
        $MessageMediaArray = array();
        $QuestionType = 'General';
        
        echo MsgDownModel::model()->addMemberMessageAAQToPartner($token, $Subject, $Body, $ItemID, $RecipientIDArray, $MessageMediaArray, $QuestionType);
        // echo EbayOtherInfoModel::model()->eBayGetAccount($token);
    }

    public function actionTest()
    {
        $dirs = scandir('public/lang/');
        $langlist = array();
        foreach ($dirs as $key => $value) {
            if ($value == '.' || $value == '..') {} else {
                $_langname = @file_get_contents('public/lang/' . $value . '/_langname.txt');
                if ($_langname) {
                    $langlist[$value] = $_langname;
                }
            }
        }
        var_dump($langlist);
    }

    public function actionTest2()
    {
        // $columns = '*';
        // $conditions = 'true';
        // $params = array();
        // $xx = EbayOrdersDAO::getInstance()->iselect($columns, $conditions, $params, true);
        // var_dump(count($xx));
        // var_dump(isset($xx));
        // $columns = array(
        // 'ebay_user_info_id' => 444444443,
        // 'shop_id' => 444444443
        // );
        // $conditions = 'ebay_user_info_id=:ebay_user_info_id and shop_id=:shop_id';
        // $params = array(
        // ':ebay_user_info_id' => 444444443,
        // ':shop_id' => 444444443
        // );
        // EbayUserShopsDAO::getInstance()->ireplaceinto($columns, $conditions, $params, true, false);
        // $columns = array(
        // 'ebay_orders_id' => 9999919,
        // 'ExternalTransactionID' => 'dddddddddddddd',
        // 'create_time' => time()
        // );
        // $conditions = 'ebay_orders_id=:ebay_orders_id and ExternalTransactionID=:ExternalTransactionID';
        // $params = array(
        // ':ebay_orders_id' => 9999919,
        // ':ExternalTransactionID' => 'dddddddddddddd'
        // );
        // EbayOrderExtTransDAO::getInstance()->ireplaceinto($columns, $conditions, $params);
    }

    public function actionTest3()
    {
        imsTool::xmlHeader();
        
        $token = 'AgAAAA**AQAAAA**aAAAAA**lP4+VQ**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6AElYGkAZKFogydj6x9nY+seQ**+uQAAA**AAMAAA**+P6R0gQ0z30XPNdYXYVHcHueeIp9vlyg2uN9lhDlJFwQ4KGhzl9trw013I91BCGemaTzUEHdArF0yUFZV6qHMdY9Vme/Ii4sUD9YmjwsYDKiX3Tr7e6wnfvGqO7HJtL8jGPb/iyciMiBMFBZRLaK3BQzYCTgQrsRVWrkZXkCaSCPKpKqhPtqa8Qv7sbBHmqCGkaGHW2eEqZTQWYCVua1kmi74XbU4JFvHEzZy+JRtn620er5vDoA8l+5zKzpQR2ofxnteFd2gO5g5GQsGi7pWr5vAsBD2lLPuaWgcoH2IDrwBfsoi3XTAEqQfwWJLRU2fR2z399NwnVJxmJZYYZJyarfgbLsroRzALoh67ld46auITYSPDx/tdWQQ0v8miebxyR+Ev9drivX7Iev6+ujjTitJMM4hbDMQP4wUGwv6fObhkkpgSkprNpnpQtwYgqJnkVyoPi4VgKJjVkn2zZMYxvzZsGv83T9lm3esSST1y3wbnQbFoVxWbmIwax0ybsLIQ8j2HIIlO+7DGpyRcX2vcQgP4HJSWt1fMW5JkOxZj25YNLONhDRfxR/9lmniO8eEcVbX4G4nf6XL/RUrys3+jwBmlZC7Bwcjvdz5YVlwbvY/2aA/ubshj6fgCVvTL/+gfA4GxlhW+3ucF/xLWoLm14ysKaFb6inxBeAGL1zw9a5fjVsaetIG8GRxFms7ICX/M//HxH5h5bMzUDu7S2qApLA7xcK0ng9HFuNeFsc39KZZLwuJZnWCn5sSB1KFZci';
        $ShippingServiceOptions = array(
            array(
                'ShippingService' => 'UK_RoyalMailAirmailInternational',
                'currencyID' => 'GBP',
                'ShippingServiceCost' => '1'
            )
        );
        $AdjustmentAmount = - 0.2;
        $AdjustmentAmountCurrencyID = 'GBP';
        $CheckoutInstructions = 'hahahahaahahahaha';
        // $OrderLineItemID = '272001633126-1553717686017';
        $OrderLineItemID = '272001633088-1554588120017';
        // $OrderID = '198704671010';
        $OrderID = '';
        $siteid = 3;
        
        echo EbayOtherInfoModel::model()->eBaySendInvoice($token, $ShippingServiceOptions, $AdjustmentAmount, $AdjustmentAmountCurrencyID, $CheckoutInstructions, $OrderLineItemID, $OrderID, $siteid);
    }

    public function actionTest4()
    {
        imsTool::xmlHeader();
        
        $token = 'AgAAAA**AQAAAA**aAAAAA**lP4+VQ**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6AElYGkAZKFogydj6x9nY+seQ**+uQAAA**AAMAAA**+P6R0gQ0z30XPNdYXYVHcHueeIp9vlyg2uN9lhDlJFwQ4KGhzl9trw013I91BCGemaTzUEHdArF0yUFZV6qHMdY9Vme/Ii4sUD9YmjwsYDKiX3Tr7e6wnfvGqO7HJtL8jGPb/iyciMiBMFBZRLaK3BQzYCTgQrsRVWrkZXkCaSCPKpKqhPtqa8Qv7sbBHmqCGkaGHW2eEqZTQWYCVua1kmi74XbU4JFvHEzZy+JRtn620er5vDoA8l+5zKzpQR2ofxnteFd2gO5g5GQsGi7pWr5vAsBD2lLPuaWgcoH2IDrwBfsoi3XTAEqQfwWJLRU2fR2z399NwnVJxmJZYYZJyarfgbLsroRzALoh67ld46auITYSPDx/tdWQQ0v8miebxyR+Ev9drivX7Iev6+ujjTitJMM4hbDMQP4wUGwv6fObhkkpgSkprNpnpQtwYgqJnkVyoPi4VgKJjVkn2zZMYxvzZsGv83T9lm3esSST1y3wbnQbFoVxWbmIwax0ybsLIQ8j2HIIlO+7DGpyRcX2vcQgP4HJSWt1fMW5JkOxZj25YNLONhDRfxR/9lmniO8eEcVbX4G4nf6XL/RUrys3+jwBmlZC7Bwcjvdz5YVlwbvY/2aA/ubshj6fgCVvTL/+gfA4GxlhW+3ucF/xLWoLm14ysKaFb6inxBeAGL1zw9a5fjVsaetIG8GRxFms7ICX/M//HxH5h5bMzUDu7S2qApLA7xcK0ng9HFuNeFsc39KZZLwuJZnWCn5sSB1KFZci';
        
        $OrderIDArray = array();
        $CreateTimeFrom = '';
        $CreateTimeTo = '';
        $OrderStatus = '';
        $PageNumber = 2;
        $EntriesPerPage = 100;
        $ListingType = '';
        $ModTimeFrom = 0;
        $ModTimeTo = 0;
        $NumberOfDays = 10;
        
        echo EbayOtherInfoModel::model()->eBayGetOrders($token, $OrderIDArray, $CreateTimeFrom, $CreateTimeTo, $OrderStatus, $PageNumber, $EntriesPerPage, $ListingType, $ModTimeFrom, $ModTimeTo, $NumberOfDays);
    }

    public function actionTest5()
    {
        $pkglst = array(
            'ETX151102A1M000204',
            'EYX151102A1M000090',
            'EYX151102A1M000203',
            'ETX151102A1M000150',
            'EYX151102A1M000089',
            'ETX151102A1M000088',
            'ETX151102A1M000202',
            'ETX151102A1M000201',
            'ETX151102A1M000118',
            'EYX151102A1M000139'
        );
        // echo LogisticsModel::model()->getPackages($pkglst);
        // echo LogisticsModel::model()->getBills($pkglst);
        echo LogisticsModel::model()->getTrackings($pkglst);
    }

    public function actionTest6()
    {
        LogisticsModel::model()->updateTracking();
    }

    public function actionTest7()
    {
        // echo AutoLabelModel::model()->getMsgLabel(11, $error);
        echo AutoLabelModel::model()->updateMsgAutoLabel();
    }

    public function actionTest8()
    {
        imsTool::xmlHeader();
        
        $fromDate = time() - 3600 * 24 * 888;
        $toDate = time() - 3600 * 24 * 888 + 3600 * 24;
        
        $token = 'AgAAAA**AQAAAA**aAAAAA**lP4+VQ**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6AElYGkAZKFogydj6x9nY+seQ**+uQAAA**AAMAAA**+P6R0gQ0z30XPNdYXYVHcHueeIp9vlyg2uN9lhDlJFwQ4KGhzl9trw013I91BCGemaTzUEHdArF0yUFZV6qHMdY9Vme/Ii4sUD9YmjwsYDKiX3Tr7e6wnfvGqO7HJtL8jGPb/iyciMiBMFBZRLaK3BQzYCTgQrsRVWrkZXkCaSCPKpKqhPtqa8Qv7sbBHmqCGkaGHW2eEqZTQWYCVua1kmi74XbU4JFvHEzZy+JRtn620er5vDoA8l+5zKzpQR2ofxnteFd2gO5g5GQsGi7pWr5vAsBD2lLPuaWgcoH2IDrwBfsoi3XTAEqQfwWJLRU2fR2z399NwnVJxmJZYYZJyarfgbLsroRzALoh67ld46auITYSPDx/tdWQQ0v8miebxyR+Ev9drivX7Iev6+ujjTitJMM4hbDMQP4wUGwv6fObhkkpgSkprNpnpQtwYgqJnkVyoPi4VgKJjVkn2zZMYxvzZsGv83T9lm3esSST1y3wbnQbFoVxWbmIwax0ybsLIQ8j2HIIlO+7DGpyRcX2vcQgP4HJSWt1fMW5JkOxZj25YNLONhDRfxR/9lmniO8eEcVbX4G4nf6XL/RUrys3+jwBmlZC7Bwcjvdz5YVlwbvY/2aA/ubshj6fgCVvTL/+gfA4GxlhW+3ucF/xLWoLm14ysKaFb6inxBeAGL1zw9a5fjVsaetIG8GRxFms7ICX/M//HxH5h5bMzUDu7S2qApLA7xcK0ng9HFuNeFsc39KZZLwuJZnWCn5sSB1KFZci';
        // $token = 'AgAAAA**AQAAAA**aAAAAA**3U5IVg**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6AHlYekC5eAogWdj6x9nY+seQ**B8ICAA**AAMAAA**35EtFx9HksbeLsXDLdYKTKgdVZUGV+E855j+gAF9AzgEbJ54VkZcp87Z9Pbk0TQHGWQrcGSMsPoFz+ESJ+aUXECo5DCKUHdGLD3yXtRyt3+HAsJ3R7DRYdpxFN/y2k+vi+U56i8Hsm3C6btSXohyGkJF9HAPiJJCutDKYCMPIdVY5mCd8L04rD7O6+nGytj0DPxLtR3CvfCTRH9dMPh3xfFg3nW9x6M3oF+mUBFUK923dDk/p0P4Tq6bnBfCwjIF+qiTfAFRn4Vm4Q8y+RGl43BJs3QW8SSj6WfvgbarByKwguRV+f8ua/3Ftvv41eshzF3dk00VKf6dpx6IAzdRmgzJj08HljQdZHCmsZOhphTAPpSSBN9D3iIUNh/E//+f2nTBVLecZrP0aKwMZEEBkf+X4L2NIx6gwPacyEmrKWmKYZwk4/H+Y0fYhvQyJnaUeGbCS4ow5Kkt4WzeV19xaXGPxMwr1xRF9k1qj6Ku4fq4RZQ4Aw5D3TeyTsx36ZGf+uCkDn3Y4zSlM19EpvDNuGx9f4vUK/mL86CFBB/jjbbaLNDSQDUa95QO4yy8qDzQlqYQl+fOPOFF88UR85RN4idjvbEPyBJQv08+Fa7ivPjF17r7y0iJ52V+xyUIg1HztOoB7yDS6BHWPAsrQRLwg8zflMjvAWGfmiv+LUvSqz32Cd0kGeR6xOlbZ3TOE3xTv6t5R3t/pso804QcE/kuw1NWAJGKvUERdIwvI6bK6SOWZZnnW3FvAqIOaGESXUGG';
        echo CaseDownModel::model()->getUserCases($fromDate, $toDate, $token);
    }

    public function actionTest9()
    {
        imsTool::xmlHeader();
        
        $fromDate = time() - 3600 * 24 * 30;
        $toDate = time();
        
        $token = 'AgAAAA**AQAAAA**aAAAAA**lP4+VQ**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6AElYGkAZKFogydj6x9nY+seQ**+uQAAA**AAMAAA**+P6R0gQ0z30XPNdYXYVHcHueeIp9vlyg2uN9lhDlJFwQ4KGhzl9trw013I91BCGemaTzUEHdArF0yUFZV6qHMdY9Vme/Ii4sUD9YmjwsYDKiX3Tr7e6wnfvGqO7HJtL8jGPb/iyciMiBMFBZRLaK3BQzYCTgQrsRVWrkZXkCaSCPKpKqhPtqa8Qv7sbBHmqCGkaGHW2eEqZTQWYCVua1kmi74XbU4JFvHEzZy+JRtn620er5vDoA8l+5zKzpQR2ofxnteFd2gO5g5GQsGi7pWr5vAsBD2lLPuaWgcoH2IDrwBfsoi3XTAEqQfwWJLRU2fR2z399NwnVJxmJZYYZJyarfgbLsroRzALoh67ld46auITYSPDx/tdWQQ0v8miebxyR+Ev9drivX7Iev6+ujjTitJMM4hbDMQP4wUGwv6fObhkkpgSkprNpnpQtwYgqJnkVyoPi4VgKJjVkn2zZMYxvzZsGv83T9lm3esSST1y3wbnQbFoVxWbmIwax0ybsLIQ8j2HIIlO+7DGpyRcX2vcQgP4HJSWt1fMW5JkOxZj25YNLONhDRfxR/9lmniO8eEcVbX4G4nf6XL/RUrys3+jwBmlZC7Bwcjvdz5YVlwbvY/2aA/ubshj6fgCVvTL/+gfA4GxlhW+3ucF/xLWoLm14ysKaFb6inxBeAGL1zw9a5fjVsaetIG8GRxFms7ICX/M//HxH5h5bMzUDu7S2qApLA7xcK0ng9HFuNeFsc39KZZLwuJZnWCn5sSB1KFZci';
        $token = 'AgAAAA**AQAAAA**aAAAAA**lP4+VQ**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6AElYGkAZKFogydj6x9nY+seQ**+uQAAA**AAMAAA**+P6R0gQ0z30XPNdYXYVHcHueeIp9vlyg2uN9lhDlJFwQ4KGhzl9trw013I91BCGemaTzUEHdArF0yUFZV6qHMdY9Vme/Ii4sUD9YmjwsYDKiX3Tr7e6wnfvGqO7HJtL8jGPb/iyciMiBMFBZRLaK3BQzYCTgQrsRVWrkZXkCaSCPKpKqhPtqa8Qv7sbBHmqCGkaGHW2eEqZTQWYCVua1kmi74XbU4JFvHEzZy+JRtn620er5vDoA8l+5zKzpQR2ofxnteFd2gO5g5GQsGi7pWr5vAsBD2lLPuaWgcoH2IDrwBfsoi3XTAEqQfwWJLRU2fR2z399NwnVJxmJZYYZJyarfgbLsroRzALoh67ld46auITYSPDx/tdWQQ0v8miebxyR+Ev9drivX7Iev6+ujjTitJMM4hbDMQP4wUGwv6fObhkkpgSkprNpnpQtwYgqJnkVyoPi4VgKJjVkn2zZMYxvzZsGv83T9lm3esSST1y3wbnQbFoVxWbmIwax0ybsLIQ8j2HIIlO+7DGpyRcX2vcQgP4HJSWt1fMW5JkOxZj25YNLONhDRfxR/9lmniO8eEcVbX4G4nf6XL/RUrys3+jwBmlZC7Bwcjvdz5YVlwbvY/2aA/ubshj6fgCVvTL/+gfA4GxlhW+3ucF/xLWoLm14ysKaFb6inxBeAGL1zw9a5fjVsaetIG8GRxFms7ICX/M//HxH5h5bMzUDu7S2qApLA7xcK0ng9HFuNeFsc39KZZLwuJZnWCn5sSB1KFZci';
        echo CaseDownModel::model()->getEBPCaseDetail('5093834383', 'EBP_INR', $token);
    }

    public function actionTest10()
    {
        imsTool::xmlHeader();
        
        $fromDate = time() - 3600 * 24 * 30;
        $toDate = time();
        
        $token = 'AgAAAA**AQAAAA**aAAAAA**lP4+VQ**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6AElYGkAZKFogydj6x9nY+seQ**+uQAAA**AAMAAA**+P6R0gQ0z30XPNdYXYVHcHueeIp9vlyg2uN9lhDlJFwQ4KGhzl9trw013I91BCGemaTzUEHdArF0yUFZV6qHMdY9Vme/Ii4sUD9YmjwsYDKiX3Tr7e6wnfvGqO7HJtL8jGPb/iyciMiBMFBZRLaK3BQzYCTgQrsRVWrkZXkCaSCPKpKqhPtqa8Qv7sbBHmqCGkaGHW2eEqZTQWYCVua1kmi74XbU4JFvHEzZy+JRtn620er5vDoA8l+5zKzpQR2ofxnteFd2gO5g5GQsGi7pWr5vAsBD2lLPuaWgcoH2IDrwBfsoi3XTAEqQfwWJLRU2fR2z399NwnVJxmJZYYZJyarfgbLsroRzALoh67ld46auITYSPDx/tdWQQ0v8miebxyR+Ev9drivX7Iev6+ujjTitJMM4hbDMQP4wUGwv6fObhkkpgSkprNpnpQtwYgqJnkVyoPi4VgKJjVkn2zZMYxvzZsGv83T9lm3esSST1y3wbnQbFoVxWbmIwax0ybsLIQ8j2HIIlO+7DGpyRcX2vcQgP4HJSWt1fMW5JkOxZj25YNLONhDRfxR/9lmniO8eEcVbX4G4nf6XL/RUrys3+jwBmlZC7Bwcjvdz5YVlwbvY/2aA/ubshj6fgCVvTL/+gfA4GxlhW+3ucF/xLWoLm14ysKaFb6inxBeAGL1zw9a5fjVsaetIG8GRxFms7ICX/M//HxH5h5bMzUDu7S2qApLA7xcK0ng9HFuNeFsc39KZZLwuJZnWCn5sSB1KFZci';
        echo CaseDownModel::model()->getActivityOptions('5091946906', 'EBP_INR', $token);
    }

    public function actionTest11()
    {
        imsTool::xmlHeader();
        
        $fromDate = time() - 3600 * 24 * 30;
        $toDate = time();
        
        $token = 'AgAAAA**AQAAAA**aAAAAA**lP4+VQ**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6AElYGkAZKFogydj6x9nY+seQ**+uQAAA**AAMAAA**+P6R0gQ0z30XPNdYXYVHcHueeIp9vlyg2uN9lhDlJFwQ4KGhzl9trw013I91BCGemaTzUEHdArF0yUFZV6qHMdY9Vme/Ii4sUD9YmjwsYDKiX3Tr7e6wnfvGqO7HJtL8jGPb/iyciMiBMFBZRLaK3BQzYCTgQrsRVWrkZXkCaSCPKpKqhPtqa8Qv7sbBHmqCGkaGHW2eEqZTQWYCVua1kmi74XbU4JFvHEzZy+JRtn620er5vDoA8l+5zKzpQR2ofxnteFd2gO5g5GQsGi7pWr5vAsBD2lLPuaWgcoH2IDrwBfsoi3XTAEqQfwWJLRU2fR2z399NwnVJxmJZYYZJyarfgbLsroRzALoh67ld46auITYSPDx/tdWQQ0v8miebxyR+Ev9drivX7Iev6+ujjTitJMM4hbDMQP4wUGwv6fObhkkpgSkprNpnpQtwYgqJnkVyoPi4VgKJjVkn2zZMYxvzZsGv83T9lm3esSST1y3wbnQbFoVxWbmIwax0ybsLIQ8j2HIIlO+7DGpyRcX2vcQgP4HJSWt1fMW5JkOxZj25YNLONhDRfxR/9lmniO8eEcVbX4G4nf6XL/RUrys3+jwBmlZC7Bwcjvdz5YVlwbvY/2aA/ubshj6fgCVvTL/+gfA4GxlhW+3ucF/xLWoLm14ysKaFb6inxBeAGL1zw9a5fjVsaetIG8GRxFms7ICX/M//HxH5h5bMzUDu7S2qApLA7xcK0ng9HFuNeFsc39KZZLwuJZnWCn5sSB1KFZci';
        echo CaseDownModel::model()->getDispute('651244970', $token);
    }

    public function actionTest12()
    {
        $key = 1111;
        var_dump(CRedisHelper::getInstance()->get($key));
    }

    public function actionTest13()
    {
        imsTool::xmlHeader();
        
        $fromDate = time() - 3600 * 24 * 30;
        $toDate = time();
        
        $token = 'AgAAAA**AQAAAA**aAAAAA**lP4+VQ**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6AElYGkAZKFogydj6x9nY+seQ**+uQAAA**AAMAAA**+P6R0gQ0z30XPNdYXYVHcHueeIp9vlyg2uN9lhDlJFwQ4KGhzl9trw013I91BCGemaTzUEHdArF0yUFZV6qHMdY9Vme/Ii4sUD9YmjwsYDKiX3Tr7e6wnfvGqO7HJtL8jGPb/iyciMiBMFBZRLaK3BQzYCTgQrsRVWrkZXkCaSCPKpKqhPtqa8Qv7sbBHmqCGkaGHW2eEqZTQWYCVua1kmi74XbU4JFvHEzZy+JRtn620er5vDoA8l+5zKzpQR2ofxnteFd2gO5g5GQsGi7pWr5vAsBD2lLPuaWgcoH2IDrwBfsoi3XTAEqQfwWJLRU2fR2z399NwnVJxmJZYYZJyarfgbLsroRzALoh67ld46auITYSPDx/tdWQQ0v8miebxyR+Ev9drivX7Iev6+ujjTitJMM4hbDMQP4wUGwv6fObhkkpgSkprNpnpQtwYgqJnkVyoPi4VgKJjVkn2zZMYxvzZsGv83T9lm3esSST1y3wbnQbFoVxWbmIwax0ybsLIQ8j2HIIlO+7DGpyRcX2vcQgP4HJSWt1fMW5JkOxZj25YNLONhDRfxR/9lmniO8eEcVbX4G4nf6XL/RUrys3+jwBmlZC7Bwcjvdz5YVlwbvY/2aA/ubshj6fgCVvTL/+gfA4GxlhW+3ucF/xLWoLm14ysKaFb6inxBeAGL1zw9a5fjVsaetIG8GRxFms7ICX/M//HxH5h5bMzUDu7S2qApLA7xcK0ng9HFuNeFsc39KZZLwuJZnWCn5sSB1KFZci';
        $token = 'AgAAAA**AQAAAA**aAAAAA**3U5IVg**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6AHlYekC5eAogWdj6x9nY+seQ**B8ICAA**AAMAAA**35EtFx9HksbeLsXDLdYKTKgdVZUGV+E855j+gAF9AzgEbJ54VkZcp87Z9Pbk0TQHGWQrcGSMsPoFz+ESJ+aUXECo5DCKUHdGLD3yXtRyt3+HAsJ3R7DRYdpxFN/y2k+vi+U56i8Hsm3C6btSXohyGkJF9HAPiJJCutDKYCMPIdVY5mCd8L04rD7O6+nGytj0DPxLtR3CvfCTRH9dMPh3xfFg3nW9x6M3oF+mUBFUK923dDk/p0P4Tq6bnBfCwjIF+qiTfAFRn4Vm4Q8y+RGl43BJs3QW8SSj6WfvgbarByKwguRV+f8ua/3Ftvv41eshzF3dk00VKf6dpx6IAzdRmgzJj08HljQdZHCmsZOhphTAPpSSBN9D3iIUNh/E//+f2nTBVLecZrP0aKwMZEEBkf+X4L2NIx6gwPacyEmrKWmKYZwk4/H+Y0fYhvQyJnaUeGbCS4ow5Kkt4WzeV19xaXGPxMwr1xRF9k1qj6Ku4fq4RZQ4Aw5D3TeyTsx36ZGf+uCkDn3Y4zSlM19EpvDNuGx9f4vUK/mL86CFBB/jjbbaLNDSQDUa95QO4yy8qDzQlqYQl+fOPOFF88UR85RN4idjvbEPyBJQv08+Fa7ivPjF17r7y0iJ52V+xyUIg1HztOoB7yDS6BHWPAsrQRLwg8zflMjvAWGfmiv+LUvSqz32Cd0kGeR6xOlbZ3TOE3xTv6t5R3t/pso804QcE/kuw1NWAJGKvUERdIwvI6bK6SOWZZnnW3FvAqIOaGESXUGG';
        echo MsgDownModel::model()->getMyMessages($token, 'ReturnHeaders', 0, $fromDate, $toDate, array());
    }

    public function actionTest14()
    {
        imsTool::xmlHeader();
        
        $fromDate = 0;
        $toDate = 0;
        
        $token = 'AgAAAA**AQAAAA**aAAAAA**lP4+VQ**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6AElYGkAZKFogydj6x9nY+seQ**+uQAAA**AAMAAA**+P6R0gQ0z30XPNdYXYVHcHueeIp9vlyg2uN9lhDlJFwQ4KGhzl9trw013I91BCGemaTzUEHdArF0yUFZV6qHMdY9Vme/Ii4sUD9YmjwsYDKiX3Tr7e6wnfvGqO7HJtL8jGPb/iyciMiBMFBZRLaK3BQzYCTgQrsRVWrkZXkCaSCPKpKqhPtqa8Qv7sbBHmqCGkaGHW2eEqZTQWYCVua1kmi74XbU4JFvHEzZy+JRtn620er5vDoA8l+5zKzpQR2ofxnteFd2gO5g5GQsGi7pWr5vAsBD2lLPuaWgcoH2IDrwBfsoi3XTAEqQfwWJLRU2fR2z399NwnVJxmJZYYZJyarfgbLsroRzALoh67ld46auITYSPDx/tdWQQ0v8miebxyR+Ev9drivX7Iev6+ujjTitJMM4hbDMQP4wUGwv6fObhkkpgSkprNpnpQtwYgqJnkVyoPi4VgKJjVkn2zZMYxvzZsGv83T9lm3esSST1y3wbnQbFoVxWbmIwax0ybsLIQ8j2HIIlO+7DGpyRcX2vcQgP4HJSWt1fMW5JkOxZj25YNLONhDRfxR/9lmniO8eEcVbX4G4nf6XL/RUrys3+jwBmlZC7Bwcjvdz5YVlwbvY/2aA/ubshj6fgCVvTL/+gfA4GxlhW+3ucF/xLWoLm14ysKaFb6inxBeAGL1zw9a5fjVsaetIG8GRxFms7ICX/M//HxH5h5bMzUDu7S2qApLA7xcK0ng9HFuNeFsc39KZZLwuJZnWCn5sSB1KFZci';
        $token = 'AgAAAA**AQAAAA**aAAAAA**rP4+VQ**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6AElYGnCZCKqQWdj6x9nY+seQ**+uQAAA**AAMAAA**tma6zy97NSi6G3jt5WnacoCBJAi6ktVBAAto65Atv78VxBduj0oMyRV9HKVIB3L+wQx1RdIWh0/+ApasGqmuLzQ4+TlFLIuBncfE4M+vmt8vUaBtgq+RBYNACraajahA0uXz+Ud0WyynJUpIEpb5hkTj2ZbwZ8qdwHGXTjY5F2knWVuSQZXDT9iPxexAxUacVk9HXR9BheMxF7aDFEL998XcSXlv2/84V5wlUVtSRMeIyR1zSA75Li+Li8lbzwAG958skNavVj8GLEwwIR1271wj5KVN3qpNlJsIkmGrLsTiL79/CrR9zeoOw3A6tNhDRu1D0n7q/a9l5CXBKbzKPvTqk34rWl3NYkQ+MqtfPXA0MLRJi4r33Q8MrvofFFRWNNLjrQ9APlpyEYYGitaPriOTf0aaDmQUT83kPdLeeuPrZPJaxJRyV3vB2r2ewc1sqB4nsxO5dFQXA7KhGGTyfMkFkoTfNkZfYzuiFfw7jiLCF3M2dF3POoEXCWoN8NjJA6viUi6QrbVyrqsZERdr5/++5AbDZ+ymdpvW3GlIOLRPrhMLWPseEYgCIs8VOxWPrqwGvLHeCYtJpXokJ42LnbsQuiEgljGtTBls9LWaCjRCFQvgqFSeoRkoxYQvcB4yzJTU6rgZcDa4davQjhlpPyBhlIOiFLnAuxQ6efDBpNmgy+azW/dfPNT4q78HWWaZXIm6o8ZG3wCVUBXbKn3OgzX7yPGsoCvR5LvQfgxC3cCwDJmJ2prJjlypJXgDGSsN';
        echo MsgDownModel::model()->getMyMessages($token, 'ReturnMessages', 0, $fromDate, $toDate, array(
            '72322308559'
        ));
    }

    public function actionTest15()
    {
        $xml = <<<eot
<?xml version='1.0' encoding='UTF-8'?>
            <getEBPCaseDetailResponse xmlns="http://www.ebay.com/marketplace/resolution/v1/services"><ack>在所有字符集中，最知名的可能要数被称为ASCII的7位字符集了。它是美国标准信息交换代码（American Standard Code for Information Interchange）的缩写, 为美国英语通信所设计。它由128个字符组成，包括大小写字母、数字0-9、标点符号、非打印字符（换行符、制表符等4个）以及控制字符（退格、响铃等）组成。
但是，由于他是针对英语设计的，当处理带有音调标号（形如汉语的拼音）的亚洲文字时就会出现问题。因此，创建出了一些包括255个字符的由ASCII扩展的字符集。其中有一种通常被称为IBM字符集，它把值为128-255之间的字符用于画图和画线，以及一些特殊的欧洲字符。另一种8位字符集是ISO 8859-1Latin 1，也简称为ISOLatin-1。它把位于128-255之间的字符用于拉丁字母表中特殊语言字符的编码，也因此而得名。
ASCII码格式
ASCII码格式
欧洲语言不是地球上的唯一语言，因此亚洲和非洲语言并不能被8位字符集所支持。仅汉语字母表（或pictograms）就有80000以上个字符。但是把汉语、日语和越南语的一些相似的字符结合起来，在不同的语言里，使不同的字符代表不同的字，这样只用2个字节就可以编码地球上几乎所有地区的文字。因此，创建了UNICODE编码。它通过增加一个高字节对ISO Latin-1字符集进行扩展，当这些高字节位为0时，低字节就是ISO Latin-1字符。UNICODE支持欧洲、非洲、中东、亚洲（包括统一标准的东亚象形汉字和韩国表音文字）。但是，UNICODE并没有提供对诸如Braille,Cherokee, Ethiopic, Khmer, Mongolian, Hmong, Tai Lu, Tai Mau文字的支持。同时它也不支持如Ahom, Akkadian, Aramaic, Babylonian Cuneiform, Balti, Brahmi, Etruscan, Hittite, Javanese, Numidian, Old Persian Cuneiform, Syrian之类的古老文字。Success</ack><version>1.3.0</version><timestamp>2015-11-17T09:13:48.993Z</timestamp><caseSummary><caseId><id>5017669485</id><type>EBP_SNAD</type></caseId><user><userId>wfgetsell</userId><role>SELLER</role></user><otherParty><userId>wicker611</userId><role>BUYER</role></otherParty><status><EBPSNADStatus>MY_RESPONSE_DUE</EBPSNADStatus></status><item><itemId>230998659120</itemId><itemTitle>30W/50W/70W/100W LED Floodlight Flood Light Waterproof IP65 Outdoor Garden Lamp</itemTitle><transactionPrice currencyId="GBP">26.09</transactionPrice><transactionDate>2012-06-26T12:34:45.000Z</transactionDate><globalId>EBAY_MAIN</globalId><transactionId>1236638821013</transactionId></item><caseQuantity>4</caseQuantity><caseAmount>104.36</caseAmount><respondByDate>2015-11-15T02:59:17.000Z</respondByDate></caseSummary><caseDetail><openReason>Unknown</openReason><decisionReasonDetail><code>16008</code><description>Item was not as described</description><content>The buyer claimed that the item didn't match the description.</content></decisionReasonDetail><decisionDate>2012-07-06T08:14:56.000Z</decisionDate><decision>OTHER</decision><FVFCredited>false</FVFCredited><globalId>EBAY_MAIN</globalId><appeal id="A.5000448938"><decision>SELLER_WINS</decision><decisionDate>2012-07-10T20:44:10.000Z</decisionDate><creationDate>2012-07-06T09:35:56.000Z</creationDate></appeal><agreedRefundAmount>501.0</agreedRefundAmount><buyerReturnShipment><trackingNumber>1ZOA293F4292713024</trackingNumber><carrierUsed>other</carrierUsed><shippingCost>0.0</shippingCost><shippingAddress><name>Tim VANVranken</name><street1>4 Beartooth Way</street1><city>Laurel</city><stateOrProvince>Montana</stateOrProvince><country>US</country><postalCode>59044</postalCode></shippingAddress></buyerReturnShipment><initialBuyerExpectationDetail><code>114</code><description>Buyer wants an alternative solution.</description></initialBuyerExpectationDetail><caseDocumentInfo><name>5017669485.jpg</name><type>OTHER</type><uploadDate>2012-07-09T20:56:18.000Z</uploadDate></caseDocumentInfo></caseDetail></getEBPCaseDetailResponse>
eot;
        // $xml = file_get_contents('5017669485.LOG');
        
        echo strlen($xml);
        echo "\n";
        echo strlen(preg_replace('/[\x00-\x08]/u', '', $xml));
        // echo preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $xml);
        
        // echo phpQuery::newDocumentXML($xml);
    }

    public function actionTest16()
    {
        // require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'package/JSONParser.php';
        error_reporting(E_ALL);

        function objStart($value, $property)
        {
            printf("{\n");
        }

        function objEnd($value, $property)
        {
            printf("}\n");
        }

        function arrayStart($value, $property)
        {
            var_dump('--------');
            var_dump($value, $property);
            var_dump('--------');
            printf("[\n");
        }

        function arrayEnd($value, $property)
        {
            printf("]\n");
        }

        function property($value, $property)
        {
            printf("Property: %s\n", $value);
        }

        function scalar($value, $property)
        {
            printf("Value: %s\n", $value);
        }
        
        // initialise the parser object
        $parser = new JSONParser();
        
        // sets the callbacks
        $parser->setArrayHandlers('arrayStart', 'arrayEnd');
        $parser->setObjectHandlers('objStart', 'objEnd');
        $parser->setPropertyHandler('property');
        $parser->setScalarHandler('scalar');
        
        echo "Parsing top level object document...\n";
        // parse the document
        $parser->parseDocument('data.json');
        
        $parser->initialise();
        
        echo "Parsing top level array document...\n";
        // parse the top level array
        $parser->parseDocument('array.json');
    }

    public function actionTest17()
    {
        imsTool::xmlHeader();
        
        $fromDate = 0;
        $toDate = 0;
        
        $token = 'AgAAAA**AQAAAA**aAAAAA**lP4+VQ**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6AElYGkAZKFogydj6x9nY+seQ**+uQAAA**AAMAAA**+P6R0gQ0z30XPNdYXYVHcHueeIp9vlyg2uN9lhDlJFwQ4KGhzl9trw013I91BCGemaTzUEHdArF0yUFZV6qHMdY9Vme/Ii4sUD9YmjwsYDKiX3Tr7e6wnfvGqO7HJtL8jGPb/iyciMiBMFBZRLaK3BQzYCTgQrsRVWrkZXkCaSCPKpKqhPtqa8Qv7sbBHmqCGkaGHW2eEqZTQWYCVua1kmi74XbU4JFvHEzZy+JRtn620er5vDoA8l+5zKzpQR2ofxnteFd2gO5g5GQsGi7pWr5vAsBD2lLPuaWgcoH2IDrwBfsoi3XTAEqQfwWJLRU2fR2z399NwnVJxmJZYYZJyarfgbLsroRzALoh67ld46auITYSPDx/tdWQQ0v8miebxyR+Ev9drivX7Iev6+ujjTitJMM4hbDMQP4wUGwv6fObhkkpgSkprNpnpQtwYgqJnkVyoPi4VgKJjVkn2zZMYxvzZsGv83T9lm3esSST1y3wbnQbFoVxWbmIwax0ybsLIQ8j2HIIlO+7DGpyRcX2vcQgP4HJSWt1fMW5JkOxZj25YNLONhDRfxR/9lmniO8eEcVbX4G4nf6XL/RUrys3+jwBmlZC7Bwcjvdz5YVlwbvY/2aA/ubshj6fgCVvTL/+gfA4GxlhW+3ucF/xLWoLm14ysKaFb6inxBeAGL1zw9a5fjVsaetIG8GRxFms7ICX/M//HxH5h5bMzUDu7S2qApLA7xcK0ng9HFuNeFsc39KZZLwuJZnWCn5sSB1KFZci';
        $token = 'AgAAAA**AQAAAA**aAAAAA**rP4+VQ**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6AElYGnCZCKqQWdj6x9nY+seQ**+uQAAA**AAMAAA**tma6zy97NSi6G3jt5WnacoCBJAi6ktVBAAto65Atv78VxBduj0oMyRV9HKVIB3L+wQx1RdIWh0/+ApasGqmuLzQ4+TlFLIuBncfE4M+vmt8vUaBtgq+RBYNACraajahA0uXz+Ud0WyynJUpIEpb5hkTj2ZbwZ8qdwHGXTjY5F2knWVuSQZXDT9iPxexAxUacVk9HXR9BheMxF7aDFEL998XcSXlv2/84V5wlUVtSRMeIyR1zSA75Li+Li8lbzwAG958skNavVj8GLEwwIR1271wj5KVN3qpNlJsIkmGrLsTiL79/CrR9zeoOw3A6tNhDRu1D0n7q/a9l5CXBKbzKPvTqk34rWl3NYkQ+MqtfPXA0MLRJi4r33Q8MrvofFFRWNNLjrQ9APlpyEYYGitaPriOTf0aaDmQUT83kPdLeeuPrZPJaxJRyV3vB2r2ewc1sqB4nsxO5dFQXA7KhGGTyfMkFkoTfNkZfYzuiFfw7jiLCF3M2dF3POoEXCWoN8NjJA6viUi6QrbVyrqsZERdr5/++5AbDZ+ymdpvW3GlIOLRPrhMLWPseEYgCIs8VOxWPrqwGvLHeCYtJpXokJ42LnbsQuiEgljGtTBls9LWaCjRCFQvgqFSeoRkoxYQvcB4yzJTU6rgZcDa4davQjhlpPyBhlIOiFLnAuxQ6efDBpNmgy+azW/dfPNT4q78HWWaZXIm6o8ZG3wCVUBXbKn3OgzX7yPGsoCvR5LvQfgxC3cCwDJmJ2prJjlypJXgDGSsN';
        echo EbayOtherInfoModel::model()->eBayGetOrders($token, array(
            '301623477730-1168119435020',
            '301671523090-1166820406020',
            '301687908539-1168100340020',
            '301692187753-1167410216020',
            '301692187753-1167689498020'
        ), time() - 3600 * 24 * 30, time());
    }

    public function actionTest18()
    {
        $OrderId = '301692187753-1167689498020';
        echo MsgDownModel::model()->getMatchUserIdByOrderId($OrderId);
    }

    public function actionTest19()
    {
        $ItemId = '281845307843';
        var_dump(MsgDownModel::model()->getMatchUserIdByItemId($ItemId));
    }

    public function actionTest20()
    {
        $columns = array(
            'seller_id' => 2,
            'user_id' => 4,
            'SKU' => '456465'
        );
        $conditions = 'seller_id=:seller_id22 and user_id=:user_id22 and SKU=:SKU22';
        $params = array(
            ':seller_id22' => 2,
            ':user_id22' => 4,
            ':SKU22' => 'bbbbbbbbbbbb'
        );
        $result = UserSkuDAO::getInstance()->iupdate($columns, $conditions, $params);
        
        var_dump($result);
    }

    public function actionTest21()
    {
        new PHPExcel();
        
        define('EOL', (PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
        
        echo date('H:i:s'), " Load from Excel2007 file", EOL;
        $callStartTime = microtime(true);
        
        $objPHPExcel = PHPExcel_IOFactory::load('1.xlsx');
        
        $callEndTime = microtime(true);
        $callTime = $callEndTime - $callStartTime;
        echo 'Call time to read Workbook was ', sprintf('%.4f', $callTime), " seconds", EOL;
        // Echo memory usage
        echo date('H:i:s'), ' Current memory usage: ', (memory_get_usage(true) / 1024 / 1024), " MB", EOL;
        
        echo date('H:i:s'), " Write to Excel2007 format", EOL;
        $callStartTime = microtime(true);
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(str_replace('.php', '.xlsx', __FILE__));
        
        $callEndTime = microtime(true);
        $callTime = $callEndTime - $callStartTime;
        
        echo date('H:i:s'), " File written to ", str_replace('.php', '.xlsx', pathinfo(__FILE__, PATHINFO_BASENAME)), EOL;
        echo 'Call time to write Workbook was ', sprintf('%.4f', $callTime), " seconds", EOL;
        // Echo memory usage
        echo date('H:i:s'), ' Current memory usage: ', (memory_get_usage(true) / 1024 / 1024), " MB", EOL;
        
        // Echo memory peak usage
        echo date('H:i:s'), " Peak memory usage: ", (memory_get_peak_usage(true) / 1024 / 1024), " MB", EOL;
        
        // Echo done
        echo date('H:i:s'), " Done writing file", EOL;
        echo 'File has been created in ', getcwd(), EOL;
    }

    public function actionTest22()
    {
        $doc = phpQuery::newDocumentHTML(file_get_contents('123.html'));
        
        phpQuery::selectDocument($doc);
        $result['Text'] = $doc->find('body')->html();
        
        echo $result['Text'];
    }

    public function actionTest23()
    {
        CaseActivityOptionsDAO::getInstance();
        sleep(10);
        $columns = array(
            '*'
        );
        $conditions = 'true';
        $params = array();
        CaseActivityOptionsDAO::getInstance()->iselect($columns, $conditions, $params);
    }
}
