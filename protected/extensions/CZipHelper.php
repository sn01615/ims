<?php
/**
 * @desc 生成压缩zip文件
 * @author heguangquan
 * @date 2014-12-12
 */
class CZipHelper 
{
	/**
	 * @var $_instance 对象
	 */
	private static $_instance;
	private static $objZip;
	/**
	 * @desc 初始化方法
	 * @author heguangquan
	 * @date 2014-12-12
	 */
	private function __construct(){
		self::$objZip = new ZipArchive ();
	}
	
	/**
     * @desc 用双冒号::操作符访问静态方法获取实例
     * @author heguangquan
     * @date 2014-11-29
     * @return CZipHelper
     */
    public static function getInstance()
    {
        if(!self::$_instance instanceof self){
        	self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * @desc 生成ZIP压缩文件
     * @author heguangquan
     * @date 2014-12-12
     * @param string $fileName 压缩的文件名
     * @param array $fileArray 需压缩的文件路径 
     * @return string 文件路径
     */
    public function createFileZip($fileName,$fileArray)
    {
    	$fileName = Yii::app()->params['excel'].$fileName.'.zip';
   	 	if (self::$objZip->open ($fileName, ZIPARCHIVE::CREATE ) !== TRUE) {
    		throw new  CException('无法打开文件，或者文件创建失败');
		}
		
		//把需压缩的文件添加到$fileName
    	foreach ( $fileArray as $file ) {
    		if(!empty($file)){
		   		self::$objZip->addFile ($file, basename ($file));
    		}
		}
		self::$objZip->close ();
		return $fileName;
    }
}
?>