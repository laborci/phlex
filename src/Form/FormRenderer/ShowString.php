<?php namespace Phlex\Form\FormRenderer;

class ShowString extends Input {

	public function render(){
		echo '<span role="px-input-show-string">'.$this->field->value.'</span>';
	}

}

