<?php namespace Phlex\Form;

use Phlex\Chameleon\HandyResponder;

class FormResponder extends HandyResponder {

	protected $formRenderer;

	protected function prepare() {
		$this->bodyClass = 'form';
	}

	protected function BODY() { ?>
		@php $this->formRenderer->render();
	<?php }
}