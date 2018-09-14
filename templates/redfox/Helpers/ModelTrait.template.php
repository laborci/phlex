<?php namespace App\Entity\{{name}}\Helpers;
/**
{{fields}}
 */
trait ModelTrait{
	public function repository(){ return $this->repositoryFactory(...(include('source.php'))); }
	public function fields():array { return include("fields.php"); }
}