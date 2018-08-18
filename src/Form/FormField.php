<?php namespace Phlex\Form;

use Phlex\Form\Validator\Validator;
use Phlex\Form\Validator\ValidatorResult;

class FormField{

	public $name;

	public $value;

	/** @var bool */
	public $bind;

	/** @var Input */
	public $input = null;

	/** @var Validator[] */
	protected $validators;

	/** @var Form */
	public $form;

	public function __construct($name, Form $form = null) {
		$this->form = $form;
		$this->name = $name;
	}

	public function setInput($label, $inputType):Input{
		$this->input = new Input($label, $inputType, $this);
		return $this->input;
	}

	public function bind():FormField{
		$this->bind = true;
		return $this;
	}

	public function addValidator($validator):FormField{
		$this->validators[] = $validator;
		return $this;
	}

	public function validate($value, ValidatorResult $result){
		foreach ($this->validators as $validator){
			if($validator->validate($value, $result, $this->name)) break;
		}
	}
}
