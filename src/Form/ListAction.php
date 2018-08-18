<?php namespace Phlex\Form;

use App\Entity\User\User;
use App\ServiceManager;
use Phlex\Chameleon\JsonResponder;
use Phlex\RedFox\Entity;
use Phlex\RedFox\Repository;

class ListAction extends JsonResponder {

	protected $entityClass;

	public function __construct() {
		parent::__construct();
		/** @var Form $form */
		$admin =  $this->getAttributesBag()->get('admin');
		$this->entityClass = $admin->entityClass;
	}

	protected function respond() {
		$orderField = $this->getJsonParamBag()->get('orderField');
		$order = $this->getJsonParamBag()->get('order');
		$pageSize = $this->getJsonParamBag()->get('pageSize');
		$page = $this->getJsonParamBag()->get('page');
		$this->search = $this->getJsonParamBag()->get('search');
		$count = 0;

		/** @var Repository $repository */
		$repository = $this->entityClass::repository();

		/** @var Entity[] $items */
		$items = $repository->search($this->search())->order($orderField . ' ' . $order)->collectPage($pageSize, $page, $count);

		$data = [
			'count' => $count,
			'list' => []
		];

		foreach ($items as $item) {
			$data['list'][] = $this->convertRow($item);
		}

		return $data;
	}

	function convertRow(Entity $item) {
		return $item->getRawData();
	}

	function search(){
		return null;
	}
}