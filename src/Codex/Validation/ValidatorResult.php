<?php namespace Phlex\Codex\Validation;

class ValidatorResult {

	public $status;
	public $message;
	public $subject;
	public $label;

	public function __construct($status, $subject=null, $label=null, $message = null) {
		$this->status = $status;
		$this->message = $message;
		$this->subject = $subject;
		$this->label = $label;
	}

	static function ok(){ return new static(true); }
	static function warning($subject, $label, $message){ return new static(true, $subject, $label,  $message); }
	static function error($subject, $label, $message){ return new static(false, $subject, $label,  $message); }

}