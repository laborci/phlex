<?php namespace Phlex\Form\Validator;

class StringLength extends Validator {

	protected $min, $max;

	public function __construct($min = 0, $max = null) {
		$this->min = $min;
		$this->max = $max;
	}

	public function validate($data, ValidatorResult $result, $fieldName):Bool{
		$length = strlen($data);
		if ($length < $this->min){
			$result->error($fieldName, 'String must be at least '.$this->min.' characters');
			return false;
		}
		if(!is_null($this->max) && $length > $this->max){
			$result->error($fieldName, 'String shoud not be longer than '.$this->max.' characters');
			return false;
		}
		return true;
	}

}