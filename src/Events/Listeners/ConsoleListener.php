<?php

declare(strict_types=1);

namespace Contributte\NewRelic\Events\Listeners;

use Contributte\NewRelic\Agent\Agent;
use Contributte\NewRelic\Formatters\CliTransactionNameFormatter;
use Symfony\Component\Console;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ConsoleListener implements EventSubscriberInterface
{

	/**
	 * @var Agent
	 */
	private $agent;

	/**
	 * @var CliTransactionNameFormatter
	 */
	private $formatter;

	public function __construct(Agent $agent, CliTransactionNameFormatter $formatter)
	{
		$this->agent = $agent;
		$this->formatter = $formatter;
	}

	public function onCommand(Console\Event\ConsoleCommandEvent $event): void
	{
		$this->agent->backgroundJob();
		$this->agent->nameTransaction($this->formatter->format($event->getCommand()));
		$this->agent->disableAutorum();
	}

	public function onError(Console\Event\ConsoleErrorEvent $event): void
	{
		$this->agent->noticeError($event->getError()->getMessage(), $event->getError());
	}

	/**
	 * @return array<string, string>
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			Console\ConsoleEvents::COMMAND => 'onCommand',
			Console\ConsoleEvents::ERROR => 'onError',
		];
	}

}
