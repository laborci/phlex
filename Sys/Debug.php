<?php namespace Phlex\Sys;


class Debug {

	static $requestId;
	static $logNotices = false;
	static $logs = array();

	static function setup(){
		set_exception_handler("Phlex\\Debug::exceptionHandler");
		set_error_handler("Phlex\\Debug::errorHandler");
		ini_set('log_errors', false);
		register_shutdown_function("Phlex\\Debug::fatalErrorHandler");
	}

	static function trace() {
		$trace = debug_backtrace();
		array_shift($trace);
		if ($trace) {
			self::send('TRACE', $trace[0]['class'] . $trace[0]['type'] . $trace[0]['function'] . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], $trace);
		}
	}

	static function pre($data) {
		self::send('MESSAGE', call_user_func_array('Dev::PGet', func_get_args()));
	}

	static function message($data) {
		self::sendData('MESSAGE', func_get_args());
	}

	static function alert($data) {
		self::sendData('ALERT', func_get_args());
	}

	private static function sendData($type, $data) {
		if (self::$requestId === null) self::$requestId = str_replace(',', '', str_replace('.', '', microtime(true) . rand()));
		$trace = debug_backtrace();
		foreach ($trace as $i => $traceData) {
			if ($traceData['class'] != 'Debug') {
				$pi = pathinfo($trace[$i - 1]['file']);
				$caller = array(
					'path'     => $pi['dirname'],
					'file'     => $pi['basename'],
					'line'     => $trace[$i - 1]['line'],
					'class'    => $traceData['class'],
					'function' => $traceData['function'],
					'type'     => $traceData['type']
				);
				break;
			}
		}

		$message = array(
			'data'      => $data,
			'requestId' => self::$requestId,
			'method'    => $_SERVER['REQUEST_METHOD'],
			'server'    => $_SERVER['SERVER_NAME'],
			'url'       => $_SERVER['REQUEST_URI'],
			'caller'    => $caller
		);
		$encodedMessage = str_replace('\\u0000', "", json_encode($message));
		error_log($type . ': ' . $encodedMessage);
	}

	static function send($type, $data) {
		$data = func_get_args();
		array_shift($data);
		self::sendData($type, $data);
	}

	static function exceptionHandler($exception) {
		if ($exception instanceof DBException) {
			self::send(
				'DB Exception',
				$exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' . $exception->getLine(),
				$exception->sql,
				$exception->getTrace()
			);
		} else {
			self::send(
				'PHP Exception',
				$exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' . $exception->getLine(),
				$exception->getTrace()
			);
		}
	}

	static function errorHandler($errno, $errstr, $errfile, $errline, $errcontext = null) {
		$errcontext = null;

		if (error_reporting() == 0) return true;
		if ($errno == E_NOTICE and !self::$logNotices) return true;

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
		self::send(
			$e_type,
			$errstr . ' in ' . $errfile . ' on line ' . $errline
		);

		return true;
	}

	static function fatalErrorHandler() {
		$error = error_get_last();
		if ($error !== null) Debug::errorHandler($error["type"], $error["message"], $error["file"], $error["line"], $errcontext);
	}

}