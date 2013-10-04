[NewRelic](http://newrelic.com) PHP agent integration for [Nette Framework](http://nette.org)
=============================================================================================

Installation
------------

```
composer require vrtak-cz/newrelic-nette
```

edit `app/bootstrap.php`

```php
// add this line after `$configurator = new Nette\Config\Configurator;`
\VrtakCZ\NewRelic\Extension::register($configurator);
```

Config
------

```yaml
newrelic:
	appName: YourApplicationName #optional
	license: yourLicenseCode #optional
	actionKey: action # default - optional - action parameter name
	parameters: #optional
		paramName: paramValue
	disable: YES #optional - force disable
```

Realtime User Monitoring
------------------------

add this component factory to your base presenter

```php
protected function createComponentNewRelicHeader()
{
	$control = $this->context->newrelic->headerControl;
	$control->disableScriptTag(); // optionall
	return $control;
}

protected function createComponentNewRelicFooter()
{
	$control = $this->context->newrelic->footerControl;
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


