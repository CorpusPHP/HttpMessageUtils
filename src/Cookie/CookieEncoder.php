<?php

namespace Corpus\HttpMessageUtils\Cookie;

use Psr\Http\Message\ResponseInterface;

/**
 * Utility to encode a Cookie into a `Set-Cookie` header
 *
 * Based on `hansott/psr7-cookies`
 * MIT License Copyright (c) 2017 Hans Ott hansott@hotmail.be
 *
 * @see https://github.com/hansott/psr7-cookies/blob/ec7bc4b3393677730b1e607c987328655d27dfbf/src/SetCookie.php#L160-L192
 */
class CookieEncoder {

	/**
	 * Encode the given Cookie to a `Set-Cookie` compatible header string
	 */
	public function encode( CookieInterface $cookie ) : string {
		$headerValue = sprintf('%s=%s', $cookie->getName(), urlencode($cookie->getValue()));

		if( $cookie->getExpiration() !== 0 ) {
			$headerValue .= sprintf(
				'; expires=%s',
				gmdate(DATE_RFC1123, time() + $cookie->getExpiration())
			);
		}

		if( $cookie->getPath() ) {
			$headerValue .= sprintf('; path=%s', $cookie->getPath());
		}

		if( $cookie->getDomain() ) {
			$headerValue .= sprintf('; domain=%s', $cookie->getDomain());
		}

		if( $cookie->isSecure() ) {
			$headerValue .= '; secure';
		}

		if( $cookie->isHttpOnly() ) {
			$headerValue .= '; httponly';
		}

		if( $cookie->getSameSite() ) {
			$headerValue .= sprintf('; samesite=%s', $cookie->getSameSite());
		}

		return $headerValue;
	}

	/**
	 * Apply the Cookie to `setcookie` a callable matching the signature of PHP 7.4+
	 * `setcookie(string $name, string $value = "", array $options = []) : bool`
	 *
	 * @param callable|null $callee The `setcookie` compatible callback to be used.
	 *                              If set to null, the default setcookie()
	 */
	public function apply( CookieInterface $cookie, ?callable $callee = null ) : bool {
		if( $callee === null ) {
			$callee = '\\setcookie';
		}

		return $callee(
			$cookie->getName(),
			$cookie->getValue(),
			[
				'expires'  => $cookie->getExpiration() + time(),
				'path'     => $cookie->getPath(),
				'domain'   => $cookie->getDomain(),
				'secure'   => $cookie->isSecure(),
				'httponly' => $cookie->isHttpOnly(),
				'samesite' => $cookie->getSameSite(),
			],
		);
	}

	/**
	 * Given a \Psr\Http\Message\ResponseInterface returns a new instance of ResponseInterface with an added
	 * `Set-Cookie` header representing this Cookie.
	 */
	public function responseWithHeaderAdded( CookieInterface $cookie, ResponseInterface $response ) : ResponseInterface {
		return $response->withAddedHeader('Set-Cookie', $this->encode($cookie));
	}

	/**
	 * Given a \Psr\Http\Message\ResponseInterface returns a new instance of ResponseInterface replacing any
	 * `Set-Cookie` headers with one representing this Cookie.
	 */
	public function responseWithHeader(  CookieInterface $cookie, ResponseInterface $response ) : ResponseInterface {
		return $response->withHeader('Set-Cookie', $this->encode($cookie));
	}

}
