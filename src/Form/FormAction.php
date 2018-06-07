<?php namespace Phlex\Form;

use App\ServiceManager;
use Phlex\Chameleon\JsonResponder;
use Phlex\RedFox\Attachment\Exception;
use Phlex\RedFox\Entity;
use Phlex\RedFox\Model;


abstract class FormAction extends JsonResponder{

	protected $formClass;
	protected $entityClass;
	/** @var Form */
	protected $form;

	protected function respond() {
		$action = $this->getRequestBag()->get('action');
		$data = $this->getRequestBag()->get('data');
		if(!$action) {
			$action = $this->getJsonParamBag()->get('action');
			$data = $this->getJsonParamBag()->get('data');
		}
		$action.='Action';
		ServiceManager::getLogger()->info($action);
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



	protected function getAttachmentsAction($data) {

		ServiceManager::getLogger()->info($data);

		/** @var Model $model */
		$model = $this->entityClass::model();

		/** @var Entity $item */
		$item = $this->entityClass::repository()->pick($data['id']);

		$result = ['attachments' => []];

		foreach ($model->getAttachmentGroups() as $group) {
			$result['attachments'][$group] = [];
			foreach ($item->getAttachmentManager($group)->getAttachments() as $filename => $attachment) {
				$attachmentDescriptor = [
					'filename' => $filename,
					'url' => $attachment->url,
					'mimetypeBase' => explode('/', $attachment->getMimeType())[0],
					'mimetypeDetail' => explode('/', $attachment->getMimeType())[1],
					'extension' => pathinfo($filename, PATHINFO_EXTENSION)
				];
				if (substr($attachment->getMimeType(), 0, 5) === 'image') {
					$attachmentDescriptor['thumbnail'] = $attachment->thumbnail->crop(100, 100)->png;
				}
				$result['attachments'][$group][] = $attachmentDescriptor;
			}
		}
		return $result;
	}


	protected function deleteAttachmentAction() {
		$result = ['status' => 'ok'];
		/** @var Entity $item */
		$item = $this->entityClass::repository()->pick($this->getRequestBag()->get('id'));

		$attachment = $item->getAttachmentManager($this->getRequestBag()->get('group'))->getAttachment($this->getRequestBag()->get('filename'));
		if (!is_null($attachment)) $attachment->delete();

		return $result;
	}

	protected function uploadAction() {
		$result = ['status' => 'ok'];
		/** @var Entity $item */
		$item = $this->entityClass::repository()->pick($this->getRequestBag()->get('id'));
		try {
			$item->getAttachmentManager($this->getRequestBag()->get('group'))->uploadFile($this->getFileBag()->get('file'));
		} catch (Exception $exception) {
			$result = ['status' => 'error', 'errorcode' => $exception->getCode(), 'message' => $exception->getMessage()];
		}

		return $result;
	}

}


