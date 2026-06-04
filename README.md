![](https://heatbadger.now.sh/github/readme/contributte/newrelic/)

<p align=center>
  <a href="https://github.com/contributte/newrelic/actions"><img src="https://badgen.net/github/checks/contributte/newrelic/master?cache=300"></a>
  <a href="https://coveralls.io/r/contributte/newrelic"><img src="https://badgen.net/coveralls/c/github/contributte/newrelic?cache=300"></a>
  <a href="https://packagist.org/packages/contributte/newrelic"><img src="https://badgen.net/packagist/dm/contributte/newrelic"></a>
  <a href="https://packagist.org/packages/contributte/newrelic"><img src="https://badgen.net/packagist/v/contributte/newrelic"></a>
</p>
<p align=center>
  <a href="https://packagist.org/packages/contributte/newrelic"><img src="https://badgen.net/packagist/php/contributte/newrelic"></a>
  <a href="https://github.com/contributte/newrelic"><img src="https://badgen.net/github/license/contributte/newrelic"></a>
  <a href="https://bit.ly/ctteg"><img src="https://badgen.net/badge/support/gitter/cyan"></a>
  <a href="https://bit.ly/cttfo"><img src="https://badgen.net/badge/support/forum/yellow"></a>
  <a href="https://contributte.org/partners.html"><img src="https://badgen.net/badge/sponsor/donations/F96854"></a>
</p>

<p align=center>
Website 🚀 <a href="https://contributte.org">contributte.org</a> | Contact 👨🏻‍💻 <a href="https://f3l1x.io">f3l1x.io</a> | Twitter 🐦 <a href="https://twitter.com/contributte">@contributte</a>
</p>

NewRelic PHP agent integration for Nette Framework.

## Versions

| State  | Version | Branch   | Nette | PHP     |
|--------|--------:|----------|-------|---------|
| dev    |  `^9.0` | `master` | 3.2   | `>=8.2` |
| stable |  `^8.0` | `master` | 3.2   | `>=8.2` |

## Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Realtime User Monitoring](#realtime-user-monitoring)
- [Console](#console)
- [Agent](#agent)

## Installation

Install package

```bash
composer require contributte/newrelic
```

Register extension

```neon
extensions:
	newrelic: Contributte\NewRelic\DI\NewRelicExtension
```

## Configuration

Basic configuration

```neon
newrelic:
	enabled: true # true is default
	# use false on dev when newrelic extension is not present
	appName: YourApplicationName # optional, defaults to "PHP Application"
```

Full configuration with default values

```neon
newrelic:
	enabled: true
	appName: PHP Application
	license: ''
	logLevel:
		- critical
		- exception
		- error
	rum:
		enabled: auto
	transactionTracer:
		enabled: true
		detail: 1
		recordSql: obfuscated
		slowSql: true
		threshold: apdex_f
		stackTraceThreshold: 500
		explainThreshold: 500
	errorCollector:
		enabled: true
		recordDatabaseErrors: true
	parameters:
		capture: false
		ignored: []
	custom:
		parameters: []
		tracers: []
```

## Realtime User Monitoring

If config option `rum/enabled` is set to `auto` (default), NewRelic extension is handling adding
of monitoring JS on its own. You can disable that behavior setting this option to `true` or `false`.
In both cases, auto instrumentation is set off. If set to `false`, `Agent` class is returning empty
string when calling `getBrowserTimingHeader()` and `getBrowserTimingFooter()` functions.

To specify where these JS should be added, you can either add `RUMControlTrait` to your
`BasePresenter` or create components your own way if you want to avoid adding `<script>` tags.
If `rum/enabled` is se to `false`, these controls returns empty string.

```php
<?php

declare(strict_types=1);

use Contributte\NewRelic\RUM\HeaderControl;
use Contributte\NewRelic\RUM\FooterControl;
use Contributte\NewRelic\RUM\RUMControlFactory;

abstract class BasePresenter extends \Nette\Application\UI\Presenter
{

	/**
	 * @var RUMControlFactory
	 * @inject
	 */
	protected $rumControlFactory;

	protected function createComponentNewRelicHeader(): HeaderControl
	{
		// Adding true avoid adding <script> tags
		return $this->rumControlFactory->createHeader(true);
	}

	protected function createComponentNewRelicFooter(): FooterControl
	{
		// Adding true avoid adding <script> tags
		return $this->rumControlFactory->createFooter(true);
	}

}
```

To your `@layout` template add `newRelicHeader` component before `</head>` tag.

```latte
{control newRelicHeader}
```

To your `@layout` template add `newRelicFooter` compenent before `</body>` tag.

```latte
{control newRelicFooter}
```

## Console

This step is not necessary, but recommended as it will give you a nice formated data even for console commands.

You will need to add [contributte/console](https://github.com/contributte/console) and [contributte/event-dispatcher](https://github.com/contributte/event-dispatcher) packages.

```bash
composer require contributte/console contributte/event-dispatcher
```

And register them.

```neon
extensions:
	events: Contributte\EventDispatcher\DI\EventDispatcherExtension
	console: Contributte\Console\DI\ConsoleExtension(%consoleMode%)
	newrelic.console: Contributte\NewRelic\DI\NewRelicConsoleExtension
```

## Agent

If you want to communicate with NewRelic extension, you can use autowired [Agent](src/Agent/ProductionAgent.php)
class, which wraps all NewRelic extension functions. If config options `enabled` is set to `false`,
NewRelic native functions are not called, so it's great for development environments where NewRelic
extension may not be installed.

For example, if you want to add your logged-in user id as custom parameter:

```php
<?php

declare(strict_types=1);

use Contributte\NewRelic\Agent\Agent;

abstract class BasePresenter extends \Nette\Application\UI\Presenter
{

	/**
	 * @var Agent
	 * @inject
	 */
	private $newRelicAgent;

	protected function startup(): void
	{
		parent::startup();

		if ($this->getUser()->isLoggedIn()) {
			$this->newRelicAgent->addCustomParameter('userId', $this->getUser()->getId());
		}
	}

}
```

## Development

See [how to contribute](https://contributte.org/contributing.html) to this package.

This package is currently maintaining by these authors.

<a href="https://github.com/foxycode">
  <img width="80" height="80" src="https://avatars2.githubusercontent.com/u/1284781?s=80&v=4">
</a>

-----

Consider to [support](https://contributte.org/partners.html) **contributte** development team.
Also thank you for using this package.
