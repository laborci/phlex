<?php namespace Phlex\Form;

use Phlex\Form\FormRenderer\Input;
use Phlex\Form\Validator\Validator;
use Phlex\Form\Validator\ValidatorResult;

class FormField{

	public $name;
	public $value;
	/** @var bool */
	public $bind;
	/** @var Input */
	public $input;
	/** @var Validator[] */
	protected $validators;
	/** @var array  */
	protected $conditions = [];

	public $context;

	public function __construct($name, $context = null) {
		$this->context = $context;
		$this->name = $name;
	}

	public function setInput(Input $input):FormField{
		$this->input = $input;
		$input->field = $this;
		return $this;
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

	public function addCondition(callable $condition){
		$this->conditions[] = $condition;
	}

	public function testConditions($form){
		foreach ($this->conditions as $condition){
			if($condition($form) === false) return false;
		}
		return true;
	}
}
