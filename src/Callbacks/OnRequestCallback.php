<?php

declare(strict_types=1);

namespace Contributte\NewRelic\Callbacks;

use Nette\Application\Application;
use Nette\Application\Request;
use Nette\Application\UI\Presenter;

class OnRequestCallback
{

	/**
	 * @var string
	 */
	private $actionKey;

	/**
	 * @param string $actionKey
	 */
	public function __construct($actionKey = Presenter::ACTION_KEY)
	{
		$this->actionKey = $actionKey;
	}

	/**
	 * @param \Nette\Application\Application $application
	 * @param \Nette\Application\Request $request
	 */
	public function __invoke(Application $application, Request $request)
	{
		if (PHP_SAPI === 'cli') {
			newrelic_background_job(true);
		}

		$params = $request->getParameters();
		$action = $request->getPresenterName();
		if (isset($params[$this->actionKey])) {
			$action = sprintf('%s:%s', $action, $params[$this->actionKey]);
		}

		newrelic_name_transaction($action);
		newrelic_disable_autorum();
	}

}
