<?php namespace Phlex\Cli;

use App\Env;
use App\ServiceManager;
use CaseHelper\CaseHelperFactory;
use Phlex\Database\Access;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class CreateEntity extends Command {
	protected function configure() {
		$this
			->setName('px:create-entity')
			->setAliases(['create'])
			->setDescription('Creates new entity')
			->addArgument('name', InputArgument::REQUIRED)
			->addArgument('table')
			->addArgument('database');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$name = ucfirst($input->getArgument('name'));

		$this->output = new SymfonyStyle($input, $output);
		$this->output->title('Creating entity: ' . $name);

		$root = Env::get('path_root');
		$entityDirectory = $root . 'App/Entity/' . $name;
		$entityHelperDirectory = $entityDirectory . '/Helpers';
		$templateDirectory = __DIR__ . '/../../templates/redfox';
		$templateHelperDirectory = $templateDirectory . '/Helpers';

		if (!is_dir($entityDirectory)) mkdir($entityDirectory);
		if (!is_dir($entityHelperDirectory)) mkdir($entityHelperDirectory);

		if (file_exists($entityHelperDirectory . '/source.php')) {
			list ($table, $database) = include($entityHelperDirectory . '/source.php');
		} else {
			$database = $input->getArgument('database');
			$table = $input->getArgument('table');
			$table = is_null($table) ? CaseHelperFactory::make(CaseHelperFactory::INPUT_TYPE_CAMEL_CASE)->toSnakeCase($name) : $table;
			$database = is_null($database) ? 'database' : $database;
		}

		$dictionary = [
			'name' => $name,
			'database' => $database,
			'table' => $table,
		];

		$this->translateFile($entityDirectory . '/' . $name . '.php', $templateDirectory . '/entity.template.php', $dictionary);
		$this->translateFile($entityDirectory . '/' . $name . 'Repository.php', $templateDirectory . '/repository.template.php', $dictionary);
		$this->translateFile($entityDirectory . '/' . $name . 'Model.php', $templateDirectory . '/model.template.php', $dictionary);
		$this->translateFile($entityHelperDirectory . '/source.php', $templateHelperDirectory . '/source.template.php', $dictionary);
		$this->translateFile($entityHelperDirectory . '/RepositoryTrait.php', $templateHelperDirectory . '/RepositoryTrait.template.php', $dictionary);
		$this->translateFile($entityHelperDirectory . '/EntityTrait.php', $templateHelperDirectory . '/EntityTrait.template.php', $dictionary);
		$this->translateFile($entityHelperDirectory . '/Finder.php', $templateHelperDirectory . '/Finder.template.php', $dictionary);
		$this->translateFile($entityHelperDirectory . '/fields.php', $templateHelperDirectory . '/fields.template.php', $dictionary);

		$this->updateFields($database, $table, $entityHelperDirectory . '/fields.php');

		$this->createModelTrait($database, $table, $name,
			$templateHelperDirectory . '/ModelTrait.template.php',
			$entityHelperDirectory . '/ModelTrait.php');

		$this->createEntityInterface($database, $table, $name,
			$templateHelperDirectory . '/EntityInterface.template.php',
			$entityHelperDirectory . '/EntityInterface.php');

		$this->createEntityTrait($database, $table, $name,
			$templateHelperDirectory . '/EntityTrait.template.php',
			$entityHelperDirectory . '/EntityTrait.php');

		$this->output->writeln('');

	}

	protected function createEntityTrait($database, $table, $name, $source, $destination) {
		$fields = '';
		$class = "\\App\\Entity\\" . $name . "\\" . $name;
		/** @var \Phlex\RedFox\Model $model */
		$model = $class::model();

		$generatedLines = [];

		$fields = $model->getFields();
		foreach ($fields as $field) {
			$fieldObj = $model->getField($field);
			$generatedLines[] = ' * @property' . ($fieldObj->readonly() ? '-read' : '') . ' ' . $fieldObj->getDataType() . ' $' . $field;
		}

		$relations = $model->getRelations();
		foreach ($relations as $relation) {
			$relationObj = $model->getRelation($relation);
			$generatedLines[] = ' * @property-read' . ' ' . $relationObj->getRelatedClass() . ' $' . $relation;
			if ($relationObj instanceof BackReference) {
				$generatedLines[] = ' * @method' . ' ' . $relationObj->getRelatedClass() . ' ' . $relation . '($order=null, $limit=null, $offset=null)';
			}
		}

		$attahcmentGroups = $model->getAttachmentGroups();
		foreach ($attahcmentGroups as $attahcmentGroup) {
			$generatedLines[] = ' * @property-read \\Phlex\\RedFox\\Attachment\\AttachmentManager $' . $attahcmentGroup;
		}

		$fields = join("\n", $generatedLines);

		$dictionary = [
			'name' => $name,
			'database' => $database,
			'table' => $table,
			'fields' => $fields,
		];
		$this->translateFile($destination, $source, $dictionary, true);
	}

	protected function createEntityInterface($database, $table, $name, $source, $destination) {
		$constants = '';
		$access = new Access(Env::get($database));
		foreach ($access->getFieldData($table) as $db_field) {
			$label = $db_field['Field'];
			$options = $access->getEnumValues($table, $db_field['Field']);
			foreach ($options as $option) {
				$constant = str_replace(' ', '_', strtoupper($label . '_' . $option));
				$constants .= "\tconst $constant = '$option';\n";
			}
		}
		$dictionary = [
			'name' => $name,
			'database' => $database,
			'table' => $table,
			'constants' => $constants,
		];
		$this->translateFile($destination, $source, $dictionary, true);
	}

	protected function createModelTrait($database, $table, $name, $source, $destination) {
		$fields = '';
		$access = new Access(Env::get($database));
		foreach ($access->getFieldData($table) as $db_field) {
			$type = $this->selectRedfoxField($db_field, $db_field['Field']);
			$label = $db_field['Field'];
			$fields .= ' * px: @property-read ' . $type . ' $' . $label . "\n";
		}

		$dictionary = [
			'name' => $name,
			'database' => $database,
			'table' => $table,
			'fields' => $fields,
		];
		$this->translateFile($destination, $source, $dictionary, true);
	}

	protected function translateFile($destination, $source, $dictionary, $force = false) {
		if (!file_exists($destination) || $force) {
			$this->output->write('Creating file: ' . $destination . ' ... ');
			$output = file_get_contents($source);
			foreach ($dictionary as $key => $value) {
				$output = str_replace('{{' . $key . '}}', $value, $output);
			}
			file_put_contents($destination, $output);
			$this->output->writeln('DONE');
		}
	}

	protected function updateFields($database, $table, $destination) {
		$this->output->write('Creating file: ' . $destination . ' ... ');
		$access = new Access(Env::get($database));

		$fields = include($destination);
		$modifiers = [];
		foreach ($fields as $field => $rest) {
			$fieldname = trim($field, '@!');
			$modifiers[$fieldname] = '';
			if (strpos($field, '@') !== false) $modifiers[$fieldname] .= '@';
			if (strpos($field, '!') !== false) $modifiers[$fieldname] .= '!';
			$fields[$fieldname] = $rest;
		}

		$encoder = new \Riimu\Kit\PHPEncoder\PHPEncoder();

		$output = '<?php return [' . "\n";
		foreach ($access->getFieldData($table) as $db_field) {
			$label = $modifiers[$db_field['Field']] . $db_field['Field'];

			if (strpos($label, '!') !== false) {
				$output .= "\t'$label' => [" . '\\' . $fields[$db_field['Field']][0] . '::class' . ($fields[$db_field['Field']][1] ? ', ' . $encoder->encode($fields[$db_field['Field']][1], ['array.inline' => true]) : '') . "],\n";
			} else {
				$type = $this->selectRedfoxField($db_field, $db_field['Field']) . '::class';
				$output .= "\t'$label' => [$type";
				$options = $access->getEnumValues($table, $db_field['Field']);
				if (count($options)) $output .= ', ' . $encoder->encode($options, ['array.inline' => true]);
				$output .= "],\n";
			}
		}
		$output .= "];";

		file_put_contents($destination, $output);

		$this->output->writeln('DONE.');
	}


	protected function selectRedfoxField($db_field, $fieldName) {
		$dbtype = $db_field['Type'];
		if ($db_field['Comment'] == 'password') return '\Phlex\RedFox\Fields\PasswordField';
		if ($db_field['Comment'] == 'json') return '\Phlex\RedFox\Fields\JsonStringField';

		if ($dbtype == 'tinyint(1)') return '\Phlex\RedFox\Fields\BoolField';
		if ($dbtype == 'date') return '\Phlex\RedFox\Fields\DateField';
		if ($dbtype == 'time') return '\Phlex\RedFox\Fields\TimeField';
		if ($dbtype == 'datetime') return '\Phlex\RedFox\Fields\DateTimeField';
		if ($dbtype == 'float') return '\Phlex\RedFox\Fields\FloatField';

		if (strpos($dbtype, 'int(11) unsigned') === 0 && (substr($fieldName, -2) == 'Id' || $fieldName == 'id')) return '\Phlex\RedFox\Fields\IdField';
		if (strpos($dbtype, 'int') === 0) return '\Phlex\RedFox\Fields\IntegerField';
		if (strpos($dbtype, 'tinyint') === 0) return '\Phlex\RedFox\Fields\IntegerField';
		if (strpos($dbtype, 'smallint') === 0) return '\Phlex\RedFox\Fields\IntegerField';
		if (strpos($dbtype, 'mediumint') === 0) return '\Phlex\RedFox\Fields\IntegerField';
		if (strpos($dbtype, 'bigint') === 0) return '\Phlex\RedFox\Fields\IntegerField';

		if (strpos($dbtype, 'varchar') === 0) return '\Phlex\RedFox\Fields\StringField';
		if (strpos($dbtype, 'char') === 0) return '\Phlex\RedFox\Fields\StringField';
		if (strpos($dbtype, 'text') === 0) return '\Phlex\RedFox\Fields\StringField';
		if (strpos($dbtype, 'text') === 0) return '\Phlex\RedFox\Fields\StringField';
		if (strpos($dbtype, 'tinytext') === 0) return '\Phlex\RedFox\Fields\StringField';
		if (strpos($dbtype, 'mediumtext') === 0) return '\Phlex\RedFox\Fields\StringField';
		if (strpos($dbtype, 'longtext') === 0) return '\Phlex\RedFox\Fields\StringField';

		if (strpos($dbtype, 'set') === 0) return '\Phlex\RedFox\Fields\SetField';
		if (strpos($dbtype, 'enum') === 0) return '\Phlex\RedFox\Fields\EnumField';

		return '\Phlex\RedFox\Fields\UnsupportedField';
	}

}
