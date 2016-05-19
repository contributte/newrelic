[NewRelic](http://newrelic.com) PHP agent integration for [Nette Framework](http://nette.org)
=============================================================================================

[![Build Status](https://img.shields.io/travis/Vrtak-CZ/NewRelic-Nette.svg?style=flat-square)](https://travis-ci.org/Vrtak-CZ/NewRelic-Nette)
[![Latest Stable Version](https://img.shields.io/packagist/v/vrtak-cz/newrelic-nette.svg?style=flat-square)](https://packagist.org/packages/vrtak-cz/newrelic-nette)
[![Composer Downloads](https://img.shields.io/packagist/dt/vrtak-cz/newrelic-nette.svg?style=flat-square)](https://packagist.org/packages/vrtak-cz/newrelic-nette)
[![Dependency Status](https://img.shields.io/versioneye/d/user/projects/534bc43bfe0d0784f300004a.svg?style=flat-square)](https://www.versioneye.com/user/projects/534bc43bfe0d0784f300004a)

## Requirements
- Nette >=2.3.0 (2.3.x support will be removed on 31 Jan 2017)
- PHP >=5.5.0 (5.5.x support will be removed on 10 Jul 2016)

Installation
------------

```
composer require vrtak-cz/newrelic-nette
```

edit `app/config/config.neon`

```yaml
extensions:
    newrelic: VrtakCZ\NewRelic\Nette\Extension
```

Config
------

```yaml
newrelic:
	enabled: Yes #default
	ratio: 1
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
		ratio: 1
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

Realtime User Monitoring
------------------------

add this component factory to your base presenter

```php
protected function createComponentNewRelicHeader()
{
	$control = $this->context->getService('newrelic.rum')->headerControl;
	$control->disableScriptTag(); // optionall
	return $control;
}

protected function createComponentNewRelicFooter()
{
	$control = $this->context->getService('newrelic.rum')->footerControl;
	$control->disableScriptTag(); // optionall
	return $control;
}
```

and add this to your `@layout` header (before `</head>`)

```smarty
{control newRelicHeader}
```

and add this to your `@layout` footer (before `</body>`)

```smarty
{control newRelicFooter}
```

License
-------
NewRelic Nette is licensed under the MIT License - see the LICENSE file for details
