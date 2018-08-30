<?php namespace Phlex\Chameleon;

abstract class SmartPageResponder extends TRexPageResponder implements SmartPageComponentInterface {

	private $serverData = [];
	private $extensions = [];
	private $loadedExtensions = [];

	protected $bodyClass = '';
	protected $language = 'hu';
	protected $title = '';

	protected function getServerData() { return $this->serverData; }
	protected function getServerDataScript() { return count($this->serverData) ? '<script> var serverData = ' . json_encode($this->serverData) . ';</script>' : ''; }
	protected function addServerData($key, $value) { $this->serverData[ $key ] = $value; }

	public function addMeta($name, $content, $useProperty = false) {
		$this->extensions[] = '<meta ' . $useProperty ? 'property' : 'name' . '="' . $name . '" content="' . $content . '" />';
	}

	public function addJsInclude($src) {
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
		$this->extensions = null;
	}

	protected function customtagNamespace() { return null; }

	protected function setCustomtagNamespace() {
		$ns = $this->customtagNamespace();
		if (!is_null($ns)) {
			echo '@ctns ' . str_replace('.', '\\', $ns) . "\n";
		}
	}

	protected function template() {
		$this->setCustomtagNamespace();
		$this->HTML();
	}

	protected function HTML() { ?>
		<!doctype html>
		<html lang="{{language}}">
		<head>
			@php echo $this->getServerDataScript();
			@php $this->writeExtensions();
			<?php $this->HEAD() ?>
		</head>
		<body class="{{bodyClass}}">
		<?php $this->BODY() ?>
		<?php $this->AFTER_BODY() ?>
		</body>
		</html>
	<?php }

	protected function HEAD() { ?>
		<title>{{title}}</title>
		<meta charset="utf-8">
	<?php }


	abstract protected function BODY();

	protected function AFTER_BODY(){}

}