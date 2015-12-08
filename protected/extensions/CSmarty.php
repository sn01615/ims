<?php
require_once (Yii::getPathOfAlias('application.extensions.smarty') . DIRECTORY_SEPARATOR . 'Smarty.class.php');
// define('SMARTY_VIEW_DIR', Yii::getPathOfAlias('application.views'));
define('SMARTY_VIEW_DIR',Yii::getPathOfAlias('webroot.public'));

/**
 * @desc user defined Smarty class
 * @author ChenLuoyong
 */
class CSmarty extends Smarty {
	const DIR_SEP = DIRECTORY_SEPARATOR;
	function __construct() {
		parent::__construct();
		$this->template_dir = SMARTY_VIEW_DIR . self::DIR_SEP . 'template';
		$this->compile_dir = SMARTY_VIEW_DIR . self::DIR_SEP . 'compile';
		$this->caching = 0;
		$this->cache_dir = SMARTY_VIEW_DIR . self::DIR_SEP . 'cache';
		$this->left_delimiter = '{';
		$this->right_delimiter = '}';
		$this->cache_lifetime = 0;
	}
	function init() {}
}