<?php

declare(strict_types=1);

namespace Contributte\NewRelic\RUM;

trait RUMControlTrait
{

	/**
	 * @var RUMControlFactory
	 */
	protected $rumControlFactory;

	public function injectRUMControlFactory(RUMControlFactory $rumControlFactory): void
	{
		$this->rumControlFactory = $rumControlFactory;
	}

	protected function createComponentNewRelicHeader(): HeaderControl
	{
		return $this->rumControlFactory->createHeader();
	}

	protected function createComponentNewRelicFooter(): FooterControl
	{
		return $this->rumControlFactory->createFooter();
	}

}
