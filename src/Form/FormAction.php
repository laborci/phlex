<?php namespace Phlex\Form;

use Phlex\Chameleon\JsonResponder;
use Phlex\RedFox\Entity;


abstract class FormAction extends JsonResponder{

	protected $formClass;
	protected $entityClass;
	/** @var Form */
	protected $form;

	protected function respond() {
		$action = $this->getJsonParamBag()->get('action').'Action';
		$data = $this->getJsonParamBag()->get('data');
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
			$result['jobs'] = ['refreshList', 'refresh'];
			$result['itemId'] = $this->persist($data);
		}
		else{
			$result['status'] = 'error';
			$result['fieldMessages'] = $validatorResult->getErrors();
		}
		return $result;
	}

	protected function deleteAction($data){
		/** @var Entity $item */
		$item = $this->entityClass::repository()->pick($data['id']);
		$result['status'] = 'ok';
		$result['itemId'] = $this->persist($data['id']);
		$item->delete();
		$result['jobs'] = ['refreshList', 'closeForm'];
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
		return $item->id;
	}

}


