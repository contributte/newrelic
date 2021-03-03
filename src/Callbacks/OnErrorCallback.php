<?php

declare(strict_types=1);

namespace Contributte\NewRelic\Callbacks;

use Contributte\NewRelic\Agent\Agent;
use Nette\Application\Application;
use Nette\Application\BadRequestException;

class OnErrorCallback
{

	/**
	 * @var Agent
	 */
	private $agent;

	public function __construct(Agent $agent)
	{
		$this->agent = $agent;
	}

	/**
	 * @param \Exception|\Throwable $e
	 */
	public function __invoke(Application $application, $e): void
	{
		if ($e instanceof BadRequestException) {
			// skip 4xx errors
			return;
		}

		$this->agent->noticeError($e->getMessage(), $e);
	}

}
