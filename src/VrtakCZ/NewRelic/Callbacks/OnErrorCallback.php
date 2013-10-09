<?php

namespace VrtakCZ\NewRelic\Callbacks;

use Nette\Application\Application;

class OnErrorCallback extends \Nette\Object
{
	/**
	 * @param \Nette\Application\Application
	 * @param \Exception
	 */
	public function __invoke(Application $application, \Exception $e)
	{
		if ($e instanceof \Nette\Application\BadRequestException) { // skip 4xx errors
			return;
		}

		newrelic_notice_error($e->getMessage(), $e);
	}

	/**
	 * @param \Nette\Application\Application
	 */
	public function register(Application $application)
	{
		$application->onError[] = $this;
	}
}
