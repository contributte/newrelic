<?php

declare(strict_types=1);

namespace Contributte\NewRelic\RUM;

use Contributte\NewRelic\Agent\Agent;
use Nette\Application\UI\Control;

class FooterControl extends Control
{

	/**
	 * @var Agent
	 */
	private $agent;

	/**
	 * @var bool
	 */
	private $enabled;

	/**
	 * @var bool
	 */
	private $withScriptTag;

	public function __construct(Agent $agent, bool $enabled = true, bool $withScriptTag = true)
	{
		$this->agent = $agent;
		$this->enabled = $enabled;
		$this->withScriptTag = $withScriptTag;
	}

	public function render(): void
	{
		if ($this->enabled) {
			echo $this->agent->getBrowserTimingFooter($this->withScriptTag);
		}
	}

}
