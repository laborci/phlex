<?php

namespace Phlex\Sys;


use App\Env;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;


class Dbg implements LoggerInterface {

	protected static $instance;

	protected $domain;
	protected $path;
	protected $method;
	protected $timestamp;
	protected $messages = array();

	protected function __construct() {
		/** @var Request $request */
		$request = Phlex::instance()->request;
		if ($request) {
			$this->path = $request->getPathInfo();
			$this->method = $request->getMethod();
			$this->domain = $request->getHost();
		} else {
			$this->method = 'CLI';
		}
		$this->timestamp = time();

		set_exception_handler(array($this, "exceptionHandler"));
		set_error_handler(array($this, "errorHandler"));
		ini_set('log_errors', false);
		register_shutdown_function(array($this, 'writeOut'));
	}

	public static function send($message, $type = 'debug', $notInClass = '') {
		static::instance()->addMessage(array('type' => $type, 'message' => $message), $notInClass);
	}

	public static function each($messages, $type = 'each') {
		foreach ($messages as $message) {
			static::instance()->addMessage(array('type' => $type, 'message' => $message));
		}
	}

	public function writeOut() {

		$error = error_get_last();
		if ($error !== null)
			$this->errorHandler($error["type"], $error["message"], $error["file"], $error["line"]);

		$line = array(
			'timestamp' => $this->timestamp,
			'method'    => $this->method,
			'domain'    => $this->domain,
			'path'      => $this->path,
			'messages'  => $this->messages
		);
		if (count($this->messages))
			file_put_contents(Env::instance()->path_log . 'dbg.log', json_encode($line) . "\n", FILE_APPEND);
	}

	public static function instance() { return static::$instance ? static::$instance : static::$instance = new static(); }

	public function emergency($message, array $context = array()) {
		$this->addMessage(array('type' => 'emergency', 'message' => $message));
	}

	public function alert($message, array $context = array()) {
		$this->addMessage(array('type' => 'alert', 'message' => $message));
	}

	public function critical($message, array $context = array()) {
		$this->addMessage(array('type' => 'critical', 'message' => $message));
	}

	public function error($message, array $context = array()) {
		$this->addMessage(array('type' => 'error', 'message' => $message));
	}

	public function warning($message, array $context = array()) {
		$this->addMessage(array('type' => 'warning', 'message' => $message));
	}

	public function notice($message, array $context = array()) {
		$this->addMessage(array('type' => 'notice', 'message' => $message));
	}

	public function info($message, array $context = array()) {
		$this->addMessage(array('type' => 'info', 'message' => $message));
	}

	public function debug($message, array $context = array()) {
		$this->addMessage(array('type' => 'debug', 'message' => $message));
	}

	public function log($level, $message, array $context = array()) {
		$this->addMessage(array('type' => 'log', 'message' => $message));
	}

	protected function addMessage($message, $notInClass = '') {
		if (is_null($message['message']))
			$message['message'] = 'null value';
		$trace = debug_backtrace();
		$source = array(
			'path'     => '',
			'file'     => '',
			'line'     => '',
			'class'    => '',
			'type'     => '',
			'function' => '',
		);
		foreach ($trace as $i => $traceData) {
			if ($traceData['class'] != get_called_class() && (!$notInClass || $traceData['class'] != $notInClass)) {
				$pi = pathinfo($trace[$i - 1]['file']);
				$source = array(
					'path'     => $pi['dirname'],
					'file'     => $pi['basename'],
					'line'     => $trace[$i - 1]['line'],
					'class'    => $traceData['class'],
					'type'     => $traceData['type'],
					'function' => $traceData['function'],
				);
				break;
			}
		}
		$message['source'] = $source;
		if (is_object($message['message'])) {
			$object = $message['message'];
			$class = get_class($object);
			if (in_array('Phlex\\Sys\\ToArray', class_implements($object))) {
				$object = $object->toArray();
			}
			$message['message'] = array($class => $object);
		}
		$this->messages[] = $message;
	}

	public function errorHandler($errno, $errstr, $errfile, $errline, $errcontext = null) {

		$errcontext = null;

		if (error_reporting() == 0)
			return true;

		switch ($errno) {
			case 1:
				$e_type = 'PHP Fatal Error';
				break;
			case 2:
				$e_type = 'PHP Warning';
				break;
			case 4:
				$e_type = 'PHP Parse Error';
				break;
			case 8:
				$e_type = 'PHP Notice';
				break;
			case 16:
				$e_type = 'PHP Core Error';
				break;
			case 32:
				$e_type = 'PHP Core Warning';
				break;
			case 64:
				$e_type = 'PHP Compile Error';
				break;
			case 128:
				$e_type = 'PHP Compile Warning';
				break;
			case 256:
				$e_type = 'PHP Admin Error';
				break;
			case 512:
				$e_type = 'PHP Admin Warning';
				break;
			case 1024:
				$e_type = 'PHP Admin Notice';
				break;
			case 2048:
				$e_type = 'PHP Strict';
				break;
			case 4096:
				$e_type = 'PHP Recoverable Error';
				break;
			case 8192:
				$e_type = 'PHP Depricated';
				break;
			case 16384:
				$e_type = 'PHP Admin Depricated';
				break;
			default:
				$e_type = 'PHP Unknown';
				break;
		}

		$this->addMessage(array('type' => $e_type, 'message' => $errstr . ' in ' . $errfile . ' on line ' . $errline));

		return true;
	}

	public function exceptionHandler($exception) {
		$this->addMessage(array(
			                  'type'    => 'PHP Exception',
			                  'message' => $exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' . $exception->getLine(),
			                  'trace'   => $exception->getTrace()
		                  ));
	}
}