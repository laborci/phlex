<?php namespace Phlex\Codex;

use Phlex\RedFox\Entity;
use Phlex\RedFox\Model;

class FormDataManager {

	/** @var Field[] */
	protected $fields = [];

	public function __construct(AdminDescriptor $adminDescriptor) {
		$this->entityClass = $adminDescriptor->getEntityClass();
	}

	public function addField($fieldName, $label = null): Field {
		$field = new Field($fieldName, $label);
		$this->fields[$fieldName] = $field;
		return $field;
	}

	public function getField($name) { return $this->fields[$name]; }

	public function validate($data) {
		$result = new Validation\ValidationResult();
		foreach ($this->fields as $fieldName => $field) {
			if (array_key_exists($fieldName, $data)) {
				$result->addValidatorResults(...$field->validate($data[$fieldName]));
			}
		}
		return $result;
	}

	public function get($id) {
		$item = $this->pick($id);
		$data = $this->extract($item);
		return [
			'id' => $data['id'],
			'record' => $data,
		];
	}

	protected function mapEntityArray($items) {
		return array_map(function (Entity $item) { return ['key'=>$item->id, 'value'=>$item->__toString()]; }, $items);
	}

	protected function idListEntityArray($items){
		return array_map(function(Entity $item){return $item->id;}, $items);
	}

	public function pick($id): Entity {
		return $id === 'new' ? new $this->entityClass() : $this->entityClass::repository()->pick($id);
	}

	protected function extract(Entity $item): array {
		return $item->getRawData();
	}

	protected function pack(Entity $item, $data) {
		/** @var Model $model */
		$model = $this->entityClass::model();
		foreach ($data as $key => $value) {
			if ($key !== 'id' && $model->fieldWritable($key)) {
				$item->$key = $value;
			}
		}
	}

	protected function persist(Entity $item, $data){
		$item->save();
		return $item->id;
	}

	public function delete($id) {
		$item = $this->pick($id);
		$item->delete();
	}

	public function save($id, $data) {
		$result = [
			'validationResult' => $this->validate($data),
			'id' => false,
		];

		if ($result['validationResult']->getStatus() === false) return $result;

		$item = $this->pick($id);
		$this->pack($item, $data);
		$result['id'] = $this->persist($item, $data);

		return $result;
	}
}