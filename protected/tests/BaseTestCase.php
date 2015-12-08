<?php
define('UNIT_TEST',true);
/**
 * @desc 测试基类
 * @author Administrator
 * @date 2014-11-4
 */
class BaseTestCase
{
	/**
	 * @var CDbConnection
	 */
	protected $dbConnection = null;
	/**
	 * @var CDbCommand
	 */
	protected $dbCommand = null;
	public function __construct()
	{
		$this->dbConnection = Yii::app()->db;
		$this->dbCommand = $this->dbConnection->createCommand();
	}
}