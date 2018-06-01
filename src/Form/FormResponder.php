<?php namespace Phlex\Form;

use Phlex\Chameleon\HandyResponder;
use Phlex\Form\FormRenderer\FormRenderer;
use Phlex\RedFox\Entity;

class FormResponder extends HandyResponder {

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
		$this->bodyClass = 'form';

		$id = $this->getPathBag()->get('id');
		if($id === 'new') {
			$this->item = new $this->entityClass();
		}else{
			$this->item = $this->entityClass::repository()->pick($id);
		}
		/** @var Form $form */
		$form = new $this->formClass();
		$form->fill($this->convert($this->item));
		$this->formRenderer = new $this->formRendererClass($form, $this->formUrl);
		$this->formRenderer->action = $this->actionUrl;
		$this->formRenderer->title = $this->title();
		$this->formRenderer->itemId = $this->item->id;
	}

	protected function title(){
		$titleField = $this->titleField;
		return '<b>'.$this->titleCode.'</b> '.$this->item->$titleField.' <em>'.($this->item->id ?? 'new').'</em>';
	}

	protected function convert(Entity $item){
		return $item->getRawData();
	}

	protected function BODY() { ?>
		@php $this->formRenderer->render();
	<?php }
}