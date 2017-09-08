<?php namespace Phlex\Cli;

use App\Env;
use CaseHelper\CaseHelperFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class CreateEntity extends Command{
	protected function configure() {
		$this
			->setName('px:create-entity')
			->setAliases(['create'])
			->setDescription('Creates new entity')
			->addArgument('name', InputArgument::REQUIRED)
			->addArgument('table')
			->addArgument('database')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$style = new SymfonyStyle($input, $output);
		$name = ucfirst($input->getArgument('name'));
		$database = $input->getArgument('database');
		$table = $input->getArgument('table');

		$style->title('Creating entity: '.$name);


		$style->write('Creating files ... ');
		$root = Env::get('path_root');
		$dir = 'App/Entity/'.$name;
		if(!is_dir($root.$dir)){
			mkdir($root.$dir);
		}

		$file = $dir.'/'.$name.'.php';
		if(!file_exists($root.$file)){
			file_put_contents($root.$file, $this->getEntityClass($name));
		}

		$file = $dir.'/'.$name.'Repository.php';
		if(!file_exists($root.$file)){
			file_put_contents($root.$file, $this->getRepositoryClass($name));
		}

		$file = $dir.'/'.$name.'Model.php';
		if(!file_exists($root.$file)){
			file_put_contents($root.$file, $this->getModelClass($name, $database, $table));
		}

		$style->writeln('done.');

		$command = $this->getApplication()->find('px:update-entity');
		$updateInput = new ArrayInput(['command' => 'px:update-entity', 'name' => $name]);
		$command->run($updateInput, $output);

		$style->writeln('');

	}

	protected function parseBlock($docblock){
		$lines = explode("\n", $docblock);
		$block = [];
		foreach($lines as $line){
			$line = ltrim(trim($line), " \t*");
			$block[] = $line;
		}
		return $block;
	}

	protected function getEntityClass($name){
		$class = file_get_contents(__DIR__.'/../../templates/entity/entity.template');
		$class = str_replace('{{name}}', $name, $class);
		return $class;
	}

	protected function getRepositoryClass($name){
		$class = file_get_contents(__DIR__.'/../../templates/entity/repository.template');
		$class = str_replace('{{name}}', $name, $class);
		return $class;
	}
	protected function getDataSourceClass($name, $database, $table){
		$class = file_get_contents(__DIR__.'/../../templates/entity/dataSource.template');
		$class = str_replace('{{name}}', $name, $class);
		$class = str_replace('{{table}}', $table, $class);
		$class = str_replace('{{database}}', $database, $class);
		return $class;
	}

	protected function getModelClass($name, $database, $table){
		$table = is_null($table) ? CaseHelperFactory::make(CaseHelperFactory::INPUT_TYPE_CAMEL_CASE)->toSnakeCase($name) : $table;
		$database = is_null($database) ? 'database' : $database;
		$class = file_get_contents(__DIR__.'/../../templates/entity/model.template');
		$class = str_replace('{{name}}', $name, $class);
		$class = str_replace('{{table}}', $table, $class);
		$class = str_replace('{{database}}', $database, $class);
		return $class;
	}

}
