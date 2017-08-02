<?php namespace Phlex\Sys\Log;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;


class RequestLog extends Log{

	protected function send($type, $messageText, $context) {
		$method = $context['method'];

		if($this->colorlog){
			$message =
				(new OutputFormatterStyle('black', 'cyan'))->apply(' REQUEST ').
				(new OutputFormatterStyle('white', 'magenta'))->apply(' '.$method.' ');
		}else{
			$message = 'REQUEST ['.$method.'] ';
		}
		$message.=' '.$messageText;

		$this->write($message);
	}

}