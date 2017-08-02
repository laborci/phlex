<?php namespace Phlex\Cli;

use App\Env;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DecorateEntity extends Command{
	protected function configure() {
		$this
			->setName('px:decorate-entity')
			->setDescription('Updates entity docblock from model')
			->addArgument('name', InputArgument::REQUIRED);
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$name = $input->getArgument('name');
		$class = "\\App\\Entity\\".$name."\\".$name;

		/** @var \Phlex\RedFox\Repository $repository */
		$repository = $class::repository();
		/** @var \Phlex\RedFox\Model $model */
		$model = $class::model();

		$ref = new \ReflectionClass($class);

		$doc = $ref->getDocComment();

		$lines = explode("\n", $doc);
		array_pop($lines);
		array_shift($lines);
		foreach ($lines as $i=>$line){
			if(strpos($line,' * px: ') === 0){
				unset($lines[$i]);
				//$lines[$i] = '';
			}
		}
		$lines = array_values($lines);

		$generatedLines = [
		" * px: @method static \\App\\Entity\\".$name."\\".$name."Repository repository()",
		" * px: @method static \\App\\Entity\\".$name."\\".$name."Model model()"
		];

		/** @var \Phlex\RedFox\Model $model */
		$model = $class::model();

		$fields = $model->getFields();
		foreach ($fields as $field){
			$fieldObj = $model->getField($field);
			$line = ' * px: @property'.(!$fieldObj->isWritable() ? '-read' : '').' '.$fieldObj->getDataType().' $'.$field;
			$generatedLines[] = $line;
		}

		$relations = $model->getRelations();
		foreach ($relations as $relation){
			$relationObj = $model->getRelation($relation);
			$line = ' * px: @property-read'.' '.$relationObj->getRelatedClass().' $'.$relation;
			$generatedLines[] = $line;
		}

		$attahcmentGroups = $model->getAttachmentGroups();
		foreach ($attahcmentGroups as $attahcmentGroup){
			$line = ' * px: @property-read \\Phlex\\RedFox\\Attachment\\AttachmentManager $'.$attahcmentGroup;
			$generatedLines[] = $line;
		}


		$newBlock = '/**'."\n".join("\n", $generatedLines)."\n".join("\n",$lines)."\n".' */';

		$source = file_get_contents($ref->getFileName());
		$source = str_replace($doc, $newBlock, $source);

		file_put_contents($ref->getFileName(), $source);
		$output->writeln('<info>💾  '.substr($ref->getFileName(), strlen(Env::instance()->path_root)).'</info>');

	}


}
