<?php

declare(strict_types=1);

namespace Contributte\NewRelic\Tracy;

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
	public function __construct(ILogger $logger, array $logLevels)
	{
		$this->oldLogger = $logger;
		$this->logLevels = $logLevels ?: $this->defaultLogLevels;
	}

	/**
	 * @param string|array $message
	 * @param string $priority
	 * @return string logged error filename
	 */
	public function log($message, $priority = null)
	{
		$exceptionFile = $this->oldLogger->log($message, $priority);

		if (in_array($priority, $this->logLevels)) {
			if (is_array($message)) {
				$message = implode(' ', $message);
			}

			newrelic_notice_error($message);
		}

		return $exceptionFile;
	}

}
