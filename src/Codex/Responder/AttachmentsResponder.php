<?php namespace Phlex\Codex\Responder;

use App\Site\Admin\Service\Admin\AdminDescriptor;
use Phlex\Chameleon\JsonResponder;
use Phlex\RedFox\Entity;
use Phlex\RedFox\Model;

class AttachmentsResponder extends JsonResponder {

	/** @var AdminDescriptor */
	protected $adminDescriptor;

	public function __construct() {
		parent::__construct();
		$adminDescriptorClass = $this->getAttributesBag()->get('admin');
		$this->adminDescriptor = new $adminDescriptorClass();
	}

	protected function respond() {

		$response = [];

		$method = $this->getAttributesBag()->get('method');
		$id = $this->getPathBag()->get('id');

		switch ($method) {
			case 'get':
				return $this->get($id);
				break;
			case 'upload':
				return $this->upload($id, $this->getRequestBag()->get('group'), $this->getFileBag()->get('file'));
				break;
			case 'copy':
				return $this->copy($id, $this->getJsonParamBag()->get('source'), $this->getJsonParamBag()->get('filename'), $this->getJsonParamBag()->get('target'));
				break;
			case 'move':
				return $this->move($id, $this->getJsonParamBag()->get('source'), $this->getJsonParamBag()->get('filename'), $this->getJsonParamBag()->get('target'));
				break;
			case 'rename':
				return $this->rename($id, $this->getJsonParamBag()->get('group'), $this->getJsonParamBag()->get('file'), $this->getJsonParamBag()->get('newname'));
				break;
			case 'delete':
				return $this->delete($id, $this->getJsonParamBag()->get('file'), $this->getJsonParamBag()->get('group'));
				break;
		}

		return $response;
	}

	protected function get($id) {

		/** @var Model $model */
		$model = $this->adminDescriptor->getEntityClass()::model();

		/** @var Entity $item */
		$item = $this->adminDescriptor->getFormDataManager()->pick($id);

		$result = [ 'attachments' => [] ];

		foreach ($model->getAttachmentGroups() as $group) {
			$result[ 'attachments' ][ $group ] = [];
			foreach ($item->getAttachmentManager($group)->getAttachments() as $filename => $attachment) {
				$attachmentDescriptor = [
					'filename'       => $filename,
					'url'            => $attachment->url,
					'mimetypeBase'   => explode('/', $attachment->getMimeType())[ 0 ],
					'mimetypeDetail' => explode('/', $attachment->getMimeType())[ 1 ],
					'extension'      => pathinfo($filename, PATHINFO_EXTENSION),
				];
				if (substr($attachment->getMimeType(), 0, 5) === 'image') {
					$attachmentDescriptor[ 'thumbnail' ] = $attachment->thumbnail->crop(100, 100)->png;
				}
				$result[ 'attachments' ][ $group ][] = $attachmentDescriptor;
			}
		}
		return $result;
	}

	protected function delete($id, $file, $group) {
		$result = [ 'status' => 'ok' ];
		/** @var Entity $item */
		$item = $this->adminDescriptor->getFormDataManager()->pick($id);

		$attachment = $item->getAttachmentManager($group)->getAttachment($file);
		if (!is_null($attachment))
			$attachment->delete();

		return $result;
	}

	protected function rename($id, $group, $file, $newname) {
		$result = [ 'status' => 'ok' ];
		/** @var Entity $item */
		$item = $this->adminDescriptor->getFormDataManager()->pick($id);

		$attachment = $item->getAttachmentManager($group)->getAttachment($file);
		$attachment->move($attachment->getPath(), $newname);

		return $result;
	}

	protected function copy($id, $group, $file, $target, $move = false) {
		$result = [ 'status' => 'ok' ];
		/** @var Entity $item */
		$item = $this->adminDescriptor->getFormDataManager()->pick($id);
		$attachment = $item->getAttachmentManager($group)->getAttachment($file);
		try {
			$item->getAttachmentManager($target)->addFile($attachment);
			if ($move) {
				$attachment->delete();
			}
		}catch (\Throwable $exception){
			$result = ['status'=>'error', 'errorcode' => $exception->getCode(), 'message' => $exception->getMessage()];
		}
		return $result;
	}
	protected function move($id, $group, $file, $target) {
		return $this->copy($id, $group, $file, $target, true);
	}

	protected function upload($id, $group, $file) {
		$result = [ 'status' => 'ok' ];

		/** @var Entity $item */
		$item = $this->adminDescriptor->getFormDataManager()->pick($id);

		try {
			$item->getAttachmentManager($group)->uploadFile($file);
		} catch (\Throwable $exception) {
			$result = [ 'status' => 'error', 'errorcode' => $exception->getCode(), 'message' => $exception->getMessage() ];
		}

		return $result;
	}

}


