<?php

/*
 *  Copyright (c) 2010-2012 Tinyboard Development Group
 */

if (realpath($_SERVER['SCRIPT_FILENAME']) == str_replace('\\', '/', __FILE__)) {
	// You cannot request this file directly.
	exit;
}

class Cache {
	private static $cache;
	public static function init() {
		global $config;
		
		switch ($config['cache']['enabled']) {
			case 'memcached':
				self::$cache = new Memcached();
				self::$cache->addServers($config['cache']['memcached']);
				break;
			case 'php':
				self::$cache = Array();
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
				$data = isset(self::$cache[$key]) ? self::$cache[$key] : false;
				break;
		}
		
		// debug
		if ($data && $config['debug']) {
			$debug['cached'][] = $key;
		}
		
		return $data;
	}
	public static function set($key, $value, $expires = false) {
		global $config;
		
		$key = $config['cache']['prefix'] . $key;
		
		if (!$expires)
			$expires = $config['cache']['timeout'];
		
		switch ($config['cache']['enabled']) {
			case 'memcached':
				if (!self::$cache)
					self::init();
				self::$cache->set($key, $value, $expires);
				break;
			case 'apc':
				apc_store($key, $value, $expires);
				break;
			case 'xcache':
				xcache_set($key, $value, $expires);
				break;
			case 'php':
				self::$cache[$key] = $value;
				break;
		}			
	}
	public static function delete($key) {
		global $config;
		
		$key = $config['cache']['prefix'] . $key;
		
		switch ($config['cache']['enabled']) {
			case 'memcached':
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
			case 'php':
				unset(self::$cache[$key]);
				break;
		}
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
				self::$cache[$key] = Array();
				break;
		}
		
		return false;
	}
}

