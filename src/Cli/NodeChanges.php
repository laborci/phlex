<?php namespace Phlex\Cli;

use App\Env;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class NodeChanges extends Command {

	protected function configure() {
		$this->setName('dev:nodechanges')->setAliases(['devnode'])->setDescription('Looking for changes in node_modules/phlex-*');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$style = new SymfonyStyle($input, $output);

		$style->title('Looking for changes');
		$folders = glob(Env::get('path_root') . 'node_modules/phlex-*');
		foreach ($folders as $folder) {
			if (is_dir($folder)) {
				chdir($folder);
				exec("publish-diff --filter='lib/**'", $output);
				if(trim(join('',$output))){
					$style->warning(basename($folder));
				}else{
					$style->success(basename($folder));
				}
				$output = '';
			}
		}

	}

}
