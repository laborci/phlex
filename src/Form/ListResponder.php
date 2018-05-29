<?php namespace Phlex\Form;

use Phlex\Chameleon\HandyResponder;

class ListResponder extends HandyResponder {

	protected $fields = [];

	protected function prepare() {
		$this->bodyClass = 'list';
	}

	protected function addField($name, $label=null, $order = true){
		$visible = true;
		if(is_null($label)) $label = $name;
		$defaultOrder = is_string($order) ? $order : false;
		$this->fields[] = ['name'=>$name, 'label'=>$label, 'order'=>$order, 'defaultOrder'=>$defaultOrder, 'hidden'=>!$visible];
	}

	protected function BODY() { ?>
		<div role="px-list-head">
			<h1>{{.listTitle}}</h1>
			<div role="px-list-buttons">
				<button role="px-list-new"><i style="color:green;" class="fa fa-plus-square"></i> New</button>
				<!--button><i style="color:gray;" class="fa fa-cogs"></i> Settings</button-->
			</div>
			<div role="px-list-pager">
				<i class="fa fa-database"></i> <b role="px-list-count"></b>
				<span role="px-list-pager-items">
					<a data-page="1" class="active">1</a>
				</span>
			</div>
		</div>

		<div role="px-list" data-target="/form/user/" data-source="/list/user">
			<div role="px-list-table">
				<div class="scrollable">
					<table>
						<thead>
						<tr>
							<?php $this->FIELDS() ?>
						</tr>
						</thead>
						<tbody>

						</tbody>
					</table>
				</div>
			</div>
		</div>
	<?php }

	protected function FIELDS(){?>
		@each .fields as field
		<th data-field="{{field:name}}" {?field:order}data-order{.} {?field:hidden}data-hidden{.} {?field:defaultOrder}data-default-order="{{field:defaultOrder}}"{.} >{{field:label}}</th>
		@end each

	<?php }
}