<?php

namespace Corpus\HttpMessageUtils\Cookie;

interface CookieInterface {

	/**
	 * Get the cookie name.
	 */
	public function getName() : string;

	/**
	 * Get the cookie value.
	 */
	public function getValue() : string;

	/**
	 * Get the cookie expiration.
	 */
	public function getExpiration() : int;

	/**
	 * Get the cookie path.
	 */
	public function getPath() : string;

	/**
	 * Get the cookie domain.
	 */
	public function getDomain() : string;

	/**
	 * Get the cookie SameSite value.
	 */
	public function getSameSite() : string;

	/**
	 * Get the cookie httpOnly flag.
	 */
	public function isHttpOnly() : bool;

	/**
	 * Get the cookie secure flag.
	 */
	public function isSecure() : bool;

}
