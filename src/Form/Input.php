<?php namespace Phlex\Form;

class Input {

	protected $conditions = [];
	protected $importedFields = [];
	protected $importFields = [];
	protected $properties = [];
	public $label;
	public $hidden=false;

	/** @var Form */
	protected $form;

	/** @var FormField */
	protected $field;

	public function __construct($label, $type, FormField $field) {
		$this->label = $label;
		$this->type = $type;
		$this->field = $field;
		$this->form = $field->form;
	}

	public function hidden(){
		$this->hidden = true;
	}

	public function setProperty($property, $value){
		$this->properties[$property] = $value;
		return $this;
	}

	public function importField($field, $as = null){
		if(is_null($as)) $as = $field;
		$this->importFields[$field] = $as;
		return $this;
	}

	public function addCondition(callable $condition){
		$this->conditions[] = $condition;
		return $this;
	}

	public function testConditions($form){
		foreach ($this->conditions as $condition){
			if($condition($form) === false) return false;
		}
		return true;
	}

	public function render(){
		foreach ($this->importFields as $field=>$as) {
			$this->properties[$as] = $this->form->fields[$field]->value;
		}
		$data = [
			'value' => $this->field->value,
			'properties' => $this->properties
		];
		echo '<px-input type="'.$this->type.'" label="'.$this->label.'" name="'.$this->field->name.'">'.json_encode($data).'</px-input>';
	}
}