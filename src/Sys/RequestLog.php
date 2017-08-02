<?php namespace Phlex\Sys;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;


class RequestLog extends Log{

	protected function send($type, $messageText, $context) {
		$method = $context['method'];

		if($this->colorlog){
			$message =
				(new OutputFormatterStyle('black', 'cyan'))->apply(' REQUEST ').
				(new OutputFormatterStyle('white', 'magenta'))->apply(' '.$method.' ');
		}else{
			$message = 'SQL / '.$method.' :';
		}
		$message.=' '.$messageText;

		$this->write($message);
	}

}