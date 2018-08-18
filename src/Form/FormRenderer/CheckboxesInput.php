<?php namespace Phlex\Form\FormRenderer;

use App\ServiceManager;

class CheckboxesInput extends Input {

	/** @var [] */
	public $options;

	public function render(){
		echo '<div role="px-input-checkboxes">';
		foreach ($this->options as $value=>$label){
			ServiceManager::getLogger()->info($this->field->value);
			echo '<input type="checkbox" name="'.$this->field->name.'" value="'.$value.'" '.(in_array($value, $this->field->value) ? "checked" : "").'>'.$label.'<br>';
		}
		//echo '</div>';
	}

	public function setOptions($options):CheckboxesInput{
		$this->options = $options;
		return $this;
	}

}