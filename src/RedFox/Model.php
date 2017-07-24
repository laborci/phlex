<?php namespace Phlex\RedFox;

use Phlex\Database\DataSource;
use Phlex\RedFox\Attachment\AttachmentDescriptor;
use Phlex\RedFox\Attachment\AttachmentManager;
use Phlex\RedFox\Relation\BackReference;
use Phlex\RedFox\Relation\CrossReference;
use Phlex\RedFox\Relation\Reference;

abstract class Model {

	private $entityClass;
	private $entityShortName;

	public function __construct($entityClass) {
		$this->entityClass = $entityClass;
		$this->entityShortName = (new \ReflectionClass($entityClass))->getShortName();
		$this->setup();
	}

	/** @var Field[] */
	private $fields    = [];
	private $relations = [];

	private function setup(){
		$this->fields();
		$this->decorateFields();
		$this->relations();
		$this->attachments();
	}

	abstract protected function fields();
	abstract protected function relations();
	abstract protected function attachments();
	abstract protected function decorateFields();

	#region Fields
	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasField(string $name) { return array_key_exists($name, $this->fields); }
	/**
	 * @param string $name
	 * @param        $value
	 * @return mixed
	 */
	public function import(string $name, $value) { return $this->fields[$name]->import($value); }
	/**
	 * @param string $name
	 * @param        $value
	 * @return mixed
	 */
	public function export(string $name, $value) { return $this->fields[$name]->export($value); }
	/**
	 * @param $name
	 * @return \Phlex\RedFox\Field
	 */
	public function getField($name) { return $this->fields[$name]; }
	/**
	 * @return array
	 */
	public function getFields() { return array_keys($this->fields); }
	/**
	 * @param string              $name
	 * @param \Phlex\RedFox\Field $field
	 */
	public function addField(string $name, Field $field) { $this->fields[$name] = $field; }
	#endregion

	#region Related Fields
	protected function belongsTo($name, $class, $field = null) {
		$this->relations[$name] = new Reference($class, is_null($field) ? $name . 'Id' : $field);
	}
	protected function hasMany($name, $class, $field) {
		$this->relations[$name] = new BackReference($class, $field);
	}
	protected function connectedTo($name, DataSource $dataSource, $class, $selfField, $otherField) {
		$this->relations[$name] = new CrossReference($dataSource, $class, $selfField, $otherField);
	}

	public function getRelations() { return array_keys($this->relations); }
	public function getRelation($name) { return $this->relations[$name]; }
	public function isRelationExists($name) { return array_key_exists($name, $this->relations); }
	public function getRelationValue($name, $object) { return $this->relations[$name]($object); }
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

