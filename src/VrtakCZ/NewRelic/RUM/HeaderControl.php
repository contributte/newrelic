<?php

namespace VrtakCZ\NewRelic\RUM;

class HeaderControl extends \Nette\Application\UI\Control
{
	/** @var bool */
	private $enabled;
	/** @var bool */
	private $withScriptTag = TRUE;

	/**
	 * @param bool
	 */
	public function __construct($enabled = TRUE)
	{
		parent::__construct();
		$this->enabled = $enabled;
	}

	/**
	 * @return HeaderControl
	 */
	public function disableScriptTag()
	{
		$this->withScriptTag = FALSE;
		return $this;
	}

	public function render()
	{
		if ($this->enabled) {
			echo newrelic_get_browser_timing_header($this->withScriptTag);
		}
	}
}
