<?php namespace Phlex\Auth;

use Phlex\Sys\InjectDependencies;

abstract class AuthService implements InjectDependencies, AuthServiceInterface {

	protected $container;
	protected $isAuthenticated;

	public function __construct(AuthContainerInterface $container){
		$this->container = $container;
		if(!$container->getUserId()) return false;

		$user = $this->getUser();
		$this->isAuthenticated = !is_null($user) && $this->validateUser($user);
		if (!$this->validateUser($user)){
			$container->forget();
		}
	}

	public function isAuthenticated(): bool {
		return $this->isAuthenticated;
	}

	public function logout(){ $this->container->forget(); }

	public function authenticate($login, $password): bool {
		try{
			$user = $this->findUser($login);
		}catch (\Exception $e){
			return false;
		}
		if($user->checkPassword($password)){
			return $this->authenticateUser($user);
		}else{
			return false;
		}
	}

	public function authenticateUser(AuthenticableInterface $user):bool{
		if($this->validateUser($user)){
			$this->container->setUserId($user->getId());
			return true;
		}else{
			return false;
		}
	}

	abstract public function getUser();
	abstract protected function findUser($login) : AuthenticableInterface;
	abstract protected function validateUser($user):bool;

}