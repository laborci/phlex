<?php namespace Phlex\Form\Validator;

abstract class Validator {

	abstract function validate($data, ValidatorResult $result, $fieldName);

}