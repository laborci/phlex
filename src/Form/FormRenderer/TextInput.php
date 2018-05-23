<?php namespace Phlex\Form\FormRenderer;

class TextInput extends Input {

	public function render(){
		echo '<input role="px-input-text" type="text" name="'.$this->field->name.'" value="'.$this->field->value.'">';
	}

}

