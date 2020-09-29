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
  enabled: Yes #default
  appName: YourApplicationName #optional
  license: yourLicenseCode #optional
  actionKey: action # default - optional - action parameter name
  logLevel: #defaults
    - critical
    - exception
    - error

  # optional options with default values
  rum:
    enabled: auto # other options are Yes/No
  transactionTracer:
    enabled: Yes
    detail: 1
    recordSql: obfuscated
    slowSql: Yes
    threshold: apdex_f
    stackTraceThreshold: 500
    explainThreshold: 500
  errorCollector:
    enabled: Yes
    recordDatabaseErrors: Yes
  parameters:
    capture: No
    ignored: []
  customParameters:
    paramName: paramValue
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

## Tracy

```php
$logLevel = [ // default (NULL means default)
    \Tracy\Logger::CRITICAL,
    \Tracy\Logger::EXCEPTION,
    \Tracy\Logger::ERROR,
];
$appName = 'PHP Application'; // default (NULL means default)
$license = 'your_licence_key';

\Contributte\NewRelic\Tracy\Bootstrap::init($logLevel, $appName, $license); // all parameters are optional
```
