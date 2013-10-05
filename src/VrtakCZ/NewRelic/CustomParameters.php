<?php

namespace VrtakCZ\NewRelic;

class CustomParameters extends \Nette\Object
{
	/** @var bool */
	private $disabled;

	/**
	 * @param bool
	 */
	public function __construct($disabled = FALSE)
	{
		$this->disabled = $disabled;
	}

	/**
	 * @param string
	 * @param mixed
	 * @return CustomParameters
	 */
	public function addParameter($name, $value)
	{
		if (!$this->disabled) {
			newrelic_add_custom_parameter($name, $value);
		}
		return $this;
	}
}
