<?php namespace Phlex\Cli;

abstract class Application extends \Symfony\Component\Console\Application {

	// http://symfony.com/doc/current/components/console/introduction.html

	/**
	 * @return \Symfony\Component\Console\Command\Command[]
	 */
	abstract public static function getCommands():array;

	public static function cli($debug = false){
		if($debug){
			ini_set('display_errors', true);
			error_reporting(E_ALL);
		}

		$application = new static();

		$application->add(new CreateEntity());
		$application->add(new UpdateEntity());
		$application->add(new DecorateEntity());
		$application->add(new Configure());

		$commands = static::getCommands($application);

		foreach($commands as $command){
			$application->add($command);
		}

		$application->run();
	}
}