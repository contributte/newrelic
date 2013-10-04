<?php

namespace VrtakCZ\NewRelic\RUM;

class HeaderControl extends \Nette\Application\UI\Control
{
	/** @var bool */
	private $disabled;
	/** @var bool */
	private $withScriptTag = TRUE;

	/**
	 * @param bool
	 */
	public function __construct($disabled = FALSE)
	{
		parent::__construct();
		$this->disabled = $disabled;
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
		if (!$this->disabled) {
			echo newrelic_get_browser_timing_header($this->withScriptTag);
		}
	}
}
