<?php namespace Phlex\Codex\Responder;

use Phlex\Chameleon\JsonResponder;

abstract class Menu extends JsonResponder {

	protected $menu = [];

	protected function respond() {
		$this->createMenu();
		return $this->menu;
	}

	abstract protected function createMenu();

	protected function addMenu($label = '', $icon = '') {
		$menu = new class($label, $icon) {

			public $items = [];
			public $label;
			public $icon;

			public function __construct($label = '', $icon = '') {
				$this->label = $label;
			}

			public function addList($label, $icon, $url) {
				$item = $this->addItem($label, $icon, 'list');
				$item[ 'options' ][ 'url' ] = $url;
				$this->items[] = $item;
				return $this;
			}

			protected function addItem($label, $icon, $action) {
				return [ 'label' => $label, 'icon' => $icon, 'action' => $action, 'options' => [] ];
			}

			public function get() {
				return [
					'label' => $this->label,
					'icon'  => $this->icon,
					'items' => $this->items,
				];
			}
		};
		$this->menu[] = $menu;
		return $menu;
	}
}
