<?php

namespace Contributte\NewRelic\RUM;

class FooterControl extends \Nette\Application\UI\Control
{

	/** @var bool */
	private $enabled;

	/** @var bool */
	private $withScriptTag = TRUE;

	/**
	 * @param bool $enabled
	 */
	public function __construct($enabled = TRUE)
	{
		$this->enabled = $enabled;
	}

	/**
	 * @return FooterControl
	 */
	public function disableScriptTag()
	{
		$this->withScriptTag = FALSE;
		return $this;
	}

	public function render()
	{
		if ($this->enabled) {
			echo newrelic_get_browser_timing_footer($this->withScriptTag);
		}
	}

}
