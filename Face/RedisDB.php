<?php
namespace Face;

class RedisDB
{
	private static $redisInstance;

	private function __construct()
	{

	}

	/**
	 * @host  [string] ip或者主机名
	 * @port  [int] 端口
	 * @pwd  [string] 密码
	 * @return [object] 返回redis实例
	 * @todo [description] 获取redis实例,默认使用config配置
	 */
	public static function start($host = null, $port = null, $pwd = null)
	{
		if (!self::$redisInstance) {
			$redis = new \Redis();
			$status = $redis->connect($host ?: Config::get('redis.host'), $port ?: Config::get('redis.port'));
			if (!$status) {
				return ['code'=>-1, 'msg'=>'redis connect failure'];
			}
			$pwd = isset($pwd) ?: Config::get('redis.pwd');
			if (!is_null($pwd)) {
				$status = $redis->auth($pwd);
			}
			if (!$status) {
				return ['code'=>-1, 'msg'=>'redis connect failure'];
			}
			self::$redisInstance = $redis;
		}
		return self::$redisInstance;
	}
}