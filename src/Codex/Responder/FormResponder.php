<?php namespace Phlex\Codex\Responder;

use App\Site\Admin\Service\Admin\AdminDescriptor;
use App\Site\Website\Form\FormRenderer\FormRenderer;
use App\Site\Website\Form\UserForm;
use Phlex\Chameleon\JsonResponder;
use Phlex\RedFox\Entity;

class FormResponder extends JsonResponder {

	/** @var AdminDescriptor */
	protected $adminDescriptor;
	/** @var Response */
	protected $response;

	public function __construct() {
		parent::__construct();
		$adminDescriptorClass = $this->getAttributesBag()->get('admin');
		$this->adminDescriptor = new $adminDescriptorClass();
		$this->response = new Response();
	}

	protected function respond() {

		$method = $this->getAttributesBag()->get('method');
		$id = $this->getPathBag()->get('id');

		switch ($method) {
			case 'save':
				$this->save($id, $this->getJsonParamBag()->get('data'));
				break;
			case 'delete':
				$this->delete($id);
				break;
			case 'getForm':
				$this->getForm($id);
				break;
		}

		return $this->response;
	}

	protected function save($id, $data) {

		$formDataManager = $this->adminDescriptor->getFormDataManager();

		try {
			$result = $formDataManager->save($id, $data);
		} catch (\Throwable $exception) {
			$this->getResponse()->setStatusCode(422);
			$this->response->addMessage('Hiba történt a mentés közben. Ellenőrizd az adatokat!');
			return;
		}

		if ($result[ 'validationResult' ]->getStatus() === false) {
			$this->getResponse()->setStatusCode(422);
			foreach ($result[ 'validationResult' ]->getMessages() as $message) {
				$this->response->addMessage($message[ 'label' ] . ' ' . $message[ 'message' ] . ' (' . $message[ 'subject' ] . ')');
			}
		}
		$this->response->set('id', $result[ 'id' ]);
	}

	protected function delete($id) {
		$entityClass = $this->adminDescriptor->getEntityClass();
		/** @var Entity $item */
		$item = $entityClass::repository()->pick($id);
		try {
			if(!$item->delete()){
				$this->getResponse()->setStatusCode(422);
				$this->response->addMessage('Nem sikerült törölni!');
			}
		} catch (\Exception $exception) {
			$this->getResponse()->setStatusCode(422);
			$this->response->addMessage('Hiba történt a törlés közben. Ellenőrizd az adatokat!');
		}
	}

	protected function getForm($id) {
		$formHandler = $this->adminDescriptor->getFormHandler();
		$formDataManager = $this->adminDescriptor->getFormDataManager();
		$this->response->set('data', $formDataManager->get($id));
		$this->response->set('form', $formHandler->get($id));
	}

}

class Response implements \JsonSerializable {

	protected $response = [];

	public function set($key, $value) {
		$this->response[ $key ] = $value;
	}

	public function addMessage($message) {
		if (!array_key_exists('messages', $this->response))
			$this->response[ 'messages' ] = [];
		$this->response[ 'messages' ][] = $message;
	}

	public function jsonSerialize() {
		return $this->response;
	}
}