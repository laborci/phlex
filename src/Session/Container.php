<?php namespace Phlex\Session;

use App\ServiceManager;
use Phlex\Sys\ServiceManager\SharedService;

abstract class Container implements SharedService {

	private $fields;
	private $namespace;

	function __construct() {
		$this->fields = $this->getFields();
		$this->namespace = $this->getNamespace();
		if(!array_key_exists($this->namespace, $_SESSION)) $this->forget();
		$this->load();
		register_shutdown_function([$this, 'flush']);
	}

	private function load(){
		foreach($this->fields as $field){
			$this->$field = $_SESSION[$this->namespace][$field];
		}
	}

	public function forget(){
		$_SESSION[$this->namespace] = [];
		foreach($this->fields as $field){
			$_SESSION[$this->namespace][$field] = $this->$field = null;
		}
	}

	public function flush(){
		foreach($this->fields as $field){
			$_SESSION[$this->namespace][$field] = $this->$field;
		}
	}

	private function getFields(){
		$fields = [];
		$properties = (new \ReflectionClass($this))->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);
		foreach($properties as $property){
			$fields[] = $property->name;
		}
		return $fields;
	}

	protected function getNamespace(){ return static::class; }

}