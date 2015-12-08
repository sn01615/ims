<?php
/**
 * @desc curl处理类
 * @author ChenLuoyong
 * @date 2014-9-17
 */
class Curl
{
	/**
	 * @desc 存储Curl操作的句柄
	 * @var object
	 */
	protected $ch = null;
	/**
	 * @desc 存储回调句柄操作
	 * @var array
	 */
	protected $callback = null;
	/**
	 * @desc 初始化curl对象构造器
	 * @author ChenLuoyong
	 * @date 2014-9-17
	 */
	public function __construct()
	{
		$this->ch = curl_init();
	}

	/**
	 * @desc 设置回调函数
	 * @author ChenLuoyong
	 * @date 2014-9-17
	 * @param string $class 回调类
	 * @param string $method 类方法
	 * @return void
	 */
	public function setCallback($class, $method) {
		if (is_callable(array($class, $method))){
			$this->callback['class'] = $class;
			$this->callback['method'] = $method;
		}
	}
	
	/**
	 * @desc 执行curl请求
	 * @author ChenLuoyong
	 * @date 2014年9月17日
	 * @param string $method 请求方式:GET,POST
	 * @param string $url 请求URL地址
	 * @param mixed	$vars 提交参数
	 * @param int $expired 请求超时时间,单位:秒
	 * @return mixed
	 */
	private function doRequest($method, $url, $vars, $expired = null)
	{
		curl_setopt($this->ch, CURLOPT_URL, $url);
		curl_setopt($this->ch, CURLOPT_HEADER, 0);
		curl_setopt($this->ch, CURLOPT_USERAGENT, $_SERVER ['HTTP_USER_AGENT']);
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		if(isset($expired)){
			$expired = intval($expired);
			if ($expired > 0) {
				curl_setopt($this->ch, CURLOPT_TIMEOUT, $expired);
			}
		}
		curl_setopt($this->ch, CURLOPT_COOKIEJAR, 'cookie.txt');
		curl_setopt($this->ch, CURLOPT_COOKIEFILE, 'cookie.txt');
		if($method == 'POST') {
			curl_setopt($this->ch, CURLOPT_POST, 1);
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, $vars);
		}
		ob_start();
		$data = curl_exec($this->ch);
		ob_end_clean();
		if($data) {
			if($this->callback) {
				$callback = $this->callback;
				$this->callback = array();
				return call_user_func(array($callback['class'], $callback['method']), $data);
			}
			else {
				return $data;
			}
		}
		else {
			$this->setError(curl_error($this->ch));
			return false;
		}
	}
	
	/**
	 * @desc 执行get方式的curl请求
	 * @author ChenLuoyong
	 * @date 2014年9月17日
	 * @param string $url 请求地址
	 * @param int $expired 超时时间,单位:秒
	 * @return mixed
	 */
	public function get($url, $expired = null) {
		return $this->doRequest('GET', $url, 'NULL', $expired);
	}
	
	/**
	 * @desc 执行post方式的curl请求
	 * @author ChenLuoyong
	 * @date 2014年9月17日
	 * @param string $url 请求地址
	 * @param mixed $vars 提交变量
	 * @param int $expired 超时时间,单位:秒
	 * @return mixed
	 */
	public function post($url, $vars, $expired = null) {
		return $this->doRequest('POST', $url, $vars, $expired);
	}
	
	/**
	 * @desc 关闭curl请求
	 * @author ChenLuoyong
	 * @date 2014年9月17日
	 * @return void
	 */
	public function close() {
		curl_close($this->ch);
	}
}
