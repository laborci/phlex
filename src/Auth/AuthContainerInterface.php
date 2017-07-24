<?php
/**
 * Created by PhpStorm.
 * User: elvis
 * Date: 2017. 07. 11.
 * Time: 7:31
 */

namespace Phlex\Auth;


interface AuthContainerInterface{
	public function setUserId($userId);
	public function getUserId() : int;
	public function hasUserId() : bool;
	public function forget();
}