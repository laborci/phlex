<?php namespace Phlex\Form\FormRenderer;


use Phlex\Form\FormField;

abstract class Input{

	/** @var FormField  */
	public $field;
	public $label;
	public $render = true;

	public function __construct($label) {
		$this->label = $label;
	}

	abstract function render();

}
