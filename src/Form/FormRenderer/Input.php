<?php namespace Phlex\Form\FormRenderer;

use App\Site\Website\Form\FormField;

abstract class Input{

	/** @var FormField  */
	public $field;
	public $label;

	public function __construct($label) {
		$this->label = $label;
	}

	abstract function render();

}
