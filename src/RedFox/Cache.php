<?php
/**
 * Created by PhpStorm.
 * User: elvis
 * Date: 2017. 02. 09.
 * Time: 10:03
 */

namespace Phlex\RedFox;


class Cache {
	private $cache = [];

	public function add(Entity $object){
		$id = $object->id;
		$this->cache[$id] = $object;
	}

	public function get($id){
		if(array_key_exists($id, $this->cache)){
			return $this->cache[$id];
		}
		return null;
	}

	public function delete($id){
		unset($this->cache[$id]);
	}
}