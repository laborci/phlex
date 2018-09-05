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


	/**
	 * @deprecated use input method instead
	 * @param $type
	 * @param $field
	 * @param array $options
	 * @return $this
	 */
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

	public function input($type, $field, $options = []){
		if(is_array($field)){
			list($field, $label) = $field;
		}else{
			$label = $this->adminDescriptor->getFormDataManager()->getField($field)->label;
		}
		$input = new Input($type, $label, $field, $options);
		$this->inputs[] = $input;
		return $input;
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
