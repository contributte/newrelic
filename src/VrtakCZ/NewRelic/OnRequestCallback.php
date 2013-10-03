<?php

namespace VrtakCZ\NewRelic;

use Nette\Application\Application;
use Nette\Application\Request;
use Nette\Application\UI\Presenter;

class OnRequestCallback extends \Nette\Object
{
	/** @var string */
	private $actionKey;

	/**
	 * @param string
	 */
	public function __construct($actionKey = Presenter::ACTION_KEY)
	{
		$this->actionKey = $actionKey;
	}

	/**
	 * @param \Nette\Application\Application
	 * @param \Nette\Application\Request
	 */
	public function __invoke(Application $application, Request $request)
	{
		if (PHP_SAPI === 'cli') {
			newrelic_background_job(TRUE);
		}

		$params = $request->getParameters();
		$action = $request->getPresenterName();
		if (isset($params[$this->actionKey])) {
			$action = sprintf('%s:%s', $action, $params[$this->actionKey]);
		}
		newrelic_name_transaction($action);
	}

	/**
	 * @param \Nette\Application\Application
	 */
	public function register(Application $application)
	{
		$application->onRequest[] = $this;
	}
}
