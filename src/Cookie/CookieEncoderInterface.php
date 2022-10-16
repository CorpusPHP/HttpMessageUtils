<?php

namespace Corpus\HttpMessageUtils\Cookie;

interface CookieEncoderInterface {

	/**
	 * Encode the given Cookie to a header string
	 */
	public function __invoke( CookieInterface $cookie ) : string;

}
