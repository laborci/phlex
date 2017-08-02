<?php namespace Phlex\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateAdmin extends Command{
	protected function configure() {
		$this
				->setName('px:create-admin')
				->setDescription('Creates admin site')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

//		$root = realpath(__DIR__.'/../../../../..');
//		$helper = $this->getHelper('question');
//		$question = new Question('Please enter the domain for your application! ', 'yourawesome.com');
//		$domain = $helper->ask($input, $output, $question);
//
//		$localConf = file_get_contents(__DIR__.'/../../templates/config/local.conf');
//		$localConf = str_replace('{{domain}}', $domain, $localConf);
//		$localConf = str_replace('{{path}}', $root, $localConf);
//		file_put_contents($root.'/config/local.conf', $localConf);
//
//
//		$question = new Question('Please enter the default database user! ', 'root');
//		$dbuser = $helper->ask($input, $output, $question);
//
//		$question = new Question('Please enter the default database password! ', 'root');
//		$dbpass = $helper->ask($input, $output, $question);
//
//		$question = new Question('Please enter the default database host! ', '127.0.0.1');
//		$dbhost = $helper->ask($input, $output, $question);
//
//		$question = new Question('Please enter the default database port! ', '3306');
//		$dbport = $helper->ask($input, $output, $question);
//
//		$question = new Question('Please enter the default database name! ', 'phlex');
//		$db = $helper->ask($input, $output, $question);
//
//		$configPhp = file_get_contents(__DIR__.'/../../templates/config/config.php');
//		$configPhp = str_replace('{{dbuser}}', $dbuser, $configPhp);
//		$configPhp = str_replace('{{dbpass}}', $dbpass, $configPhp);
//		$configPhp = str_replace('{{dbhost}}', $dbhost, $configPhp);
//		$configPhp = str_replace('{{dbport}}', $dbport, $configPhp);
//		$configPhp = str_replace('{{db}}', $db, $configPhp);
//		file_put_contents($root.'/config/config.php', $configPhp);

	}

}
