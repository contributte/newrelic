<?php

namespace Contributte\NewRelic\RUM;

class User
{

	/** @var bool */
	private $enabled;

	/**
	 * @param bool $enabled
	 */
	public function __construct($enabled = TRUE)
	{
		$this->enabled = $enabled;
	}

	/**
	 * @param string $user
	 * @param string $account
	 * @param string $product
	 * @return User
	 */
	public function setAttributes($user, $account, $product)
	{
		if ($this->enabled) {
			newrelic_set_user_attributes($user, $account, $product);
		}
		return $this;
	}

}
