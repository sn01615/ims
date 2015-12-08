<?php
/**
 * @desc 常用的函数操作
 * @author ChenLuoyong
 * @date 2014-10-15
 */
class Utility
{
	/**
	 * @desc 获取时间,时间转换
	 * @author yanjunwei
	 * @date 2014-10-11
	 */
	public static function getTimestamp($time = 'Etc/GMT',$date='now')
	{
		date_default_timezone_set($time);
		return gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", strtotime($date));
	}

	/**
	 * @desc 使用mongodb记录亚马逊调用返回结果状况
	 * @param string $apiclass 要实例化的亚马逊接口类
	 * @param string $apimethod 调用亚马逊方法
	 * @param array $apiparam 接口参数
	 * @return mixed 成功调用返回xml object ,调用失败返回出错原因信息
	 * @author shiyongbao
	 * @date 2014-10-18
	 */
	public static function recordAPILog($apiclass, $apimethod, $apiparam){
			$mongo = CMongodbHelper::getInstance();
			$mongo->setDBName("test");
			try{
				$API = new $apiclass();
				$rs=$API ->callAPI($apimethod, $apiparam);
				$mongo->insert("c1",array(
										'api'       => $apimethod,
										'input'     => $apiparam,
										'ctime'     => time(),
										'exception' => 0,
										'output'    => htmlspecialchars($rs->asXML()),
				));
				return $rs;
			}catch(MarketplaceWebServiceOrders_Exception $e){
				$mongo->insert("c1",array(
										'api'       => $apimethod,
										'input'     => $apiparam,
										'ctime'     => time(),
										'exception' => 1,
										'output'    => htmlspecialchars($e->getXML()),
				));
				return $e->getMessage();
			}	
	}

	/**
	 * @desc 转换数据到表定义的数据库存储码
	 * @param string $string 要转义的字符串 
	 * @param array $searchSet 用户定义的转义数组 
	 * @return mixed 该值对应的数据库存储码
	 * @author Weixun Luo
	 * @date 2014-10-24
	 */
	public static function toDBCode($string, $searchSet, $default = 0){
		if(empty($string) || empty($searchSet) || !is_array($searchSet)){
			return $default;
		}
		$code = array_search($string, $searchSet);
		return ($code === false) ? $default : $code;
	}

	/**
	 * @desc 将数据库存储代码转成实际意义的值
	 * @param mixed $key 在数据库里存储的值 
	 * @param array $searchSet 用户定义的转义数组 
	 * @return 该数据库存储码实际代表的值
	 * @author Weixun Luo
	 * @date 2014-10-24
	 */
	public static function toActual($key, $searchSet, $default = ''){
		if(!isset($key) || empty($searchSet) || !is_array($searchSet)){
			return $default;
		}
		return Utility::getArrayValue($searchSet, $key, $default);
	}

	/**
	 * @desc 判断数组里是否存在该键值对应的元素
	 * @param array $searchArray 要查找的数组
	 * @param string $key 要查找的键值
	 * @param $default 不存在时候的默认值
	 * @return mixed，有值则返回，其余返回默认值
	 * @author Weixun Luo
	 * @date 2014-10-28
	 */
	public static function getArrayValue($searchArray, $key, $default = ''){
		if(!is_array($searchArray) || empty($searchArray) || !isset($key)){
			return $default;
		}
		$returnValue = $default;
		if(array_key_exists($key, $searchArray)){
			$returnValue = $searchArray[$key];
			if($searchArray[$key] === '' || $searchArray[$key] === NULL){
				// 空字符窜返回默认值
				$returnValue = $default;
			}
			if(is_array($searchArray[$key]) && empty($searchArray[$key])){
				// 空数组返回默认值
				$returnValue = $default;
			}
		}
		return $returnValue;
	}

	/**
	 * @desc 按国家名首字母字典序列举所有国家
	 * @return array $countryArray 国家名首字母字典序分类国家爱数据数组
	 * @author Weixun Luo
	 * @date 2014-12-21
	 */
	public static function listCountry(){
		$countryArrayEn = Country::$countryNameEn;
		$countryArrayCn = Country::$countryNameCn;
		$countryArray = array(
			'lists' => array(),
			'usual' => array(
				// 常用国家
				Country::US_CODE => array(
					'en' => Utility::toActual(Country::US_CODE, $countryArrayEn), 
					'cn' => Utility::toActual(Country::US_CODE, $countryArrayCn)), // 美国
				Country::GB_CODE => array(
					'en' => Utility::toActual(Country::GB_CODE, $countryArrayEn), 
					'cn' => Utility::toActual(Country::GB_CODE, $countryArrayCn)), // 英国
				Country::CA_CODE => array(
					'en' => Utility::toActual(Country::CA_CODE, $countryArrayEn), 
					'cn' => Utility::toActual(Country::CA_CODE, $countryArrayCn)), // 加拿大
				Country::DE_CODE => array(
					'en' => Utility::toActual(Country::DE_CODE, $countryArrayEn), 
					'cn' => Utility::toActual(Country::DE_CODE, $countryArrayCn)), // 德国
				Country::ES_CODE => array(
					'en' => Utility::toActual(Country::ES_CODE, $countryArrayEn), 
					'cn' => Utility::toActual(Country::ES_CODE, $countryArrayCn)), // 西班牙
				Country::FR_CODE => array(
					'en' => Utility::toActual(Country::FR_CODE, $countryArrayEn), 
					'cn' => Utility::toActual(Country::FR_CODE, $countryArrayCn)), // 法国
				Country::IT_CODE => array(
					'en' => Utility::toActual(Country::IT_CODE, $countryArrayEn), 
					'cn' => Utility::toActual(Country::IT_CODE, $countryArrayCn)), // 意大利
				Country::AU_CODE => array(
					'en' => Utility::toActual(Country::AU_CODE, $countryArrayEn), 
					'cn' => Utility::toActual(Country::AU_CODE, $countryArrayCn)), // 澳大利亚
				),
			);
		foreach ($countryArrayEn as $code => $nameEn) {
			$field = strtolower($nameEn[0]);
			if(!isset($countryArray['lists'][$field])){
				$countryArray['lists'][$field] = array();
			}
			$nameCn = Utility::toActual($code, $countryArrayCn);
			$countryArray['lists'][$field][$code] = array('en' => $nameEn, 'cn' => $nameCn);
		}
		return $countryArray;
	}

    /**
     * @desc 分页信息 传入记录数获取 分页的HTML
     * @author YangLong
     * @param number $recordcount 记录总数
     * @return array 返回HTML,offset,limit
     * @date 2014-12-5
     */
    public static function getPage($recordcount = 0)
    {
        $page = CInputFilter::getString('page', 1);
        $page = intval($page);
        $psize = CInputFilter::getInt('psize', SHPublic::DEFAULT_PAGESIZE);
        $page > 0 or $page = 1;
        $psize > 0 or $psize = 1;
        $data['page']['offset'] = ($page - 1) * $psize;
        $data['page']['limit'] = $psize;
        if ($recordcount > 0) {
            if ($psize == $recordcount) {
                $pgcount = (int) ($recordcount / $psize);
            } else {
                $pgcount = (int) ($recordcount / $psize) + 1;
            }
            $data['html'] = '<ul id="Pager1">';
            $str = '';
            foreach ($_GET as $key => $value) {
                if ($key != 'page' & $key != 'psize') {
                    $str .= ((empty($str) ? '' : '&') . $key . '=' . $value);
                }
            }
            $pp = $page - 1;
            $pp = (int) ($pp / 5);
            $pp = $pp * 5;
            $pp ++;
            if ($page <= 1) {
                $data['html'] .= "<li class='active'><a>首页</a></li>";
                $data['html'] .= "<li class='active'><a>&lt;&lt;</a></li>";
            } else {
                $data['html'] .= "<li><a href='?{$str}&page=1&psize={$psize}'>首页</a></li>";
                $data['html'] .= "<li><a href='?{$str}&page=" . ($page - 1) . "&psize={$psize}'>&lt;&lt;</a></li>";
            }
            if ($pp > 1) {
                $data['html'] .= "<li><a href='?{$str}&page=" . ($page - 5) . "&psize={$psize}'>...</a></li>";
            }
            for ($i = $pp; $i < $pp + 5; $i ++) {
                if ($i > $pgcount) {
                    break;
                }
                if ($i == $page) {
                    $data['html'] .= "<li class='active'><a>{$i}</a></li>";
                } else {
                    $data['html'] .= "<li><a href='?{$str}&page={$i}&psize={$psize}'>{$i}</a></li>";
                }
            }
            if (($page + 5) < $pgcount) {
                $data['html'] .= "<li><a href='?{$str}&page=" . ($page + 5) . "&psize={$psize}'>...</a></li>";
            }
            if ($page < $pgcount) {
                $data['html'] .= "<li><a href='?{$str}&page=" . ($page + 1) . "&psize=" . $psize . "'>&gt;&gt;</a></li>";
            } else {
                $data['html'] .= "<li class='active'><a>&gt;&gt;</a></li>";
            }
            if ($page == $pgcount) {
                $data['html'] .= "<li class='active'><a>尾页</a></li>";
            } else {
                $data['html'] .= "<li><a href='?{$str}&page={$pgcount}&psize={$psize}'>尾页</a></li>";
            }
            
            $data['html'] .= "<li class='select-size'>每页{$psize}条，共<strong>{$pgcount}</strong>页<strong>{$recordcount}</strong>条</li>";
            $data['html'] .= '</ul>';
        }
        
        return $data;
    }

	/**
	 * @desc 检查请求处理平台是否为系统所支持
	 * @param int $platform 平台代码常量
	 * @author Weixun Luo
	 * @date 2014-12-04
	 */
	public static function checkPlatform($platform){
		if(empty($platform)){
    		return false;
    	}
    	if(!is_int($platform)){
    		$platform = intval($platform);
    	}
		if($platform === Platform::EBAY || $platform === Platform::AMAZON || $platform === Platform::ALI || $platform === Platform::WISH){
    		// 系统目前仅支持除eBay、amazon、aliExpress、wish平台
    		return true;
    	}
    	return false;
	}

	/**
	 * @desc 将查询结果数组变成以某个值为键的带键数组
	 * @param array $queryArray 查询结果数组
	 * @param mixed $keyColumn 其值要作为键的列名string或下标int
	 * @param bool $isUnset 是否删除作为键的值
	 * @param bool $isMerge 是否合并成键值对（数组只有两个值时）
	 * @return array $resultArray 转换后的键值对数组
	 * @author Weixun Luo
	 * @date 2014-11-13
	 */
	public static function arrayWithKey($queryArray, $keyColumn, $isUnset = false, $isMerge = false){
		if(empty($queryArray) || !is_array($queryArray) || empty($keyColumn)){
			return false;
		}
		$resultArray = array();
		foreach ($queryArray as $row) {
			$resultKey = $row[$keyColumn];
			if($isUnset){
				unset($row[$keyColumn]);
			}
			if($isMerge && count($row) === 1){
				$row = array_pop($row);
			}
			$resultArray[$resultKey] = $row;
		}
		return $resultArray;
	}
}