<?php

namespace VrtakCZ\NewRelic\Custom;

class Tracers extends \Nette\Object
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
	 * @return Tracers
	 */
	public function addTracer($function)
	{
		if ($this->enabled) {
			newrelic_add_custom_tracer($function);
		}
		return $this;
	}
}
