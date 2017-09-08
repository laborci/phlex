<?php namespace Phlex\RedFox;

use Phlex\Database\DataSource;
use Phlex\RedFox\Attachment\AttachmentDescriptor;
use Phlex\RedFox\Attachment\AttachmentManager;
use Phlex\RedFox\Relation\BackReference;
use Phlex\RedFox\Relation\CrossReference;
use Phlex\RedFox\Relation\Reference;
use Phlex\RedFox\Relation\UniqueBackReference;


abstract class Model {

	private $entityClass;
	private $entityShortName;

	abstract function repository();

	private $repositoryCache = [];

	protected function repositoryFactory($table, $database) {
		$key = $database.'/'.$table;
		if(!array_key_exists($key, $this->repositoryCache)) {
			$repositoryClass = $this->entityClass.'Repository';
			$this->repositoryCache[$key] = new $repositoryClass(new DataSource($table, $database), $this->entityClass);
		}
		return $this->repositoryCache[$key];
	}

	static function instance($entityClass){ static $instance; return !is_null($instance) ? $instance : $instance = new static($entityClass); }

	private function __construct($entityClass) {
		$this->entityClass = $entityClass;
		$this->entityShortName = (new \ReflectionClass($entityClass))->getShortName();
		$this->setup();
	}

	/** @var Field[] */
	private $fields    = [];
	private $relations = [];

	private function setup(){
		$fields = $this->fields();
		foreach ($fields as $name => $field) {
			$class = array_shift($field);
			/** @var \Phlex\RedFox\Field $field */
			$field = new $class(...$field);
			if ( strpos($name, '@') !== false ) $field->readonly(true);
			$name = trim($name, '@!');
			$this->fields[$name] = $field;
		}
		$this->relations();
		$this->attachments();
	}

	abstract public function fields():array;
	abstract protected function relations();
	abstract protected function attachments();

	#region Fields

	public function fieldExists(string $name):bool { return array_key_exists($name, $this->fields); }
	public function fieldWritable(string $name):bool { return array_key_exists($name, $this->fields) && !$this->fields[$name]->readonly(); }

//	public function import(string $name, $value) { return $this->fields[$name]->import($value); }
//	public function export(string $name, $value) { return $this->fields[$name]->export($value); }

	public function getField($name):Field { return $this->fields[$name]; }
	public function getFields():array { return array_keys($this->fields); }

//	private function hasField(string $name, Field $field) { $this->fields[$name] = $field; return $field;}
	#endregion

	#region Related Fields
	protected function belongsTo($name, $class, $field = null) {
		$this->relations[$name] = new Reference($class, is_null($field) ? $name . 'Id' : $field);
	}
	protected function hasMany($name, $class, $field) {
		$this->relations[$name] = new BackReference($class, $field);
	}
	protected function hasOne($name, $class, $field) {
		$this->relations[$name] = new UniqueBackReference($class, $field);
	}
	protected function connectedTo($name, DataSource $dataSource, $class, $selfField, $otherField) {
		$this->relations[$name] = new CrossReference($dataSource, $class, $selfField, $otherField);
	}

	public function getRelations() { return array_keys($this->relations); }
	public function getRelation($name) { return $this->relations[$name]; }
	public function isRelationExists($name) { return array_key_exists($name, $this->relations); }
	//public function getRelationValue($name, $object) { return $this->relations[$name]($object); }
	#endregion

	/** @var  AttachmentDescriptor[] */
	private $attachmentGroups=[];

	protected function hasAttachmentGroup($called){
		$descriptor = new AttachmentDescriptor($called, $this->entityShortName);
		$this->attachmentGroups[$called] = $descriptor;
		return $descriptor;
	}

	public function isAttachmentGroupExists($name){ return array_key_exists($name, $this->attachmentGroups); }
	public function getAttachmentManager($name, $object){ return new AttachmentManager($object, $this->attachmentGroups[$name]); }
	public function getAttachmentGroups(){return array_keys($this->attachmentGroups);}
}

