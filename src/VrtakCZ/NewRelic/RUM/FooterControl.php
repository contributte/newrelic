<?php

namespace VrtakCZ\NewRelic\RUM;

class FooterControl extends \Nette\Application\UI\Control
{
	/** @var bool */
	private $withScriptTag = TRUE;

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
		echo newrelic_get_browser_timing_footer($this->withScriptTag);
	}
}
