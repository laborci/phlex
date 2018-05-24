<?php namespace Phlex\Form\FormRenderer;


use Phlex\Form\Form;

class FormRenderer {

	public $action;
	public $title;

	public function __construct(Form $form, $title = null) {
		$this->form = $form;
		$this->title = $title;
	}

	public function render() {
		?>
		<form role="px-form" title="<?php echo $this->title ?>" action="<?php echo $this->action ?>">
			<header>
				<h1><?php echo $this->title ?></h1>
				<div role="px-buttons"><?php $this->renderButtons(); ?></div>
			</header>
			<div role="px-form-message"></div>
			<div role="px-fields">
				<?php $this->renderFields(); ?>
			</div>
		</form>
	<?php }

	protected function renderButtons() {
		?>
		<button role="px-button" action="save"><i style="color:green" class="fa fa-check"></i> Save</button>
		<button role="px-button" action="delete"><i style="color:darkred" class="fa fa-minus-circle"></i> Delete</button>
		<button role="px-button" job="refresh"><i style="color:orange" class="fa fa-recycle"></i> Refresh</button>
	<?php }

	protected function renderFields() {
		foreach ($this->form->fields as $field) {
			if ($field->testConditions($this->form)) {
				if ($field->input->render) {
					echo '<div role="px-field">';
					echo '<label for="' . $field->name . '">' . $field->input->label . '</label>';
					$field->input->render();
					echo '<div role="px-field-message" for="' . $field->name . '"></div>';
					echo '</div>';
				} else {
					$field->input->render();
				}
			}
		}
	}
}