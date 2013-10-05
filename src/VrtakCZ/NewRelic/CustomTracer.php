<?php

namespace VrtakCZ\NewRelic;

class CustomTracer extends \Nette\Object
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
	 * @param string
	 * @return CustomTracer
	 */
	public function addTracer($function)
	{
		if ($this->enabled) {
			newrelic_add_custom_tracer($function);
		}
		return $this;
	}
}
