<?php

namespace Corpus\HttpMessageUtils\Authorization;

/**
 * Representation of the parts of an Authorization Header:
 *   `Authorization: <type> <credentials>`
 */
interface AuthorizationPartsInterface {

	/**
	 * The specified authorization type
	 */
	public function getType() : string;

	/**
	 * The specified authorization credentials
	 */
	public function getCredentials() : string;

}
