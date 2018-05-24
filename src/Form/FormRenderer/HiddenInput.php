<?php namespace Phlex\Form\FormRenderer;

class HiddenInput extends Input {

	public $render = false;

	public function render(){
		echo '<input role="px-input-hidden" type="hidden" name="'.$this->field->name.'" value="'.$this->field->value.'">';
	}

}

