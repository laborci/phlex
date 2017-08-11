<?php namespace Phlex\Cli;

use App\Env;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class GetEnv extends Command{
	protected function configure() {
		$this
			->setName('px:getenv')
			->setDescription('Shows the available Env::get() keys and values');
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$style = new SymfonyStyle($input, $output);
		$style->title('Available in Env');

		$cfg = Env::get();
		$config = [];

		foreach ($cfg as $key=>$value){
			$config[] = [$key, $value];
		}
		$style->table(
				array('Key', 'Value'),
				$config
		);
 	}

}
