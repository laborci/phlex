<?php namespace Phlex\Database;

class Hierarchy {

	private $db, $table;
	private $isTree;
	private $fields = array(
		'id' => 'id',
		'ordinal' => 'ordinal',
		'parentId' => 'parentId'
	);
	private $row;


	/**
	 * @param Access $db
	 * @param $table
	 * @param bool   $isTree - ha sima lista, akkor false, ha fába rendezett lista, akkor true
	 * @param array  $fields - ha a kulcsmezők eltérnek a defaulttól (id, ordinal, parentId)
	 */
	public function __construct(Access $db, $table, $isTree = false, $fields = null){
		$this->db = $db;
		$this->isTree = $isTree;
		if($fields) $this->fields = $fields;
		$this->table = $table;
	}

	/**
	 * Elem (id) kiválasztása a mozgatáshoz
	 * @param $rowId
	 * @return $this
	 */
	public function move($rowId){
		$this->row = $this->getRow($rowId);
		return $this;
	}

	/**
	 * Melyik elem elé mozgatjuk
	 * @param $rowId
	 */
	public function before($rowId){
		if(!$this->row or $this->row['id'] == $rowId) return false;
		$relRow = $this->getRow($rowId);
		if(!$relRow) return false;
		if($this->isTree and $relRow['parentId'] != $this->row['parentId']) return false;
		$position = $relRow['ordinal'];
		$this->createHole($position);
		$this->setRow($position);
		$this->reorder($this->isTree?$this->row['parentId']:null);
		return true;
	}

	/**
	 * Melyik elem után mozgatjuk
	 * @param $rowId
	 */
	public function after($rowId){
		if(!$this->row or $this->row['id'] == $rowId) return false;
		$relRow = $this->getRow($rowId);
		if(!$relRow) return false;
		if($this->isTree and $relRow['parentId'] != $this->row['parentId']) return false;
		$position = $relRow['ordinal']+1;
		$this->createHole($position);
		$this->setRow($position);
		$this->reorder($this->isTree?$this->row['parentId']:null);
		return true;
	}

	/**
	 * Sima lista esetében az utolsó helyre tesszük
	 */
	public function last(){
		if(!$this->row or $this->isTree) return;
		$count = $this->db->getField("SELECT Count(*) FROM `".$this->table."`");
		$this->setRow($count+1, $rowId);
		$this->reorder();
	}

	/**
	 * Fa esetében a szülő megválasztása, melyben utolsó helyre kerül az elem
	 * @param type $rowId
	 * @param type $force
	 */
	public function under($rowId, $force = false){
		if(!$this->row or !$this->isTree) return;
		$previusParentId = $this->row['parentId'];
		if($previusParentId == $rowId && !$force) return;

		if($rowId != 0){
			$parentRow = $this->getRow($rowId);
			if(!$parentRow) return;
		}

		$count = $this->db->getField($this->translateSql("SELECT Count({id}) FROM {table} WHERE {parentId} = $1"), $rowId);
		$this->setRow($count+1, $rowId);
		$this->reorder($previusParentId);
	}

	public function childrenUnder($rowId = 0) {
		if(!$this->row or !$this->isTree) return;
		if ($rowId === true) $rowId = $this->row['parentId'];
		$this->db->query($this->translateSql("UPDATE {table} SET {parentId} = $1 WHERE {parentId} = $2 "), $rowId, $this->row['id']);
		$this->reorder($rowId);
	}

	/**
	 * Újrarendezés
	 * @param null $parentId fa esetében, hogy melyik ág legyen újrarendezve
	 * @param null $byField Melyik mező alapján történjen a rendezés (default: ordinal ASC)
	 */
	public function reorder($parentId = null, $byField = null){
		if(!$byField) $byField = $this->fields['ordinal'];

		$sql = "INSERT INTO {table} ({id}, {ordinal})
					(
						SELECT
							sequence.{id},
							sequence.num
						FROM
							(SELECT {id}, @num := @num + 1 as num from {table}, (SELECT @num := 0) as sequencestart {{treecondition}} ORDER BY `".$byField."`) as sequence
					)
					ON DUPLICATE KEY UPDATE {ordinal} = sequence.num";

		if($this->isTree){
			if($parentId === null) return;
			$sql = str_replace('{{treecondition}}', "WHERE {parentId} = ".$parentId, $sql);
		}
		else $sql = str_replace('{{treecondition}}', "", $sql);

		$this->db->query($this->translateSql($sql));
	}

	// = = = = = = = = = = = = PRIVATE METHODS = = = = = = = = = = = =

	private function createHole($from){
		$this->db->query($this->translateSql("UPDATE {table} SET {ordinal} = {ordinal} + 1 WHERE {ordinal} >= ".$from));
	}

	private function setRow($ordinal, $parentId = null){
		$data = array();
		$data[$this->fields['ordinal']] = $ordinal;
		if ($parentId !== null) $data[$this->fields['parentId']] = $parentId;
		$this->db->update($this->table, $data, $this->fields['id'].'='.$this->row['id']);
	}

	private function getRow($rowId){
		if(!$this->isTree) return $this->db->getRow($this->translateSql("SELECT {id} as id, {ordinal} as ordinal FROM {table} WHERE {id} = $1"), $rowId);
		else return $this->db->getRow($this->translateSql("SELECT {id} as id, {ordinal} as ordinal, {parentId} as parentId FROM {table} WHERE {id} = $1"), $rowId);
	}

	private function translateSql($sql){
		$pattern = array('{id}','{ordinal}', '{parentId}', '{table}');
		$replacement = array('`'.$this->fields['id'].'`', '`'.$this->fields['ordinal'].'`', '`'.$this->fields['parentId'].'`', '`'.$this->table.'`');
		$sql = str_replace($pattern, $replacement, $sql);
		return $sql;
	}

}