<?php namespace Phlex\Codex;

use App\ServiceManager;
use Phlex\RedFox\Entity;
use Phlex\RedFox\Fields\BoolField;
use Phlex\RedFox\Fields\DateField;
use Phlex\RedFox\Fields\DateTimeField;
use Phlex\RedFox\Fields\PasswordField;
use Phlex\RedFox\Fields\SetField;
use Phlex\RedFox\Model;

class FormDataManager {

	/** @var Field[] */
	protected $fields = [];

	public function __construct(AdminDescriptor $adminDescriptor) {
		$this->entityClass = $adminDescriptor->getEntityClass();
	}

	public function addField($fieldName, $label = null, $default = null): Field {
		$field = new Field($fieldName, $label, $default);
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
		if($id === 'new'){
			$item = new $this->entityClass();
			foreach ($this->fields as $field){
				$fieldname = $field->field;
				if(!is_null($field->default)) $item->$fieldname = $field->default;
			}
			return $item;
		}else{
			return $this->entityClass::repository()->pick($id);
		}
		//return $id === 'new' ? new $this->entityClass() : $this->entityClass::repository()->pick($id);
	}

	protected function extract(Entity $item): array {
		$data = [];
		/** @var Model $model */
		$model = $this->entityClass::model();
		$fields = $model->getFields();

		foreach ($fields as $name){
			$field = $model->getField($name);
			switch (get_class($field)){
				case DateTimeField::class:
					$data[$name] = is_null($item->$name) || is_bool($item->$name) ? null : $item->$name->format('Y-m-d\TH:i');
					break;
				case DateField::class:
					$data[$name] = is_null($item->$name) || is_bool($item->$name) ? null : $item->$name->format('Y-m-d');
					break;
				case PasswordField::class:
					$data[$name] = null;
					break;
				default:
					$data[$name] = $item->$name;
			}
		}
		return $data;
		//		return $item->getRawData();
	}

	protected function pack(Entity $item, $data) {
		/** @var Model $model */
		$model = $this->entityClass::model();
		foreach ($data as $key => $value) {

			if ($key !== 'id' && $model->fieldWritable($key)) {

				switch (get_class($model->getField($key))){
					case DateTimeField::class:
						$item->$key = is_null($item->$key) ? null :  new \DateTime($data[$key]);
						break;
					case DateField::class:
						$item->$key = is_null($item->$key) ? null : new \DateTime($data[$key]);
						break;
					case PasswordField::class:
						if(!is_null($data[$key])) $item->$key = $data[$key];
						break;
					default:
						$item->$key = $value;
				}

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