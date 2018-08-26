<?php namespace Phlex\Codex\Responder;

use App\Site\Admin\Service\Admin\AdminDescriptor;
use Phlex\Chameleon\JsonResponder;


class ListResponder extends JsonResponder {

	/** @var AdminDescriptor */
	protected $adminDescriptor;

	public function __construct() {
		parent::__construct();
		$adminDescriptorClass = $this->getAttributesBag()->get('admin');
		$this->adminDescriptor = new $adminDescriptorClass();
	}

	protected function respond() {
		$listHandler = $this->adminDescriptor->getListHandler();
		$sorting = $this->getJsonParamBag()->get('sorting');
		$filter = $this->getJsonParamBag()->get('filter');
		$page = $this->getJsonParamBag()->get('page');
		return $listHandler->get($page, $sorting, $filter);
	}

}


