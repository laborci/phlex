<?php namespace Phlex\Sys;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;


class SqlLog extends Log{

	protected function send($type, $messageText, $context) {
		$method = $context['method'];

		if($this->colorlog){
			$message =
				(new OutputFormatterStyle('white', 'magenta'))->apply(' SQL ').
				(new OutputFormatterStyle('cyan', 'blue'))->apply(' '.$method.' ');
		}else{
			$message = 'SQL / '.$method.' :';
		}
		$message.=' '.$messageText;

		$this->write($message);
	}

}