<?php namespace Phlex\Form;

use Phlex\Chameleon\JsonResponder;
use Phlex\RedFox\Entity;


abstract class FormAction extends JsonResponder{

	protected $formClass;
	protected $entityClass;
	/** @var Form */
	protected $form;

	protected function respond() {
		$action = $this->getRequestBag()->get('action').'Action';
		$data = $this->getRequestBag()->get('data');
		return $this->$action($data);
	}

	public function __construct() {
		parent::__construct();
		$this->form = new $this->formClass();
	}

	protected function saveAction($data) {
		$validatorResult = $this->form->validate($data);
		if($validatorResult->getStatus()){
			$result['status'] = 'ok';
			$this->persist($data);
		}
		else{
			$result['status'] = 'error';
			$result['fieldMessages'] = $validatorResult->getErrors();
		}
		return $result;
	}

	protected function persist($data){
		if($data['id']){
			/** @var Entity $item */
			$item = $this->entityClass::repository()->pick($data['id']);
		}else{
			/** @var Entity $item */
			$item = new $this->entityClass();
		}
		foreach ($data as $key=>$value){
			if($key != 'id'){
				$item->$key = $value;
			}
		}

		$item->save();
	}

}


