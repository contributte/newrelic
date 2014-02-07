<?php

namespace VrtakCZ\NewRelic;

class Logger extends \Nette\Diagnostics\Logger
{
	/** @var array */
	private $logLevel;

	/**
	 * @param array
	 */
	public function __construct(array $logLevel)
	{
		$oldLogger = \Nette\Diagnostics\Debugger::getLogger();
		$this->emailSnooze =& $oldLogger->emailSnooze;
		$this->mailer =& $oldLogger->mailer;
		$this->directory =& $oldLogger->directory;
		$this->email =& $oldLogger->email;

		$this->logLevel = $logLevel;
	}

	/**
	 * @param string
	 * @param string
	 * @return bool
	 */
	public function log($message, $priority = self::INFO)
	{
		$res = parent::log($message, $priority);

		if (in_array($priority, $this->logLevel)) {
			if (is_array($message)) {
				$message = implode(' ', $message);
			}

			newrelic_notice_error($message);
		}

		return $res;
	}

	/**
	 * @param \Nette\Application\Application
	 */
	public function register(Application $application)
	{
		$application->onError[] = $this;
	}
}
