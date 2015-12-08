<?php
/**
 * @desc 定义信息状态枚举类
 * @author heguangquan
 * @date 2015-01-27
 */
class EnumMsgStatus
{
	/**
	 * @var 定义常量
	 */
	const READ_NOT = '0';						//未读
	const READ_SUCCE = '1';					//已读
	const STAR_NOT = '0';						//取消标星
	const STAR_SUCCE = '1';					//标星
	const HANDLED_NOT = '0';					//未处理
	const HANDLED_YES = '1';				//已处理
	const REPLIED_NOT = '0';					//未回复
	const REPLIED_SUCCE = '1';				//已回复
	const SEND_STATUS_FAILURE = '1';				//信息发送失败
	const SEND_STATUS_NORMAL = '0';				//信息发送成功
	
	/**
	 * @var 定义信息删除常量
	 */
	const MSG_DELETE_NOT = 0;				//信息未删除
	const MSG_DELETE_YES = 1;				//信息已删除(已删除)
	const MSG_DELETE_SUCCE = 2;				//已删除信息删除(用户看不到)
	
	/**
	 * @var EBAY 站点列表
	 */
 	const EBAY_SITE_US = 0;					// 美国站United States (0)
	const EBAY_SITE_CANDA = 2;				// 加拿大站Canda (2)
    const EBAY_SITE_UK = 3;					//英国站 UK (3)
	const EBAY_SITE_AUSTRALIA = 15;			//澳大利亚站  15 Australia (15)
	const EBAY_SITE_AUSTRIA =16;			//奥地利站  16 Austria (16)
	const EBAY_SITE_BELGIUMF =23;			//比利时法语站	 23 Belgium (French) (23)
	const EBAY_SITE_FRANCE = 71;			//法国站  71 France (71)
	const EBAY_SITE_GERMANY = 77;			//德国站 	 77 Germany (77)
	const EBAY_SITE_MOTORS = 100;			//汽车交易平台  Motors (100)
	const EBAY_SITE_ITALLY = 101;			//意大利站    Italy (101)
	const EBAY_SITE_BELGIUMD = 123;			//比利时荷兰语站    Belgium (Dutch) (123)
	const EBAY_SITE_NETERLANDS = 146;		//荷兰站    Netherlands (146)
	const EBAY_SITE_SPAIN = 186;			//西班牙站     Spain (186)
	const EBAY_SITE_SWITZERLAND = 193;		//瑞士站    Switzerland (193)
	const EBAY_SITE_HK = 201;				//香港站    Hong Kong (201)
	const EBAY_SITE_INDIA = 203;			//印度站  India (203)
	const EBAY_SITE_IRELANS = 205;			//爱尔兰站    Ireland (205)
	const EBAY_SITE_MALAYSIA = 207;			//马来西亚站    Malaysia (207)
	const EBAY_SITE_CANADAF = 210;			//加拿大法语站    Canada (French) (210)
	const EBAY_SITE_PHILIPPINES = 211;		//菲律宾站    Philippines (211)
	const EBAY_SITE_POLAND = 212;			//波兰站   Poland (212)
	const EBAY_SITE_SINGAPORE = 216;		//新加坡站    Singapore (216)
	
	public static $siteOptions = array(
		self::EBAY_SITE_US     		=>'United States',
		self::EBAY_SITE_CANDA 	    =>'Canda',
		self::EBAY_SITE_UK			=>'UK',
		self::EBAY_SITE_AUSTRALIA	=>'Australia',
		self::EBAY_SITE_AUSTRIA		=>'Austria',
		self::EBAY_SITE_BELGIUMF	=>'Belgium (French)',
		self::EBAY_SITE_FRANCE		=>'France',
		self::EBAY_SITE_GERMANY		=>'Germany',
		self::EBAY_SITE_MOTORS		=>'Motors',
		self::EBAY_SITE_ITALLY		=>'Italy',
		self::EBAY_SITE_BELGIUMD	=>'Belgium (Dutch)',		
		self::EBAY_SITE_NETERLANDS	=>'Netherlands',
		self::EBAY_SITE_SPAIN		=>'Spain',
		self::EBAY_SITE_SWITZERLAND	=>'Switzerland',
		self::EBAY_SITE_HK			=>'Hong Kong',
		self::EBAY_SITE_INDIA		=>'India',
		self::EBAY_SITE_IRELANS		=>'Ireland',
		self::EBAY_SITE_MALAYSIA	=>'Malaysia',	
		self::EBAY_SITE_CANADAF		=>'Canada',
		self::EBAY_SITE_PHILIPPINES	=>'Philippines',
		self::EBAY_SITE_POLAND		=>'Poland',
		self::EBAY_SITE_SINGAPORE	=>'Singapore',
	
	);
	
	/**
	 * @var array ebay站点代码
	 */
    public static $SiteCodeType = array(
        0 => 'US',
        2 => 'Canada',
        3 => 'UK',
        15 => 'Australia',
        16 => 'Austria',
        23 => 'Belgium_French',
        71 => 'France',
        77 => 'Germany',
        101 => 'Italy',
        123 => 'Belgium_Dutch',
        146 => 'Netherlands',
        186 => 'Spain',
        193 => 'Switzerland',
        201 => 'HongKong',
        203 => 'India',
        205 => 'Ireland',
        207 => 'Malaysia',
        210 => 'CanadaFrench',
        211 => 'Philippines',
        212 => 'Poland',
        215 => 'Russia',
        216 => 'Singapore'
    );
    
    /**
     * @desc Global ID Values
     * @var array
     */
    public static $GlobalIDValues = array(
        array(
            'EBAY-AT',
            'de-AT',
            'AT',
            'Austria',
            16
        ),
        array(
            'EBAY-AU',
            'en-AU',
            'AU',
            'Australia',
            15
        ),
        array(
            'EBAY-CH',
            'de-CH',
            'CH',
            'Switzerland',
            193
        ),
        array(
            'EBAY-DE',
            'en-DE',
            'DE',
            'Germany',
            77
        ),
        array(
            'EBAY-ENCA',
            'en-CA',
            'CA',
            'Canada (English)',
            2
        ),
        array(
            'EBAY-ES',
            'es-ES',
            'ES',
            'Spain',
            186
        ),
        array(
            'EBAY-FR',
            'fr-FR',
            'FR',
            'France',
            71
        ),
        array(
            'EBAY-FRBE',
            'fr-BE',
            'BE',
            'Belgium (French)',
            23
        ),
        array(
            'EBAY-FRCA',
            'fr-CA',
            'CA',
            'Canada (French)',
            210
        ),
        array(
            'EBAY-GB',
            'en-GB',
            'GB',
            'UK',
            3
        ),
        array(
            'EBAY-HK',
            'zh-Hant',
            'HK',
            'Hong Kong',
            201
        ),
        array(
            'EBAY-IE',
            'en-IE',
            'IE',
            'Ireland',
            205
        ),
        array(
            'EBAY-IN',
            'en-IN',
            'IN',
            'India',
            203
        ),
        array(
            'EBAY-IT',
            'it-IT',
            'IT',
            'Italy',
            101
        ),
        array(
            'EBAY-MOTOR',
            'en-US',
            'US',
            'Motors',
            100
        ),
        array(
            'EBAY-MY',
            'en-MY',
            'MY',
            'Malaysia',
            207
        ),
        array(
            'EBAY-NL',
            'nl-NL',
            'NL',
            'Netherlands',
            146
        ),
        array(
            'EBAY-NLBE',
            'nl-BE',
            'BE',
            'Belgium (Dutch)',
            123
        ),
        array(
            'EBAY-PH',
            'en-PH',
            'PH',
            'Philippines',
            211
        ),
        array(
            'EBAY-PL',
            'pl-PL',
            'PL',
            'Poland',
            212
        ),
        array(
            'EBAY-SG',
            'en-SG',
            'SG',
            'Singapore',
            216
        ),
        array(
            'EBAY-US',
            'en-US',
            'US',
            'United States',
            0
        )
    );
    
    public static $caseStatus = array(
        'CLOSED' =>'已关闭',
        'WAITINGBUYER'=>'等待买家处理',
       	'WAITINGSELLER'=>'等待卖家处理',
        'SENTENCE'=>'待裁决'
    );
    
}