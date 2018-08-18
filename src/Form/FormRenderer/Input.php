<?php namespace Phlex\Form\FormRenderer;


use Phlex\Form\FormField;

abstract class Input{

	/** @var FormField  */
	public $field;
	public $label;
	public $render = true;
	public $properties = [];

	public function __construct($label) {
		$this->label = $label;
	}

	abstract function render();

	public function setProperty($property, $value){
		$this->properties[$property] = $value;
	}

	public function noRender(){
		$this->render = false;
		return $this;
	}

}
