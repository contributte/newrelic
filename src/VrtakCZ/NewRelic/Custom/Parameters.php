<?php

namespace VrtakCZ\NewRelic\Custom;

class Parameters extends \Nette\Object
{
	/** @var bool */
	private $enabled;

	/**
	 * @param bool
	 */
	public function __construct($enabled = TRUE)
	{
		$this->enabled = $enabled;
	}

	/**
	 * @param string
	 * @param mixed
	 * @return Parameters
	 */
	public function addParameter($name, $value)
	{
		if ($this->enabled) {
			newrelic_add_custom_parameter($name, $value);
		}
		return $this;
	}
}
