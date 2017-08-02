<?php namespace Phlex\Cli;

use App\Env;
use CaseHelper\CaseHelperFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateEntity extends Command{
	protected function configure() {
		$this
			->setName('px:create-entity')
			->setDescription('Creates new entity')
			->addArgument('name', InputArgument::REQUIRED);
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$name = $input->getArgument('name');
		$dir = Env::get('path_root').'App/Entity/'.$name;
		@mkdir($dir);

		if(!file_exists($dir.'/'.$name.'.php')){
			file_put_contents($dir.'/'.$name.'.php', $this->getEntityClass($name));
			$output->writeln('<info>💾  '.'App/Entity/'.$name.'/'.$name.'.php'.'</info>');
		}
		if(!file_exists($dir.'/'.$name.'Repository.php')){
			file_put_contents($dir.'/'.$name.'Repository.php', $this->getRepositoryClass($name));
			$output->writeln('<info>💾  '.'App/Entity/'.$name.'/'.$name.'Repository.php'.'</info>');
		}
		if(!file_exists($dir.'/'.$name.'Model.php')){
			file_put_contents($dir.'/'.$name.'Model.php', $this->getModelClass($name));
			$output->writeln('<info>💾  '.'App/Entity/'.$name.'/'.$name.'Model.php'.'</info>');
		}
		if(!file_exists($dir.'/'.$name.'DataSource.php')){
			file_put_contents($dir.'/'.$name.'DataSource.php', $this->getDataSourceClass($name));
			$output->writeln('<info>💾  '.'App/Entity/'.$name.'/'.$name.'DataSource.php'.'</info>');
		}
		$output->writeln('done.');
		$output->writeln('Run command: <info>phlex px:update-entity '.$name.'</info>');
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
	protected function getDataSourceClass($name){
		$table = CaseHelperFactory::make(CaseHelperFactory::INPUT_TYPE_CAMEL_CASE)->toSnakeCase($name);
		$class = file_get_contents(__DIR__.'/../../templates/entity/dataSource.template');
		$class = str_replace('{{name}}', $name, $class);
		$class = str_replace('{{table}}', $table, $class);
		return $class;
	}

	protected function getModelClass($name){
		$class = file_get_contents(__DIR__.'/../../templates/entity/model.template');
		$class = str_replace('{{name}}', $name, $class);
		return $class;	}

}
