<?php namespace Phlex\Sys;

/**
 * Class Env
 *
 * @package Phlex\Sys
 *
 * @property-read bool   $devmode
 * @property-read string $path_root
 * @property-read string $path_var
 * @property-read string $path_config
 * @property-read string $path_caches
 * @property-read string $path_log
 * @property-read string $path_files
 * @property-read string $path_thumbnails
 * @property-read string $path_tmp
 * @property-read string $url_thumbnails
 * @property-read string $url_files
 * @property-read string $thumbnailCrc32Salt
 * @property-read string $passwordCrc32Salt
 *
 */

abstract class Environment {

	protected static $instance;
	public static function instance() { return is_null(static::$instance) ? static::$instance = new static() : static::$instance; }

	protected $config = [];

	public function __get($name) {
		if(array_key_exists($name, $this->config)) return $this->config[$name];
		return null;
	}

	protected function initialize(){
		$this->bindService('cache.template')->sharedService(FileCache::class, $this->path_caches.'templates', 'phtml');
		$this->bindService('cache.response')->sharedService(FileCache::class, $this->path_caches.'responses', 'phtml');
	}

	protected function setPaths() {

		$this->config['path_root'] = getenv('ROOT') . '/';

		$this->config['path_var'] = $this->path_root . 'var/';
		$this->config['path_config'] = $this->path_root . 'config/';
		$this->config['path_caches'] = $this->path_var . 'caches/';
		$this->config['path_log'] = $this->path_var . 'log/';
		$this->config['path_tmp'] = $this->path_var . 'tmp/';
		$this->config['path_files'] = $this->path_var . 'files/';
		$this->config['path_thumbnails'] = $this->path_var . 'thumbnails/';
		$this->config['path_sessions'] = $this->path_var . 'sessions/';

		$this->config['url_thumbnails'] = '/thumbnails/';
		$this->config['url_files'] = '/files/';
	}


	protected function __construct() {

		$root = getenv('ROOT');
		$cfg = getenv('PXCONFIG');

		if(!$root){
			throw new \Exception('ROOT env is not set');
		}

		$this->setPaths();
		if(!$cfg)$cfg = 'config.php';
		$this->config = array_merge($this->config, include $root.'/config/'.$cfg);
		$this->initialize();
	}

	/** @var ServiceFactory[] */
	private $services = [];
	private $servicesInContext = [];

	public function bindService($name, ...$for){
		$service = new ServiceFactory($name);
		if(count($for)){
			foreach($for as $ctx){
				if(!is_array($this->servicesInContext[$ctx])){
					$this->servicesInContext[$ctx] = [];
				}
				$this->servicesInContext[$ctx][$name] = $service;
			}
		}else{
			$this->services[$name] = $service;
		}
		return $service;
	}

	public function getService($name){

		if(!empty($this->servicesInContext)) {
			$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2);
			$caller = isset($trace[1]) ? $trace[1]['class'] : '';

			if (array_key_exists($caller, $this->servicesInContext) && array_key_exists($name, $this->servicesInContext[$caller])) {
				return $this->servicesInContext[$caller][$name]->get();
			}
		}

		if(array_key_exists($name, $this->services)){
			return $this->services[$name]->get();
		}

		if(class_exists($name)){
			// register the service on the fly
			$reflect = new \ReflectionClass($name);
			if($reflect->implementsInterface(SharedService::class)){
				$this->bindService($name)->sharedService($name);
			}else{
				$this->bindService($name)->service($name);
			};

			return $this->getService($name);
		}
		//TODO: else throw some exception
	}

	public static function get($name, ...$arguments){
		return static::instance()->getService($name, ...$arguments);
	}
	public static function bind($name, ...$for){
		return static::instance()->bindService($name, ...$for);
	}

}