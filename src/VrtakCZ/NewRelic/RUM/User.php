<?php

namespace VrtakCZ\NewRelic\RUM;

class User extends \Nette\Object
{
	/** @var bool */
	private $disabled;

	/**
	 * @param bool
	 */
	public function __construct($disabled = FALSE)
	{
		parent::__construct();
		$this->disabled = $disabled;
	}

	/**
	 * @param string
	 * @param string
	 * @param string
	 * @return User
	 */
	public function setAttributes($user, $account, $product)
	{
		if (!$this->disabled) {
			newrelic_set_user_attributes($user, $account, $product);
		}
		return $this;
	}
}
