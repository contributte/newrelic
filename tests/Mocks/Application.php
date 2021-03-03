<?php

declare(strict_types=1);

namespace ContributteTests\NewRelic\Mocks;

final class Application
{

	/**
	 * @var callable[]
	 */
	public $onRequest = [];

	/**
	 * @var callable[]
	 */
	public $onError = [];

}
