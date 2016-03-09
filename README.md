# planit-php-sdk

## Usage

Add `plan-it-app/planit-php-sdk` as a dependency in your project's `composer.json` file:

```json
{
	"require": {
		"plan-it-app/planit-php-sdk": "^1.0"
	}
}
```

 create new instance
```php
use Planit\API;

$x = new \Planit\API(TOKEN, 
	'ignoreSSL' => false, // default
	'cookies_jar' => true // default is true, can set a new \GuzzleHttp\Cookie\CookieJar if you want
]);
```
