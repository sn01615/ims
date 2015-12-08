<?php
/**
 * @desc EXCEL表格操作类
 * @author heguangquan
 * @date 2014-11-28
 */
class CExcelHelper
{
	/**
	 * @var $_instance 对象
	 */
	private static $_instance;
	private static $objExcel;
	
	private function __construct(){
		self::$objExcel = new PHPExcel();
	}
	
	/**
     * @desc 用双冒号::操作符访问静态方法获取实例
     * @author heguangquan
     * @date 2014-11-29
     * @return CExcelHelper
     */
    public static function getInstance()
    {
        if(!self::$_instance instanceof self){
        	self::$_instance = new self();
        }
        return self::$_instance;
    }
    
	/**
	 * @desc 生成excel表
	 * @author heguangquan
	 * @date 2014-11-29
	 * @param array $headArray 设置标题栏
	 * @param array $conArray 订单内容
	 * @param string $fileName excel表格文件名
	 * @param string $tableName excel表格的sheet名
	 */
	public function createExcelTable($headArray,$conArray,$fileName,$tableName,$isZip = false)
	{
		if(empty($headArray) || empty($conArray) || empty($fileName) || empty($tableName)){
			return false;
		}
		self::$objExcel = new PHPExcel();
		//设置文件属性
		self::$objExcel->getProperties()->setCreator("Maarten Balliauw")
							 ->setLastModifiedBy("Maarten Balliauw")
							 ->setTitle("Office 2007 XLSX Test Document")
							 ->setSubject("Office 2007 XLSX Test Document")
							 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
							 ->setKeywords("office 2007 openxml php")
							 ->setCategory("Test result file");
		//设置打开文档默认为第一张表
        self::$objExcel->setActiveSheetIndex(0);
        //生成头部
		$this->createExcelHeader($headArray);
		//写入excel表格内容
		$this->createExcelContent($conArray);
     	// 给当前活动的表设置名称
		self::$objExcel->getActiveSheet()->setTitle($tableName);
       
		if($isZip){
			//把生成的多个Excel文件压缩成zip
			$fileName = $fileName.date("YmdHis");
			$objWriter = PHPExcel_IOFactory::createWriter(self::$objExcel, 'Excel5');
			$objWriter->save(Yii::app()->params['excel'].$fileName.'.xls');
			return Yii::app()->params['excel'].$fileName.'.xls';
		}else{
			//下载单个Excel
			 // Redirect output to a client’s web browser (Excel2007)
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="'.$fileName.'.xls"');
			header('Cache-Control: max-age=0');
			// If you're serving to IE 9, then the following may be needed
			header('Cache-Control: max-age=1');
			
			// If you're serving to IE over SSL, then the following may be needed
			header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
			header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
			header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
			header ('Pragma: public'); // HTTP/1.0
			
			$objWriter = PHPExcel_IOFactory::createWriter(self::$objExcel, 'Excel5');
			$objWriter->save('php://output');
		}
	}
	
	/**
	 * @desc 获取excel表格内容
	 * @author heguangquan
	 * @date 2014-12-04
	 * @param string $fileName	excel文件名
	 * @return array 文件数据
	 */
	public function excelToArray($fileName)
	{
		if(empty($fileName)){
			return false;
		}
		/*创建对象,针对Excel2003*/
		$objExcel = PHPExcel_IOFactory::createReader('Excel5');
		$objExcel->setReadDataOnly(true);
		/*加载对象路径*/
		$objPHPExcel=$objExcel->load($fileName);
		/*获取工作表*/
		$objWorksheet=$objPHPExcel->getActiveSheet();
		/*得到总行数*/
		$highestRow=$objWorksheet->getHighestRow();
		/*得到总列数*/
		$highestColumn=$objWorksheet->getHighestColumn();
		$highestColumnIndex=PHPExcel_Cell::columnIndexFromString($highestColumn);
		$excelData=array();
		for($row=2;$row<=$highestRow;++$row){
			for($col=0;$col<=$highestColumnIndex;++$col){
				$excelData[$row][]=$objWorksheet->getCellByColumnAndRow($col,$row)->getValue();
	        }
		}
		return $excelData;
	}
	
	/**
	 * @desc 生成excel标题栏
	 * @author heguangquan
	 * @date 2014-11-29
	 * @param $paramArray
	 */
	public function createExcelHeader($paramArray = array())
	{
		if(empty($paramArray)){
			return false;
		}
		//设置头部
		$strExcel = self::$objExcel->setActiveSheetIndex(0);
		$num = 1;
		foreach($paramArray as $key => $param)
		{
			$fieldName = $this->createCoords($key);
			$strExcel = $strExcel->setCellValue($fieldName.$num, $param);
		}
        return $strExcel;
	}
	
	/**
	 * @desc excel写入内容
	 * @author heguangquan
	 * @date 2014-11-29
	 * @param $contentArray 订单数据
	 */
	public function createExcelContent($contentArray = array())
	{
		if(empty($contentArray)){
			return false;
		}
		 //添加数据
		$strExcel = self::$objExcel->setActiveSheetIndex(0);
		
		//处理单条记录的
		$keyV = 0;
		$single=2;
		foreach($contentArray as $key => $content)
		{
			$num = $key + 2;
			$k = 0;
			if(is_array($content)){
				foreach($content as $con){
					$strExcel = $strExcel->setCellValue($this->createCoords($k).$num,trim($con));
					$k++;
				}
			}else{
				$strExcel = $strExcel->setCellValue($this->createCoords($keyV).$single,trim($content));
				$keyV++;
			}
		}
		return $strExcel;
	}
	
	/**
	 * @desc 生成excel表格坐标
	 * @author heguangquan
	 * @date 2014-11-29
	 * @param $num
	 * @return $fieldName 单元格坐标
	 */
	public function createCoords($num)
	{
		$fieldArray = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
		if($num<26){
			$fieldName = $fieldArray[$num];
		}else{
			$re = intval($num / 26);
			$remaind = $num % 26;
			$fieldName = $fieldArray[$re-1].$fieldArray[$remaind];
		}
		return $fieldName;
	}
    
	/**
	 * @desc 把内容填充到指定的模板
	 * @author heguangquan
	 * @date 2015-01-19
	 * @param array $contentArr 数据内容
	 * @param string $fileName 文件名称
	 * @param string $filePath 模板文件路径
	 * @isBoool bool true:速卖通(列有下拉菜单);false:其他的平台
	 */
	public function fillContent($contentArr,$fileName,$filePath,$isBool = false)
	{
		$objReader = PHPExcel_IOFactory::createReader('Excel5');
		
		
		$objPHPExcel = $objReader->load($filePath);
		$strExcel = $objPHPExcel->getActiveSheet();
		$fileName = $fileName.date("YmdHis");
		//判断是否下拉框
		if($isBool){
			$count = count($contentArr)+100;
			for($i=2;$i<$count;++$i)
			{
				$objValidation = $strExcel->getCell("B".$i)->getDataValidation(); //这一句为要设置数据有效性的单元格
				$objValidation -> setType(PHPExcel_Cell_DataValidation::TYPE_LIST)  
	           -> setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION)  
	           -> setAllowBlank(true)  
	           -> setShowInputMessage(true)  
	           -> setShowErrorMessage(true)  
	           -> setShowDropDown(true)
	           -> setFormula1('"Part Shipment,Full Shipment"');
	           
	           $objValidation = $strExcel->getCell("C".$i)->getDataValidation(); //这一句为要设置数据有效性的单元格
				$objValidation -> setType(PHPExcel_Cell_DataValidation::TYPE_LIST)  
	           -> setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION)  
	           -> setAllowBlank(true)  
	           -> setShowInputMessage(true)  
	           -> setShowErrorMessage(true)  
	           -> setShowDropDown(true)
	           -> setFormula1('"EMS,ePacket,DHL Global Mail,DHL,UPS Express Saver,UPS Expedited,FedEx,TNT,SF Express,China Post Air Mail,China Post Air Parcel,Hongkong Post Air Mail,Hongkong Post Air Parcel"');
			}
		}
		$strExcel->fromArray($contentArr, NULL, 'A2');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        
        $objWriter->save(Yii::app()->params['excel'].$fileName.'.xls');
        unset($objWriter);
        return Yii::app()->params['excel'].$fileName.'.xls';
	}
}