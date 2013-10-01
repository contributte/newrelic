<?php

namespace VrtakCZ\Newrelic;

use Nette\Diagnostics\Debugger;

class Logger extends \Nette\Diagnostics\Logger
{
	public function __construct()
	{
		$oldLogger = Debugger::$logger;
		static::$emailSnooze =& $oldLogger::$emailSnooze;
		$this->mailer =& $oldLogger->mailer;
		$this->directory =& $oldLogger->directory;
		$this->email =& $oldLogger->email;
	}

	/**
	 * @param string
	 * @param string
	 * @return bool
	 */
	public function log($message, $priority = self::INFO)
	{
		$res = parent::log($message, $priority);

		if (in_array($priority, array(self::ERROR, self::CRITICAL))) {
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
