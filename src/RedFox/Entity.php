<?php namespace Phlex\RedFox;


/**
 * Class Entity
 * @package Phlex\RedFox
 * @property-read int $id
 */

abstract class Entity {

	/** @var  Record */
	private $record;
	private $deleted = false;

	/**
	 * @return Repository
	 */
	public static function repository(){
		static $repository;
		if(is_null($repository)){
			$dataSourceClass = get_called_class().'DataSource';
			$repositoryClass = get_called_class().'Repository';
			$repository = new $repositoryClass(new $dataSourceClass, get_called_class());
		}
		return $repository;
	}

	/**
	 * @return Model
	 */
	public static function model(){
		static $model;
		if(is_null($model)){
			$class = get_called_class().'Model';
			$model = new $class(get_called_class());
		}
		return $model;
	}

	/**
	 * @return Cache
	 */
	public static function cache(){
		// TODO: create EntityCache!!!
		static $cache;
		if(is_null($cache)){
			$cache = new Cache();
		}
		return $cache;
	}

	public function isExists(){ return (bool)$this->record->get('id'); }
	public function isDeleted(){ return $this->deleted; }
	public function save(){
		if($this->isExists()){
			if($this->onBeforeUpdate() === false) return false;
			static::repository()->update($this);
			$this->onUpdate();
		}else{
			if($this->onBeforeInsert() === false) return false;
			$id = static::repository()->insert($this);
			$this->id = $id;
			$this->onInsert();
		}
		return $this->id;
	}
	public function getRawData(){ return $this->record->getRawData(); }



	public function __construct($data = null) {
		$this->record = new Record($this->model(), $data);
		if($this->id){
			static::cache()->add($this);
		}
	}

	#region Evenet Handlers
	public function onBeforeInsert(){return true;}
	public function onBeforeUpdate(){return true;}
	public function onBeforeDelete(){return true;}
	public function onInsert(){}
	public function onUpdate(){}
	public function onDelete(){}
	#endregion

	private $attachmentManagers = [];

	public function __get($name) {
		if(method_exists($this, $method = '__get'.ucfirst($name))){
			return $this->$method();
		}else if($this->record->hasField($name)){
			return $this->record->get($name);
		}else if(static::model()->isRelationExists($name)){
			return static::model()->getRelationValue($name, $this);
		}else if(array_key_exists($name, $this->attachmentManagers)){
			return $this->attachmentManagers[$name];
		}else if(static::model()->isAttachmentGroupExists($name)){
			$this->attachmentManagers[$name] = static::model()->getAttachmentManager($name, $this);
			return $this->attachmentManagers[$name];
		}
		return null;
	}

	public function __set($name, $value) {
		if(method_exists($this, $method = '__set'.ucfirst($name))){
			$this->$method($value);
		}else if($this->record->hasField($name)){
			$this->record->set($name, $value);
		}
	}

	function __toString() { return $this->id; }

	function getId(){
		return $this->id;
	}

}