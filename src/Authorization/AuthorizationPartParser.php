<?php

namespace Corpus\HttpMessageUtils\Authorization;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Utility to split an Authorization header into <type> and <credentials> ala:
 * `Authorization: <type> <credentials>`
 *
 * The parser itself is authorization type agnostic and works with any RFC7235
 * conforming authorization type.
 */
class AuthorizationPartParser {

	private const DEFAULT_HEADER = 'authorization';
	/** @var AuthorizationPartFactory */
	private $factory;

	/**
	 * @param AuthorizationPartFactory|null $factory Optional factory for construction of result objects
	 */
	public function __construct(
		?AuthorizationPartFactory $factory = null
	) {
		if( !$factory ) {
			$factory = new AuthorizationPartFactory;
		}

		$this->factory = $factory;
	}

	/**
	 * Parses an Authorization header into Type and Credentials
	 *
	 * @param string $headerValue The header value to parse
	 * @return AuthorizationPartsInterface|null AuthorizationParts on success, null on failure.
	 * Reasons for failure include empty string and non-RFC7235 compliant header values.
	 */
	public function parseString(
		string $headerValue
	) : ?AuthorizationPartsInterface {
		$headerValue = trim($headerValue);
		if( !$headerValue ) {
			return null;
		}

		$result = preg_split('/\s+/', $headerValue, 2, PREG_SPLIT_NO_EMPTY);
		if( count($result) === 2 ) {
			return $this->factory->make($result[0], $result[1]);
		}

		return null;
	}

	/**
	 * Helper to easily parse from a PSR ServerRequestInterface
	 *
	 * @param ServerRequestInterface $request    The PSR ServerRequestInterface to read from
	 * @param string                 $headerName Optional header name to parse. Defaults to Authorization.
	 * @return AuthorizationPartsInterface|null AuthorizationParts on success, null on failure.
	 * @see self::parseString
	 */
	public function parseServerRequest(
		ServerRequestInterface $request,
		string $headerName = self::DEFAULT_HEADER
	) : ?AuthorizationPartsInterface {
		$header = $request->getHeaderLine($headerName);

		return $this->parseString($header);
	}

}
