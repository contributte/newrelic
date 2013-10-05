<?php

namespace VrtakCZ\NewRelic\RUM;

class User extends \Nette\Object
{
	/** @var bool */
	private $enabled;

	/**
	 * @param bool
	 */
	public function __construct($enabled = TRUE)
	{
		parent::__construct();
		$this->enabled = $enabled;
	}

	/**
	 * @param string
	 * @param string
	 * @param string
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
