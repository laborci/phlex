<?php namespace Phlex\Sys;



class Logger implements ServiceManager\SharedService{

	protected $logfile;
	protected $colors;

	public function __construct() {
		$this->logfile = ini_get('error_log');
		$this->colors = [
			'error' => new LoggerFormatter(LoggerFormatter::fg_light_red, LoggerFormatter::bg_light_yellow, array('bold', 'blink')),
			'warning' => new LoggerFormatter(LoggerFormatter::fg_light_yellow, LoggerFormatter::bg_black, array('bold')),
			'exception' => new LoggerFormatter(LoggerFormatter::fg_light_red, LoggerFormatter::bg_black, array('bold', 'blink')),
			'file' => new LoggerFormatter(LoggerFormatter::fg_dark_gray,LoggerFormatter::bg_black, array('bold')),
			'notice' => new LoggerFormatter(LoggerFormatter::fg_light_cyan, LoggerFormatter::bg_black),
			'sql' => new LoggerFormatter(LoggerFormatter::fg_light_cyan, LoggerFormatter::bg_light_blue),
			'request' => new LoggerFormatter(LoggerFormatter::fg_white, LoggerFormatter::bg_magenta),
			'info' => new LoggerFormatter(LoggerFormatter::fg_light_cyan, LoggerFormatter::bg_black),
		];
	}


	protected function friendlyErrorType($type){
		switch($type)
		{
			case E_ERROR: // 1 //
				return 'E_ERROR';
			case E_WARNING: // 2 //
				return 'E_WARNING';
			case E_PARSE: // 4 //
				return 'E_PARSE';
			case E_NOTICE: // 8 //
				return 'E_NOTICE';
			case E_CORE_ERROR: // 16 //
				return 'E_CORE_ERROR';
			case E_CORE_WARNING: // 32 //
				return 'E_CORE_WARNING';
			case E_COMPILE_ERROR: // 64 //
				return 'E_COMPILE_ERROR';
			case E_COMPILE_WARNING: // 128 //
				return 'E_COMPILE_WARNING';
			case E_USER_ERROR: // 256 //
				return 'E_USER_ERROR';
			case E_USER_WARNING: // 512 //
				return 'E_USER_WARNING';
			case E_USER_NOTICE: // 1024 //
				return 'E_USER_NOTICE';
			case E_STRICT: // 2048 //
				return 'E_STRICT';
			case E_RECOVERABLE_ERROR: // 4096 //
				return 'E_RECOVERABLE_ERROR';
			case E_DEPRECATED: // 8192 //
				return 'E_DEPRECATED';
			case E_USER_DEPRECATED: // 16384 //
				return 'E_USER_DEPRECATED';
		}
		return "";
	}

	function setErrorHandler(){
		set_error_handler([$this, 'errorHandler']);
	}

	function handleException(\Throwable $exception) {
		$line = $exception->getLine();
		$file = $exception->getFile();
		$message = $exception->getMessage().' ('.$exception->getCode().')';
		$trace = $exception->getTraceAsString();
		$type = get_class($exception);
		if ($exception instanceof \Error) {
			$this->writeOut($type, $message, 'error', [$file,$line]);
		}else{
			$this->writeOut($type, $message, 'exception', [$file,$line], $trace);
		}
	}

	function errorHandler( $type, $message, $file, $line, $errContext){
		if($type < E_USER_ERROR) $level = 'warning';
		if($type == E_NOTICE) $level = 'notice';
		$this->writeOut($this->friendlyErrorType($type), $message, $level, [$file, $line], null);
		return true;
	}

	public function sql($sql, $method){
		$this->writeOut(' SQL ', $method, 'sql', null, $sql);
	}

	public function request($method, $uri){
		$this->writeOut(' - Request / '.$method.' - ', $uri, 'request', null, null, true);
	}

	public function __invoke($message) {
		$this->info($message);
	}

	public function info($message){
		if(is_scalar($message)){
			$details = null;
		}else{
			$details = json_encode($message, JSON_PRETTY_PRINT);
			$message = gettype($message) . (is_object($message) ? ': '.get_class($message): '');
		}
		$this->writeOut('info', $message, 'info', null,  $details);
	}

	protected function writeOut($label, $message = null, $level = null, $location = null, $details = null, $date = false){
		$label = !is_null($level) ? $this->colors[$level]->apply($label) : $label;
		$location = $location ? PHP_EOL."at ".$this->colors['file']->apply($location[0]) .' line '.$this->colors['file']->apply($location[1]) : null;
		$output =
			($date ? date('m-d H:i').' ' : '').
			$label. ($message ? ' '.$message : '').
			($location ? $location : '').
			($details ? PHP_EOL.$details : '');
		file_put_contents( $this->logfile, $output.PHP_EOL.PHP_EOL, FILE_APPEND );
	}



}

