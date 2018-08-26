<?php namespace Phlex\Codex\Validation;

class StringLengthValidator extends Validator {

	protected $min, $max;

	public function __construct($min = 0, $max = null) {
		$this->min = $min;
		$this->max = $max;
	}

	public function validate($data):ValidatorResult{
		$length = strlen($data);
		if ($length < $this->min){
			return $this->error('must be at least '.$this->min.' characters');
		}
		if(!is_null($this->max) && $length > $this->max){
			return $this->error('shoud not be longer than '.$this->max.' characters');
		}
		return $this->ok();
	}

}