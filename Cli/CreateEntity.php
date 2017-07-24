<?php namespace Phlex\Cli;

use App\Env;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateEntity extends Command{
	protected function configure() {
		$this
			->setName('entity:create')
			->setDescription('Creates new entity')
			->addArgument('name', InputArgument::REQUIRED);
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$name = $input->getArgument('name');
		$dir = Env::instance()->path_root.'App/Entity/'.$name;
		@mkdir($dir);
		if(!file_exists(Env::instance()->path_root.'App/Entity/'.$name.'/'.$name.'.php')) file_put_contents(Env::instance()->path_root.'App/Entity/'.$name.'/'.$name.'.php', $this->getEntityClass($name));
		if(!file_exists(Env::instance()->path_root.'App/Entity/'.$name.'/'.$name.'Repository.php')) file_put_contents(Env::instance()->path_root.'App/Entity/'.$name.'/'.$name.'Repository.php', $this->getRepositoryClass($name));
		if(!file_exists(Env::instance()->path_root.'App/Entity/'.$name.'/'.$name.'Model.php')) file_put_contents(Env::instance()->path_root.'App/Entity/'.$name.'/'.$name.'Model.php', $this->getModelClass($name));

		/*$class = User::class;

		$ref = new \ReflectionClass($class);

		$docblock = $ref->getDocComment();
		print_r($this->parseBlock($docblock));

		$output->writeln($ref->getDocComment());
		$output->writeln($ref->getFileName());
		$methods = $ref->getMethods();

		foreach($methods as $method){
			if(substr($method->name,0,5) == '__get'){
				$output->writeln($method->getDocComment());
			}
		}

		$model = $class::model();
		$fields = $model->getFields();
		print_r($fields);

		$output->writeln('Done...');
		*/
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
		return "<?php namespace App\\Entity\\".$name.";

/**
 * @method static \\App\\Entity\\".$name."\\".$name."Repository repository()
 * @method static \\App\\Entity\\".$name."\\".$name."Model model()
 * @property-read integer                       \$id
 */

class ".$name." extends \\Phlex\\RedFox\\Entity{

}
";
	}

	protected function getRepositoryClass($name){
		return "<?php namespace App\\Entity\\".$name.";

/**
 * @method \\App\\Entity\\".$name."\\".$name." pick(int \$id)
 * @method \\App\\Entity\\".$name."\\".$name."[] collect(array \$id_list)
 */

class ".$name."Repository extends \\Phlex\\RedFox\\Repository {

}";
	}

	protected function getModelClass($name){
		return "<?php namespace App\\Entity\\".$name.";

class ".$name."Model extends \\Phlex\\RedFox\\Model{

	protected function fields(){
		\$this->addField('id', new \\Phlex\\RedFox\\Fields\\IdField());
	}
	
	protected function decorateFields(){}
	protected function relations(){}
	protected function attachments() {}

}";
	}

}
