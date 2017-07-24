<?php namespace Phlex\Sys;

class Bag {

	protected $pockets = array();
	protected static $bags = array();
	protected $name;

	private function __construct($name) {
		$this->name = $name;
	}

	/**
	 * @param $bag
	 * @return static
	 */
	public static function getBag($bag){
		if(!array_key_exists($bag, static::$bags)){
			static::$bags[$bag] = new static($bag);
		}
		return static::$bags[$bag];
	}

	public function has($pocket){
		return array_key_exists($pocket, $this->pockets);
	}

	public function get($pocket){
		return $this->pockets[$pocket];
	}

	public function set($pocket, $pocketContent){
		$this->pockets[$pocket] = $pocketContent;
		return $pocketContent;
	}

	public function del($pocket){
		unset($this->pockets[$pocket]);
	}
}