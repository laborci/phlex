<?php namespace Phlex\Form\FormRenderer;

class SelectInput extends Input {

	/** @var [] */
	public $options;

	public function render(){
		echo '<select role="px-input-select" name="'.$this->field->name.'" value="'.$this->field->value.'">';
		foreach ($this->options as $value=>$label){
			echo '<option value="'.$value.'" '.($value == $this->field->value ? "selected" : "").'>'.$label.'</option>';
		}
		echo '</select>';
	}

	public function setOptions($options):SelectInput{
		$this->options = $options;
		return $this;
	}

}