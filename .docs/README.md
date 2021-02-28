# NewRelic

## Content

- [Setup](#setup)
- [Usage](#usage)
- [Realtime User Monitoring](#realtime-user-monitoring)
- [Tracy](#tracy)

## Setup

**Install package with composer:**

```
composer require contributte/newrelic
```

**Register as nette extension:**

```yaml
extensions:
  newrelic: Contributte\NewRelic\DI\NewRelicExtension
```

## Usage

```yaml
newrelic:
  enabled: true #default
  appName: YourApplicationName #optional
  license: yourLicenseCode #optional
  actionKey: action # default - optional - action parameter name
  logLevel: #defaults
    - critical
    - exception
    - error

  # optional options with default values
  rum:
    enabled: auto # other options are true/false
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
    recordDatabaseErrors: Yes
  parameters:
    capture: false
    ignored: []
  custom:
    parameters:
      paramName: paramValue
    tracers:
```

## Realtime User Monitoring

Add this component factory to your base presenter:

```php
/**
 * @var \Contributte\NewRelic\RUM\HeaderControl
 * @inject
 */
protected $headerControl;

/**
 * @var \Contributte\NewRelic\RUM\FooterControl
 * @inject
 */
protected $footerControl;

protected function createComponentNewRelicHeader()
{
    $this->headerControl->disableScriptTag(); // optional
    return $this->headerControl;
}

protected function createComponentNewRelicFooter()
{
    $this->footerControl->disableScriptTag(); // optional
    return $this->footerControl;
}
```

And add this to your `@layout` header (before `</head>`):

```smarty
{control newRelicHeader}
```

And add this to your `@layout` footer (before `</body>`):

```smarty
{control newRelicFooter}
```

## Console
This step is not necessary, but recommended as it will give you a nice formated data even for console commands.
You will need to add two packages, [contributte/console](https://github.com/contributte/console) and [contributte/event-dispatcher](https://github.com/contributte/event-dispatcher).
```bash
composer require contributte/console
```

```yaml
extensions:
  console: Contributte\Console\DI\ConsoleExtension(%consoleMode%)
```

```bash
composer require contributte/event-dispatcher
```

```yaml
extensions:
  events: Contributte\EventDispatcher\DI\EventDispatcherExtension
```

Last step is registration of `NewRelicConsoleExtension`.

```yaml
extensions:
  newrelic.console: Contributte\NewRelic\DI\NewRelicConsoleExtension
```
