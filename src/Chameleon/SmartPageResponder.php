<?php namespace Phlex\Chameleon;


abstract class SmartPageResponder extends TRexPageResponder implements SmartPageComponentInterface {

	private $serverData = array();
	private $headerExtensions = [];
	private $dependecyExtensions = [];
	private $loadedExtensions = [];

	protected $lang = 'hu';
	protected $title = "Phlex:Chameleon";

	protected function getServerData() { return $this->serverData; }

	protected function addServerData($key, $value) { $this->serverData[$key] = $value; }

	public function addMeta($name, $content, $useProperty = false) { $this->headerExtensions[] = '<meta ' . $useProperty ? 'property' : 'name' . '="' . $name . '" content="' . $content . '" />'; }

	public function addJsInclude($src, $code = false) {
		if (!in_array($src, $this->loadedExtensions)) {
			$this->dependecyExtensions[] = $code ? $src : '<script src="' . $src . '"></script>';
			$this->loadedExtensions[] = $src;
		}
	}

	public function addCssInclude($src) {
		if (!in_array($src, $this->loadedExtensions)) {
			if (!is_null($this->headerExtensions)) {
				$this->headerExtensions['js-' . $src] = '<link rel="stylesheet" type="text/css" href="' . $src . '" />';
			} else {
				$this->dependecyExtensions['js-' . $src] = '<link rel="stylesheet" type="text/css" href="' . $src . '" />';
			}
			$this->loadedExtensions[] = $src;
		}
	}

	protected function writeDependencyExtensions() {
		foreach ($this->dependecyExtensions as $dependecyExtension) {
			echo $dependecyExtension . "\n";
		}
		$this->dependecyExtensions = null;
	}

	protected function writeHeaderExtensions() {
		foreach ($this->headerExtensions as $headerExtension) {
			echo $headerExtension . "\n";
		}
		$this->headerExtensions = null;
	}

	protected function template() {
		?>
		<!doctype html>
		<html lang="{{.lang}}">
		<head>
			<?php $this->headTpl() ?>
			@php $this->writeHeaderExtensions();
		</head>
		<body>
		<?php $this->bodyTpl() ?>
		@var serverDataJson = json_encode($this->getServerData());
		<script> var serverData = {{serverDataJson}}; </script>
		@php $this->writeDependencyExtensions();
		@php $this->runScript();
		</body>
		</html>
	<?php }

	protected function headTpl() {
		?>
		<title>{{.title}}</title>
		<meta charset="utf-8">
		<?php
	}

	abstract protected function bodyTpl();

	protected function runScript(){}

}