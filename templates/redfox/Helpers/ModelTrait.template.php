<?php namespace App\Entity\{{name}}\Helpers;
/**
{{fields}}
 */
trait ModelTrait{
	public function repository(){ return $this->repositoryFactory('{{table}}', '{{database}}'); }
	public function fields():array { return include("fields.php"); }
}