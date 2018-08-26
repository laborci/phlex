<?php namespace Phlex\Codex;

class FormHandler {

	/** @var FormSection[] */
	protected $sections = [];

	/** @var AdminDescriptor  */
	protected $adminDescriptor;

	/** @var bool  */
	protected $attachments;

	protected $urlBase;
	protected $titleField;
	protected $icon;

	protected $plugins = [];

	public function __construct(AdminDescriptor $adminDescriptor) {
		$this->adminDescriptor = $adminDescriptor;
		$this->attachments = $adminDescriptor->isAttachments();
		$this->urlBase = $adminDescriptor->getUrl();
		$this->titleField = $adminDescriptor->getTitleField();
		$this->icon = $adminDescriptor->getIcon();
	}

	public function addPlugin($plugin){ $this->plugins[] = $plugin; }

	public function addSection($label = null) {
		$section = new FormSection($label, $this->adminDescriptor);
		$this->sections[] = $section;
		return $section;
	}

	public function get() {
		$output = [
			'sections' => [],
			'urlBase' => $this->urlBase,
			'titleField' => $this->titleField,
			'icon' => $this->icon,
			'attachments' => $this->attachments,
			'plugins' => $this->plugins,
		];
		foreach ($this->sections as $section) {
			$output['sections'][] = $section->get();
		}
		return $output;
	}

	public function findInput($field){
		foreach ($this->sections as $section){
			if($input = $section->findInput($field)) return $input;
		}
		return false;
	}
}
