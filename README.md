# CookieConsent plugin for CakePHP

[![php](https://github.com/atlasconsulting/cakephp-cookie-consent/actions/workflows/php.yml/badge.svg)](https://github.com/atlasconsulting/cakephp-cookie-consent/actions/workflows/php.yml)
[![image](https://img.shields.io/packagist/v/atlasconsulting/cakephp-cookie-consent.svg?label=stable)](https://packagist.org/packages/atlasconsulting/cakephp-cookie-consent)
[![image](https://img.shields.io/github/license/atlasconsulting/cakephp-cookie-consent.svg)](https://github.com/atlasconsulting/cakephp-cookie-consent/blob/main/LICENSE.txt)

This plugin helps to remove cookies your app set and on which a user didn't consent.
It works reading a configurable cookie that contain information about the categories of
cookie accepted by the user.
It works out of the box with [Cookie Consent](https://github.com/orestbida/cookieconsent).

## Installation

You can install this plugin into your CakePHP application using [composer](https://getcomposer.org).

The recommended way to install composer packages is:

```
composer require atlasconsulting/cakephp-cookie-consent
```

## Usage

Add the `CookieCosentMiddleware` to the application middleware queue.

```php
public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
{
    return $middlewareQueue
        // Catch any exceptions in the lower layers,
        // and make an error page/response
        ->add(new ErrorHandlerMiddleware(Configure::read('Error')))

        ->add(new CookieConsentMiddleware([
            'remove' => [
                'preferences' => ['lang'], // remove `lang` cookie if `preferences` category isn't accepted
                'analytics' => ['my_analytics'], // remove `my_analytics` cookie if `analytics` category isn't accepted
            ],
        ]));

        // other middlewares here
}
```

## Configuration

The middleware is configurable with:

'cookieName' => 'cc_cookie',
        'searchIn' => 'level',
        'remove' => [
            'preferences' => [],
            'analytics' => [],
            'targeting' => [],
        ],

* `cookieName` (default `cc_cookie`), the cookie consent to analyze
* `searchIn` (default `level`), the key to look for in the json string value of cookie.
  The value must be an array of cookies' categories accepted by the user, for example
  `['preferences', 'analytics']`.
* `remove` an array divided by cookie categories that are to remove if not accepted
