<?php

/*
 *  Copyright (c) 2010-2013 Tinyboard Development Group
 */

defined('TINYBOARD') or exit;

class Cache {
	private static $cache;
	public static function init() {
		global $config;
		
		switch ($config['cache']['enabled']) {
			case 'memcached':
				self::$cache = new Memcached();
				self::$cache->addServers($config['cache']['memcached']);
				break;
			case 'redis':
				self::$cache = new Redis();
				self::$cache->connect($config['cache']['redis'][0], $config['cache']['redis'][1]);
				if ($config['cache']['redis'][2]) {
					self::$cache->auth($config['cache']['redis'][2]);
				}
				self::$cache->select($config['cache']['redis'][3]) or die('cache select failure');
				break;
			case 'php':
				self::$cache = [];
				break;
		}
	}
	public static function get($key) {
		global $config, $debug;
		
		$key = $config['cache']['prefix'] . $key;
		
		$data = false;
		switch ($config['cache']['enabled']) {
			case 'memcached':
				if (!self::$cache)
					self::init();
				$data = self::$cache->get($key);
				break;
			case 'apc':
				$data = apc_fetch($key);
				break;
			case 'xcache':
				$data = xcache_get($key);
				break;
			case 'php':
				$data = self::$cache[$key] ?? false;
				break;
			case 'fs':
				$key = str_replace('/', '::', $key);
				$key = str_replace("\0", '', $key);
				if (!file_exists('tmp/cache/'.$key)) {
					$data = false;
				}
				else {
					$data = file_get_contents('tmp/cache/'.$key);
					$data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
				}
				break;
			case 'redis':
				if (!self::$cache)
					self::init();
				$data = json_decode((string) self::$cache->get($key), true, 512, JSON_THROW_ON_ERROR);
				break;
		}
		
		if ($config['debug'])
			$debug['cached'][] = $key . ($data === false ? ' (miss)' : ' (hit)');
		
		return $data;
	}
	public static function set($key, $value, $expires = false) {
		global $config, $debug;
		
		$key = $config['cache']['prefix'] . $key;
		
		if (!$expires)
			$expires = $config['cache']['timeout'];
		
		switch ($config['cache']['enabled']) {
			case 'memcached':
				if (!self::$cache)
					self::init();
				self::$cache->set($key, $value, $expires);
				break;
			case 'redis':
				if (!self::$cache)
					self::init();
				self::$cache->setex($key, $expires, json_encode($value, JSON_THROW_ON_ERROR));
				break;
			case 'apc':
				apc_store($key, $value, $expires);
				break;
			case 'xcache':
				xcache_set($key, $value, $expires);
				break;
			case 'fs':
				$key = str_replace('/', '::', $key);
				$key = str_replace("\0", '', $key);
				file_put_contents('tmp/cache/'.$key, json_encode($value, JSON_THROW_ON_ERROR));
				break;
			case 'php':
				self::$cache[$key] = $value;
				break;
		}
		
		if ($config['debug'])
			$debug['cached'][] = $key . ' (set)';
	}
	public static function delete($key) {
		global $config, $debug;
		
		$key = $config['cache']['prefix'] . $key;
		
		switch ($config['cache']['enabled']) {
			case 'memcached':
			case 'redis':
				if (!self::$cache)
					self::init();
				self::$cache->delete($key);
				break;
			case 'apc':
				apc_delete($key);
				break;
			case 'xcache':
				xcache_unset($key);
				break;
			case 'fs':
				$key = str_replace('/', '::', $key);
				$key = str_replace("\0", '', $key);
				@unlink('tmp/cache/'.$key);
				break;
			case 'php':
				unset(self::$cache[$key]);
				break;
		}
		
		if ($config['debug'])
			$debug['cached'][] = $key . ' (deleted)';
	}
	public static function flush() {
		global $config;
		
		switch ($config['cache']['enabled']) {
			case 'memcached':
				if (!self::$cache)
					self::init();
				return self::$cache->flush();
			case 'apc':
				return apc_clear_cache('user');
			case 'php':
				self::$cache = [];
				break;
			case 'fs':
				$files = glob('tmp/cache/*');
				foreach ($files as $file) {
					unlink($file);
				}
				break;
			case 'redis':
				if (!self::$cache)
					self::init();
				return self::$cache->flushDB();
		}
		
		return false;
	}
}

