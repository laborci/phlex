<?php namespace Phlex\Sys;

abstract class Environment {

	/** @var  static */
	protected static $instance;
	protected $config = [];

	protected function __construct() {
		$root = getenv('ROOT');

		if (!$root) {
			throw new \Exception('ROOT env is not set');
		}

		$this->setPaths();

		$config = getenv('PXCONFIG');
		if (!$config) $config = ['config.php'];
		else $config = explode(',', $config);
		foreach ($config as $configFile) {
			$this->config = array_merge($this->config, include $root . '/config/' . $configFile);
		}
	}

	protected function initialize(){
		\App\ServiceManager::bind('cache.template')->sharedService(FileCache::class, $this->config['path_caches'].'templates', 'phtml');
		\App\ServiceManager::bind('cache.response')->sharedService(FileCache::class, $this->config['path_caches'].'responses', 'phtml');
	}

	protected function setPaths() {
		$this->config['path_root'] = getenv('ROOT') . '/';
		$this->config['path_public'] = getenv('ROOT') . '/public/';
		$this->config['path_var'] = $this->config['path_root'] . 'var/';
		$this->config['path_config'] = $this->config['path_root'] . 'config/';
		$this->config['path_caches'] = $this->config['path_var'] . 'caches/';
		$this->config['path_log'] =$this->config['path_var'] . 'log/';
		$this->config['path_tmp'] = $this->config['path_var'] . 'tmp/';
		$this->config['path_files'] = $this->config['path_public'] . 'files/';
		$this->config['path_thumbnails'] = $this->config['path_public'] . 'thumbnails/';
		$this->config['path_sessions'] = $this->config['path_var'] . 'sessions/';
		$this->config['url_thumbnails'] = '/thumbnails/';
		$this->config['url_files'] = '/files/';
	}

	// Behind static facade
	public static function load(){
		static::$instance = new static();
		static::$instance->initialize();
	}

	public function loadConfig($file){

	}

	/**
	 * @param string|null $name
	 *
	 * @return string|array
	 * @throws \Exception
	 */
	public static function get(string $name = null){
		if(is_null($name)){
			return static::$instance->config;
		}
		if(!array_key_exists($name, static::$instance->config)) throw new \Exception("[$name] was not found in environment configuration");
		return static::$instance->config[$name];
	}

	public static function set($key, $value){
		static::$instance->config[$key] = $value;
	}

}
