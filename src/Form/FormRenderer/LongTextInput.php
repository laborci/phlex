<?php namespace Phlex\Form\FormRenderer;

class LongTextInput extends Input {

	public function render(){
		echo '<textarea role="px-input-lingtext" name="'.$this->field->name.'">'.$this->field->value.'</textarea>';
	}

}

