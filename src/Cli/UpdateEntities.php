<?php namespace Phlex\Cli;

use App\Env;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class UpdateEntities extends Command{
	protected function configure() {
		$this
			->setName('px:update-entities')
			->setDescription('Updates model from database table')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$style = new SymfonyStyle($input, $output);

		$style->title('Updating all entites');
		$folders = glob(Env::get('path_root').'App/Entity/*');
		foreach ($folders as $folder){
			if(is_dir($folder)){
				$name = basename($folder);
				$command = $this->getApplication()->find('px:update-entity');
				$updateInput = new ArrayInput(['command' => 'px:update-entity', 'name' => $name, '--autodecorate'=>true]);
				$command->run($updateInput, $output);			}
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
