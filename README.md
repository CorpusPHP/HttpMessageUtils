# HTTP Message Utils

[![Latest Stable Version](https://poser.pugx.org/corpus/http-message-utils/version)](https://packagist.org/packages/corpus/http-message-utils)
[![License](https://poser.pugx.org/corpus/http-message-utils/license)](https://packagist.org/packages/corpus/http-message-utils)
[![Build Status](https://github.com/CorpusPHP/HttpMessageUtils/workflows/CI/badge.svg?)](https://github.com/CorpusPHP/HttpMessageUtils/actions?query=workflow%3ACI)


Utilities for working with [PSR-7 Http Message](https://www.php-fig.org/psr/psr-7/) objects.

## Requirements

- **psr/http-message**: ^1.0
- **php**: >=7.2

## Installing

Install the latest version with:

```bash
composer require 'corpus/http-message-utils'
```

## Documentation

### Class: \Corpus\HttpMessageUtils\ProxyAwareSchemer

Utility to map a Uri or ServerRequestInterface's Uri to the external scheme
detected from a proxy such as an AWS load balancer.

Will only ever upgrade to https, never downgrade.

### Example:

```php
$serverRequest = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();
$serverRequest = (new \Corpus\HttpMessageUtils\ProxyAwareSchemer)->withUriWithDetectedScheme($serverRequest);
```

```php
<?php
namespace Corpus\HttpMessageUtils;

class ProxyAwareSchemer {
	public const HTTPS_EXPECTED_SERVER_VALUES = ['HTTP_X_FORWARDED_PROTOCOL' => 'https', 'HTTP_X_FORWARDED_PROTO' => 'https', 'HTTP_X_FORWARDED_SSL' => 'on', 'HTTP_FRONT_END_HTTPS' => 'on', 'HTTP_X_URL_SCHEME' => 'https', 'HTTPS' => 'on'];
	public const PORT_EXPECTED_SERVER_KEYS = ['HTTP_X_FORWARDED_PORT'];
	/** Value for `default port` arguments to remove the port from the URI */
	public const REMOVE_PORT = -65536;
}
```

#### Method: ProxyAwareSchemer->__construct

```php
function __construct([ ?array $server = null [, ?array $proxyServerHttpsKeyValues = null [, ?array $proxyServerPortKeys = null]]])
```

##### Parameters:

- ***array*** | ***null*** `$server` - Server array to inspect. Defaults to $_SERVER.
- ***array*** | ***null*** `$proxyServerHttpsKeyValues` - Map of $_SERVER keys to their expected https-positive value.
Defaults to ProxyAwareSchemer::HTTPS_EXPECTED_SERVER_VALUES
- ***string[]*** | ***null*** `$` - Array of $_SERVER keys to check for a forwarded port value.

---

#### Method: ProxyAwareSchemer->withUriWithDetectedScheme

```php
function withUriWithDetectedScheme(\Psr\Http\Message\ServerRequestInterface $serverRequest [, bool $detectPort = true [, ?int $defaultOnHttps = self::REMOVE_PORT]]) : \Psr\Http\Message\ServerRequestInterface
```

Given a \Psr\Http\Message\ServerRequestInterface returns a new instance of ServerRequestInterface with a new Uri  
having the scheme adjusted to match the detected external scheme as defined by the proxies headers.

##### Parameters:

- ***bool*** `$detectPort` - Enable / Disable proxy port sniffing.
- ***int*** | ***null*** `$defaultOnHttps` - Default port to use if sniffing fails but HTTPS proxy is detected.
Defaults to ProxyAwareSchemer::REMOVE_PORT which removes the port information
from the URI.
Passing null will leave port as-is.

---

#### Method: ProxyAwareSchemer->withDetectedScheme

```php
function withDetectedScheme(\Psr\Http\Message\UriInterface $uri [, bool $detectPort = true [, ?int $defaultOnHttps = self::REMOVE_PORT]]) : \Psr\Http\Message\UriInterface
```

Given a \Psr\Http\Message\UriInterface returns a instance of UriInterface having the scheme adjusted to match  
the detected external scheme as defined by the proxies headers.

##### Parameters:

- ***bool*** `$detectPort` - Enable / Disable proxy port sniffing.
- ***int*** | ***null*** `$defaultOnHttps` - Default port to use if sniffing fails but HTTPS proxy is detected.
Defaults to ProxyAwareSchemer::REMOVE_PORT which removes the port information
from the URI.
Passing null will leave port as-is.

---

#### Method: ProxyAwareSchemer->withDetectedPort

```php
function withDetectedPort(\Psr\Http\Message\UriInterface $uri [, ?int $default = null]) : \Psr\Http\Message\UriInterface
```

Given a \Psr\Http\Message\UriInterface returns a instance of UriInterface having the port adjusted to match  
the detected external scheme as defined by the proxies headers.

##### Parameters:

- ***int*** | ***null*** `$default` - Defines a default fallback port.
Passing ProxyAwareSchemer::REMOVE_PORT will default to removing the port information.
Defaults to null - null leaves port as-is.

### Class: \Corpus\HttpMessageUtils\ResponseSender

Utility to actualize a PSR7 ResponseInterface

Sends headers and body.

### Example:

```php
$response = new \GuzzleHttp\Psr7\Response();
(new \Corpus\HttpMessageUtils\ResponseSender)->send($response);
```

Inspired by `http-interop/response-sender`
MIT License Copyright (c) 2017 Woody Gilk

#### Method: ResponseSender->__construct

```php
function __construct([ bool $fullHttpStmtHeader = false [, bool $rewindBody = true]])
```

ResponseSender constructor.

##### Parameters:

- ***bool*** `$fullHttpStmtHeader` - Setting to `true` enables full HTTP statement construction which allows
non-standard reason phrases and potentially mismatched protocol versions.
Use with care.
- ***bool*** `$rewindBody` - Setting to `false` allows you to disable rewinding the body of the response
before transmission.

---

#### Method: ResponseSender->send

```php
function send(\Psr\Http\Message\ResponseInterface $response) : void
```

Trigger the transmission of the given \Psr\Http\Message\ResponseInterface