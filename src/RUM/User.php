<?php

declare(strict_types=1);

namespace Contributte\NewRelic\RUM;

use Contributte\NewRelic\Agent\Agent;

class User
{

	/**
	 * @var Agent
	 */
	private $agent;

	/**
	 * @var bool
	 */
	private $enabled;

	public function __construct(Agent $agent, bool $enabled = true)
	{
		$this->agent = $agent;
		$this->enabled = $enabled;
	}

	public function setAttributes(string $user, string $account, string $product): self
	{
		if ($this->enabled) {
			$this->agent->setUserAttributes($user, $account, $product);
		}

		return $this;
	}

}
