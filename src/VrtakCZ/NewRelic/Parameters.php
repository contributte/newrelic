<?php

namespace VrtakCZ\NewRelic;

class Parameters extends \Nette\Object
{
	/**
	 * @param string
	 * @param mixed
	 * @return Parameters
	 */
	public function setParameter($name, $value)
	{
		newrelic_add_custom_parameter($name, $value);
		return $this;
	}
}
