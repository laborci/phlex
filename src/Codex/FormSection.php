<?php namespace Phlex\Codex;

class FormSection{
	/** @var Input[] */
	protected $inputs = [];
	protected $label;
	protected $adminDescriptor;

	public function __construct($label, AdminDescriptor $adminDescriptor) {
		$this->label = $label;
		$this->adminDescriptor = $adminDescriptor;
	}

	public function addInput($type, $field, $options = []) {
		if(is_array($field)){
			list($field, $label) = $field;
		}else{
			$label = $this->adminDescriptor->getFormDataManager()->getField($field)->label;
		}
		$input = new Input($type, $label, $field, $options);
		$this->inputs[] = $input;
		return $this;
	}

	public function get(){
		$output = [
			'label' => $this->label,
			'inputs' => []
		];
		foreach ($this->inputs as $input){
			$output['inputs'][] = $input->get();
		}
		return $output;
	}

	public function findInput($field){
		foreach ($this->inputs as $input){
			$inputDescriptor = $input->get();
			if($inputDescriptor['field'] === $field) return $inputDescriptor;
		}
		return false;
	}
}
