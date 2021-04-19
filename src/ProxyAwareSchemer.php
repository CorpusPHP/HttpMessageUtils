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

	/** @var array */
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

	/** @var array|string[] */
	private $proxyServerHttpsKeyValues;

	/** @var string[] */
	private $proxyServerPortKeys;

	/**
	 * @param array|null $server Server array to inspect. Defaults to $_SERVER.
	 *
	 * @param array|null $proxyServerHttpsKeyValues Map of $_SERVER keys to their expected https-positive value.
	 *                                              Defaults to ProxyAwareSchemer::HTTPS_EXPECTED_SERVER_VALUES
	 *
	 * @param string[]|null Array of $_SERVER keys to check for a forwarded port value.
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
	 */
	public function withUriWithDetectedScheme(
		ServerRequestInterface $serverRequest, bool $detectPort = true, ?int $defaultOnHttps = self::REMOVE_PORT
	) : ServerRequestInterface {
		return $serverRequest->withUri(
			$this->withDetectedScheme($serverRequest->getUri(), $detectPort, $defaultOnHttps)
		);
	}

	/**
	 * Given a \Psr\Http\Message\UriInterface returns a instance of UriInterface having the scheme adjusted to match
	 * the detected external scheme as defined by the proxies headers.
	 */
	public function withDetectedScheme(
		UriInterface $uri,
		bool $detectPort = true,
		?int $defaultOnHttps = self::REMOVE_PORT
	) : UriInterface {
		foreach( $this->proxyServerHttpsKeyValues as $serverKey => $serverValue ) {
			if( isset($this->server[$serverKey])
				&& strtolower($this->server[$serverKey]) === $serverValue
			) {
				$newUri = $uri->withScheme('https');

				return $detectPort ? $this->withDetectedPort($newUri, $defaultOnHttps) : $newUri;
			}
		}

		return $detectPort ? $this->withDetectedPort($uri) : $uri;
	}

	/**
	 * Given a \Psr\Http\Message\UriInterface returns a instance of UriInterface having the port adjusted to match
	 * the detected external scheme as defined by the proxies headers.
	 *
	 * @param int|null $default Defines a default fallback port
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
