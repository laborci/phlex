<?php namespace Phlex\Codex\Validation;

class RangeValidator extends Validator {

	protected $min, $max;

	public function __construct($min = 0, $max = null) {
		$this->min = $min;
		$this->max = $max;
	}

	public function validate($data): ValidatorResult {
		if ($data < $this->min) {
			return $this->error('must be at least ' . $this->min);
		}
		if (!is_null($this->max) && $data > $this->max) {
			return $this->error('shoud not be larger than ' . $this->max);
		}
		return $this->ok();
	}

}


