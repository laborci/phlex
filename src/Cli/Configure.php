<?php namespace Phlex\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;


class Configure extends Command{
	protected function configure() {
		$this
				->setName('px:configure')
				->setDescription('Creates config files')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$style = new SymfonyStyle($input, $output);

		$root = realpath(__DIR__.'/../../../../..');
		$helper = $this->getHelper('question');

		$style->title('Configure Phlex instance');

		$domain = $style->askQuestion(new Question('Application domain', 'yourawesome.com'));


		$localConf = file_get_contents(__DIR__.'/../../templates/config/local.conf.template');
		$localConf = str_replace('{{domain}}', $domain, $localConf);
		$localConf = str_replace('{{path}}', $root, $localConf);
		file_put_contents($root.'/config/local.conf', $localConf);
		$style->success('💾  /config/local.conf');

//		$htaccess = file_get_contents(__DIR__.'/../../templates/config/htaccess.template');
//		$htaccess = str_replace('{{path}}', $root, $htaccess);
//		file_put_contents($root.'/public/.htaccess', $htaccess);
//		$style->success('💾  /public/.htaccess');

		$style->section('Default database connection:');

		$dbhost = $style->askQuestion(new Question('host', '127.0.0.1'));
		$db = $style->askQuestion(new Question('database', 'phlex'));
		$dbuser = $style->askQuestion(new Question('user', 'root'));
		$dbpass = $style->askQuestion(new Question('password', 'root'));
		$dbport = $style->askQuestion(new Question('port', '3306'));
		$random = md5(time());

		$configPhp = file_get_contents(__DIR__.'/../../templates/config/config.template');
		$configPhp = str_replace('{{domain}}', $domain, $configPhp);
		$configPhp = str_replace('{{dbuser}}', $dbuser, $configPhp);
		$configPhp = str_replace('{{dbpass}}', $dbpass, $configPhp);
		$configPhp = str_replace('{{dbhost}}', $dbhost, $configPhp);
		$configPhp = str_replace('{{dbport}}', $dbport, $configPhp);
		$configPhp = str_replace('{{db}}', $db, $configPhp);
		$configPhp = str_replace('{{random}}', $random, $configPhp);
		file_put_contents($root.'/config/config.php', $configPhp);
		$style->success('💾  /config/config.php');
	}

}
