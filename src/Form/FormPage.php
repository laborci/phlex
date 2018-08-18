<?php namespace Phlex\Form;

use Phlex\Chameleon\HandyResponder;
use Phlex\Form\FormRenderer;
use Phlex\RedFox\Entity;


/**
 * @css style
 * @jsappmodule Form
 */
class FormPage extends HandyResponder {

	protected $entityClass;
	protected $formClass;
	protected $titleCode;
	protected $titleField ;
	protected $actionUrl;
	protected $formUrl;

	/** @var Entity */
	protected $item;
	protected $formRendererClass = FormRenderer::class;
	/** @var FormRenderer */
	protected $formRenderer;

	protected function prepare() {

		$admin = $this->getAttributesBag()->get('admin');

		$this->entityClass = $admin->entityClass;
		$this->formClass = $admin->formClass;
		$this->titleCode = $admin->titleCode;
		$this->titleField = $admin->titleField;
		$this->actionUrl = $admin->actionUrl;
		$this->formUrl = $admin->formUrl;
		$this->hasAttachments = $admin->hasAttachments;

		$this->bodyClass = 'form';

		$id = $this->getPathBag()->get('id');
		if($id === 'new') {
			$this->item = new $this->entityClass();
		}else{
			$this->item = $this->entityClass::repository()->pick($id);
		}

		/** @var Form $form */
		$form = $admin->getForm();

		$data = $admin->itemConverter($this->item);

		$form->fill($data);
		$this->formRenderer = new $this->formRendererClass($form, $this->formUrl);
		$this->formRenderer->hasAttachments = $this->hasAttachments;
		$this->formRenderer->action = $this->actionUrl;
		$this->formRenderer->title = $this->title();
		$this->formRenderer->itemId = $this->item->id;
	}

	protected function title(){
		$titleField = $this->titleField;
		return '<b>'.$this->titleCode.'</b> '.$this->item->$titleField.' <em>'.($this->item->id ?? 'new').'</em>';
	}

	protected function BODY() { ?>
		@php $this->formRenderer->render();
	<?php }
}