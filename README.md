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
\VrtakCZ\Newrelic\Extension::register($configurator);
```

Config
------

```yaml
newrelic:
	appName: YourApplicationName #optional
	license: yourLicenseCode #optional
	actionKey: action # default - optional - action parameter name
```

License
-------
NewRelic Nette is licensed under the MIT License - see the LICENSE file for details


