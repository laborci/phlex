<?php namespace Phlex\Form\FormRenderer;

use App\ServiceManager;

class CheckboxInput extends Input {

	/** @var [] */
	public $options;

	public function render(){
		echo '<span role="px-input-checkbox">';
		echo '<input type="hidden" name="'.$this->field->name.'" value="'.$this->field->value.'"> <input type="checkbox" '.($this->field->value ? 'checked' : '').'>';
		echo '</span>';
	}

}