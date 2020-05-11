<?php

declare(strict_types=1);

namespace Contributte\NewRelic\Callbacks;

use Nette\Application\Application;
use Nette\Application\BadRequestException;

class OnErrorCallback
{

	/**
	 * @param \Nette\Application\Application $application
	 * @param \Exception|\Throwable $e
	 */
	public function __invoke(Application $application, $e)
	{
		if ($e instanceof BadRequestException) { // skip 4xx errors
			return;
		}

		newrelic_notice_error($e->getMessage(), $e);
	}

}
