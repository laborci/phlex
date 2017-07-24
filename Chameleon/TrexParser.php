<?php namespace Phlex\Chameleon;


use Phlex\Parser\TRex;
use App\Env;


trait TrexParser {

	final protected function respondTemplate($method = 'template') {

		$key = str_replace('\\', '_', get_class($this)) . '-' . $method;
		$templateCache = Env::get('cache.template');

		if (!$templateCache->exists($key) || $this->isDevAndDirty($key)) {
			//trigger_error('template_caching: '.$key);
			ob_start();
			$this->$method();
			$template = ob_get_clean();
			$output = TRex::parseString($template);
			$templateCache->set($key, $output);
		}

		include $templateCache->file($key);
	}

	final protected function isDevAndDirty($key) {

		if(!Env::instance()->devmode) return false;

		$templateCache = Env::get('cache.template');

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