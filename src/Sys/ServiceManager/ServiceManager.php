<?php namespace Phlex\Sys\ServiceManager;

abstract class ServiceManager{

	private $services = [];
	private $servicesInContext = [];
	protected static $instance;

	protected static function instance() { return is_null(static::$instance) ? static::$instance = new static() : static::$instance; }
	protected function __construct() {}

	public static function bind($name, ...$for):ServiceFactory{ return static::instance()->bindService($name, ...$for); }
	public static function get($name){ return static::instance()->getService($name); }


	protected function bindService($name, ...$for):ServiceFactory{
		$service = new ServiceFactory($name);

		if(count($for)){
			if(!array_key_exists($name, $this->servicesInContext)) $this->servicesInContext[$name] = [];
			foreach($for as $ctx){
				$this->servicesInContext[$name][$ctx] = $service;
			}
		}else{
			$this->services[$name] = $service;
		}
		return $service;
	}


	public function getService($name){

		if(!empty($this->servicesInContext) && array_key_exists($name, $this->servicesInContext)) {
			$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,3);
			$caller = isset($trace[2]) ? $trace[2]['class'] : '';
			$services = $this->servicesInContext[$name];

			if (array_key_exists($caller, $services)) {
				return $this->servicesInContext[$name][$caller]->get();
			}
		}


		if(array_key_exists($name, $this->services)){
			return $this->services[$name]->get();
		}

		if(class_exists($name)){
			$reflect = new \ReflectionClass($name);
			if($reflect->implementsInterface(SharedService::class)){
				$this->bind($name)->sharedService($name);
			}else{
				$this->bind($name)->service($name);
			};
			return $this->get($name);
		}

		trigger_error('SERVICE NOT FOUND '.$name);
		//TODO: else throw some exception
	}
}