<?php namespace Phlex\Sys;

class ServiceFactory{

	protected $name;
	protected $isShared = false;
	protected $isValue = false;
	protected $serviceFactory;
	protected $sharedService;
	protected $value = null;
	protected $arguments;

	public function __construct($name) {
		$this->name = $name;
	}

	public function service($service, ...$arguments){
		$this->serviceFactory = $service;
		$this->arguments = $arguments;
		return $this;
	}

	public function sharedService($service, ...$arguments){
		$this->isShared = true;
		return $this->service($service, ...$arguments);
	}

	public function value($value){
		$this->isValue = true;
		$this->value = $value;
		return $this;
	}

	public function get(){
		if($this->isValue){
			return $this->value;
		}elseif($this->isShared && !is_null($this->sharedService)) {
			return $this->sharedService;
		}else{
			if(is_callable($this->serviceFactory)){
				$function = $this->serviceFactory;
				$service = $function(...$this->arguments);
			}else{
				$class = $this->serviceFactory;

				$reflect = new \ReflectionClass($class);
				if($reflect->implementsInterface(InjectDependencies::class)){
					$constructor = $reflect->getConstructor();
					$arguments = [];
					if(!is_null($constructor)) {
						$parameters = $constructor->getParameters();
						foreach ($parameters as $parameter) {
							$arguments[] = ServiceManager::get(strval($parameter->getType()));
						}
					}
					$service = new $class(...$arguments);
				}else{
					$service = new $class(...$this->arguments);
				};
			}

			if($this->isShared){
				$this->sharedService = $service;
			}
			return $service;
		}
	}
}