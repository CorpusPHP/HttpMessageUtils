<?php

namespace Corpus\HttpMessageUtils;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * Utility to map a Uri or ServerRequestInterface's Uri to the external scheme
 * detected from a proxy such as an AWS load balancer.
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

	/** @var array|string[] */
	private $proxyServerValues;

	/**
	 * @param array|null $proxyServerValues Map of $_SERVER keys to their expected https-positive value. Defaults to
	 *                                      self::HTTPS_EXPECTED_SERVER_VALUES
	 * @param array|null $server            Server array to inspect. Defaults to $_SERVER.
	 */
	public function __construct(
		?array $proxyServerValues = null,
		?array &$server = null
	) {
		$this->proxyServerValues = $proxyServerValues ?? self::HTTPS_EXPECTED_SERVER_VALUES;

		if( is_array($server) ) {
			$this->server =& $server;
		} else {
			$this->server =& $_SERVER;
		}
	}

	/**
	 * Given a \Psr\Http\Message\ServerRequestInterface returns a new instance of ServerRequestInterface with a new Uri
	 * having the scheme adjusted to match the detected external scheme as defined by the proxies headers.
	 */
	public function withUriWithDetectedScheme(
		ServerRequestInterface $serverRequest
	) : ServerRequestInterface {
		return $serverRequest->withUri(
			$this->withDetectedScheme($serverRequest->getUri())
		);
	}

	/**
	 * Given a \Psr\Http\Message\UriInterface returns a new instance of UriInterface having the scheme adjusted to match
	 * the detected external scheme as defined by the proxies headers.
	 */
	public function withDetectedScheme( UriInterface $uri ) : UriInterface {
		foreach( $this->proxyServerValues as $serverKey => $serverValue ) {
			if( isset($this->server[$serverKey])
				&& strtolower($this->server[$serverKey]) === $serverValue
			) {
				return $uri->withScheme('https');
			}
		}

		return $uri;
	}

}
