<?php

namespace Attendance;

use AgroEgw\Api\User as UserApi;

class User
{
	private $user;
	private $contactID;

	public function __construct(int $user)
	{
		$this->user = $user;
	}

	public function Exists()
	{
		$user = UserApi::Read($this->user);

		return empty($user) ? false : true;
	}

	public function hasValidContract()
	{
		$contracts = (new Contracts(true))->Load();

		foreach ($contracts as $contract) {
			if ($this->user == $contract['user']) {
				return true;
			}
		}

		return false;
	}
}
