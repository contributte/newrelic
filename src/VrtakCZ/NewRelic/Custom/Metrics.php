<?php

namespace VrtakCZ\NewRelic\Custom;

class Metrics extends \Nette\Object
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
	 * @param string $name
	 * @param float $value Miliseconds
	 * @return Metrics
	 */
	public function addMetric($name, $value)
	{
		if ($this->enabled) {
			newrelic_custom_metric('Custom/' . $name, $value);
		}
		return $this;
	}
}
