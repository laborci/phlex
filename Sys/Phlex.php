<?php namespace Phlex\Sys;

use App\Env;
use Symfony\Component\HttpFoundation\Request;


class Phlex{

	#region singleton
	protected static $instance;
	final public static function instance() {
		return isset(static::$instance)
			? static::$instance
			: static::$instance = new static;
	}
	final private function __construct() {}
	final private function __wakeup() {}
	final private function __clone() {}
	#endregion

	public $request = null;

	public static function boot(){
		$phlex = static::instance();
		$phlex->request = Request::createFromGlobals();
		if(Env::$dev) $phlex->bootDev();
	}

	protected function bootDev(){
		Dbg::instance();
		
		$templates = glob(Env::$px_path_templates.'*.phtml');
		if(is_array($templates) && $templates) foreach($templates as $template) unlink($template);
	}

}