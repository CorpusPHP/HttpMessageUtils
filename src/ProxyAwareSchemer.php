<?php

namespace Corpus\HttpMessageUtils;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * Utility to map a Uri or ServerRequestInterface's Uri to the external scheme
 * detected from a proxy such as an AWS load balancer.
 *
 * Will only ever upgrade to https, never downgrade.
 *
 * ### Example:
 *
 * ```php
 * $serverRequest = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();
 * $serverRequest = (new \Corpus\HttpMessageUtils\ProxyAwareSchemer)->withUriWithDetectedScheme($serverRequest);
 * ```
 */
class ProxyAwareSchemer {

	/** @var array<string,scalar> */
	private $server;

	public const HTTPS_EXPECTED_SERVER_VALUES = [
		'HTTP_X_FORWARDED_PROTOCOL' => 'https',
		'HTTP_X_FORWARDED_PROTO'    => 'https',
		'HTTP_X_FORWARDED_SSL'      => 'on',
		'HTTP_FRONT_END_HTTPS'      => 'on',
		'HTTP_X_URL_SCHEME'         => 'https',
		'HTTPS'                     => 'on',
	];

	public const PORT_EXPECTED_SERVER_KEYS = [
		'HTTP_X_FORWARDED_PORT',
	];

	/** Value for `default port` arguments to remove the port from the URI */
	public const REMOVE_PORT = -65536;

	/** @var array<string,string> */
	private $proxyServerHttpsKeyValues;

	/** @var string[] */
	private $proxyServerPortKeys;

	/**
	 * @param array<string,scalar> $server Server array to inspect. Defaults to $_SERVER.
	 *
	 * @param array<string,string>|null $proxyServerHttpsKeyValues Map of $_SERVER keys to their expected https-positive value.
	 *                                                             Defaults to ProxyAwareSchemer::HTTPS_EXPECTED_SERVER_VALUES
	 *
	 * @param string[]|null $proxyServerPortKeys Array of $_SERVER keys to check for a forwarded port value.
	 */
	public function __construct(
		?array $server = null,
		?array $proxyServerHttpsKeyValues = null,
		?array $proxyServerPortKeys = null
	) {
		if( is_array($server) ) {
			$this->server = $server;
		} else {
			$this->server = $_SERVER;
		}

		$this->proxyServerHttpsKeyValues = $proxyServerHttpsKeyValues ?? self::HTTPS_EXPECTED_SERVER_VALUES;
		$this->proxyServerPortKeys       = $proxyServerPortKeys ?? self::PORT_EXPECTED_SERVER_KEYS;
	}

	/**
	 * Given a \Psr\Http\Message\ServerRequestInterface returns a new instance of ServerRequestInterface with a new Uri
	 * having the scheme adjusted to match the detected external scheme as defined by the proxies headers.
	 *
	 * @param bool     $detectPort     Enable / Disable proxy port sniffing.
	 * @param int|null $defaultOnHttps Default port to use if sniffing fails but HTTPS proxy is detected.
	 *                                 Defaults to ProxyAwareSchemer::REMOVE_PORT which removes the port information
	 *                                 from the URI.
	 *                                 Passing null will leave port as-is.
	 */
	public function withUriWithDetectedScheme(
		ServerRequestInterface $serverRequest,
		bool $detectPort = true,
		?int $defaultOnHttps = self::REMOVE_PORT
	) : ServerRequestInterface {
		return $serverRequest->withUri(
			$this->withDetectedScheme($serverRequest->getUri(), $detectPort, $defaultOnHttps)
		);
	}

	/**
	 * Given a \Psr\Http\Message\UriInterface returns an instance of UriInterface having the scheme adjusted to match
	 * the detected external scheme as defined by the proxies headers.
	 *
	 * @param bool     $detectPort     Enable / Disable proxy port sniffing.
	 * @param int|null $defaultOnHttps Default port to use if sniffing fails but HTTPS proxy is detected.
	 *                                 Defaults to ProxyAwareSchemer::REMOVE_PORT which removes the port information
	 *                                 from the URI.
	 *                                 Passing null will leave port as-is.
	 */
	public function withDetectedScheme(
		UriInterface $uri,
		bool $detectPort = true,
		?int $defaultOnHttps = self::REMOVE_PORT
	) : UriInterface {
		foreach( $this->proxyServerHttpsKeyValues as $serverKey => $serverValue ) {
			if( isset($this->server[$serverKey])
				&& strtolower((string)$this->server[$serverKey]) === $serverValue
			) {
				$newUri = $uri->withScheme('https');

				return $detectPort ? $this->withDetectedPort($newUri, $defaultOnHttps) : $newUri;
			}
		}

		return $detectPort ? $this->withDetectedPort($uri) : $uri;
	}

	/**
	 * Given a \Psr\Http\Message\UriInterface returns an instance of UriInterface having the port adjusted to match
	 * the detected external scheme as defined by the proxies headers.
	 *
	 * @param int|null $default Defines a default fallback port.
	 *                          Passing ProxyAwareSchemer::REMOVE_PORT will default to removing the port information.
	 *                          Defaults to null - null leaves port as-is.
	 */
	public function withDetectedPort( UriInterface $uri, ?int $default = null ) : UriInterface {
		foreach( $this->proxyServerPortKeys as $portKey ) {
			if( isset($this->server[$portKey]) ) {
				$port = (int)$this->server[$portKey];
				if( $port > 0 && $port <= 65535 ) {
					return $uri->withPort($port);
				}
			}
		}

		if( $default === self::REMOVE_PORT ) {
			return $uri->withPort(null);
		}

		return $default ? $uri->withPort($default) : $uri;
	}

}
