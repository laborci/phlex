<?php namespace Phlex\Auth;

interface AuthenticableInterface {
	public function getId();
	public function checkPassword($password): bool;
}