<?php

namespace Corpus\HttpMessageUtils\Authorization;

class AuthorizationPartFactory {

	public function make( string $type, string $credentials ) : AuthorizationPartsInterface {
		return new AuthorizationParts($type, $credentials);
	}

}
