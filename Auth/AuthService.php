<?php
/**
 * Created by PhpStorm.
 * User: elvis
 * Date: 2017. 07. 11.
 * Time: 7:33
 */

namespace Phlex\Auth;


use Phlex\Sys\InjectDependencies;

abstract class AuthService implements InjectDependencies {

	protected $container;

	public function __construct(AuthContainerInterface $container){
		$this->container = $container;
	}

	public function isAuthenticated(): bool { return $this->container->hasUserId(); }
	public function logout(){ $this->container->forget(); }

	public function authenticate($login, $password): bool {
		$user = $this->findUser($login);
		if($user->checkPassword($password)){
			$this->container->setUserId($user->getId());
			return true;
		}else{
			return false;
		}
	}

	abstract public function getUser();
	abstract protected function findUser($login) : AuthenticableInterface;

}