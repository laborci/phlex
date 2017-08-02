<?php namespace Phlex\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class Configure extends Command{
	protected function configure() {
		$this
				->setName('px:configure')
				->setDescription('Creates config files')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$root = realpath(__DIR__.'/../../../../..');
		$helper = $this->getHelper('question');
		$question = new Question('Application domain (yourawesome.com) :', 'yourawesome.com');
		$domain = $helper->ask($input, $output, $question);

		$localConf = file_get_contents(__DIR__.'/../../templates/config/local.conf.template');
		$localConf = str_replace('{{domain}}', $domain, $localConf);
		$localConf = str_replace('{{path}}', $root, $localConf);
		file_put_contents($root.'/config/local.conf', $localConf);

		$output->writeln('<info>💾  /config/local.conf</info>');

		$output->writeln('');
		$output->writeln('Default database:');

		$question = new Question('- host (127.0.0.1) :', '127.0.0.1');
		$dbhost = $helper->ask($input, $output, $question);

		$question = new Question('- name (phlex) :', 'phlex');
		$db = $helper->ask($input, $output, $question);

		$question = new Question('- user (root) :', 'root');
		$dbuser = $helper->ask($input, $output, $question);

		$question = new Question('- password (no password) :', '');
		$dbpass = $helper->ask($input, $output, $question);

		$question = new Question('- port (3306) :', '3306');
		$dbport = $helper->ask($input, $output, $question);

		$random = md5(time());

		$configPhp = file_get_contents(__DIR__.'/../../templates/config/config.template');
		$configPhp = str_replace('{{dbuser}}', $dbuser, $configPhp);
		$configPhp = str_replace('{{dbpass}}', $dbpass, $configPhp);
		$configPhp = str_replace('{{dbhost}}', $dbhost, $configPhp);
		$configPhp = str_replace('{{dbport}}', $dbport, $configPhp);
		$configPhp = str_replace('{{db}}', $db, $configPhp);
		$configPhp = str_replace('{{random}}', $random, $configPhp);
		file_put_contents($root.'/config/config.php', $configPhp);

		$output->writeln('<info>💾  /config/config.php</info>');

	}

}
