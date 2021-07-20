<?php

namespace Corpus\HttpMessageUtils\Authorization;

class AuthorizationParts implements AuthorizationPartsInterface {

	/** @var string */
	private $type;
	/** @var string */
	private $credentials;

	public function __construct( string $type, string $credentials ) {
		$this->type        = $type;
		$this->credentials = $credentials;
	}

	public function getType() : string {
		return $this->type;
	}

	public function getCredentials() : string {
		return $this->credentials;
	}

}
