<?php namespace Phlex\Cli;

class Application extends \Symfony\Component\Console\Application {

	// http://symfony.com/doc/current/components/console/introduction.html

	public static function cli($debug = false){

		if($debug){
			ini_set('display_errors', true);
			error_reporting(E_ALL);
		}

		$application = new static();

		$application->add(new CreateEntity());
		$application->add(new UpdateEntityModel());
		$application->add(new UpdateEntityDocBlock());

		$application->run();
	}
}