<?php namespace Phlex\Chameleon;


abstract class SmartPageResponder extends TRexPageResponder implements SmartPageComponentInterface {

	private $serverData = array();
	private $extensions = [];
	private $loadedExtensions = [];
	private $bodyClass = [];

	protected $lang = 'hu';
	protected $title = "Phlex:Chameleon";

	protected function addBodyClass(string $class){ $this->bodyClass[$class] = $class; }
	protected function removeBodyClass(string $class){ unset($this->bodyClass[$class]); }
	protected function getBodyClass(){return join(' ', $this->bodyClass); }

	protected function getServerData() { return $this->serverData; }
	protected function getServerDataJson() { return json_encode($this->serverData); }
	protected function addServerData($key, $value) { $this->serverData[$key] = $value; }

	public function addMeta($name, $content, $useProperty = false) { $this->headerExtensions[] = '<meta ' . $useProperty ? 'property' : 'name' . '="' . $name . '" content="' . $content . '" />'; }

	public function addJsInclude( $src) {
		if (!in_array($src, $this->loadedExtensions)) {
			$this->extensions[] = '<script src="' . $src . '"></script>';
			$this->loadedExtensions[] = $src;
		}
	}

	public function addCssInclude($src) {
		if (!in_array($src, $this->loadedExtensions)) {
			$this->extensions[] = '<link rel="stylesheet" type="text/css" href="' . $src . '" />';
			$this->loadedExtensions[] = $src;
		}
	}

	protected function writeExtensions() {
		foreach ($this->extensions as $extension) echo $extension . "\n";
		$this->headerExtensions = null;
	}

	protected function template() {
		?>
		@var serverDataJson = $this->getServerDataJson();
		@var bodyClass = $this->getBodyClass();

		<!doctype html>
		<html lang="{{.lang}}">
		<head>
			<?php $this->headTpl() ?>
			@php $this->writeExtensions();
			<script> var serverData = {{serverDataJson}}; </script>
		</head>
		<body class="{{bodyClass}}">
			<?php $this->bodyTpl() ?>
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
}