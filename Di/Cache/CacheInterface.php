<?php
namespace Di\Cache;

interface CacheInterface
{
	public function get($key);

	public function add($key, $value, $min);

	public function forget($key);

	public function has($key);
}