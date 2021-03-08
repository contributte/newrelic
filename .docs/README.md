# NewRelic

## Content

- [Setup](#setup)
- [Configuration](#configuration)
- [Realtime User Monitoring](#realtime-user-monitoring)
- [Console](#console)
- [Agent](#agent)

## Setup

Install package

```bash
composer require contributte/newrelic
```

Register extension

```yaml
extensions:
  newrelic: Contributte\NewRelic\DI\NewRelicExtension
```

## Configuration

Basic configuration

```yaml
newrelic:
  enabled: true # true is default
  # use false on dev when newrelic extension is not present
  appName: YourApplicationName # optional, defaults to "PHP Application"
```

Full configuration with default values

```yaml
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

```smarty
{control newRelicHeader}
```

To your `@layout` template add `newRelicFooter` compenent before `</body>` tag.

```smarty
{control newRelicFooter}
```

## Console

This step is not necessary, but recommended as it will give you a nice formated data even for console commands.

You will need to add [contributte/console](https://github.com/contributte/console) and [contributte/event-dispatcher](https://github.com/contributte/event-dispatcher) packages.

```bash
composer require contributte/console contributte/event-dispatcher
```

And register them.

```yaml
extensions:
  events: Contributte\EventDispatcher\DI\EventDispatcherExtension
  console: Contributte\Console\DI\ConsoleExtension(%consoleMode%)
  newrelic.console: Contributte\NewRelic\DI\NewRelicConsoleExtension
```

## Agent

If you want to communicate with NewRelic extension, you can use autowired [Agent](../src/Agent/ProductionAgent.php)
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
