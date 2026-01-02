<?php declare(strict_types = 1);

namespace Contributte\NewRelic\Callbacks;

use Contributte\NewRelic\Agent\Agent;
use Nette\Application\Application;
use Nette\Application\BadRequestException;

class OnErrorCallback
{

	private Agent $agent;

	public function __construct(Agent $agent)
	{
		$this->agent = $agent;
	}

	public function __invoke(Application $application, \Throwable|\Throwable $e): void
	{
		if ($e instanceof BadRequestException) {
			// skip 4xx errors
			return;
		}

		$this->agent->noticeError($e->getMessage(), $e);
	}

}
