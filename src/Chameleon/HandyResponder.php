<?php

namespace Phlex\Chameleon;

use App\ServiceManager;
use Phlex\Chameleon\SmartPageResponder;
use zpt\anno\Annotations;

abstract class HandyResponder extends SmartPageResponder {

	protected $classAnnotations;

	protected function customtagNamespace() { return ServiceManager::get('HandyResponderCustomTagNamespace'); }
	protected function distFolder() { return ServiceManager::get('HandyResponderDistFolder'); }

	protected function prepareParser() {
		$reflector = new \ReflectionClass($this);
		$this->classAnnotations = (new Annotations($reflector))->asArray();
	}

	protected function HEAD(){
		parent::HEAD();
		if (array_key_exists('css', $this->classAnnotations)) {
			if (!is_array($this->classAnnotations['css'])) $this->classAnnotations['css'] = [$this->classAnnotations['css']];
			foreach($this->classAnnotations['css'] as $css){
				echo '<link rel="stylesheet" type="text/css" href="'.$this->distFolder().$css.'.css" />'."\n";
			}
		}
	}

	protected function AFTER_BODY() {
		if (array_key_exists('js', $this->classAnnotations)) {
			if (!is_array($this->classAnnotations['js'])) $this->classAnnotations['js'] = [$this->classAnnotations['js']];
			foreach($this->classAnnotations['js'] as $js){
				echo '<script src="'.$this->distFolder().$js.'"></script>'."\n";
			}
		}
		if (array_key_exists('jsappmodule', $this->classAnnotations)) {
			if (!is_array($this->classAnnotations['jsappmodule'])) $this->classAnnotations['jsappmodule'] = [$this->classAnnotations['jsappmodule']];
			echo '<script src="'.$this->distFolder().'app.js" modules="'.join(' ', $this->classAnnotations['jsappmodule']).'"></script>'."\n";
		}
	}
}