<?php declare(strict_types = 1);

namespace Tests\Mocks;

final class Application
{

	/** @var callable[] */
	public array $onRequest = [];

	/** @var callable[] */
	public array $onError = [];

}
