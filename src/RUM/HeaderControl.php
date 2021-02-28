<?php

declare(strict_types=1);

namespace Contributte\NewRelic\RUM;

use Contributte\NewRelic\Agent\Agent;
use Nette\Application\UI\Control;

class HeaderControl extends Control
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
	private $withScriptTag = true;

	public function __construct(Agent $agent, bool $enabled = true)
	{
		$this->agent = $agent;
		$this->enabled = $enabled;
	}

	public function disableScriptTag(): self
	{
		$this->withScriptTag = false;
		return $this;
	}

	public function render(): void
	{
		if ($this->enabled) {
			echo $this->agent->getBrowserTimingHeader($this->withScriptTag);
		}
	}

}
