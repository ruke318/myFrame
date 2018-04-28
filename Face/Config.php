<?php
namespace Face;

class Config
{
	private static $config;
	private static $self;
	private function __construct()
	{
		self::$config = require_once ROOT.'\\config.php';
	}

	// 初始化Cinfig,保证只有一个Config
	private static function _init()
	{
		if (!self::$self) {
			self::$self = new self;
		}
	}

	/**
	 * @todo 获取配置,层级之间可以使用`.`符号分割 eg: redis.host === ['redis']['host']
	 * @param $key [string] 必须, 键值
	 * @return [arr || string]
	 */
	public static function get($key)
	{
		if (empty($key)) return error('请输入参数');
		self::_init();
		return self::getValue($key);
	}

	/**
	 * @todo  暂时设置配置项的值,层级之间可以使用`.`符号分割 eg: redis.host === ['redis']['host']
	 * @param [string] $key 项,必须
	 * @param [mix] $value 值,必须
	 */
	public static function set($key, $value)
	{
		if(!isset($key) || !isset($value)) return error('必须两个参数');
		self::_init();
		self::setValue($key, $value);
	}

	private static function getValue ($key) {
		$arr = explode('.', $key);
		$ret = self::$config;
		foreach($arr as $a) {
			if (!array_key_exists($a, $ret)) { 
				return error("config['{$key}']".'不存在');
			}
			$ret = $ret[$a];
		}
		return $ret;
	}

	private static function setValue ($key, $value) {
		$arr = explode('.', $key);
		$ret = &self::$config;
		foreach($arr as $a) {
			if (!array_key_exists($a, $ret)) { 
				return error("config['{$key}']".'不存在');
			}
			$ret = &$ret[$a];
		}
		$ret = $value;
	}
}