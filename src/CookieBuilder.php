<?php

namespace Corpus\HttpMessageUtils;

use Psr\Http\Message\ResponseInterface;

/**
 * Utility to build an HTTP Cookie header
 *
 * Inspired by and partly based on `hansott/psr7-cookies`
 * MIT License Copyright (c) 2017 Hans Ott hansott@hotmail.be
 */
class CookieBuilder {

	private string $name;
	private string $value;
	private int $expiration;
	private string $path;
	private string $domain;
	private bool $secure;
	private bool $httpOnly;
	private string $sameSite;

	/**
	 * @param string $name       The name of the cookie
	 * @param int    $expiration The number of seconds for which this cookie will be valid. `time() + $expiration`
	 * @param string $path       The path on which this cookie is available
	 * @param string $domain     The domain to which the cookie is sent
	 * @param bool   $secure     Indicates that the cookie should only be transmitted over a secure HTTPS connection
	 * @param bool   $httpOnly   When true the cookie will be made accessible only through the HTTP protocol, e.g. Not
	 *                           JavaScript
	 * @param string $sameSite   Set the SameSite value - expects "None", "Lax" or "Strict". If the samesite element is
	 *                           empty, no SameSite cookie attribute is set
	 */
	public function __construct(
		string $name,
		string $value = '',
		int $expiration = 0,
		string $path = '',
		string $domain = '',
		bool $secure = false,
		bool $httpOnly = false,
		string $sameSite = "None"
	) {
		$this->name       = $name;
		$this->value      = $value;
		$this->expiration = $expiration;
		$this->path       = $path;
		$this->domain     = $domain;
		$this->secure     = $secure;
		$this->httpOnly   = $httpOnly;
		$this->sameSite   = $sameSite;
	}

	/**
	 * Apply the Cookie to a callable matching the signature of PHP 7.4+
	 * `setcookie(string $name, string $value = "", array $options = []) : bool`
	 */
	public function apply( callable $callee = "\\setcookie" ) : bool {
		return $callee(
			$this->name,
			$this->value,
			[
				'expires'  => $this->expiration + time(),
				'path'     => $this->path,
				'domain'   => $this->domain,
				'secure'   => $this->secure,
				'httponly' => $this->httpOnly,
				'samesite' => $this->sameSite,
			],
		);
	}

	/**
	 * Given a \Psr\Http\Message\ResponseInterface returns a new instance of ResponseInterface with an added
	 * `Set-Cookie` header representing this Cookie.
	 */
	public function responseWithHeaderAdded( ResponseInterface $response ) : ResponseInterface {
		return $response->withAddedHeader('Set-Cookie', $this->toHeaderValue());
	}

	/**
	 * Given a \Psr\Http\Message\ResponseInterface returns a new instance of ResponseInterface replacing any
	 * `Set-Cookie` headers with one representing this Cookie.
	 */
	public function responseWithHeader( ResponseInterface $response ) : ResponseInterface {
		return $response->withHeader('Set-Cookie', $this->toHeaderValue());
	}

	/**
	 * Return an instance with the specified name.
	 */
	public function withName( string $name ) : self {
		$clone       = clone $this;
		$clone->name = $name;

		return $clone;
	}

	/**
	 * Return an instance with the specified value.
	 */
	public function withValue( string $value ) : self {
		$that        = clone $this;
		$that->value = $value;

		return $that;
	}

	/**
	 * Return an instance with an expiration in the past and a cleared value.
	 */
	public function withExpireNow() : self {
		$that             = clone $this;
		$that->expiration = -604800;
		$that->value      = '';

		return $that;
	}

	/**
	 * Return an instance with the specified duration.
	 *
	 * @param int $expiration The number of seconds for which this cookie will be valid. `time() + $expiration`
	 */
	public function withExpiration( int $expiration ) : self {
		$that             = clone $this;
		$that->expiration = $expiration;

		return $that;
	}

	/**
	 * Return an instance with the specified path.
	 */
	public function withPath( string $path ) : self {
		$that       = clone $this;
		$that->path = $path;

		return $that;
	}

	/**
	 * Return an instance with the specified domain.
	 */
	public function withDomain( string $domain ) : self {
		$that         = clone $this;
		$that->domain = $domain;

		return $that;
	}

	/**
	 * Return an instance with the specified secure flag.
	 */
	public function withSecure( bool $secure ) : self {
		$that         = clone $this;
		$that->secure = $secure;

		return $that;
	}

	/**
	 * Return an instance with the specified httpOnly flag.
	 */
	public function withHttpOnly( bool $httpOnly ) : self {
		$that           = clone $this;
		$that->httpOnly = $httpOnly;

		return $that;
	}

	/**
	 * Return an instance with the specified SameSite value.
	 */
	public function withSameSite( string $sameSite ) : self {
		$that           = clone $this;
		$that->sameSite = $sameSite;

		return $that;
	}

	/**
	 * Get the cookie name.
	 */
	public function getName() : string {
		return $this->name;
	}

	/**
	 * Get the cookie value.
	 */
	public function getValue() : string {
		return $this->value;
	}

	/**
	 * Get the cookie expiration.
	 */
	public function getExpiration() : int {
		return $this->expiration;
	}

	/**
	 * Get the cookie path.
	 */
	public function getPath() : string {
		return $this->path;
	}

	/**
	 * Get the cookie domain.
	 */
	public function getDomain() : string {
		return $this->domain;
	}

	/**
	 * Get the cookie secure flag.
	 */
	public function isSecure() : bool {
		return $this->secure;
	}

	/**
	 * Get the cookie httpOnly flag.
	 */
	public function isHttpOnly() : bool {
		return $this->httpOnly;
	}

	/**
	 * Get the cookie SameSite value.
	 */
	public function getSameSite() : string {
		return $this->sameSite;
	}

	/**
	 * Get the cookie as a header value.
	 */
	public function toHeaderValue() : string {
		$headerValue = sprintf('%s=%s', $this->name, urlencode($this->value));

		if( $this->expiration !== 0 ) {
			$headerValue .= sprintf(
				'; expires=%s',
				gmdate(DATE_RFC1123, time() + $this->expiration)
			);
		}

		if( $this->path ) {
			$headerValue .= sprintf('; path=%s', $this->path);
		}

		if( $this->domain ) {
			$headerValue .= sprintf('; domain=%s', $this->domain);
		}

		if( $this->secure ) {
			$headerValue .= '; secure';
		}

		if( $this->httpOnly ) {
			$headerValue .= '; httponly';
		}

		if( $this->sameSite ) {
			$headerValue .= sprintf('; samesite=%s', $this->sameSite);
		}

		return $headerValue;
	}

}
