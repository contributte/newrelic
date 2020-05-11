<?php

namespace VrtakCZ\NewRelic\Tracy\Custom;

class Parameters
{

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public static function addParameter($name, $value)
	{
		if (\VrtakCZ\NewRelic\Tracy\Bootstrap::isEnabled()) {
			newrelic_add_custom_parameter($name, $value);
		}
	}

}
