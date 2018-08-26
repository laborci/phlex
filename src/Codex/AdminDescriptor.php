<?php namespace Phlex\Codex;

use Phlex\Routing\Router;


abstract class AdminDescriptor {


	/** @var FormDataManager */
	protected $formDataManager;
	/** @var ListHandler */
	protected $listHandler;
	/** @var FormHandler */
	protected $formHandler;

	protected $url;
	protected $entityClass;
	protected $title;
	protected $titleField;
	protected $icon;
	/** @var bool */
	protected $attachments;

	abstract protected static function setOptions(&$url, &$entityClass, &$title, &$titleField, &$icon, &$attachments);

	static public function getOptions() {
		static::setOptions($url, $entityClass, $title, $titleField, $icon, $attachments);
		return compact('url', 'entityClass', 'title', 'titleField', 'icon', 'attachments');
	}

	public function __construct() {
		$options = static::getOptions();
		foreach ($options as $key => $value) $this->$key = $value;

		$this->formDataManager = $this->createFormDataManager();
		$this->listHandler = $this->createListHandler();
		$this->formHandler = $this->createFormHandler();

		$this->setFields($this->formDataManager);
		$this->decorateListHandler($this->listHandler);
		$this->decorateFormHandler($this->formHandler);
	}

	protected function createFormDataManager(){ return new FormDataManager($this); }
	protected function createListHandler(){ return new ListHandler($this); }
	protected function createFormHandler(){ return new FormHandler($this); }

	abstract protected function setFields(FormDataManager $formDataManager);
	abstract protected function decorateListHandler(ListHandler $listHandler);
	abstract protected function decorateFormHandler(FormHandler $formHandler);

	public static function createRoutes(Router $router) {
		$options = static::getOptions();
		$router->post($options['url'] . 'list', Responder\ListResponder::class, ['admin' => static::class]);
		$router->get($options['url'] . 'form/{id}', Responder\FormResponder::class, ['admin' => static::class, 'method' => 'getForm']);
		$router->delete($options['url'] . 'form/{id}', Responder\FormResponder::class, ['admin' => static::class, 'method' => 'delete']);
		$router->post($options['url'] . 'form/{id}', Responder\FormResponder::class, ['admin' => static::class, 'method' => 'save']);
		$router->get($options['url'] . '{id}/attachments', Responder\AttachmentsResponder::class, ['admin' => static::class, 'method' => 'get']);
		$router->post($options['url'] . '{id}/attachments', Responder\AttachmentsResponder::class, ['admin' => static::class, 'method' => 'upload']);
		$router->delete($options['url'] . '{id}/attachments', Responder\AttachmentsResponder::class, ['admin' => static::class, 'method' => 'delete']);

	}

	public static function getMenuArguments() {
		$options = static::getOptions();
		return [$options['title'], $options['icon'], $options['url'] . 'list'];
	}

	public function getListHandler(): ListHandler { return $this->listHandler; }
	public function getFormHandler(): FormHandler { return $this->formHandler; }
	public function getFormDataManager(): FormDataManager { return $this->formDataManager; }
	public function getTitle() { return $this->title; }
	public function getTitleField() { return $this->titleField; }
	public function getEntityClass() { return $this->entityClass; }
	public function getIcon() { return $this->icon; }
	public function getUrl() { return $this->url; }
	public function isAttachments(): bool { return $this->attachments; }
}


