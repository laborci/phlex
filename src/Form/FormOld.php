<?php namespace Phlex\Form;

use App\ServiceManager;
use Phlex\Form\FormRenderer\HiddenInput;
use Phlex\Form\Validator\ValidatorResult;

class Form {

	/** @var FormField[] */
	public $fields = [];

	/** @var bool */
	public $hasAttachments = false;

	public function __construct($addIdField = true) {
		if ($addIdField) {
			$this->add('id')
				->bind()
				->setInput((new HiddenInput('Id')))
				->addCondition(function ($form) {
					return (bool)$this->fields['id']->value;
				});
		}
	}

	public function add($field): FormField {
		if (array_key_exists($field, $this->fields)) {
			trigger_error('Duplicate formfield definition', E_USER_WARNING);
		}
		$formField = new FormField($field, $this);
		$this->fields[$field] = $formField;
		return $formField;
	}


	public function applyBindings($data) {
		foreach ($this->fields as $field) {
			if ($field->bind) {
				$data[$field->bind] = $field->value;
			}
		}
		return $data;
	}

	public function fill($data) {
		foreach ($this->fields as $field) {
			if (array_key_exists($field->name, $data)) {
				$field->value = $data[$field->name];
			}
		}
	}

	public function validate($data) {

		$result = new ValidatorResult();

		foreach ($this->fields as $field) {
			if (array_key_exists($field->name, $data)) {
				$field->validate($data[$field->name], $result);
			}
		}

		return $result;

	}

}