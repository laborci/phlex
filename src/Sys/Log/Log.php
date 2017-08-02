<?php namespace Phlex\Sys\Log;

use App\Env;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;


class Log implements LoggerInterface {

	protected $log;
	protected $path;
	protected $colorlog;
	protected $message_type;
	/** @var OutputFormatterStyle[] */
	protected $colors;

	public function __construct($path = null) {

		$this->log = Env::get('dev-mode');

		$this->colorlog = Env::get('color-log');

		$this->message_type = is_null($path) ? 0 : 3;
		$this->path = $path;
		$this->colors = [
			'emergency'=> new OutputFormatterStyle('red', 'yellow', array('bold', 'blink')),
			'alert'=>     new OutputFormatterStyle('red', 'yellow', array('bold', 'blink')),
			'critical'=>  new OutputFormatterStyle('red', 'yellow', array('bold', 'blink')),
			'error'=>     new OutputFormatterStyle('red', 'yellow'),
			'warning'=>   new OutputFormatterStyle('yellow', 'red'),
			'notice'=>    new OutputFormatterStyle('cyan', 'blue'),
			'info'=>      new OutputFormatterStyle('cyan', 'blue'),
			'debug'=>     new OutputFormatterStyle('cyan', 'blue'),
			'log'=>       new OutputFormatterStyle('white', 'blue'),
		];
	}

	public function emergency($message, array $context = array()) {
		if(!$this->log) return;
		$this->send('emergency', $message, $context);
	}

	public function alert($message, array $context = array()) {
		if(!$this->log) return;
		$this->send('alert', $message, $context);
	}

	public function critical($message, array $context = array()) {
		if(!$this->log) return;
		$this->send('critical', $message, $context);
	}

	public function error($message, array $context = array()) {
		if(!$this->log) return;
		$this->send('error', $message, $context);
	}

	public function warning($message, array $context = array()) {
		if(!$this->log) return;
		$this->send('warning', $message, $context);
	}

	public function notice($message, array $context = array()) {
		if(!$this->log) return;
		$this->send('notice', $message, $context);
	}

	public function info($message, array $context = array()) {
		if(!$this->log) return;
		$this->send('info', $message, $context);
	}

	public function debug($message, array $context = array()) {
		if(!$this->log) return;
		$this->send('debug', $message, $context);
	}

	public function log($level, $message, array $context = array()) {
		if(!$this->log) return;
		$this->send('log', $message, $context);
	}

	protected function send($type, $messageText, $context) {


		if($this->colorlog){
			$message =
				$this->colors[$type]->apply(' '.ucfirst($type).' ');
		}else{
			$message = ucfirst($type);
		}
		$message.=' - '.$messageText;

		$this->write($message);
	}

	protected function write($message){
		error_log($message, $this->message_type, $this->path);
	}

}