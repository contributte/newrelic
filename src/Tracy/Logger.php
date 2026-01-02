<?php declare(strict_types = 1);

namespace Contributte\NewRelic\Tracy;

use Contributte\NewRelic\Agent\Agent;
use Tracy\ILogger;

class Logger implements ILogger
{

	/** @var string[] */
	private array $defaultLogLevels = [
		ILogger::CRITICAL,
		ILogger::EXCEPTION,
		ILogger::ERROR,
	];

	private Agent $agent;

	private ILogger $oldLogger;

	/** @var string[] */
	private array $logLevels;

	/**
	 * @param string[] $logLevels
	 */
	public function __construct(Agent $agent, ILogger $logger, array $logLevels)
	{
		$this->agent = $agent;
		$this->oldLogger = $logger;
		$this->logLevels = $logLevels !== []
			? $logLevels
			: $this->defaultLogLevels;
	}

	/**
	 * @return string logged error filename
	 */
	public function log(mixed $message, mixed $priority = null)
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
