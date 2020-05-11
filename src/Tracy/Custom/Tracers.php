<?php

namespace Contributte\NewRelic\Tracy\Custom;

class Tracers
{

	/**
	 * @param string $function functionName / ClassName::functionName
	 */
	public static function addTracer($function)
	{
		if (\Contributte\NewRelic\Tracy\Bootstrap::isEnabled()) {
			newrelic_add_custom_tracer($function);
		}
	}

}
