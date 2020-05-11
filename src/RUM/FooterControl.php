<?php

declare(strict_types=1);

namespace Contributte\NewRelic\RUM;

use Nette\Application\UI\Control;

class FooterControl extends Control
{

	/**
	 * @var bool
	 */
	private $enabled;

	/**
	 * @var bool
	 */
	private $withScriptTag = true;

	/**
	 * @param bool $enabled
	 */
	public function __construct($enabled = true)
	{
		$this->enabled = $enabled;
	}

	/**
	 * @return FooterControl
	 */
	public function disableScriptTag()
	{
		$this->withScriptTag = false;
		return $this;
	}

	public function render()
	{
		if ($this->enabled) {
			echo newrelic_get_browser_timing_footer($this->withScriptTag);
		}
	}

}
