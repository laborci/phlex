<?php

namespace Phlex\Database;

use Phlex\Sys\ServiceManager\ServiceManager;
use Phlex\Sys\ServiceManager\SharedService;

abstract class Cache{

	protected $runtimeCache = [];
	protected $prefix = '';
	/** @var \Phlex\Database\Access */
	protected $access;
	protected $table;
	protected $lifespan;

	protected function __construct(Access $access, $table, $prefix='', $lifespan = null) {
		$this->prefix = $prefix;
		$this->access = $access;
		$this->table = $table;
		$this->lifespan = $lifespan;

	}

	public function get($key) {
		if (!array_key_exists($key, $this->runtimeCache)) {
			$data = $this->access->getRow("SELECT value, `timestamp` FROM ".$this->table." WHERE `key`=$1", $key);
			if(!$data) return null;
			if($this->lifespan && time() > $data['timestamp'] + $this->lifespan){
				$this->del($key);
				return null;
			}
			$this->runtimeCache[$key] = unserialize($data['value']);
		}
		return $this->runtimeCache[$key];
	}

	public function set($key, $value) {
		if ($value === null) {
			$this->del($key);
		} else {
			$this->runtimeCache[$this->prefix.$key] = $value;
			$value = serialize($value);
			$this->access->query("INSERT INTO " . $this->table . " (`key`, `value`) VALUES ($1, $2) ON DUPLICATE KEY UPDATE `value`=$2", $key, $value);
		}
	}

	public function del($key) {
		$key = $this->prefix.$key;
		$this->access->delete($this->table, Filter::where("`key` = $1", $key));
	}

}

//class MyCache extends Cache implements SharedService {
//	public function __construct() {
//		parent::__construct(ServiceManager::get('database'), 'cache', 'someService', 3000);
//	}
//}
//
//
//class SomeService{
//
//	/** @var \Phlex\Database\Cache $cache */
//	protected $cache;
//
//	public function __construct() {
//		$this->cache = new MyCache();
//	}
//
//
//	public function getData($userId){
//		$data = $this->cache->get($userId);
//		if(is_null($data)){
//			$data = 'collect data';
//			$this->cache->set($userId, $data);
//		}
//		return $data;
//	}
//
//
//}