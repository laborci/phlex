<?php namespace Phlex\Codex;

use Phlex\RedFox\Entity;
use Phlex\RedFox\Repository;

class ListHandler {

	protected $fields = [];
	protected $plugins = [];
	protected $sorting = null;
	protected $entityClass;
	protected $pageSize = 50;
	protected $title;

	protected $some;

	protected $adminDescriptor;


	public function __construct(AdminDescriptor $adminDescriptor) {
		$this->adminDescriptor = $adminDescriptor;
		$this->title = $adminDescriptor->getTitle();
		$this->entityClass = $adminDescriptor->getEntityClass();
		$this->url = $adminDescriptor->getUrl();
	}

	public function setPageSize(int $pageSize): void { $this->pageSize = $pageSize; }
	public function addPlugin($plugin){ $this->plugins[] = $plugin; }

	protected function getRows($page, $sorting, $filter, &$paging){
		if($sorting === null){ $sorting = $this->sorting; }

		/** @var Repository $repository */
		$repository = $this->entityClass::repository();
		$filter = $this->constructFilter($filter);

		/** @var Entity[] $items */
		$items = $repository->search($filter)->order($sorting['field'] . ' ' . $sorting['order'])->collectPage($this->pageSize, $page, $count);

		$paging = [
			'page' => $page,
			'count' => $count,
		];
		$rows = [];

		foreach ($items as $item) {
			$data = $this->extract($item);
			$rows[] = [
				'id'=>$item->id,
				'data'=>$data
			];
		}
		return $rows;
	}

	protected function extract(Entity $item): array {
		return $item->getRawData();
	}

	protected function constructFilter($filter){
		return null;
	}

	public function get($page, $sorting, $filter) {
		$rows = $this->getRows($page, $sorting, $filter, $paging);
		return [
			'options' => $this->getOptions(),
			'rows' => $rows,
			'paging' => $paging,
		];
	}

	public function addField($field, $sortable=true, $type = 'text') {

		if(is_array($field)){
			list($field, $label) = $field;
		}else{
			$label = $this->adminDescriptor->getFormDataManager()->getField($field)->label;
		}

		$this->fields[$field] = [
			'field' => $field,
			'label' => $label,
			'sortable' => $sortable,
			'type' => $type,
		];

		if(is_null($this->sorting) && $sortable){
			$this->sorting = ['field'=>$field, 'order'=>'asc'];
		}
		if($sortable === 'asc' || $sortable === 'desc'){
			$this->sorting = ['field'=>$field, 'order'=>$sortable];
		}
	}

	public function getOptions() {
		return [
			'plugins' => $this->plugins,
			'fields' => $this->fields,
			'sorting' => $this->sorting,
			'url' => $this->url,
			'title' => $this->title,
			'pageSize' => $this->pageSize
		];
	}

}
