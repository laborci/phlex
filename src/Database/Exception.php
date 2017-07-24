<?php
/**
 * Created by PhpStorm.
 * Admin: elvis
 * Date: 11/02/16
 * Time: 21:42
 */

namespace Phlex\Database;

class Exception extends \Exception{
	public $sql;
	public $pdoException;

	/**
	 * Exception constructor.
	 *
	 * @param string             $message
	 * @param int                $error_code
	 * @param null               $prev
	 * @param null               $sql
	 * @param \PDOException|null $pdoEx
	 */
	function __construct($message, $error_code, $prev=null, $sql=null, \PDOException $pdoEx = null){
		parent::__construct($message, is_numeric($error_code)?$error_code:null, $prev);
		if ($pdoEx) $this->pdoException = $pdoEx;
		$this->code = $error_code;
		$this->sql = $sql;
	}

	function getMySQLErrNo() {
		if ($this->pdoException) return $this->pdoException->errorInfo[1];
		return null;
	}

	function getMySQLErrString() {
		if ($this->pdoException) return $this->pdoException->errorInfo[2];
		return null;
	}
}