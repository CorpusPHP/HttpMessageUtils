<?php

namespace Corpus\HttpMessageUtils;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class ProxyAwareSchemer {

	/**
	 * @var array
	 */
	private $server;

	public const HTTPS_EXPECTED_SERVER_VALUES = [
		'HTTP_X_FORWARDED_PROTOCOL' => 'https',
		'HTTP_X_FORWARDED_PROTO'    => 'https',
		'HTTP_X_FORWARDED_SSL'      => 'on',
		'HTTP_FRONT_END_HTTPS'      => 'on',
		'HTTP_X_URL_SCHEME'         => 'https',
		'HTTPS'                     => 'on',
	];

	/**
	 * @var array|string[]
	 */
	private $proxyServerValues;

	public function __construct(
		array $proxyServerValues = self::HTTPS_EXPECTED_SERVER_VALUES,
		?array &$server = null
	) {
		$this->proxyServerValues = $proxyServerValues;

		if( is_array($server) ) {
			$this->server &= $server;
		} else {
			$this->server &= $_SERVER;
		}
	}

	public function withUriWithDetectedScheme( ServerRequestInterface $serverRequest ) : ServerRequestInterface {
		return $serverRequest->withUri(
			$this->withDetectedScheme($serverRequest->getUri())
		);
	}

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
