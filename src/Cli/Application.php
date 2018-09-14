<?php namespace Phlex\Cli;

use App\Env;


abstract class Application extends \Symfony\Component\Console\Application {

	// http://symfony.com/doc/current/components/console/introduction.html

	/**
	 * @return \Symfony\Component\Console\Command\Command[]
	 */
	abstract public static function getCommands():array;

	public static function cli(){

		$application = new static();

		$application->add(new CreateEntity());
		$application->add(new UpdateEntities());
		$application->add(new Configure());
		$application->add(new GetEnv());
		$application->add(new NodeChanges());

		$commands = static::getCommands($application);

		foreach($commands as $command){
			$application->add($command);
		}

		$application->run();
	}
}