<?php namespace Phlex\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;


class Clientversion extends Command {
	protected function configure() {
		$this
			->setName('px:clientversion')
			->setAliases(['cv'])
			->setDescription('Creates config files');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$style = new SymfonyStyle($input, $output);

		$root = realpath(__DIR__ . '/../../../../..');
		$version = file_get_contents('var/clientversion');
		$version ++;
		file_put_contents('var/clientversion', $version);
		$style->success('Version: ' + $version);
	}

}
