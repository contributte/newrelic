<?php

namespace VrtakCZ\NewRelic\Nette\Callbacks;

use Nette\Application\Application;

class OnErrorCallback
{

	/**
	 * @param \Nette\Application\Application $application
	 * @param \Exception|\Throwable $e
	 */
	public function __invoke(Application $application, $e)
	{
		if ($e instanceof \Nette\Application\BadRequestException) { // skip 4xx errors
			return;
		}

		newrelic_notice_error($e->getMessage(), $e);
	}

	/**
	 * @param \Nette\Application\Application $application
	 */
	public function register(Application $application)
	{
		$application->onError[] = $this;
	}

}
