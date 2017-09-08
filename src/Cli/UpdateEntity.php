<?php namespace Phlex\Cli;

use App\Env;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class UpdateEntity extends Command{
	protected function configure() {
		$this
			->setName('px:update-entity')
			->setDescription('Updates model from database table')
			->addArgument('name', InputArgument::REQUIRED)
			->addOption('autodecorate')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$style = new SymfonyStyle($input, $output);

		$style->write('Updating entity fields ... ');

		$name = $input->getArgument('name');
		$class = "\\App\\Entity\\".$name."\\".$name;

		try {
			/** @var \Phlex\RedFox\Repository $repository */
			$repository = $class::repository();
			/** @var \Phlex\RedFox\Model $model */
			$model = $class::model();
		}catch (\Exception $exception){
			print_r($exception);
		}

		$table =  $repository->getDataSource()->getTable();
		$access = $repository->getDataSource()->getAccess();

		$fields = [];
		foreach ($access->getFieldData($table) as $db_field) {
			$field = ['label'=>$db_field['Field'],'data'=>[]];
			$field['data'][] = $this->fieldSelector($db_field['Type'], $db_field['Field']).'::class';
			$options = $access->getEnumValues($table, $db_field['Field']);
			if(count($options)) $field['data'][] = $options;
			$fields[$db_field['Field']] = $field;
		}

		foreach ($model->fields() as $key=>$field){
			$fieldName = trim($key, '@!');
			if(array_key_exists($fieldName, $fields)) {
				if (strpos($key, '@') !== false) {
					$fields[$fieldName]['label'] = '@'.	$fields[$fieldName]['label'];
				}
				if (strpos($key, '!') !== false) {
					$fields[$fieldName]['label'] = '!'.	$fields[$fieldName]['label'];
					$fields[$fieldName]['data'] = $field;
					$fields[$fieldName]['data'][0] = '\\'.$fields[$fieldName]['data'][0].'::class';
				}
			}
		}

		$labelLength = 0;
		foreach ($fields as $field){
			if(strlen($field['label'])>$labelLength) $labelLength = strlen($field['label']);
		}


		$body = ["\t\treturn ["];
		$encoder = new \Riimu\Kit\PHPEncoder\PHPEncoder();
		foreach ($fields as $field){
			$fieldClass = array_shift($field['data']);
			if(count($field['data'])){
				$data = ', '.substr($encoder->encode($field['data'], ['array.inline' => true]),1,-1);
			}else{
				$data = '';
			}
			$body[] = "\t\t\t'".str_pad($field['label']."'", $labelLength+1, ' ').' => ['.$fieldClass.$data.'],';
		}

		$body[] = "\t\t];";

		$ref = new \ReflectionClass($class.'Model');
		$fieldsMethod = $ref->getMethod('fields');

		$start = $fieldsMethod->getStartLine();
		$end = $fieldsMethod->getEndLine()-1;
		$source = file($ref->getFileName());

		array_splice($source, $start, $end - $start, $body);
		array_walk($source, function(&$line){ $line = trim($line, "\n");});
		file_put_contents($ref->getFileName(), join("\n", $source));


		$style->writeln('done.');


		exec("./phlex px:decorate-entity " . $name, $lines, $return);
		foreach ($lines as $line) {
			$output->writeln($line);
		}

	}

	protected function fieldSelector($dbtype, $fieldName){
		if($dbtype == 'tinyint(1)') return '\Phlex\RedFox\Fields\BoolField';
		if($dbtype == 'date') return '\Phlex\RedFox\Fields\DateField';
		if($dbtype == 'datetime') return '\Phlex\RedFox\Fields\DateTimeField';
		if($dbtype == 'float') return '\Phlex\RedFox\Fields\FloatField';

		if(strpos($dbtype, 'int(11) unsigned')===0 && (substr($fieldName, -2) == 'Id' || $fieldName == 'id')) return '\Phlex\RedFox\Fields\IdField';
		if(strpos($dbtype, 'int')===0) return '\Phlex\RedFox\Fields\IntegerField';
		if(strpos($dbtype, 'tinyint')===0) return '\Phlex\RedFox\Fields\IntegerField';
		if(strpos($dbtype, 'smallint')===0) return '\Phlex\RedFox\Fields\IntegerField';
		if(strpos($dbtype, 'mediumint')===0) return '\Phlex\RedFox\Fields\IntegerField';
		if(strpos($dbtype, 'bigint')===0) return '\Phlex\RedFox\Fields\IntegerField';

		if(strpos($dbtype, 'varchar')===0) return '\Phlex\RedFox\Fields\StringField';
		if(strpos($dbtype, 'char')===0) return '\Phlex\RedFox\Fields\StringField';
		if(strpos($dbtype, 'text')===0) return '\Phlex\RedFox\Fields\StringField';
		if(strpos($dbtype, 'text')===0) return '\Phlex\RedFox\Fields\StringField';
		if(strpos($dbtype, 'tinytext')===0) return '\Phlex\RedFox\Fields\StringField';
		if(strpos($dbtype, 'mediumtext')===0) return '\Phlex\RedFox\Fields\StringField';
		if(strpos($dbtype, 'longtext')===0) return '\Phlex\RedFox\Fields\StringField';

		if(strpos($dbtype, 'set')===0) return '\Phlex\RedFox\Fields\SetField';
		if(strpos($dbtype, 'enum')===0) return '\Phlex\RedFox\Fields\EnumField';

		return '\Phlex\RedFox\Fields\UnsupportedField';
	}

}
