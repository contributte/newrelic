<?php

namespace VrtakCZ\NewRelic;

class CustomTracer extends \Nette\Object
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
	 * @param string
	 * @return CustomTracer
	 */
	public function addTracer($function)
	{
		if (!$this->disabled) {
			newrelic_add_custom_tracer($function);
		}
		return $this;
	}
}
