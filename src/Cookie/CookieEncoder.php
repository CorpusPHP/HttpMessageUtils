<?php

namespace Corpus\HttpMessageUtils\Cookie;

/**
 * Utility to encode a Cookie into a `Set-Cookie` header
 *
 * Based on `hansott/psr7-cookies`
 * MIT License Copyright (c) 2017 Hans Ott hansott@hotmail.be
 * @see https://github.com/hansott/psr7-cookies/blob/ec7bc4b3393677730b1e607c987328655d27dfbf/src/SetCookie.php#L160-L192
 */
class CookieEncoder implements CookieEncoderInterface {

	public function __invoke( CookieInterface $cookie ) : string {
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

}
