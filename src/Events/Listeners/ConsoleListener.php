<?php

declare(strict_types=1);

namespace Contributte\NewRelic\Events\Listeners;

use Contributte\NewRelic\Helpers;
use Symfony\Component\Console;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ConsoleListener implements EventSubscriberInterface
{

	public static function getSubscribedEvents(): array
	{
		return [
			Console\ConsoleEvents::COMMAND => 'onCommand',
			Console\ConsoleEvents::ERROR => 'onError',
		];
	}


	public function onCommand(Console\Event\ConsoleCommandEvent $event)
	{
		newrelic_background_job(true);
		newrelic_name_transaction(Helpers::getConsoleCommand());
		newrelic_disable_autorum();
	}


	public function onError(Console\Event\ConsoleErrorEvent $event)
	{
		newrelic_notice_error($event->getError()->getMessage(), $event->getError());
	}

}
