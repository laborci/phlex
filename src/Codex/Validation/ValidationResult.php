<?php namespace Phlex\Codex\Validation;

class ValidationResult implements \JsonSerializable {

	protected $messages = [];
	protected $status = true;

	public function getStatus() {
		return $this->status;
	}

	public function getMessages() {
		return $this->messages;
	}

	public function addValidatorResults(ValidatorResult ...$results) {
		foreach ($results as $result) {
			$this->status = $this->status && $result->status;
			if ($result->message) {
				$this->messages[] = [
					'level' => $result->status ? 'WARNING' : 'ERROR',
					'message' => $result->message,
					'subject' => $result->subject,
					'label' => $result->label,
				];
			}
		}
	}

	public function jsonSerialize() {
		return [
			'status' => $this->status,
			'messages' => $this->messages,
		];
	}
}