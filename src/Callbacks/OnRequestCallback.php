<?php

declare(strict_types=1);

namespace Contributte\NewRelic\Callbacks;

use Contributte\NewRelic\Agent\Agent;
use Contributte\NewRelic\Environment;
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
	 * @var Environment
	 */
	private $environment;

	/**
	 * @var WebTransactionNameFormatter
	 */
	private $formatter;

	public function __construct(Agent $agent, Environment $environment, WebTransactionNameFormatter $formatter)
	{
		$this->agent = $agent;
		$this->environment = $environment;
		$this->formatter = $formatter;
	}

	public function __invoke(Application $application, Request $request): void
	{
		if ($this->environment->isCli()) {
			$this->agent->backgroundJob(true);
			$name = $this->formatter->formatArgv();
		} else {
			$name = $this->formatter->format($request);
		}

		$this->agent->nameTransaction($name);
		$this->agent->disableAutorum();
	}

}
