<?php declare(strict_types = 1);

namespace Contributte\NewRelic\RUM;

use Contributte\NewRelic\Agent\Agent;

final class RUMControlFactory
{

	private Agent $agent;

	private bool $enabled;

	public function __construct(Agent $agent, bool $enabled = true)
	{
		$this->agent = $agent;
		$this->enabled = $enabled;
	}

	public function createHeader(bool $withScriptTag = true): HeaderControl
	{
		return new HeaderControl($this->agent, $this->enabled, $withScriptTag);
	}

	public function createFooter(bool $withScriptTag = true): FooterControl
	{
		return new FooterControl($this->agent, $this->enabled, $withScriptTag);
	}

}
