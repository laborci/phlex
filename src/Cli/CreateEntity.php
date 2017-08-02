<?php namespace Phlex\Cli;

use App\Env;
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
		$dir = Env::instance()->path_root.'App/Entity/'.$name;
		@mkdir($dir);
		if(!file_exists(Env::instance()->path_root.'App/Entity/'.$name.'/'.$name.'.php')){
			file_put_contents(Env::instance()->path_root.'App/Entity/'.$name.'/'.$name.'.php', $this->getEntityClass($name));
			$output->writeln('<info>💾  '.'App/Entity/'.$name.'/'.$name.'.php'.'</info>');
		}
		if(!file_exists(Env::instance()->path_root.'App/Entity/'.$name.'/'.$name.'Repository.php')){
			file_put_contents(Env::instance()->path_root.'App/Entity/'.$name.'/'.$name.'Repository.php', $this->getRepositoryClass($name));
			$output->writeln('<info>💾  '.'App/Entity/'.$name.'/'.$name.'Repository.php'.'</info>');
		}
		if(!file_exists(Env::instance()->path_root.'App/Entity/'.$name.'/'.$name.'Model.php')){
			file_put_contents(Env::instance()->path_root.'App/Entity/'.$name.'/'.$name.'Model.php', $this->getModelClass($name));
			$output->writeln('<info>💾  '.'App/Entity/'.$name.'/'.$name.'Model.php'.'</info>');
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

		return str_replace('{{name}}', $name, file_get_contents(__DIR__.'/../../templates/entity/entity.php'));

		return "<?php namespace App\\Entity\\".$name.";

/**
 * px: @method static \\App\\Entity\\".$name."\\".$name."Repository repository()
 * px: @method static \\App\\Entity\\".$name."\\".$name."Model model()
 * px: @property-read integer                       \$id
 */

class ".$name." extends \\Phlex\\RedFox\\Entity{

}
";
	}

	protected function getRepositoryClass($name){
		return str_replace('{{name}}', $name, file_get_contents(__DIR__.'/../../templates/entity/repository.php'));

		return "<?php namespace App\\Entity\\".$name.";

/**
 * @method \\App\\Entity\\".$name."\\".$name." pick(int \$id)
 * @method \\App\\Entity\\".$name."\\".$name."[] collect(array \$id_list)
 */

class ".$name."Repository extends \\Phlex\\RedFox\\Repository {

}";
	}

	protected function getModelClass($name){
		return str_replace('{{name}}', $name, file_get_contents(__DIR__.'/../../templates/entity/model.php'));

		return "<?php namespace App\\Entity\\".$name.";

class ".$name."Model extends \\Phlex\\RedFox\\Model{

	protected function fields(){
	}
	
	protected function decorateFields(){}
	protected function relations(){}
	protected function attachments(){}

}";
	}

}
