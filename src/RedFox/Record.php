<?php namespace Phlex\RedFox;

class Record {

	protected $record;
	protected $data = [];
	protected $dirty = false;
	protected $dirtyFields = [];
	protected $model;

	public function __construct(Model $model, $data = null) {
		$this->model = $model;
		$this->fill($data);
	}

	public function getRawData() {
		if($this->dirty) {
			$this->exportDirty();
		}
		return $this->record;
	}

	public function isDirty() { return $this->dirty; }

	public function clearDirtyState(){
		if(count($this->dirtyFields)) return false;
		else{
			$this->dirty = false;
			return true;
		}
	}

	public function hasField($name) { return $this->model->hasField($name); }

	protected function exportDirty(){
		foreach(array_keys($this->dirtyFields) as $name){
			$field = $this->model->getField($name);
			$this->record[$name] = $field->export($this->data[$name]);
		}
		$this->dirtyFields = [];
	}

	public function get($name) {
		if(array_key_exists($name, $this->data)) return $this->data[$name];
		return $this->model->getField($name)->import($this->record[$name]);
	}

	public function set($name, $value, $force = false) {
		$field = $this->model->getField($name);
		if($field->isWritable() || $force || ($field->isConstant() && is_null($this->get($name)))) {
			$this->dirty = true;
			$this->dirtyFields[$name] = true;
			$this->data[$name] = $field->set($value);
		}
	}

	protected function fill($data) {
		$fields = $this->model->getFields();
		foreach($fields as $field) {
			$this->record[$field] = $data[$field];
		}
	}

}