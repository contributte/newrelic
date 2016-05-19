<?php

namespace VrtakCZ\NewRelic\Nette\Callbacks;

use Nette\Application\Application;
use Nette\Application\Request;
use Nette\Application\UI\Presenter;
use Nette\Utils\Strings;

class OnRequestCallback extends \Nette\Object
{

	/** @var array */
	private $map;

	/** @var string */
	private $license;

	/** @var string */
	private $actionKey;

	/**
	 * @param array $map
	 * @param string $license
	 * @param string $actionKey
	 */
	public function __construct(array $map, $license, $actionKey = Presenter::ACTION_KEY)
	{
		$this->map = $map;
		$this->license = $license;
		$this->actionKey = $actionKey;
	}

	/**
	 * @param \Nette\Application\Application $application
	 * @param \Nette\Application\Request $request
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

		if (!empty($this->map)) {
			foreach ($this->map as $pattern => $appName) {
				if ($pattern === '*') {
					continue;
				}
				if (Strings::endsWith($pattern, '*')) {
					$pattern = Strings::substring($pattern, 0, -1);
				}
				if (Strings::startsWith($pattern, ':')) {
					$pattern = Strings::substring($pattern, 1);
				}

				if (Strings::startsWith($action, $pattern)) {
					\VrtakCZ\NewRelic\Tracy\Bootstrap::setup($appName, $this->license);
					break;
				}
			}
		}

		newrelic_name_transaction($action);
		newrelic_disable_autorum();
	}

	/**
	 * @param \Nette\Application\Application $application
	 */
	public function register(Application $application)
	{
		$application->onRequest[] = $this;
	}

}
