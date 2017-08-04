<?php namespace Phlex\Cli;

use App\Env;
use Phlex\RedFox\Relation\BackReference;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class DecorateEntity extends Command{
	protected function configure() {
		$this
			->setName('px:decorate-entity')
			->setDescription('Updates entity docblock from model')
			->addArgument('name', InputArgument::REQUIRED)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$style = new SymfonyStyle($input, $output);

		$name = ucfirst($input->getArgument('name'));
		$class = "\\App\\Entity\\".$name."\\".$name;

		$style->title('Decorating entity '.$name);

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
			$generatedLines[] = ' * px: @property'.(!$fieldObj->isWritable() ? '-read' : '').' '.$fieldObj->getDataType().' $'.$field;
		}

		$relations = $model->getRelations();
		foreach ($relations as $relation){
			$relationObj = $model->getRelation($relation);
			$generatedLines[] = ' * px: @property-read'.' '.$relationObj->getRelatedClass().' $'.$relation;
			$style->warning(get_class($relationObj));
			if($relationObj instanceof BackReference){
				$generatedLines[] = ' * px: @method'.' '.$relationObj->getRelatedClass().' '.$relation.'($order=null, $limit=null, $offset=null)';
			}
		}

		$attahcmentGroups = $model->getAttachmentGroups();
		foreach ($attahcmentGroups as $attahcmentGroup){
			$generatedLines[] = ' * px: @property-read \\Phlex\\RedFox\\Attachment\\AttachmentManager $'.$attahcmentGroup;
		}


		$newBlock = '/**'."\n".join("\n", $generatedLines)."\n".join("\n",$lines)."\n".' */';

		$source = file_get_contents($ref->getFileName());
		$source = str_replace($doc, $newBlock, $source);

		file_put_contents($ref->getFileName(), $source);
		$style->success('💾  '.substr($ref->getFileName(), strlen(Env::get('path_root'))));

		exit(0);
	}


}
