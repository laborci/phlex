<?php namespace Phlex\Chameleon;


use App\Env;
use App\ServiceManager;
use Phlex\Parser\TRex\TRex;


trait TrexParser {

	final protected function respondTemplate($method = 'template') {
		$key = str_replace('\\', '_', get_class($this)) . '-' . $method;
		/** @var \Phlex\Sys\FileCache $templateCache */
		$templateCache = ServiceManager::get('cache.template');

		if (!$templateCache->exists($key) || $this->isDevAndDirty($key) || true) {
			ob_start();
			$this->prepareParser();
			$this->$method();
			$template = ob_get_clean();
			$ref = new \ReflectionClass($this);
			$template = '@ctns '.$ref->getNamespaceName()."\n".$template;
			$output = TRex::parse($template);
			$templateCache->set($key, $output);
		}

		ob_start();
		include $templateCache->file($key);
		$buffer = ob_get_clean();
		return $buffer;
	}

	protected function prepareParser(){}

	final protected function isDevAndDirty($key) {

		if(!Env::get('dev-mode')) return false;

		/** @var \Phlex\Sys\FileCache $templateCache */
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