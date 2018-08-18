<?php namespace Phlex\Form;

use Phlex\RedFox\Entity;

abstract class Admin {

	public $entityClass;
	public $listTitle;
	public $titleCode;
	public $titleField;
	public $urlPostFix;
	public $hasAttachments = false;

	public $actionUrl;
	public $listUrl;
	public $formUrl;

	protected $form = null;

	function __construct() {
		$this->actionUrl = '/action/'.$this->urlPostFix.'/';
		$this->formUrl = '/form/'.$this->urlPostFix.'/';
		$this->listUrl = '/list/'.$this->urlPostFix.'/';
	}

	abstract public function decorateList(ListPage $list);
	abstract public function decorateForm(Form $form);
	abstract public function itemConverter(Entity $item);

	public function getForm(){
		if($this->form == null){
			$this->form = new Form();
			$this->decorateForm($this->form);
		}
		return $this->form;
	}
}