<?php

declare(strict_types=1);

namespace Contributte\NewRelic\Tracy;

use Contributte\NewRelic\Agent\Agent;
use Tracy\ILogger;

class Logger implements ILogger
{

	/**
	 * @var string[]
	 */
	private $defaultLogLevels = [
		ILogger::CRITICAL,
		ILogger::EXCEPTION,
		ILogger::ERROR,
	];

	/**
	 * @var Agent
	 */
	private $agent;

	/**
	 * @var ILogger
	 */
	private $oldLogger;

	/**
	 * @var string[]
	 */
	private $logLevels;

	/**
	 * @param string[] $logLevels
	 */
	public function __construct(Agent $agent, ILogger $logger, array $logLevels)
	{
		$this->agent = $agent;
		$this->oldLogger = $logger;
		$this->logLevels = $logLevels ?? $this->defaultLogLevels;
	}

	/**
	 * @param mixed $message
	 * @param mixed $priority
	 * @return string logged error filename
	 */
	public function log($message, $priority = null)
	{
		$exceptionFile = $this->oldLogger->log($message, $priority);

		if (in_array($priority, $this->logLevels, true)) {
			if (is_array($message)) {
				$message = implode(' ', $message);
			}

			$this->agent->noticeError($message);
		}

		return $exceptionFile;
	}

}
