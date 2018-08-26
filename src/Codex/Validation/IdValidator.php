<?php namespace Phlex\Codex\Validation;

class IdValidator extends Validator {

	public function validate($data):ValidatorResult{
		if(!is_numeric($data) || $data < 1){
			return $this->error('can not be empty!');
		}
		return $this->ok();
	}

}