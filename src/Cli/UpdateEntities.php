<?php namespace Phlex\Cli;

use App\Env;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateEntities extends Command {

	protected function configure() {
		$this->setName('px:update-entities')->setAliases(['update'])->setDescription('Updates model from database table');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$style = new SymfonyStyle($input, $output);

		$style->title('Updating all entites');
		$folders = glob(Env::get('path_root') . 'App/Entity/*');
		foreach ($folders as $folder) {
			if (is_dir($folder)) {
				$name = basename($folder);
				$command = $this->getApplication()->find('px:create-entity');
				$updateInput = new ArrayInput(['command' => 'px:create-entity', 'name' => $name]);
				$command->run($updateInput, $output);
			}
		}

	}

}
