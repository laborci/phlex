<?php namespace Phlex\Form;

use Phlex\Chameleon\JsonResponder;


abstract class FormAction extends JsonResponder{

	protected $formClass;
	/** @var Form */
	protected $form;

	protected function respond() {
		$action = $this->getRequestBag()->get('action').'Action';
		$data = $this->getRequestBag()->get('data');
		return $this->$action($data);
	}

	public function __construct() {
		parent::__construct();
		$formClass = $this->formClass;
		$this->form = new $formClass();
	}

	protected function saveAction($data) {
		$validatorResult = $this->form->validate($data);
		if($validatorResult->getStatus()){
			$result['status'] = 'ok';
			$this->persist();
		}
		else{
			$result['status'] = 'error';
			$result['fieldMessages'] = $validatorResult->getErrors();
		}
		return $result;
	}

	protected function persist(){

	}

}


