<?php

declare(strict_types=1);

namespace Contributte\NewRelic\Callbacks;

use Contributte\NewRelic\Agent\Agent;
use Contributte\NewRelic\Formatters\WebTransactionNameFormatter;
use Nette\Application\Application;
use Nette\Application\Request;

class OnRequestCallback
{

	/**
	 * @var Agent
	 */
	private $agent;

	/**
	 * @var WebTransactionNameFormatter
	 */
	private $formatter;

	public function __construct(Agent $agent, WebTransactionNameFormatter $formatter)
	{
		$this->agent = $agent;
		$this->formatter = $formatter;
	}

	public function __invoke(Application $application, Request $request): void
	{
		if (PHP_SAPI === 'cli') {
			$this->agent->backgroundJob(true);
			$name = $this->formatter->formatArgv();
		} else {
			$name = $this->formatter->format($request);
		}

		$this->agent->nameTransaction($name);
		$this->agent->disableAutorum();
	}

}
