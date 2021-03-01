<?php

declare(strict_types=1);

namespace Contributte\NewRelic\Callbacks;

use Contributte\NewRelic\Agent\Agent;
use Contributte\NewRelic\Helpers;
use Nette\Application\Application;
use Nette\Application\Request;
use Nette\Application\UI\Presenter;

class OnRequestCallback
{

	/**
	 * @var Agent
	 */
	private $agent;

	/**
	 * @var string
	 */
	private $actionKey;

	public function __construct(Agent $agent, string $actionKey = Presenter::ACTION_KEY)
	{
		$this->agent = $agent;
		$this->actionKey = $actionKey;
	}

	public function __invoke(Application $application, Request $request): void
	{
		if (PHP_SAPI === 'cli') {
			$this->agent->backgroundJob(true);
		}

		$params = $request->getParameters();
		$action = $request->getPresenterName();
		if (isset($params[$this->actionKey])) {
			$action = sprintf('%s:%s', $action, $params[$this->actionKey]);
		}

		if (PHP_SAPI === 'cli') {
			$action = Helpers::getConsoleCommand();
		}

		$this->agent->nameTransaction($action);
		$this->agent->disableAutorum();
	}

}
