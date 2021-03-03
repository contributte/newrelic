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

	public function __construct(Agent $agent)
	{
		$this->agent = $agent;
	}

	public function __invoke(Application $application, Request $request): void
	{
		if (PHP_SAPI === 'cli') {
			$this->agent->backgroundJob(true);
		}

		$params = $request->getParameters();
		$action = $request->getPresenterName();
		if (isset($params[Presenter::ACTION_KEY])) {
			$action = sprintf('%s:%s', $action, $params[Presenter::ACTION_KEY]);
		}

		if (PHP_SAPI === 'cli') {
			$action = Helpers::getConsoleCommand();
		}

		$this->agent->nameTransaction($action);
		$this->agent->disableAutorum();
	}

}
