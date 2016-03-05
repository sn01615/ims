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
        $token = 'AgAAAA**AQAAAA**aAAAAA**hVSXVg**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6AGl4qoCJaAqAWdj6x9nY+seQ**B8ICAA**AAMAAA**LrNsub/MnPvFk2Srr358lgIM4BmhhzrF1kpqT6rNDS9QvEAkH+NDj+zrhnzjiErhnkkAaz8nWCg0lhiQUVRAMMDAGnOLhda7p3/UKu0tOVy3HMm8m76Dtg0x7kbzeEaJPXBWvRPYuFV6EihYoMJ8cqB9Joa6AJBTpqjQs8NrjYHs9b+HIKjAeYFxeWtPHHyqhXdnpn9u0p4qQ74xZrZE3dmXRsDash2J4cdgzR3G1umWMacHpCfBZIV0w0BMPx3/oiJnnUh2+TKjYVmdtyyNiX5UrF7dryIKuj5tegFhTyDhTt4uoGRWBGVna4n/E5IKp5mHqDqmovqgrcyp8p63StCvgA/j3XsTYD9aLNdJGAVGr9uff109mA3hwKzR7q8Hn4zUOrv6mfmqfOY20yr4/3G0RUPIii5wGIQv6X6KVJXMIhx3oRmUZgC63IDHfMvdyA6yK3riEVu1HPuC7WcqNt2vnkPs1uz2nFh/enz4fD4gUHJe6Ht7qj1ezwKw4W3+QthxreF3sdzq1NlyHZa+cZ2Hq6eTF7BPVKhSeKH1gTwM5OWnC6HWIz/DC6+LlvJDHXKiSSOI/RS4h84apob3ndFYE4r3lACDeTHw6gYbjgllITLTQdJMwq2HIEi3+PwqtJSpEp0LCZ6i/KAKM2NskRLqunaj8P05t+NTJR5CR5N5mtyLCUpkqSU8J4cf041qsueAfdaZD90W0pOyqFCltbh6xICKsMJSUJNWJjRaU2EfM9Unthon1qCKlcGyoHAz';
        // $token = 'AgAAAA**AQAAAA**aAAAAA**3U5IVg**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6AHlYekC5eAogWdj6x9nY+seQ**B8ICAA**AAMAAA**35EtFx9HksbeLsXDLdYKTKgdVZUGV+E855j+gAF9AzgEbJ54VkZcp87Z9Pbk0TQHGWQrcGSMsPoFz+ESJ+aUXECo5DCKUHdGLD3yXtRyt3+HAsJ3R7DRYdpxFN/y2k+vi+U56i8Hsm3C6btSXohyGkJF9HAPiJJCutDKYCMPIdVY5mCd8L04rD7O6+nGytj0DPxLtR3CvfCTRH9dMPh3xfFg3nW9x6M3oF+mUBFUK923dDk/p0P4Tq6bnBfCwjIF+qiTfAFRn4Vm4Q8y+RGl43BJs3QW8SSj6WfvgbarByKwguRV+f8ua/3Ftvv41eshzF3dk00VKf6dpx6IAzdRmgzJj08HljQdZHCmsZOhphTAPpSSBN9D3iIUNh/E//+f2nTBVLecZrP0aKwMZEEBkf+X4L2NIx6gwPacyEmrKWmKYZwk4/H+Y0fYhvQyJnaUeGbCS4ow5Kkt4WzeV19xaXGPxMwr1xRF9k1qj6Ku4fq4RZQ4Aw5D3TeyTsx36ZGf+uCkDn3Y4zSlM19EpvDNuGx9f4vUK/mL86CFBB/jjbbaLNDSQDUa95QO4yy8qDzQlqYQl+fOPOFF88UR85RN4idjvbEPyBJQv08+Fa7ivPjF17r7y0iJ52V+xyUIg1HztOoB7yDS6BHWPAsrQRLwg8zflMjvAWGfmiv+LUvSqz32Cd0kGeR6xOlbZ3TOE3xTv6t5R3t/pso804QcE/kuw1NWAJGKvUERdIwvI6bK6SOWZZnnW3FvAqIOaGESXUGG';
        echo CaseDownModel::model()->getUserCases($fromDate, $toDate, $token);
    }

    public function actionTest9()
    {
        imsTool::xmlHeader();
        
        $token = 'AgAAAA**AQAAAA**aAAAAA**lP4+VQ**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6AElYGkAZKFogydj6x9nY+seQ**+uQAAA**AAMAAA**+P6R0gQ0z30XPNdYXYVHcHueeIp9vlyg2uN9lhDlJFwQ4KGhzl9trw013I91BCGemaTzUEHdArF0yUFZV6qHMdY9Vme/Ii4sUD9YmjwsYDKiX3Tr7e6wnfvGqO7HJtL8jGPb/iyciMiBMFBZRLaK3BQzYCTgQrsRVWrkZXkCaSCPKpKqhPtqa8Qv7sbBHmqCGkaGHW2eEqZTQWYCVua1kmi74XbU4JFvHEzZy+JRtn620er5vDoA8l+5zKzpQR2ofxnteFd2gO5g5GQsGi7pWr5vAsBD2lLPuaWgcoH2IDrwBfsoi3XTAEqQfwWJLRU2fR2z399NwnVJxmJZYYZJyarfgbLsroRzALoh67ld46auITYSPDx/tdWQQ0v8miebxyR+Ev9drivX7Iev6+ujjTitJMM4hbDMQP4wUGwv6fObhkkpgSkprNpnpQtwYgqJnkVyoPi4VgKJjVkn2zZMYxvzZsGv83T9lm3esSST1y3wbnQbFoVxWbmIwax0ybsLIQ8j2HIIlO+7DGpyRcX2vcQgP4HJSWt1fMW5JkOxZj25YNLONhDRfxR/9lmniO8eEcVbX4G4nf6XL/RUrys3+jwBmlZC7Bwcjvdz5YVlwbvY/2aA/ubshj6fgCVvTL/+gfA4GxlhW+3ucF/xLWoLm14ysKaFb6inxBeAGL1zw9a5fjVsaetIG8GRxFms7ICX/M//HxH5h5bMzUDu7S2qApLA7xcK0ng9HFuNeFsc39KZZLwuJZnWCn5sSB1KFZci';
        $token = 'AgAAAA**AQAAAA**aAAAAA**hVSXVg**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6AGl4qoCJaAqAWdj6x9nY+seQ**B8ICAA**AAMAAA**LrNsub/MnPvFk2Srr358lgIM4BmhhzrF1kpqT6rNDS9QvEAkH+NDj+zrhnzjiErhnkkAaz8nWCg0lhiQUVRAMMDAGnOLhda7p3/UKu0tOVy3HMm8m76Dtg0x7kbzeEaJPXBWvRPYuFV6EihYoMJ8cqB9Joa6AJBTpqjQs8NrjYHs9b+HIKjAeYFxeWtPHHyqhXdnpn9u0p4qQ74xZrZE3dmXRsDash2J4cdgzR3G1umWMacHpCfBZIV0w0BMPx3/oiJnnUh2+TKjYVmdtyyNiX5UrF7dryIKuj5tegFhTyDhTt4uoGRWBGVna4n/E5IKp5mHqDqmovqgrcyp8p63StCvgA/j3XsTYD9aLNdJGAVGr9uff109mA3hwKzR7q8Hn4zUOrv6mfmqfOY20yr4/3G0RUPIii5wGIQv6X6KVJXMIhx3oRmUZgC63IDHfMvdyA6yK3riEVu1HPuC7WcqNt2vnkPs1uz2nFh/enz4fD4gUHJe6Ht7qj1ezwKw4W3+QthxreF3sdzq1NlyHZa+cZ2Hq6eTF7BPVKhSeKH1gTwM5OWnC6HWIz/DC6+LlvJDHXKiSSOI/RS4h84apob3ndFYE4r3lACDeTHw6gYbjgllITLTQdJMwq2HIEi3+PwqtJSpEp0LCZ6i/KAKM2NskRLqunaj8P05t+NTJR5CR5N5mtyLCUpkqSU8J4cf041qsueAfdaZD90W0pOyqFCltbh6xICKsMJSUJNWJjRaU2EfM9Unthon1qCKlcGyoHAz';
        echo CaseDownModel::model()->getEBPCaseDetail('5099859375', 'EBP_INR', $token);
    }

    public function actionTest9r()
    {
        imsTool::xmlHeader();
        
        $token = 'AgAAAA**AQAAAA**aAAAAA**lP4+VQ**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6AElYGkAZKFogydj6x9nY+seQ**+uQAAA**AAMAAA**+P6R0gQ0z30XPNdYXYVHcHueeIp9vlyg2uN9lhDlJFwQ4KGhzl9trw013I91BCGemaTzUEHdArF0yUFZV6qHMdY9Vme/Ii4sUD9YmjwsYDKiX3Tr7e6wnfvGqO7HJtL8jGPb/iyciMiBMFBZRLaK3BQzYCTgQrsRVWrkZXkCaSCPKpKqhPtqa8Qv7sbBHmqCGkaGHW2eEqZTQWYCVua1kmi74XbU4JFvHEzZy+JRtn620er5vDoA8l+5zKzpQR2ofxnteFd2gO5g5GQsGi7pWr5vAsBD2lLPuaWgcoH2IDrwBfsoi3XTAEqQfwWJLRU2fR2z399NwnVJxmJZYYZJyarfgbLsroRzALoh67ld46auITYSPDx/tdWQQ0v8miebxyR+Ev9drivX7Iev6+ujjTitJMM4hbDMQP4wUGwv6fObhkkpgSkprNpnpQtwYgqJnkVyoPi4VgKJjVkn2zZMYxvzZsGv83T9lm3esSST1y3wbnQbFoVxWbmIwax0ybsLIQ8j2HIIlO+7DGpyRcX2vcQgP4HJSWt1fMW5JkOxZj25YNLONhDRfxR/9lmniO8eEcVbX4G4nf6XL/RUrys3+jwBmlZC7Bwcjvdz5YVlwbvY/2aA/ubshj6fgCVvTL/+gfA4GxlhW+3ucF/xLWoLm14ysKaFb6inxBeAGL1zw9a5fjVsaetIG8GRxFms7ICX/M//HxH5h5bMzUDu7S2qApLA7xcK0ng9HFuNeFsc39KZZLwuJZnWCn5sSB1KFZci';
        $token = 'AgAAAA**AQAAAA**aAAAAA**hVSXVg**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6AGl4qoCJaAqAWdj6x9nY+seQ**B8ICAA**AAMAAA**LrNsub/MnPvFk2Srr358lgIM4BmhhzrF1kpqT6rNDS9QvEAkH+NDj+zrhnzjiErhnkkAaz8nWCg0lhiQUVRAMMDAGnOLhda7p3/UKu0tOVy3HMm8m76Dtg0x7kbzeEaJPXBWvRPYuFV6EihYoMJ8cqB9Joa6AJBTpqjQs8NrjYHs9b+HIKjAeYFxeWtPHHyqhXdnpn9u0p4qQ74xZrZE3dmXRsDash2J4cdgzR3G1umWMacHpCfBZIV0w0BMPx3/oiJnnUh2+TKjYVmdtyyNiX5UrF7dryIKuj5tegFhTyDhTt4uoGRWBGVna4n/E5IKp5mHqDqmovqgrcyp8p63StCvgA/j3XsTYD9aLNdJGAVGr9uff109mA3hwKzR7q8Hn4zUOrv6mfmqfOY20yr4/3G0RUPIii5wGIQv6X6KVJXMIhx3oRmUZgC63IDHfMvdyA6yK3riEVu1HPuC7WcqNt2vnkPs1uz2nFh/enz4fD4gUHJe6Ht7qj1ezwKw4W3+QthxreF3sdzq1NlyHZa+cZ2Hq6eTF7BPVKhSeKH1gTwM5OWnC6HWIz/DC6+LlvJDHXKiSSOI/RS4h84apob3ndFYE4r3lACDeTHw6gYbjgllITLTQdJMwq2HIEi3+PwqtJSpEp0LCZ6i/KAKM2NskRLqunaj8P05t+NTJR5CR5N5mtyLCUpkqSU8J4cf041qsueAfdaZD90W0pOyqFCltbh6xICKsMJSUJNWJjRaU2EfM9Unthon1qCKlcGyoHAz';
        
        $comments = '';
        
        echo CaseDownModel::model()->issueFullRefund('5099859375', 'EBP_INR', $comments, $token);
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
        // var_dump(CRedisHelper::getInstance()->set($key,8888,10));
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
        // $token = 'AgAAAA**AQAAAA**aAAAAA**u2eWVQ**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6AEk4SoC5mCog6dj6x9nY+seQ**B8ICAA**AAMAAA**wjhrKPGIDJ4KY555XS8gE3ny1uRyuwNk+dhoRmoXGqq83xeN3CNYsua8W1Fz8KrjD3NHIkO5PboHqV8u49Ytz5QvowRUJ3Qai5KPV9TsEmPalYFQzXHj3ZgzvPWuRigNU6M2LcMKifFRM8vcCh35ZAJatxiP/Dcs+ZIzMUGU9w6nU6BCjSIysQSznKqrRaaTQ5el1itL4siTpSNAgnA52/wBKhHXn+7i2CYzBYSNcVf5MVvfehD21TWaTn8y/ZtOr+hDEQ4v1Miam21bVrD6dEhttl46f0/uU9SBXnB4n/cdfOtJJriy8Bw/EkmdzGIXCt6SEY6P3GrwEqRKvRYNAoAxWTyEZCACoVnW6yhJiTo7qI+NdJxHU6lKWTZfdLfTA+dWNifrUh5kCu6LQauXycp5bvNN7zCElSqRoinaYDP3xbu3OHiSA9dJ/WYRKavygNRNYlIKK7yLkUwFuBg9qabMfSZtoIfuuTYhayvdLTIxZOGqDwZLNEoTLAe/OUa5VVwj3IykhSMR61qevfW3dfZs6c4ZGvNEStIpiv78V9jE2Yk0HWu0devnC1T10fmwAGMxJ/4Mtkq1biYgHyE0u4HTBftbfDdRQnlkd2+vWsMWLZFto+8mpyxbe8+Lx6OdKp6pYPSz1fQ2aExGYhLL6Bo8zoEj1thrugd2/ghNudzc4QiPXCOKTx8qQJKdJeRFsbpxI+xYNJR47ImG+KMNUdUkME9aemDgrC4+sVdgVqWIN6z/jntxe//aW4Sjqauw';
        echo MsgDownModel::model()->getMyMessages($token, 'ReturnMessages', 0, $fromDate, $toDate, array(
            '76742747530'
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

    public function actionTest24()
    {
        $caseId = 15;
        $columns = array(
            'number'
        );
        $conditions = 'case_id=' . $caseId;
        $params = array();
        $number = CaseResponseHistoryDAO::getInstance()->iselect($columns, $conditions, $params, 'queryScalar', array(), '', 'number desc');
        var_dump($number);
        empty($number) ? $number = 1 : $number ++;
    }

    public function actionTest25()
    {
        iMemcache::getInstance()->set('test', true, 50);
        var_dump(iMemcache::getInstance()->get('test'));
    }

    public function actionTest26()
    {
        $caseId = '5099595380';
        $serverUrl = "https://api.ebay.com/post-order/v2/casemanagement/{$caseId}";
        // $serverUrl = "https://api.ebay.com/post-order/v2/cancellation/search";
        $session = new eBayRESTful($serverUrl);
        $session->headers[] = "Content-Type: application/json";
        $session->headers[] = "Authorization: TOKEN AgAAAA**AQAAAA**aAAAAA**hVSXVg**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6AGl4qoCJaAqAWdj6x9nY+seQ**B8ICAA**AAMAAA**LrNsub/MnPvFk2Srr358lgIM4BmhhzrF1kpqT6rNDS9QvEAkH+NDj+zrhnzjiErhnkkAaz8nWCg0lhiQUVRAMMDAGnOLhda7p3/UKu0tOVy3HMm8m76Dtg0x7kbzeEaJPXBWvRPYuFV6EihYoMJ8cqB9Joa6AJBTpqjQs8NrjYHs9b+HIKjAeYFxeWtPHHyqhXdnpn9u0p4qQ74xZrZE3dmXRsDash2J4cdgzR3G1umWMacHpCfBZIV0w0BMPx3/oiJnnUh2+TKjYVmdtyyNiX5UrF7dryIKuj5tegFhTyDhTt4uoGRWBGVna4n/E5IKp5mHqDqmovqgrcyp8p63StCvgA/j3XsTYD9aLNdJGAVGr9uff109mA3hwKzR7q8Hn4zUOrv6mfmqfOY20yr4/3G0RUPIii5wGIQv6X6KVJXMIhx3oRmUZgC63IDHfMvdyA6yK3riEVu1HPuC7WcqNt2vnkPs1uz2nFh/enz4fD4gUHJe6Ht7qj1ezwKw4W3+QthxreF3sdzq1NlyHZa+cZ2Hq6eTF7BPVKhSeKH1gTwM5OWnC6HWIz/DC6+LlvJDHXKiSSOI/RS4h84apob3ndFYE4r3lACDeTHw6gYbjgllITLTQdJMwq2HIEi3+PwqtJSpEp0LCZ6i/KAKM2NskRLqunaj8P05t+NTJR5CR5N5mtyLCUpkqSU8J4cf041qsueAfdaZD90W0pOyqFCltbh6xICKsMJSUJNWJjRaU2EfM9Unthon1qCKlcGyoHAz";
        $session->headers[] = "X-EBAY-C-MARKETPLACE-ID: EBAY-US";
        $session->headers[] = "Accept: application/json";
        
        $requestXmlBody = json_encode('');
        $responseXml = $session->sendHttpRequest($requestXmlBody);
        echo $responseXml;
    }

    public function actionTest27()
    {
        $columns['Text'] = <<<eot
<!DOCTYPE html> <!--c191f619-83d2-27df-7f8b-c2ceca5af4f6_v36--> <html> <head> <meta http-equiv="Content-Type" content="text/html; charset=utf-8"></meta><style id="DS3Style" type="text/css"> @media only screen and (max-width: 620px) { body[yahoo] .device-width { width: 450px !important } body[yahoo] .threeColumns { width: 140px !important } body[yahoo] .threeColumnsTd { padding: 10px 4px !important } body[yahoo] .fourColumns { width: 225px !important } body[yahoo] .fourColumnsLast { width: 225px !important } body[yahoo] .fourColumnsTd { padding: 10px 0px !important } body[yahoo] .fourColumnsPad { padding: 0 0 0 0 !important } body[yahoo] .secondary-product-image { width: 200px !important; height: 200px !important } body[yahoo] .center { text-align: center !important } body[yahoo] .twoColumnForty { width: 200px !important height: 200px !important } body[yahoo] .twoColumnForty img { width: 200px !important; height: 200px !important } body[yahoo] .twoColumnSixty { width: 228px !important } body[yahoo] .secondary-subhead-right { display: none !important } body[yahoo] .secondary-subhead-left { width: 450px !important } } @media only screen and (max-width: 479px) { body[yahoo] .navigation { display: none !important } body[yahoo] .device-width { width: 300px !important; padding: 0 } body[yahoo] .threeColumns { width: 150px !important } body[yahoo] .fourColumns { width: 150px !important } body[yahoo] .fourColumnsLast { width: 150px !important } body[yahoo] .fourColumnsTd { padding: 10px 0px !important } body[yahoo] .fourColumnsPad { padding: 0 0 0 0 !important } body[yahoo] .secondary-product-image { width: 240px !important; height: 240px !important } body[yahoo] .single-product-table { float: none !important; margin-bottom: 10px !important; margin-right: auto !important; margin-left: auto !important; } body[yahoo] .single-product-pad { padding: 0 0 0 0 !important; } body[yahoo] .single-product-image { align:center; width: 200px !important; height: 200px !important } body[yahoo] .mobile-full-width { width: 300px !important } body[yahoo] .twoColumnForty { align:center; !important width: 200px !important } body[yahoo] .twoColumnForty img { } body[yahoo] .twoColumnSixty { padding-left: 0px !important; width: 300px !important } body[yahoo] .secondary-subhead-left { width: 300px !important } body[yahoo] .ThreeColumnItemTable{ padding: 0px 0px 0px 74px !important } body[yahoo] .FourColumnFloater { float: right !important; } span.message-history{ text-align: left !important; float: right !important; } } body[yahoo] .mobile-full-width { min-width: 103px; max-width: 300px; height: 38px; } body[yahoo] .mobile-full-width a { display: block; padding: 10px 0; } body[yahoo] .mobile-full-width td{ padding: 0px !important } td.wrapText{ white-space: -moz-pre-wrap; white-space: -pre-wrap; white-space: -o-pre-wrap; word-wrap: break-word; } @-moz-document url-prefix() { td.wrapTextFF_Fix { display: inline-block } } body { width: 100% !important; -webkit-text-size-adjust: 100% !important; -ms-text-size-adjust: 100% !important; -webkit-font-smoothing: antialiased !important; margin: 0 !important; padding: 0 0 100px !important; font-family: Helvetica, Arial, sans-serif !important; background-color:#f9f9f9} .ReadMsgBody { width: 100% !important; background-color: #ffffff !important; } .ExternalClass { width: 100% !important; } .ExternalClass { line-height: 100% !important; } img { display: block; outline: none !important; text-decoration: none !important; -ms-interpolation-mode: bicubic !important; } td{word-wrap: break-word;} </style> <!--[if gte mso 9]> <style>td.product-details-block{word-break:break-all}.threeColumns{width:140px !important}.threeColumnsTd{padding:10px 20px !important}.fourColumns{width:158px !important}.fourColumnsPad{padding: 0 18px 0 0 !important}.fourColumnsTd{padding:10px 0px !important}.twoColumnSixty{width:360px !important}table{mso-table-lspace:0pt; mso-table-rspace:0pt;}</style> <![endif]--> </head> <body yahoo="fix"><table id="area2Container" width="100%" border="0" cellpadding="0" cellspacing="0" align="center" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none; background-color:#f9f9f9"><tr><td width="100%" valign="top" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;"><table width="600" class="device-width header-logo" border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="#f9f9f9" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;"> <tr> <td valign="top" style="border-collapse: collapse !important; border-spacing: 0 !important; padding: 0; border: none;"><p style="font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: normal; color: #888888; text-align: left; font-size: 11px; margin: 5px 0 10px 0; color: #333;" align="left">新訊息： Thanks got lights ok but the remote wont work…</p></td> </tr> </table></td></tr></table> <table id="area3Container" width="100%" border="0" cellpadding="0" cellspacing="0" align="center" style="border-collapse:collapse !important;border-spacing:0 !important;border:none;background-color:#f9f9f9;"> <tr> <td width="100%" valign="top" style="border-collapse:collapse !important;border-spacing:0 !important;border:none;"> <table width="100%" height="7" border="0" cellpadding="0" cellspacing="0" align="center" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none; background-image: url('http://p.ebaystatic.com/aw/navbar/preHeaderBottomShadow.png'); background-repeat: repeat-y no-repeat; margin: 0; padding: 0"> <!--[if gte mso 9]> <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="mso-width-percent:1000;height:1px;"> <v:fill type="tile" color="#dddddd" /> </v:rect> <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="mso-width-percent:1000;height:6px;"> <v:fill type="tile" src="http://p.ebaystatic.com/aw/navbar/preHeaderBottomShadow.png" color="#f9f9f9" /> <div style="width:0px; height:0px; overflow:hidden; display:none; visibility:hidden; mso-hide:all;"> <![endif]--> <tr> <td width="100%" height="1" valign="top" align="center" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none; background-color: #dddddd; font-size: 1px; line-height: 1px;"> <!--[if gte mso 15]>&nbsp;<![endif]--> </td> </tr> <tr> <td width="100%" height="6" valign="top" align="center" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none; background-color: none; font-size: 1px; line-height: 1px;">&nbsp;</td> </tr> <!--[if gte mso 9]> </div> </v:rect> <![endif]--> </table> </td> </tr> </table> <table id="area4Container" width="100%" border="0" cellpadding="0" cellspacing="0" align="center" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none; background-color:#f9f9f9"> <tr> <td width="100%" valign="top" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;"> <table width="600" class="device-width header-logo" border="0" cellpadding="0" cellspacing="0" align="center" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;"> <tr> <td valign="top" style="border-collapse: collapse !important; border-spacing: 0 !important; padding: 15px 0 20px; border: none;"><a href="http://rover.ebay.com/rover/0/e11050.m1831.l3127/7?euid=36a7a81faa994f86872bb030b859c14b&loc=http%3A%2F%2Fpages.ebay.com%2Flink%2F%3Fnav%3Dhome%26alt%3Dweb%26globalID%3DEBAY-HK%26referrer%3Dhttp%253A%252F%252Frover.ebay.com%252Frover%252F0%252Fe11050.m1831.l3127%252F7%253Feuid%253D36a7a81faa994f86872bb030b859c14b%2526cp%253D1" style="text-decoration: none; color: #0654ba;"><img src="http://p.ebaystatic.com/aw/logos/header_ebay_logo_132x46.gif" width="132" height="46" border="0" alt="eBay" align="left" style="display: inline block; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; border: none;" /></a><img src="http://rover.ebay.com/roveropen/0/e11050/7?euid=36a7a81faa994f86872bb030b859c14b" alt="" style="border:0; height:1;"/></td> </tr> </table> </td> </tr> </table> <table id="area5Container" width="100%" border="0" cellpadding="0" cellspacing="0" align="center" style="border-collapse: collapse !important; border-spacing: 0 !important; background-color:#f9f9f9"> <tr> <td width="100%" valign="top" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;"> <table id="PrimaryMessage" width="600" class="device-width" border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="#f9f9f9" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;"> <tr> <td valign="top" class="secondary-headline" style="border-collapse: collapse !important; border-spacing: 0 !important; padding: 20px 0 5px;"> <h1 style="font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: 15px; color: #808284; text-align: left; font-size: 13px; margin: 0 0 4px;" align="left">新訊息寄件人： <a href="http://rover.ebay.com/rover/0/e11050.m44.l1181/7?euid=36a7a81faa994f86872bb030b859c14b&loc=http%3A%2F%2Fpages.ebay.com%2Flink%2F%3Fnav%3Duser.view%26user%3Dsbrown801%26globalID%3DEBAY-HK%26referrer%3Dhttp%253A%252F%252Frover.ebay.com%252Frover%252F0%252Fe11050.m44.l1181%252F7%253Feuid%253D36a7a81faa994f86872bb030b859c14b%2526cp%253D1" style="text-decoration: none; font-weight: bold; color: #336fb7;">sbrown801</a> <a href="http://rover.ebay.com/rover/0/e11050.m44.l1183/7?euid=36a7a81faa994f86872bb030b859c14b&loc=http%3A%2F%2Ffeedback.ebay.com.hk%2Fws%2FeBayISAPI.dll%3FViewFeedback%26userid%3Dsbrown801%26ssPageName%3DSTRK%3AME%3AUFS" style="text-decoration: none; color: #888888;">(116<img src="http://pics.ebaystatic.com/aw/pics/icon/iconTealStar_25x25.gif" alt="Turquoise Star" width="18" height="18" class="inline-image" style="position: relative; top: -2px; display: inline !important; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; vertical-align: middle !important; border: none;" />）</a></h1></td> </tr> <tr> <td valign="top" class="viewing-problem-block" style="border-collapse: collapse !important; border-spacing: 0 !important; border-bottom-width: 1px; border-bottom-color: #f9f9f9; padding: 10px 0 30px; border-style: none none solid;"> <table width="600" class="device-width" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none; border:0; cellpadding:0; cellspacing:0; align:center; bgcolor:#f9f9f9;"> <tr> <td width="100%" class="wrapText device-width" valign="top" style="overflow: hidden; border-collapse: collapse !important; border-spacing: 0 !important; border: none; display: inline-block; max-width:600px;"><h3 style="font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: 19px; color: #231f20; text-align: left; font-size: 14px; margin: 0 0 2px; font-weight:none;" align="left"><div id="UserInputtedText">Thanks got lights ok but the remote wont work stuck on one colour all the time ,</div></h3> <span style="color:#666666"> </span> </td> </tr> <tr> <td valign="top" width="15" height="15" style="border-collapse: collapse !important; border-spacing: 20 !important; border: none;"></td> </tr> <tr> <td valign="top" class="cta-block" style="border-collapse: collapse !important; border-spacing: 0 !important; padding: 5px 0 5px; border: none;"> <table align="left" cellpadding="0" cellspacing="0" border="0" class="mobile-full-width" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;"> <tr> <td valign="top" class="center cta-button primary-cta-button" align="center" style="border-collapse: collapse !important; border-spacing: 0 !important; font-size: 14px; line-height: normal; font-weight: bold; box-shadow: 2px 3px 0 #e5e5e5; filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#0079bc', endColorstr='#00519e',GradientType=0 ); background-image: linear-gradient(to bottom, #0079bc 0%,#00519e 100%); background-color: #0079bc; padding: 10px 17px; border: 1px solid #00519e;" bgcolor="#0079bc"><a href="http://rover.ebay.com/rover/0/e11050.m44.l1139/7?euid=36a7a81faa994f86872bb030b859c14b&loc=http%3A%2F%2Fcontact.ebay.com.hk%2Fws%2FeBayISAPI.dll%3FM2MContact%26item%3D271401733690%26requested%3Dsbrown801%26qid%3D1236123620014%26redirect%3D0" style="text-decoration: none; color: #ffffff; font-size: 14px; line-height: normal; font-weight: bold; font-family: Helvetica, Arial, sans-serif; text-shadow: 1px 1px 0 #00519e;"><span style="padding: 0px 10px">回覆</span></a></td> </tr> </table> </td> </tr> </table> </td> </tr> </table> <!--[if !mso]><!--> <div id="V4PrimaryMessage" style="max-height: 0px; font-size: 0; overflow:hidden; display: none !important;"> <div> <table border="0" cellpadding="2" cellspacing="3" width="100%"> <tr> <td> <font style="font-size:10pt; font-family:arial, sans-serif; color:#000"><strong>fasteasybuylight，你好！</strong><br><br>Thanks got lights ok but the remote wont work stuck on one colour all the time ,<br><br></font><div style="font-weight:bold; font-size:10pt; font-family:arial, sans-serif; color:#000">- sbrown801</div> </td> <td valign="top" width="185"> <div></div> </td> </tr> </table> </div> </div> <!--<![endif]--> </td> </tr> </table> <table id="area8Container" width="100%" border="0" cellpadding="0" cellspacing="0" align="center" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none; border-top: solid 1px #dddddd; background-color: #ffffff"><tr><td style="font-size:0px; line-height:0px" height="1">&nbsp;</td></tr></table> <table id="area7Container" width="100%" border="0" cellpadding="0" cellspacing="0" align="center" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none; background-color:#f9f9f9;"> <tr> <td width="100%" valign="top" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;"> <table class="device-width" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;" align="center" bgcolor="#f9f9f9" border="0" cellpadding="0" cellspacing="0" width="600"> <tr> <td style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;" valign="top"><h1 style="font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: 28px; color: #2258a7; text-align: left; font-size: 24px; margin: 1px;" align="left"><br><a href="http://rover.ebay.com/rover/0/e11050.m43.l1123/7?euid=36a7a81faa994f86872bb030b859c14b&loc=http%3A%2F%2Fpages.ebay.com%2Flink%2F%3Fnav%3Ditem.view%26id%3D271401733690%26alt%3Dweb%26globalID%3DEBAY-HK%26referrer%3Dhttp%253A%252F%252Frover.ebay.com%252Frover%252F0%252Fe11050.m43.l1123%252F7%253Feuid%253D36a7a81faa994f86872bb030b859c14b%2526cp%253D1" style="text-decoration: none; color: #0654ba;">5050 RGB LED Strip Lights Dampproof For Kitchen Under Plinth Display Cabinet UK</a></h1></td> </tr> <tr> <td valign="top" class="secondary-headline" style="border-collapse: collapse !important; border-spacing: 0 !important; border-bottom-color: #dddddd; border-bottom-width: 1px; border-style: none none solid;"><h1 style="font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: 28px; color: #00a550; font-weight: bold; text-align: left; font-size: 20px; margin: 0 0 14px;" align="left"></h1></td> </tr> </table> <table class="device-width" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;" align="center" bgcolor="#f9f9f9" border="0" cellpadding="0" cellspacing="0" width="600"> <tr> <td class="single-product-block" style="border-collapse: collapse !important; border-spacing: 0 !important; padding: 15px 0 50px; border: none;" valign="top"> <table class="single-product-table" style="align: left; border-collapse: collapse !important; border-spacing: 0 !important; border: none;" align="left" border="0" cellpadding="0" cellspacing="0" width="215"> <tr> <td valign="middle" width="200" height="200" align="center" class="single-product-image" style="border-collapse: collapse !important; border-spacing: 0 !important; border: 1px solid #dddddd; background-color: #ffffff;"><a href="http://rover.ebay.com/rover/0/e11050.m43.l1123/7?euid=36a7a81faa994f86872bb030b859c14b&loc=http%3A%2F%2Fpages.ebay.com%2Flink%2F%3Fnav%3Ditem.view%26id%3D271401733690%26alt%3Dweb%26globalID%3DEBAY-HK%26referrer%3Dhttp%253A%252F%252Frover.ebay.com%252Frover%252F0%252Fe11050.m43.l1123%252F7%253Feuid%253D36a7a81faa994f86872bb030b859c14b%2526cp%253D1" style="text-decoration: none; color: #0654ba;"><img src="http://thumbs.ebaystatic.com/d/l200/pict/271401733690_5.jpg" alt="5050 RGB LED Strip Lights Dampproo" class="product-image" border="0" style="display: block; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; margin: 0; border: none;" /></a></td> <td class="single-product-pad" style="padding: 0 15px 0 0; border-collapse: collapse !important; border-spacing: 0 !important; border: none;"></td> </tr> </table> <table class="twoColumnSixty" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;" align="left" border="0" cellpadding="0" cellspacing="0" width="380"> <tr> <td class="product-price" style="border-collapse: collapse !important; border-spacing: 0 !important; font-family: Helvetica, Arial, sans-serif; text-align: left; font-size: 20px; line-height: 18px; font-weight: bold; border: none;" align="left">訂單狀態：已付款</td> </tr> <tr> <td class="product-shipping" style="border-collapse: collapse !important; border-spacing: 0 !important; font-family: Helvetica, Arial, sans-serif; text-align: left; font-size: 13px; padding-bottom: 8px; padding-top: 2px; border: none;" align="left">已於 2016-02-13 19:23 HKT 使用 PAYPAL 支付 GBP 15.94</td> </tr> <tr> <td class="product-bids" style="border-collapse: collapse !important; border-spacing: 0 !important; font-family: Helvetica, Arial, sans-serif; text-align: left; font-size: 12px; color: #666666; border: none;" align="left"> 物品編號： 271401733690<br> 交易編號： 1605350291017<br> </td> </tr> <tr> <td style="border-collapse: collapse !important; border-spacing: 0 !important; font-family: Helvetica, Arial, sans-serif; text-align: left; font-size: 12px; color: #666666; border: none;" align="left"> </td> </tr> <tr> <td valign="top" class="cta-block" style="border-collapse: collapse !important; border-spacing: 0 !important; padding: 25px 0 40px; border: none;"> <table align="left" cellpadding="0" cellspacing="0" border="0" class="mobile-full-width" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;"> <tr> <td valign="top" class="center cta-button primary-cta-button" align="center" style="border-collapse: collapse !important; border-spacing: 0 !important; font-size: 14px; line-height: normal; font-weight: bold; box-shadow: 2px 3px 0 #e5e5e5; filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#0079bc', endColorstr='#00519e',GradientType=0 ); background-image: linear-gradient(to bottom, #0079bc 0%,#00519e 100%); background-color: #0079bc; padding: 10px 17px; border: 1px solid #00519e;" bgcolor="#0079bc"><a href="http://rover.ebay.com/rover/0/e11050.m43.l1503/7?euid=36a7a81faa994f86872bb030b859c14b&loc=http%3A%2F%2Fpayments.ebay.com.hk%2Fws%2FeBayISAPI.dll%3FViewPaymentStatus%26transId%3D1605350291017%26qu%3D1%26itemId%3D271401733690" style="text-decoration: none; color: #ffffff; font-size: 14px; line-height: normal; font-weight: bold; font-family: Helvetica, Arial, sans-serif; text-shadow: 1px 1px 0 #00519e;"><span style="padding: 0px 10px">檢視訂單詳細資料</span></a></td> </tr> </table> </td> </tr> </table> </td> </tr> </table> </td> </tr> </table> <table id="area8Container" width="100%" border="0" cellpadding="0" cellspacing="0" align="center" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none; border-top: solid 1px #dddddd; background-color: #ffffff"><tr><td style="font-size:0px; line-height:0px" height="1">&nbsp;</td></tr></table> <table id="area10Container" class="whiteSection" width="100%" border="0" cellpadding="0" cellspacing="0" align="center" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none; background-color: #ffffff"> <tr> <td width="100%" valign="top" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;"> <table width="600" class="device-width" border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="#ffffff" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;"> <tr> <td valign="top" class="viewing-problem-block" style="border-collapse: collapse !important; border-spacing: 0 !important; border-bottom-width: 1px; border-bottom-color: #dddddd; padding: 40px 0 30px; border-style: none none solid;"><p style="font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: normal; color: #888888; text-align: left; font-size: 11px; margin: 0 0 10px;" align="center">只有 eBay 上購買的物品才享有 eBay 購物保障計劃。要求交易對方在 eBay 以外完成交易，是違規的行為。</p></td> </tr> </table> </td> </tr> </table> <table id="area11Container" class="whiteSection" width="100%" border="0" cellpadding="0" cellspacing="0" align="center" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none; background-color: #ffffff"> <tr> <td width="100%" valign="top" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;"> <table width="600" class="device-width" border="0" cellpadding="0" cellspacing="0" align="center" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;"> <tr> <td valign="top" class="ebay-footer-block" style="border-collapse: collapse !important; border-spacing: 0 !important; padding: 40px 0 60px; border: none;"> <div id="ReferenceId"> <p style="font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: normal; color: #888888; text-align: left; font-size: 11px; margin: 0 0 10px;" align="left"><strong> 電郵參考編號： [#a05-bn9emxjthd#]_[#36a7a81faa994f86872bb030b859c14b#] </strong></p></div> <p style="font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: normal; color: #888888; text-align: left; font-size: 11px; margin: 0 0 10px;" align="left"> 我們不會檢查此郵箱，因此請不要回覆這個訊息：如果你有問題，請前往「<a style="text-decoration: none; color: #555555;" href="http://rover.ebay.com/rover/0/e11050.m1852.l6369/7?euid=36a7a81faa994f86872bb030b859c14b&loc=http%3A%2F%2Focsnext.ebay.com.hk%2Focs%2Fhome" target="_blank">支援及聯絡</a>」。 </p> <p style="font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: normal; color: #888888; text-align: left; font-size: 11px; margin: 0 0 10px;" align="left"> 此訊息是由 eBay 寄給 杨 新辉（fasteasybuylight）。進一步了解<a style="text-decoration: none; color: #555555;" href="http://rover.ebay.com/rover/0/e11050.m1852.l3167/7?euid=36a7a81faa994f86872bb030b859c14b&loc=http%3A%2F%2Fpages.ebay.com.hk%2Fhelp%2Faccount%2Fprotecting-account.html" target="_blank">保障帳戶安全</a>。eBay 致力保障你的私隱。進一步了解我們的<a style="text-decoration: none; color: #555555;" href="http://rover.ebay.com/rover/0/e11050.m1852.l3168/7?euid=36a7a81faa994f86872bb030b859c14b&loc=http%3A%2F%2Fpages.ebay.com.hk%2Fhelp%2Fpolicies%2Fprivacy-policy.html" target="_blank">私隱權政策</a>和<a style="text-decoration: none; color: #555555;" href="http://rover.ebay.com/rover/0/e11050.m1852.l3165/7?euid=36a7a81faa994f86872bb030b859c14b&loc=http%3A%2F%2Fpages.ebay.com.hk%2Fhelp%2Fpolicies%2Fuser-agreement.html" target="_blank">會員合約</a>。 </p> <p style="font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: normal; color: #888888; text-align: left; font-size: 11px; margin: 0 0 10px;" align="left"> &copy;2016 eBay Inc., eBay International AG Helvetiastrasse 15/17 - P.O. Box 133, 3000 Bern 6, Switzerland </p> </td> </tr> </table> </td> </tr> </table></body> </html>
eot;
        $columns['Text'] = tidyTool::cleanRepair($columns['Text']);
        
        $hdoc = phpQuery::newDocumentHTML($columns['Text']);
        phpQuery::selectDocument($hdoc);
        
        if (pq('#UserInputtedText')->length > 0) {
            $effect_content = pq('#UserInputtedText')->html();
            var_dump($effect_content);
        }
    }

    public function actionTest28()
    {
        $query = array(
            'time' => array(
                '$gt' => 1444988860
            )
        );
        $result = iMongo::getInstance()->setCollection('addMemberMessagesAAQToBidder')->count($query);
        var_dump($result);
    }

    public function actionTest29()
    {
        $columns['Text'] = <<<eot
<!DOCTYPE html> <!--a71f943b-ab8b-549b-4cb2-514a60170b90_v36--> <html> <head> <meta http-equiv="Content-Type" content="text/html; charset=utf-8"></meta><style id="DS3Style" type="text/css"> @media only screen and (max-width: 620px) { body[yahoo] .device-width { width: 450px !important } body[yahoo] .threeColumns { width: 140px !important } body[yahoo] .threeColumnsTd { padding: 10px 4px !important } body[yahoo] .fourColumns { width: 225px !important } body[yahoo] .fourColumnsLast { width: 225px !important } body[yahoo] .fourColumnsTd { padding: 10px 0px !important } body[yahoo] .fourColumnsPad { padding: 0 0 0 0 !important } body[yahoo] .secondary-product-image { width: 200px !important; height: 200px !important } body[yahoo] .center { text-align: center !important } body[yahoo] .twoColumnForty { width: 200px !important height: 200px !important } body[yahoo] .twoColumnForty img { width: 200px !important; height: 200px !important } body[yahoo] .twoColumnSixty { width: 228px !important } body[yahoo] .secondary-subhead-right { display: none !important } body[yahoo] .secondary-subhead-left { width: 450px !important } } @media only screen and (max-width: 479px) { body[yahoo] .navigation { display: none !important } body[yahoo] .device-width { width: 300px !important; padding: 0 } body[yahoo] .threeColumns { width: 150px !important } body[yahoo] .fourColumns { width: 150px !important } body[yahoo] .fourColumnsLast { width: 150px !important } body[yahoo] .fourColumnsTd { padding: 10px 0px !important } body[yahoo] .fourColumnsPad { padding: 0 0 0 0 !important } body[yahoo] .secondary-product-image { width: 240px !important; height: 240px !important } body[yahoo] .single-product-table { float: none !important; margin-bottom: 10px !important; margin-right: auto !important; margin-left: auto !important; } body[yahoo] .single-product-pad { padding: 0 0 0 0 !important; } body[yahoo] .single-product-image { align:center; width: 200px !important; height: 200px !important } body[yahoo] .mobile-full-width { width: 300px !important } body[yahoo] .twoColumnForty { align:center; !important width: 200px !important } body[yahoo] .twoColumnForty img { } body[yahoo] .twoColumnSixty { padding-left: 0px !important; width: 300px !important } body[yahoo] .secondary-subhead-left { width: 300px !important } body[yahoo] .ThreeColumnItemTable{ padding: 0px 0px 0px 74px !important } body[yahoo] .FourColumnFloater { float: right !important; } span.message-history{ text-align: left !important; float: right !important; } } body[yahoo] .mobile-full-width { min-width: 103px; max-width: 300px; height: 38px; } body[yahoo] .mobile-full-width a { display: block; padding: 10px 0; } body[yahoo] .mobile-full-width td{ padding: 0px !important } td.wrapText{ white-space: -moz-pre-wrap; white-space: -pre-wrap; white-space: -o-pre-wrap; word-wrap: break-word; } @-moz-document url-prefix() { td.wrapTextFF_Fix { display: inline-block } } body { width: 100% !important; -webkit-text-size-adjust: 100% !important; -ms-text-size-adjust: 100% !important; -webkit-font-smoothing: antialiased !important; margin: 0 !important; padding: 0 0 100px !important; font-family: Helvetica, Arial, sans-serif !important; background-color:#f9f9f9} .ReadMsgBody { width: 100% !important; background-color: #ffffff !important; } .ExternalClass { width: 100% !important; } .ExternalClass { line-height: 100% !important; } img { display: block; outline: none !important; text-decoration: none !important; -ms-interpolation-mode: bicubic !important; } td{word-wrap: break-word;} </style> <!--[if gte mso 9]> <style>td.product-details-block{word-break:break-all}.threeColumns{width:140px !important}.threeColumnsTd{padding:10px 20px !important}.fourColumns{width:158px !important}.fourColumnsPad{padding: 0 18px 0 0 !important}.fourColumnsTd{padding:10px 0px !important}.twoColumnSixty{width:360px !important}table{mso-table-lspace:0pt; mso-table-rspace:0pt;}</style> <![endif]--> </head> <body yahoo="fix"><table id="area2Container" width="100%" border="0" cellpadding="0" cellspacing="0" align="center" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none; background-color:#f9f9f9"><tr><td width="100%" valign="top" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;"><table width="600" class="device-width header-logo" border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="#f9f9f9" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;"> <tr> <td valign="top" style="border-collapse: collapse !important; border-spacing: 0 !important; padding: 0; border: none;"><p style="font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: normal; color: #888888; text-align: left; font-size: 11px; margin: 5px 0 10px 0; color: #333;" align="left">新訊息： Dear buyer, Thanks for your confirmed reply. …</p></td> </tr> </table></td></tr></table> <table id="area3Container" width="100%" border="0" cellpadding="0" cellspacing="0" align="center" style="border-collapse:collapse !important;border-spacing:0 !important;border:none;background-color:#f9f9f9;"> <tr> <td width="100%" valign="top" style="border-collapse:collapse !important;border-spacing:0 !important;border:none;"> <table width="100%" height="7" border="0" cellpadding="0" cellspacing="0" align="center" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none; background-image: url('http://p.ebaystatic.com/aw/navbar/preHeaderBottomShadow.png'); background-repeat: repeat-y no-repeat; margin: 0; padding: 0"> <!--[if gte mso 9]> <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="mso-width-percent:1000;height:1px;"> <v:fill type="tile" color="#dddddd" /> </v:rect> <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="mso-width-percent:1000;height:6px;"> <v:fill type="tile" src="http://p.ebaystatic.com/aw/navbar/preHeaderBottomShadow.png" color="#f9f9f9" /> <div style="width:0px; height:0px; overflow:hidden; display:none; visibility:hidden; mso-hide:all;"> <![endif]--> <tr> <td width="100%" height="1" valign="top" align="center" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none; background-color: #dddddd; font-size: 1px; line-height: 1px;"> <!--[if gte mso 15]>&nbsp;<![endif]--> </td> </tr> <tr> <td width="100%" height="6" valign="top" align="center" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none; background-color: none; font-size: 1px; line-height: 1px;">&nbsp;</td> </tr> <!--[if gte mso 9]> </div> </v:rect> <![endif]--> </table> </td> </tr> </table> <table id="area4Container" width="100%" border="0" cellpadding="0" cellspacing="0" align="center" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none; background-color:#f9f9f9"> <tr> <td width="100%" valign="top" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;"> <table width="600" class="device-width header-logo" border="0" cellpadding="0" cellspacing="0" align="center" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;"> <tr> <td valign="top" style="border-collapse: collapse !important; border-spacing: 0 !important; padding: 15px 0 20px; border: none;"><a href="http://rover.ebay.com/rover/0/e11051.m1831.l3127/7?euid=f0c9d1254d99449da2613037aa6475ee&loc=http%3A%2F%2Fpages.ebay.com%2Flink%2F%3Fnav%3Dhome%26alt%3Dweb%26globalID%3DEBAY-HK%26referrer%3Dhttp%253A%252F%252Frover.ebay.com%252Frover%252F0%252Fe11051.m1831.l3127%252F7%253Feuid%253Df0c9d1254d99449da2613037aa6475ee%2526cp%253D1" style="text-decoration: none; color: #0654ba;"><img src="http://p.ebaystatic.com/aw/logos/header_ebay_logo_132x46.gif" width="132" height="46" border="0" alt="eBay" align="left" style="display: inline block; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; border: none;" /></a><img src="http://rover.ebay.com/roveropen/0/e11051/7?euid=f0c9d1254d99449da2613037aa6475ee" alt="" style="border:0; height:1;"/></td> </tr> </table> </td> </tr> </table> <table id="area5Container" width="100%" border="0" cellpadding="0" cellspacing="0" align="center" style="border-collapse: collapse !important; border-spacing: 0 !important; background-color:#f9f9f9"> <tr> <td width="100%" valign="top" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;"> <table id="PrimaryMessage" width="600" class="device-width" border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="#f9f9f9" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;"> <tr> <td valign="top" class="secondary-headline" style="border-collapse: collapse !important; border-spacing: 0 !important; padding: 20px 0 5px;"> <h1 style="font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: 15px; color: #808284; text-align: left; font-size: 13px; margin: 0 0 4px;" align="left">新訊息收件人： <a href="http://rover.ebay.com/rover/0/e11051.m44.l1181/7?euid=f0c9d1254d99449da2613037aa6475ee&loc=http%3A%2F%2Fpages.ebay.com%2Flink%2F%3Fnav%3Duser.view%26user%3Dav2682%26globalID%3DEBAY-HK%26referrer%3Dhttp%253A%252F%252Frover.ebay.com%252Frover%252F0%252Fe11051.m44.l1181%252F7%253Feuid%253Df0c9d1254d99449da2613037aa6475ee%2526cp%253D1" style="text-decoration: none; font-weight: bold; color: #336fb7;">av2682</a></h1></td> </tr> <tr> <td valign="top" class="viewing-problem-block" style="border-collapse: collapse !important; border-spacing: 0 !important; border-bottom-width: 1px; border-bottom-color: #f9f9f9; padding: 10px 0 30px; border-style: none none solid;"> <table width="600" class="device-width" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none; border:0; cellpadding:0; cellspacing:0; align:center; bgcolor:#f9f9f9;"> <tr> <td width="100%" class="wrapText device-width" valign="top" style="overflow: hidden; border-collapse: collapse !important; border-spacing: 0 !important; border: none; display: inline-block; max-width:600px;"><h3 style="font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: 19px; color: #231f20; text-align: left; font-size: 14px; margin: 0 0 2px; font-weight:none;" align="left"><div id="UserInputtedText">Dear buyer,<br />Thanks for your confirmed reply. <br />Pls don't worry, we will try our best to help you. <br />I just issued you the full refund via eBay, pls check and accept the request.<br />Sorry for all the inconvenience.<br />Please kindly contact us for help if something unsatisfied you, we would like to avoid the unexpected unhappy feedback as our best effort.<br />We will improve our service step by step.<br />Sincerely,<br />Ann</div></h3> <span style="color:#666666"> </span> </td> </tr> <tr> <td valign="top" width="15" height="15" style="border-collapse: collapse !important; border-spacing: 20 !important; border: none;"></td> </tr> </table> </td> </tr> </table> <!--[if !mso]><!--> <div id="V4PrimaryMessage" style="max-height: 0px; font-size: 0; overflow:hidden; display: none !important;"> <div> <table border="0" cellpadding="2" cellspacing="3" width="100%"> <tr> <td> <font style="font-size:10pt; font-family:arial, sans-serif; color:#000"><strong>av2682，你好！</strong><br><br>Dear buyer,<br />Thanks for your confirmed reply. <br />Pls don't worry, we will try our best to help you. <br />I just issued you the full refund via eBay, pls check and accept the request.<br />Sorry for all the inconvenience.<br />Please kindly contact us for help if something unsatisfied you, we would like to avoid the unexpected unhappy feedback as our best effort.<br />We will improve our service step by step.<br />Sincerely,<br />Ann<br><br></font><div style="font-weight:bold; font-size:10pt; font-family:arial, sans-serif; color:#000">- fasteasybuylight</div> </td> <td valign="top" width="185"> <div></div> </td> </tr> </table> </div> </div> <!--<![endif]--> </td> </tr> </table> <div id="area6TopBorder"> <table width="100%" class="device-width" border="0" cellpadding="0" cellspacing="0" align="center" style="border-top-color: #dddddd; border-top-width: 1px; border-style: solid none none;"> <tr> <td> </td> </tr> </table> </div> <table id="MessageHistory1" class="whiteSection" width="100%" align="center" cellspacing="0" cellpadding="0" border="0" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none; background-color: #ffffff"> <tr> <td width="100%" valign="top" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;"> <table width="600" class="device-width" border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="#ffffff" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;"> <tr> <td valign="top" class="viewing-problem-block" style="border-collapse: collapse !important; border-spacing: 0 !important; padding: 20px 0 30px;"> <p style="font-family: Helvetica, Arial, sans-serif; font-weight:bold; line-height: normal; color: #85b716; text-align: left; font-size: 13px; margin: 0 0 10px;" align="center"> <a href="http://rover.ebay.com/rover/0/e11051.m4046.l1181/7?euid=f0c9d1254d99449da2613037aa6475ee&loc=http%3A%2F%2Fpages.ebay.com%2Flink%2F%3Fnav%3Duser.view%26user%3Dav2682%26globalID%3DEBAY-HK%26referrer%3Dhttp%253A%252F%252Frover.ebay.com%252Frover%252F0%252Fe11051.m4046.l1181%252F7%253Feuid%253Df0c9d1254d99449da2613037aa6475ee%2526cp%253D1" style="text-decoration: none; color: #85b716;">av2682</a> </p> <table width="600" class="device-width" border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="#ffffff" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;"> <tr> <td> <table class="device-width" border="0" cellpadding="0" cellspacing="0" align="left" bgcolor="#ffffff" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;"> <tr> <td class="wrapText device-width" valign="top" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;"> <span style="font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: 19px; color: #231f20; text-align: left; font-size: 14px; margin: 0 0 10px;" align="left"> <div id="UserInputtedText1">Please cancel order and refund the money<br /><br />On 03/03/2016, 03:57, fasteasybuylight wrote:<br /><br />Re: av2682 has sent a question about item #391155709100, ending on 22-Mar-16 15:04:24 GMT - 1 Way 1/2 Gang Luxury Crystal Glass Panel Touch or Remote or Dimmer Light Switch<br /><br />Dear buyer,<br />Thanks for your update.<br />We are sorry to tell you that the items you ordered is not the dimmable ones.<br />We do not have dimmable light on sell at present.<br />Do you still want us to send out this item for you?<br />We will take the next action once get your confirmed reply.<br />S…</div> </span> </td> </tr> </table> </td> </tr> </table> </td> </tr> </table> </td> </tr> </table> <!--[if !mso]><!--> <div id="V4EmailHeader1" style="max-height: 0px; font-size: 0; overflow:hidden; display: none !important;"> <div style="font-size:8pt; font-family:verdana, sans-serif; color:#666"><br><br><hr/>寄件人： av2682<br>收件人： fasteasybuylight<br>主旨： av2682 針對物品編號 391155709100 提出問題，結束時間為 2016-03-22 23:04:24–1 Way 1/2 Gang Luxury Crystal Glass Panel Touch or Remote or Dimmer Light Switch<br>送出日期： Mar-02-16 22:20:41 PST<br><br></div> </div> <div id="V4PrimaryMessage1" style="max-height: 0px; font-size: 0; overflow:hidden; display: none !important;"> <div> <table border="0" cellpadding="2" cellspacing="3" width="100%"> <tr> <td> <font style="font-size:10pt; font-family:arial, sans-serif; color:#000"><strong>fasteasybuylight，你好！</strong><br><br>Please cancel order and refund the money<br /><br />On 03/03/2016, 03:57, fasteasybuylight wrote:<br /><br />Re: av2682 has sent a question about item #391155709100, ending on 22-Mar-16 15:04:24 GMT - 1 Way 1/2 Gang Luxury Crystal Glass Panel Touch or Remote or Dimmer Light Switch<br /><br />Dear buyer,<br />Thanks for your update.<br />We are sorry to tell you that the items you ordered is not the dimmable ones.<br />We do not have dimmable light on sell at present.<br />Do you still want us to send out this item for you?<br />We will take the next action once get your confirmed reply.<br />S…<br><br></font><div style="font-weight:bold; font-size:10pt; font-family:arial, sans-serif; color:#000">- av2682</div> </td> <td valign="top" width="185"> <div></div> </td> </tr> </table> </div> </div> <!--<![endif]--> <table id="area3Container" width="100%" border="0" cellpadding="0" cellspacing="0" align="center" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none; background-color:#f9f9f9"> <tr> <td width="100%" valign="top" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;"><table width="100%" height="7" border="0" cellpadding="0" cellspacing="0" align="center" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none; background-image: url(&#39;http://p.ebaystatic.com/aw/navbar/preHeaderBottomShadow.png&#39;); background-repeat: repeat-y no-repeat; margin: 0; padding: 0"> <tr><td width="100%" height="1" valign="top" align="center" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none; background-color: #dddddd;"></td></tr> <tr><td width="100%" height="6" valign="top" align="center" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none; background-color: none;"></td></tr></table> </td> </tr> </table> <table id="area7Container" width="100%" border="0" cellpadding="0" cellspacing="0" align="center" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none; background-color:#f9f9f9;"> <tr> <td width="100%" valign="top" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;"> <table class="device-width" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;" align="center" bgcolor="#f9f9f9" border="0" cellpadding="0" cellspacing="0" width="600"> <tr> <td style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;" valign="top"><h1 style="font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: 28px; color: #2258a7; text-align: left; font-size: 24px; margin: 1px;" align="left"><br><a href="http://rover.ebay.com/rover/0/e11051.m43.l1123/7?euid=f0c9d1254d99449da2613037aa6475ee&loc=http%3A%2F%2Fpages.ebay.com%2Flink%2F%3Fnav%3Ditem.view%26id%3D391155709100%26alt%3Dweb%26globalID%3DEBAY-HK%26referrer%3Dhttp%253A%252F%252Frover.ebay.com%252Frover%252F0%252Fe11051.m43.l1123%252F7%253Feuid%253Df0c9d1254d99449da2613037aa6475ee%2526cp%253D1" style="text-decoration: none; color: #0654ba;">1 Way 1/2 Gang Luxury Crystal Glass Panel Touch or Remote or Dimmer Light Switch</a></h1></td> </tr> <tr> <td valign="top" class="secondary-headline" style="border-collapse: collapse !important; border-spacing: 0 !important; border-bottom-color: #dddddd; border-bottom-width: 1px; border-style: none none solid;"><h1 style="font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: 28px; color: #00a550; font-weight: bold; text-align: left; font-size: 20px; margin: 0 0 14px;" align="left"></h1></td> </tr> </table> <table class="device-width" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;" align="center" bgcolor="#f9f9f9" border="0" cellpadding="0" cellspacing="0" width="600"> <tr> <td class="single-product-block" style="border-collapse: collapse !important; border-spacing: 0 !important; padding: 15px 0 50px; border: none;" valign="top"> <table class="single-product-table" style="align: left; border-collapse: collapse !important; border-spacing: 0 !important; border: none;" align="left" border="0" cellpadding="0" cellspacing="0" width="215"> <tr> <td valign="middle" width="200" height="200" align="center" class="single-product-image" style="border-collapse: collapse !important; border-spacing: 0 !important; border: 1px solid #dddddd; background-color: #ffffff;"><a href="http://rover.ebay.com/rover/0/e11051.m43.l1123/7?euid=f0c9d1254d99449da2613037aa6475ee&loc=http%3A%2F%2Fpages.ebay.com%2Flink%2F%3Fnav%3Ditem.view%26id%3D391155709100%26alt%3Dweb%26globalID%3DEBAY-HK%26referrer%3Dhttp%253A%252F%252Frover.ebay.com%252Frover%252F0%252Fe11051.m43.l1123%252F7%253Feuid%253Df0c9d1254d99449da2613037aa6475ee%2526cp%253D1" style="text-decoration: none; color: #0654ba;"><img src="http://thumbs.ebaystatic.com/d/l200/pict/391155709100_1.jpg" alt="1 Way 1/2 Gang Luxury Crystal Glas" class="product-image" border="0" style="display: block; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; margin: 0; border: none;" /></a></td> <td class="single-product-pad" style="padding: 0 15px 0 0; border-collapse: collapse !important; border-spacing: 0 !important; border: none;"></td> </tr> </table> <table class="twoColumnSixty" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;" align="left" border="0" cellpadding="0" cellspacing="0" width="380"> <tr> <td class="product-price" style="border-collapse: collapse !important; border-spacing: 0 !important; font-family: Helvetica, Arial, sans-serif; text-align: left; font-size: 20px; line-height: 18px; font-weight: bold; border: none;" align="left">訂單狀態：已付款</td> </tr> <tr> <td class="product-shipping" style="border-collapse: collapse !important; border-spacing: 0 !important; font-family: Helvetica, Arial, sans-serif; text-align: left; font-size: 13px; padding-bottom: 8px; padding-top: 2px; border: none;" align="left">已於 2016-03-03 14:50 HKT 使用 PAYPAL 為 件物品支付 GBP 44.97 的款項</td> </tr> <tr> <td class="product-bids" style="border-collapse: collapse !important; border-spacing: 0 !important; font-family: Helvetica, Arial, sans-serif; text-align: left; font-size: 12px; color: #666666; border: none;" align="left"> 物品編號： 391155709100<br> 交易編號： 630035341026<br> </td> </tr> <tr> <td style="border-collapse: collapse !important; border-spacing: 0 !important; font-family: Helvetica, Arial, sans-serif; text-align: left; font-size: 12px; color: #666666; border: none;" align="left"> </td> </tr> <tr> <td valign="top" class="cta-block" style="border-collapse: collapse !important; border-spacing: 0 !important; padding: 25px 0 40px; border: none;"> <table align="left" cellpadding="0" cellspacing="0" border="0" class="mobile-full-width" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;"> <tr> <td valign="top" class="center cta-button primary-cta-button" align="center" style="border-collapse: collapse !important; border-spacing: 0 !important; font-size: 14px; line-height: normal; font-weight: bold; box-shadow: 2px 3px 0 #e5e5e5; filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#0079bc', endColorstr='#00519e',GradientType=0 ); background-image: linear-gradient(to bottom, #0079bc 0%,#00519e 100%); background-color: #0079bc; padding: 10px 17px; border: 1px solid #00519e;" bgcolor="#0079bc"><a href="http://rover.ebay.com/rover/0/e11051.m43.l1503/7?euid=f0c9d1254d99449da2613037aa6475ee&loc=http%3A%2F%2Fpayments.ebay.com.hk%2Fws%2FeBayISAPI.dll%3FViewPaymentStatus%26transId%3D630035341026%26qu%3D3%26itemId%3D391155709100" style="text-decoration: none; color: #ffffff; font-size: 14px; line-height: normal; font-weight: bold; font-family: Helvetica, Arial, sans-serif; text-shadow: 1px 1px 0 #00519e;"><span style="padding: 0px 10px">檢視訂單詳細資料</span></a></td> </tr> </table> </td> </tr> </table> </td> </tr> </table> </td> </tr> </table> <table id="area8Container" width="100%" border="0" cellpadding="0" cellspacing="0" align="center" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none; border-top: solid 1px #dddddd; background-color: #ffffff"><tr><td style="font-size:0px; line-height:0px" height="1">&nbsp;</td></tr></table> <table id="area10Container" class="whiteSection" width="100%" border="0" cellpadding="0" cellspacing="0" align="center" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none; background-color: #ffffff"> <tr> <td width="100%" valign="top" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;"> <table width="600" class="device-width" border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="#ffffff" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;"> <tr> <td valign="top" class="viewing-problem-block" style="border-collapse: collapse !important; border-spacing: 0 !important; border-bottom-width: 1px; border-bottom-color: #dddddd; padding: 40px 0 30px; border-style: none none solid;"><p style="font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: normal; color: #888888; text-align: left; font-size: 11px; margin: 0 0 10px;" align="center">只有 eBay 上購買的物品才享有 eBay 購物保障計劃。要求交易對方在 eBay 以外完成交易，是違規的行為。</p></td> </tr> </table> </td> </tr> </table> <table id="area11Container" class="whiteSection" width="100%" border="0" cellpadding="0" cellspacing="0" align="center" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none; background-color: #ffffff"> <tr> <td width="100%" valign="top" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;"> <table width="600" class="device-width" border="0" cellpadding="0" cellspacing="0" align="center" style="border-collapse: collapse !important; border-spacing: 0 !important; border: none;"> <tr> <td valign="top" class="ebay-footer-block" style="border-collapse: collapse !important; border-spacing: 0 !important; padding: 40px 0 60px; border: none;"> <div id="ReferenceId"> <p style="font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: normal; color: #888888; text-align: left; font-size: 11px; margin: 0 0 10px;" align="left"><strong> 電郵參考編號： [#a10-zpobw9b9kk#]_[#f0c9d1254d99449da2613037aa6475ee#] </strong></p></div> <p style="font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: normal; color: #888888; text-align: left; font-size: 11px; margin: 0 0 10px;" align="left"> 我們不會檢查此郵箱，因此請不要回覆這個訊息：如果你有問題，請前往「<a style="text-decoration: none; color: #555555;" href="http://rover.ebay.com/rover/0/e11051.m1852.l6369/7?euid=f0c9d1254d99449da2613037aa6475ee&loc=http%3A%2F%2Focsnext.ebay.com.hk%2Focs%2Fhome" target="_blank">支援及聯絡</a>」。 </p> <p style="font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: normal; color: #888888; text-align: left; font-size: 11px; margin: 0 0 10px;" align="left"> 此訊息是由 eBay 寄給 杨 新辉（fasteasybuylight）。進一步了解<a style="text-decoration: none; color: #555555;" href="http://rover.ebay.com/rover/0/e11051.m1852.l3167/7?euid=f0c9d1254d99449da2613037aa6475ee&loc=http%3A%2F%2Fpages.ebay.com.hk%2Fhelp%2Faccount%2Fprotecting-account.html" target="_blank">保障帳戶安全</a>。eBay 致力保障你的私隱。進一步了解我們的<a style="text-decoration: none; color: #555555;" href="http://rover.ebay.com/rover/0/e11051.m1852.l3168/7?euid=f0c9d1254d99449da2613037aa6475ee&loc=http%3A%2F%2Fpages.ebay.com.hk%2Fhelp%2Fpolicies%2Fprivacy-policy.html" target="_blank">私隱權政策</a>和<a style="text-decoration: none; color: #555555;" href="http://rover.ebay.com/rover/0/e11051.m1852.l3165/7?euid=f0c9d1254d99449da2613037aa6475ee&loc=http%3A%2F%2Fpages.ebay.com.hk%2Fhelp%2Fpolicies%2Fuser-agreement.html" target="_blank">會員合約</a>。 </p> <p style="font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: normal; color: #888888; text-align: left; font-size: 11px; margin: 0 0 10px;" align="left"> &copy;2016 eBay Inc., eBay International AG Helvetiastrasse 15/17 - P.O. Box 133, 3000 Bern 6, Switzerland </p> </td> </tr> </table> </td> </tr> </table></body> </html>
eot;
        $columns['Text'] = tidyTool::cleanRepair($columns['Text']);
        
        $hdoc = phpQuery::newDocumentHTML($columns['Text']);
        phpQuery::selectDocument($hdoc);
        
        $OrderId = pq('#ItemDetails')->find('table table')
            ->find('tr')
            ->eq(2)
            ->find('td')
            ->eq(1)
            ->html();
        preg_match('/^\d{11,13}-\d{11,13}|\d{11,13}-0|\d{11,13}$/', $OrderId, $matches);
        if (empty($matches)) {
            $OrderId = pq('#area7Container')->find('table table.twoColumnSixty')
                ->find('tr')
                ->eq(2)
                ->find('td')
                ->eq(0)
                ->html();
            echo $OrderId;
            if (! empty($OrderId)) {
                preg_match_all('/(?<=\D)\d{11,13}-\d{11,13}|\d{11,13}-0|\d{11,13}(?=\D)/', $OrderId, $matches);
                if (! empty($matches) && empty($matches[0][0]) && empty($matches[0][1])) {
                    $OrderId = $matches[0][0] . '-' . $matches[0][1];
                } else {
                    $OrderId = null;
                }
            } else {
                $OrderId = null;
            }
        }
        
        var_dump($OrderId, $matches);
    }
}
