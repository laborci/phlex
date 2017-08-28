<?php namespace Phlex\Chameleon;


use App\Env;
use Phlex\Parser\TRex;
use App\ServiceManager;


trait TrexParser {

	final protected function respondTemplate($method = 'template') {
		$key = str_replace('\\', '_', get_class($this)) . '-' . $method;
		/** @var \Phlex\Sys\FileCache $templateCache */
		$templateCache = ServiceManager::get('cache.template');

		if (!$templateCache->exists($key) || $this->isDevAndDirty($key)) {
			//trigger_error('template_caching: '.$key);
			ob_start();
			$this->$method();
			$template = ob_get_clean();
			$output = TRex::parseString($template);
			$templateCache->set($key, $output);
		}
		ob_start();
		include $templateCache->file($key);
		return ob_get_clean();
	}

	final protected function isDevAndDirty($key) {

		if(!Env::get('dev-mode')) return false;

		$templateCache = ServiceManager::get('cache.template');

		$time = 0;
		$classes = class_parents($this);
		$classes[get_class($this)] = get_class($this);

		foreach ($classes as $class) {
			$reflector = new \ReflectionClass($class);
			$fn = $reflector->getFileName();
			if ($time < filemtime($fn)) {
				$time = filemtime($fn);
			}
		}

		return $time > $templateCache->getTime($key);

	}

}