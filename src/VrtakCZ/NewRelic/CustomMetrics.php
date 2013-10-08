<?php

namespace VrtakCZ\NewRelic;

class CustomMetrics extends \Nette\Object
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
	 * @return CustomMetrics
	 */
	public function addMetric($name, $value)
	{
		if ($this->enabled) {
			newrelic_custom_metric('Custom/' . $name, $value);
		}
		return $this;
	}
}
